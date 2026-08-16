<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\queue;

use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Queue as QueueHelper;
use craft\queue\Queue;
use yii\queue\JobInterface;
use yii\queue\sqs\Queue as SqsQueue;

/**
 * Schedules Craft queue jobs without exceeding a bounded proxy delay.
 *
 * @since 5.38.0
 */
final class PortableQueueScheduler
{
    private const SQS_MAX_DELAY = 900;

    /**
     * Schedule a job after a relative delay.
     *
     * @param list<string> $identityTokens Serialized-payload tokens that identify every row in the schedule
     */
    public static function push(
        JobInterface $job,
        int $delay,
        array $identityTokens,
        string $mutexName,
        int $mutexTimeout = 5,
        ?int $priority = null,
        ?int $ttr = null,
        ?Queue $queue = null,
    ): ?string {
        if ($delay < 0) {
            throw new \InvalidArgumentException('Queue delay must be zero or greater.');
        }

        return self::pushAt(
            job: $job,
            targetTimestamp: DateTimeHelper::currentTimeStamp() + $delay,
            identityTokens: $identityTokens,
            mutexName: $mutexName,
            mutexTimeout: $mutexTimeout,
            priority: $priority,
            ttr: $ttr,
            queue: $queue,
        );
    }

    /**
     * Schedule a job for an absolute Unix timestamp.
     *
     * @param list<string> $identityTokens Serialized-payload tokens that identify every row in the schedule
     */
    public static function pushAt(
        JobInterface $job,
        int $targetTimestamp,
        array $identityTokens,
        string $mutexName,
        int $mutexTimeout = 5,
        ?int $priority = null,
        ?int $ttr = null,
        ?Queue $queue = null,
    ): ?string {
        self::validateIdentity($identityTokens, $mutexName, $mutexTimeout);

        $queue ??= Craft::$app->getQueue();
        if (!$queue instanceof Queue) {
            throw new \RuntimeException('Portable queue scheduling requires Craft\'s database queue.');
        }

        return self::pushAtInternal(
            job: $job,
            targetTimestamp: $targetTimestamp,
            identityTokens: $identityTokens,
            mutexName: $mutexName,
            mutexTimeout: $mutexTimeout,
            priority: $priority,
            ttr: $ttr,
            queue: $queue,
            chainId: bin2hex(random_bytes(16)),
        );
    }

    /**
     * Continue a serialized deferred handoff.
     *
     * @internal Queue runtime entry point used by {@see DeferredQueueJob}.
     */
    public static function continue(DeferredQueueJob $handoff, Queue $queue): void
    {
        $mutex = Craft::$app->getMutex();
        if (!$mutex->acquire($handoff->mutexName, $handoff->mutexTimeout)) {
            throw new \RuntimeException('Unable to acquire the portable queue schedule lock.');
        }

        try {
            $currentJobId = $queue->getJobId();
            if (!self::currentHandoffExists($currentJobId, $handoff->chainId)) {
                return;
            }

            if (self::pendingIdentityExists($handoff->identityTokens)) {
                return;
            }

            self::pushAtInternal(
                job: $handoff->job,
                targetTimestamp: $handoff->targetTimestamp,
                identityTokens: $handoff->identityTokens,
                mutexName: $handoff->mutexName,
                mutexTimeout: $handoff->mutexTimeout,
                priority: $handoff->priority,
                ttr: $handoff->ttr,
                queue: $queue,
                chainId: $handoff->chainId,
            );
        } finally {
            $mutex->release($handoff->mutexName);
        }
    }

    /**
     * @param list<string> $identityTokens
     */
    private static function pushAtInternal(
        JobInterface $job,
        int $targetTimestamp,
        array $identityTokens,
        string $mutexName,
        int $mutexTimeout,
        ?int $priority,
        ?int $ttr,
        Queue $queue,
        string $chainId,
    ): ?string {
        $remainingDelay = max(0, $targetTimestamp - DateTimeHelper::currentTimeStamp());
        $delayLimit = self::delayLimit($queue);

        if ($delayLimit === null || $remainingDelay <= $delayLimit) {
            return QueueHelper::push($job, $priority, $remainingDelay, $ttr, $queue);
        }

        $handoff = new DeferredQueueJob([
            'job' => $job,
            'targetTimestamp' => $targetTimestamp,
            'identityTokens' => $identityTokens,
            'mutexName' => $mutexName,
            'mutexTimeout' => $mutexTimeout,
            'priority' => $priority,
            'ttr' => $ttr,
            'chainId' => $chainId,
        ]);

        return QueueHelper::push($handoff, $priority, $delayLimit, $ttr, $queue);
    }

    private static function delayLimit(Queue $queue): ?int
    {
        return $queue->proxyQueue instanceof SqsQueue ? self::SQS_MAX_DELAY : null;
    }

    private static function currentHandoffExists(string $jobId, string $chainId): bool
    {
        return (new Query())
            ->from('{{%queue}}')
            ->where(['id' => $jobId])
            ->andWhere(['like', 'job', self::jobClassToken(DeferredQueueJob::class)])
            ->andWhere(['like', 'job', $chainId])
            ->exists();
    }

    /**
     * @param list<string> $identityTokens
     */
    private static function pendingIdentityExists(array $identityTokens): bool
    {
        $query = (new Query())
            ->from('{{%queue}}')
            ->where(['fail' => false, 'timeUpdated' => null]);

        foreach ($identityTokens as $token) {
            $query->andWhere(['like', 'job', $token]);
        }

        return $query->exists();
    }

    /**
     * @param list<string> $identityTokens
     */
    private static function validateIdentity(array $identityTokens, string $mutexName, int $mutexTimeout): void
    {
        if ($identityTokens === []) {
            throw new \InvalidArgumentException('Portable queue identity tokens must not be empty.');
        }

        foreach ($identityTokens as $token) {
            if ($token === '') {
                throw new \InvalidArgumentException('Portable queue identity tokens must not be empty strings.');
            }
        }

        if ($mutexName === '') {
            throw new \InvalidArgumentException('Portable queue mutex name must not be empty.');
        }

        if ($mutexTimeout < 0) {
            throw new \InvalidArgumentException('Portable queue mutex timeout must be zero or greater.');
        }
    }

    /**
     * @param class-string $jobClass
     */
    private static function jobClassToken(string $jobClass): string
    {
        $parts = explode('\\', $jobClass);

        return end($parts) ?: $jobClass;
    }
}
