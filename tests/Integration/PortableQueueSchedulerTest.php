<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\queue\BaseJob;
use craft\queue\Queue;
use lindemannrock\base\helpers\RecurringQueueHelper;
use lindemannrock\base\helpers\RecurringQueueResult;
use lindemannrock\base\queue\DeferredQueueJob;
use lindemannrock\base\queue\PortableQueueScheduler;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\queue\sqs\Queue as SqsQueue;

/**
 * Pins portable absolute-time queue scheduling and handoff ownership.
 *
 * @since 5.38.0
 */
final class PortableQueueSchedulerTest extends IntegrationTestCase
{
    public const OWNER_TOKEN = 'portable-queue-test';
    private const MUTEX_NAME = 'lindemannrock-base:test:portable-queue';
    private const START_TIMESTAMP = 1_800_000_000;

    private ?Queue $originalQueue = null;
    private ?RecordingSqsQueue $proxyQueue = null;
    private bool $timePaused = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteOwnedQueueRows();
        PortableQueueTestJob::$executions = 0;
    }

    protected function cleanupExternalState(): void
    {
        $this->deleteOwnedQueueRows();
        PortableQueueTestJob::$executions = 0;

        if ($this->timePaused) {
            DateTimeHelper::resume();
            $this->timePaused = false;
        }

        if ($this->originalQueue !== null) {
            Craft::$app->set('queue', $this->originalQueue);
            $this->originalQueue = null;
        }

        $this->proxyQueue = null;
        parent::cleanupExternalState();
    }

    #[DataProvider('shortDelayProvider')]
    public function testBoundedQueuePushesShortDelaysDirectly(int $delay): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);

        PortableQueueScheduler::push(
            job: new PortableQueueTestJob(['payload' => "short-$delay"]),
            delay: $delay,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            queue: $queue,
        );

        $row = $this->fetchOnlyOwnedRow();
        self::assertInstanceOf(PortableQueueTestJob::class, $this->unserializeJob($row));
        self::assertSame($delay, (int) $row['delay']);
        self::assertSame([$delay], $this->proxyDelays());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function shortDelayProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield '899' => [899];
        yield '900' => [900];
    }

    #[DataProvider('firstHandoffProvider')]
    public function testBoundedQueueUsesMaximumFirstHandoffDelay(int $delay): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);

        PortableQueueScheduler::push(
            job: new PortableQueueTestJob(['payload' => "long-$delay"]),
            delay: $delay,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            queue: $queue,
        );

        $handoff = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $handoff);
        self::assertSame(self::START_TIMESTAMP + $delay, $handoff->targetTimestamp);
        self::assertSame([900], $this->proxyDelays());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function firstHandoffProvider(): iterable
    {
        yield '901 seconds' => [901];
        yield '1,278 seconds' => [1_278];
        yield 'six hours' => [21_600];
        yield 'daily' => [86_400];
        yield 'weekly' => [604_800];
    }

    public function testAbsoluteTargetSurvivesMultipleHopsWithoutEarlyConsumerDispatch(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $target = self::START_TIMESTAMP + 21_600;

        $firstId = PortableQueueScheduler::pushAt(
            job: new PortableQueueTestJob(['payload' => 'absolute']),
            targetTimestamp: $target,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            queue: $queue,
        );

        self::assertNotNull($firstId);
        $this->pauseAt(self::START_TIMESTAMP + 900);
        self::assertTrue($queue->executeJob($firstId));
        $second = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $second);
        self::assertSame($target, $second->targetTimestamp);
        self::assertSame(0, PortableQueueTestJob::$executions);

        $secondId = (string) $this->fetchOnlyOwnedRow()['id'];
        $this->pauseAt(self::START_TIMESTAMP + 1_800);
        self::assertTrue($queue->executeJob($secondId));
        $third = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $third);
        self::assertSame($target, $third->targetTimestamp);
        self::assertSame(0, PortableQueueTestJob::$executions);

        $thirdId = (string) $this->fetchOnlyOwnedRow()['id'];
        $this->pauseAt($target - 300);
        self::assertTrue($queue->executeJob($thirdId));
        $consumerRow = $this->fetchOnlyOwnedRow();
        self::assertInstanceOf(PortableQueueTestJob::class, $this->unserializeJob($consumerRow));
        self::assertSame(300, (int) $consumerRow['delay']);
        self::assertSame(0, PortableQueueTestJob::$executions);
        self::assertSame([900, 900, 900, 300], $this->proxyDelays());
        self::assertLessThanOrEqual(900, max($this->proxyDelays()));
    }

    public function testLateHandoffDispatchesConsumerImmediately(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $jobId = PortableQueueScheduler::push(
            job: new PortableQueueTestJob(['payload' => 'late']),
            delay: 1_278,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            queue: $queue,
        );

        self::assertNotNull($jobId);
        $this->pauseAt(self::START_TIMESTAMP + 1_300);
        self::assertTrue($queue->executeJob($jobId));

        $consumerRow = $this->fetchOnlyOwnedRow();
        self::assertInstanceOf(PortableQueueTestJob::class, $this->unserializeJob($consumerRow));
        self::assertSame(0, (int) $consumerRow['delay']);
        self::assertSame([900, 0], $this->proxyDelays());
    }

    public function testUnboundedCraftQueueRetainsTheOriginalLongDelay(): void
    {
        $queue = $this->installTestQueue(false);
        $this->pauseAt(self::START_TIMESTAMP);

        PortableQueueScheduler::push(
            job: new PortableQueueTestJob(['payload' => 'local']),
            delay: 604_800,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            queue: $queue,
        );

        $row = $this->fetchOnlyOwnedRow();
        self::assertInstanceOf(PortableQueueTestJob::class, $this->unserializeJob($row));
        self::assertSame(604_800, (int) $row['delay']);
        self::assertSame([], $this->proxyDelays());
    }

    public function testConsumerSerializationPriorityAndTtrReachTheFinalRow(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $jobId = PortableQueueScheduler::push(
            job: new PortableQueueTestJob(['payload' => 'serialized-payload']),
            delay: 1_278,
            identityTokens: $this->identityTokens(),
            mutexName: self::MUTEX_NAME,
            priority: 42,
            ttr: 777,
            queue: $queue,
        );

        self::assertNotNull($jobId);
        $handoffRow = $this->fetchOnlyOwnedRow();
        self::assertSame(42, (int) $handoffRow['priority']);
        self::assertSame(777, (int) $handoffRow['ttr']);

        $this->pauseAt(self::START_TIMESTAMP + 900);
        self::assertTrue($queue->executeJob($jobId));
        $consumerRow = $this->fetchOnlyOwnedRow();
        $consumer = $this->unserializeJob($consumerRow);
        self::assertInstanceOf(PortableQueueTestJob::class, $consumer);
        self::assertSame('serialized-payload', $consumer->payload);
        self::assertSame(42, (int) $consumerRow['priority']);
        self::assertSame(777, (int) $consumerRow['ttr']);
    }

    public function testRecurringHelperDeduplicatesPortableLongDelayRows(): void
    {
        $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);

        $first = $this->ensureRecurring(1_278, 'first');
        $second = $this->ensureRecurring(1_278, 'second');

        self::assertSame(RecurringQueueResult::STATUS_CREATED, $first->status);
        self::assertSame(RecurringQueueResult::STATUS_EXISTING, $second->status);
        self::assertSame($first->jobId, $second->jobId);
        self::assertSame(1, $this->countOwnedQueueRows());
        self::assertSame([900], $this->proxyDelays());
    }

    public function testCancellationRemovesDatabaseRowAfterProxyMessageWasSent(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $result = $this->ensureRecurring(1_278, 'cancel-proxy');

        self::assertNotNull($result->jobId);
        self::assertSame([900], $this->proxyDelays());
        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        self::assertSame(0, $this->countOwnedQueueRows());

        $this->pauseAt(self::START_TIMESTAMP + 900);
        self::assertFalse($queue->executeJob($result->jobId));
        self::assertSame(0, $this->countOwnedQueueRows());
        self::assertSame([900], $this->proxyDelays());
    }

    public function testCancellationOfReservedHandoffPreventsItsSuccessor(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $result = $this->ensureRecurring(21_600, 'cancel-reserved');
        self::assertNotNull($result->jobId);

        $handoff = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $handoff);
        $this->markExecuting($queue, $result->jobId);

        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        $handoff->execute($queue);

        self::assertSame(0, $this->countOwnedQueueRows());
        self::assertSame([900], $this->proxyDelays());
        $this->markExecuting($queue, null);
    }

    public function testCancellationRemovesFinalConsumerHandoff(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $result = $this->ensureRecurring(901, 'cancel-final');
        self::assertNotNull($result->jobId);

        $this->pauseAt(self::START_TIMESTAMP + 900);
        self::assertTrue($queue->executeJob($result->jobId));
        self::assertInstanceOf(PortableQueueTestJob::class, $this->unserializeJob($this->fetchOnlyOwnedRow()));

        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        self::assertSame(0, $this->countOwnedQueueRows());
        self::assertSame([900, 1], $this->proxyDelays());
    }

    public function testCancellationRemovesFailedHandoffSoManualRetryCannotRestoreIt(): void
    {
        $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $this->ensureRecurring(1_278, 'failed-handoff');
        Craft::$app->getDb()->createCommand()
            ->update('{{%queue}}', ['fail' => true], $this->ownedQueueWhere())
            ->execute();

        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        self::assertSame(0, $this->countOwnedQueueRows());
    }

    public function testScheduleReplacementLeavesOnlyTheNewAbsoluteTarget(): void
    {
        $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $this->ensureRecurring(1_278, 'old');

        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        $replacement = $this->ensureRecurring(21_600, 'new');
        self::assertSame(RecurringQueueResult::STATUS_CREATED, $replacement->status);
        self::assertSame(1, $this->countOwnedQueueRows());

        $handoff = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $handoff);
        self::assertSame(self::START_TIMESTAMP + 21_600, $handoff->targetTimestamp);
        self::assertInstanceOf(PortableQueueTestJob::class, $handoff->job);
        self::assertSame('new', $handoff->job->payload);
    }

    public function testReplayDoesNotCreateAnAvoidableDuplicateSuccessor(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $result = $this->ensureRecurring(21_600, 'replay');
        self::assertNotNull($result->jobId);

        $handoff = $this->unserializeJob($this->fetchOnlyOwnedRow());
        self::assertInstanceOf(DeferredQueueJob::class, $handoff);
        $this->markExecuting($queue, $result->jobId);
        $this->pauseAt(self::START_TIMESTAMP + 900);

        $handoff->execute($queue);
        $handoff->execute($queue);

        self::assertSame(2, $this->countOwnedQueueRows());
        self::assertSame([900, 900], $this->proxyDelays());
        $this->markExecuting($queue, null);
    }

    public function testProxyFailurePropagatesAndLeavesInspectableDatabaseRow(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        self::assertNotNull($this->proxyQueue);
        $this->proxyQueue->failPushes = true;

        try {
            PortableQueueScheduler::push(
                job: new PortableQueueTestJob(['payload' => 'failure']),
                delay: 1_278,
                identityTokens: $this->identityTokens(),
                mutexName: self::MUTEX_NAME,
                queue: $queue,
            );
            self::fail('Expected the proxy push failure to propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Portable queue test proxy failure.', $exception->getMessage());
        }

        self::assertSame(1, $this->countOwnedQueueRows());
        self::assertInstanceOf(DeferredQueueJob::class, $this->unserializeJob($this->fetchOnlyOwnedRow()));
    }

    public function testCancellationPreservesUnrelatedQueueRows(): void
    {
        $queue = $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $this->ensureRecurring(1_278, 'owned');
        $queue->delay(100)->push(new PortableQueueUnrelatedJob());

        self::assertSame(1, RecurringQueueHelper::deletePending(self::OWNER_TOKEN, PortableQueueTestJob::class));
        self::assertSame(0, $this->countOwnedQueueRows());
        self::assertSame(1, (int) (new Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', PortableQueueUnrelatedJob::OWNER_TOKEN])
            ->count());
    }

    public function testCancellationLockFailureIsObservableAndPreservesTheSchedule(): void
    {
        $this->installTestQueue(true);
        $this->pauseAt(self::START_TIMESTAMP);
        $this->ensureRecurring(1_278, 'lock-contention');
        $mutex = Craft::$app->getMutex();
        self::assertTrue($mutex->acquire(self::MUTEX_NAME, 0));

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to acquire the recurring queue cancellation lock.');
            RecurringQueueHelper::deletePending(
                pluginToken: self::OWNER_TOKEN,
                jobClass: PortableQueueTestJob::class,
                mutexName: self::MUTEX_NAME,
                mutexTimeout: 0,
            );
        } finally {
            self::assertSame(1, $this->countOwnedQueueRows());
            $mutex->release(self::MUTEX_NAME);
        }
    }

    public function testRuntimeHasNoCraftCloudDependency(): void
    {
        $packageRoot = dirname(__DIR__, 2);
        $runtime = file_get_contents($packageRoot . '/src/queue/PortableQueueScheduler.php')
            . file_get_contents($packageRoot . '/src/queue/DeferredQueueJob.php');
        $composer = file_get_contents($packageRoot . '/composer.json');

        self::assertIsString($runtime);
        self::assertIsString($composer);
        self::assertStringNotContainsString('craft\\cloud', $runtime);
        self::assertStringNotContainsString('craftcms/cloud', $composer);
    }

    private function installTestQueue(bool $bounded): Queue
    {
        if ($this->originalQueue === null) {
            $original = Craft::$app->getQueue();
            self::assertInstanceOf(Queue::class, $original);
            $this->originalQueue = $original;
        }

        $this->proxyQueue = $bounded ? new RecordingSqsQueue() : null;
        $queue = new Queue([
            'db' => Craft::$app->getDb(),
            'mutex' => Craft::$app->getMutex(),
            'proxyQueue' => $this->proxyQueue,
        ]);
        Craft::$app->set('queue', $queue);

        $installedQueue = Craft::$app->getQueue();
        self::assertInstanceOf(Queue::class, $installedQueue);

        return $installedQueue;
    }

    private function pauseAt(int $timestamp): void
    {
        if ($this->timePaused) {
            DateTimeHelper::resume();
        }

        DateTimeHelper::pause(new \DateTime("@$timestamp"));
        $this->timePaused = true;
    }

    /**
     * @return non-empty-list<string>
     */
    private function identityTokens(): array
    {
        return [self::OWNER_TOKEN, 'PortableQueueTestJob'];
    }

    private function ensureRecurring(int $delay, string $payload): RecurringQueueResult
    {
        return RecurringQueueHelper::ensurePending(
            pluginToken: self::OWNER_TOKEN,
            jobClass: PortableQueueTestJob::class,
            delay: $delay,
            jobFactory: fn() => new PortableQueueTestJob(['payload' => $payload]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchOnlyOwnedRow(): array
    {
        $rows = $this->ownedQueueQuery()->orderBy(['id' => SORT_ASC])->all();
        self::assertCount(1, $rows);

        return $rows[0];
    }

    private function countOwnedQueueRows(): int
    {
        return (int) $this->ownedQueueQuery()->count();
    }

    private function ownedQueueQuery(): Query
    {
        return (new Query())->from('{{%queue}}')->where($this->ownedQueueWhere());
    }

    /**
     * @return array<int, mixed>
     */
    private function ownedQueueWhere(): array
    {
        return ['like', 'job', self::OWNER_TOKEN];
    }

    private function deleteOwnedQueueRows(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}', [
                'or',
                $this->ownedQueueWhere(),
                ['like', 'job', PortableQueueUnrelatedJob::OWNER_TOKEN],
            ])
            ->execute();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function unserializeJob(array $row): object
    {
        $queue = Craft::$app->getQueue();
        self::assertInstanceOf(Queue::class, $queue);
        $job = $queue->serializer->unserialize((string) $row['job']);
        self::assertIsObject($job);

        return $job;
    }

    /**
     * @return list<int>
     */
    private function proxyDelays(): array
    {
        if ($this->proxyQueue === null) {
            return [];
        }

        return array_column($this->proxyQueue->pushes, 'delay');
    }

    private function markExecuting(Queue $queue, ?string $jobId): void
    {
        if ($jobId !== null) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%queue}}', ['timeUpdated' => DateTimeHelper::currentTimeStamp()], ['id' => $jobId])
                ->execute();
        }

        $property = new \ReflectionProperty(Queue::class, '_executingJobId');
        $property->setValue($queue, $jobId);
    }
}

/**
 * Records proxy pushes without contacting AWS.
 */
final class RecordingSqsQueue extends SqsQueue
{
    /**
     * @var list<array{delay: int, priority: mixed, ttr: int}>
     */
    public array $pushes = [];

    public bool $failPushes = false;

    protected function pushMessage($message, $ttr, $delay, $priority): string
    {
        if ($this->failPushes) {
            throw new \RuntimeException('Portable queue test proxy failure.');
        }

        $this->pushes[] = [
            'delay' => (int) $delay,
            'priority' => $priority,
            'ttr' => (int) $ttr,
        ];

        return 'portable-proxy-' . count($this->pushes);
    }
}

/**
 * Serializable consumer fixture.
 */
final class PortableQueueTestJob extends BaseJob
{
    public static int $executions = 0;

    public string $ownerToken = PortableQueueSchedulerTest::OWNER_TOKEN;

    public string $payload = '';

    public function execute($queue): void
    {
        self::$executions++;
    }

    protected function defaultDescription(): ?string
    {
        return 'Portable queue consumer test job';
    }
}

/**
 * Unrelated queue fixture used to pin ownership boundaries.
 */
final class PortableQueueUnrelatedJob extends BaseJob
{
    public const OWNER_TOKEN = 'portable-queue-unrelated-test';

    public string $ownerToken = self::OWNER_TOKEN;

    public function execute($queue): void
    {
    }
}
