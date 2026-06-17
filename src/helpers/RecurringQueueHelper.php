<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\db\Query;
use craft\queue\BaseJob;
use yii\db\Expression;

/**
 * Deployment-safe helpers for owning recurring Craft queue rows.
 *
 * @since 5.27.0
 */
class RecurringQueueHelper
{
    /**
     * Ensure exactly one pending queue row exists for a recurring job identity.
     *
     * The check/push path is protected by Craft's DB-backed mutex so concurrent
     * deploy bootstrap requests cannot all pass the same empty queue check.
     *
     * @param string $pluginToken Stable token found in the serialized job payload, e.g. `searchmanager`
     * @param class-string<BaseJob> $jobClass Recurring job class
     * @param int $delay Queue delay in seconds
     * @param callable(): BaseJob $jobFactory Factory called only when no pending row exists
     * @param string[] $extraLikeTokens Additional serialized-payload tokens that identify the recurring row
     * @param string|null $mutexName Optional explicit mutex name
     * @param int $mutexTimeout Seconds to wait for the scheduler lock
     * @return RecurringQueueResult Queue ownership result
     */
    public static function ensurePending(
        string $pluginToken,
        string $jobClass,
        int $delay,
        callable $jobFactory,
        array $extraLikeTokens = [],
        ?string $mutexName = null,
        int $mutexTimeout = 5,
    ): RecurringQueueResult {
        if ($delay <= 0) {
            return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
        }

        $lockName = $mutexName ?? self::mutexName($pluginToken, $jobClass, $extraLikeTokens);
        $mutex = Craft::$app->getMutex();

        if (!$mutex->acquire($lockName, $mutexTimeout)) {
            return new RecurringQueueResult(RecurringQueueResult::STATUS_LOCK_MISSED);
        }

        try {
            $rows = self::pendingRows($pluginToken, $jobClass, $extraLikeTokens);

            if ($rows !== []) {
                $keptId = (string) $rows[0]['id'];
                $duplicatesDeleted = self::deleteRowsAfterFirst($rows);

                return new RecurringQueueResult(
                    RecurringQueueResult::STATUS_EXISTING,
                    $keptId,
                    $duplicatesDeleted,
                );
            }

            $job = $jobFactory();
            if (!$job instanceof BaseJob) {
                throw new \InvalidArgumentException('Recurring queue job factory must return a Craft BaseJob instance.');
            }

            return new RecurringQueueResult(
                RecurringQueueResult::STATUS_CREATED,
                Craft::$app->getQueue()->delay($delay)->push($job),
            );
        } finally {
            $mutex->release($lockName);
        }
    }

    /**
     * Delete pending queue rows for a recurring job identity.
     *
     * @param string $pluginToken Stable token found in the serialized job payload
     * @param class-string<BaseJob> $jobClass Recurring job class
     * @param string[] $extraLikeTokens Additional serialized-payload tokens that identify the recurring row
     * @return int Number of deleted rows
     */
    public static function deletePending(string $pluginToken, string $jobClass, array $extraLikeTokens = []): int
    {
        $rows = self::pendingRows($pluginToken, $jobClass, $extraLikeTokens);
        if ($rows === []) {
            return 0;
        }

        return self::deleteRows($rows);
    }

    /**
     * Check whether a pending queue row exists for a recurring job identity.
     *
     * @param string $pluginToken Stable token found in the serialized job payload
     * @param class-string<BaseJob> $jobClass Recurring job class
     * @param string[] $extraLikeTokens Additional serialized-payload tokens that identify the recurring row
     */
    public static function hasPending(string $pluginToken, string $jobClass, array $extraLikeTokens = []): bool
    {
        return self::pendingQuery($pluginToken, $jobClass, $extraLikeTokens)->exists();
    }

    /**
     * Fetch pending queue rows for a recurring job identity.
     *
     * @param class-string<BaseJob> $jobClass
     * @param string[] $extraLikeTokens
     * @return list<array{id: int|string}>
     */
    private static function pendingRows(string $pluginToken, string $jobClass, array $extraLikeTokens): array
    {
        /** @var list<array{id: int|string}> $rows */
        $rows = self::pendingQuery($pluginToken, $jobClass, $extraLikeTokens)
            ->select(['id'])
            ->orderBy(new Expression('[[timePushed]] + [[delay]] ASC'))
            ->addOrderBy(['priority' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return $rows;
    }

    /**
     * Build the pending-row query for a recurring job identity.
     *
     * @param class-string<BaseJob> $jobClass
     * @param string[] $extraLikeTokens
     */
    private static function pendingQuery(string $pluginToken, string $jobClass, array $extraLikeTokens): Query
    {
        $query = (new Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', $pluginToken])
            ->andWhere(['like', 'job', self::jobClassToken($jobClass)])
            ->andWhere(['fail' => false])
            ->andWhere(['timeUpdated' => null]);

        foreach ($extraLikeTokens as $token) {
            $query->andWhere(['like', 'job', $token]);
        }

        return $query;
    }

    /**
     * Delete all rows except the first row from an ordered pending-row list.
     *
     * @param list<array{id: int|string}> $rows
     */
    private static function deleteRowsAfterFirst(array $rows): int
    {
        if (count($rows) <= 1) {
            return 0;
        }

        return self::deleteRows(array_slice($rows, 1));
    }

    /**
     * Delete the provided queue rows by ID.
     *
     * @param list<array{id: int|string}> $rows
     */
    private static function deleteRows(array $rows): int
    {
        $ids = array_map(static fn(array $row): string => (string) $row['id'], $rows);

        return Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}', ['id' => $ids])
            ->execute();
    }

    /**
     * Build a stable lock name for a recurring job identity.
     *
     * @param class-string<BaseJob> $jobClass
     * @param string[] $extraLikeTokens
     */
    private static function mutexName(string $pluginToken, string $jobClass, array $extraLikeTokens): string
    {
        return sprintf(
            'lindemannrock-base:recurring-queue:%s:%s:%s',
            $pluginToken,
            self::jobClassToken($jobClass),
            hash('sha256', implode("\n", $extraLikeTokens)),
        );
    }

    /**
     * Get the serialized-payload class token used in queue LIKE predicates.
     *
     * @param class-string<BaseJob> $jobClass
     */
    private static function jobClassToken(string $jobClass): string
    {
        $parts = explode('\\', $jobClass);

        return end($parts) ?: $jobClass;
    }
}
