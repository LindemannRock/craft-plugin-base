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
use craft\queue\BaseJob;
use lindemannrock\base\helpers\RecurringQueueHelper;
use lindemannrock\base\helpers\RecurringQueueResult;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins deployment-safe recurring queue ownership.
 *
 * @since 5.27.0
 */
final class RecurringQueueHelperTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteTestQueueRows();
    }

    protected function cleanupExternalState(): void
    {
        $this->deleteTestQueueRows();
        parent::cleanupExternalState();
    }

    public function testEnsurePendingKeepsOnePendingRowForRepeatedBootstrapCalls(): void
    {
        $firstResult = RecurringQueueHelper::ensurePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
            delay: 300,
            jobFactory: fn() => new RecurringQueueTestJob(),
        );

        self::assertSame(RecurringQueueResult::STATUS_CREATED, $firstResult->status);
        self::assertTrue($firstResult->wasCreated());
        self::assertNotNull($firstResult->jobId);
        self::assertSame(0, $firstResult->duplicatesDeleted);
        self::assertSame(1, $this->countTestQueueRows());

        $secondResult = RecurringQueueHelper::ensurePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
            delay: 300,
            jobFactory: fn() => new RecurringQueueTestJob(),
        );

        self::assertSame(RecurringQueueResult::STATUS_EXISTING, $secondResult->status);
        self::assertFalse($secondResult->wasCreated());
        self::assertTrue($secondResult->hasPending());
        self::assertSame($firstResult->jobId, $secondResult->jobId);
        self::assertSame(0, $secondResult->duplicatesDeleted);
        self::assertSame(1, $this->countTestQueueRows());
    }

    public function testEnsurePendingCollapsesDuplicatePendingRowsFromLegacyBootstrapRace(): void
    {
        Craft::$app->getQueue()->delay(300)->push(new RecurringQueueTestJob());
        Craft::$app->getQueue()->delay(300)->push(new RecurringQueueTestJob());
        self::assertSame(2, $this->countTestQueueRows());

        $result = RecurringQueueHelper::ensurePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
            delay: 300,
            jobFactory: fn() => new RecurringQueueTestJob(),
        );

        self::assertSame(RecurringQueueResult::STATUS_EXISTING, $result->status);
        self::assertNotNull($result->jobId);
        self::assertSame(1, $result->duplicatesDeleted);
        self::assertSame(1, $this->countTestQueueRows());
        self::assertSame((string) $result->jobId, (string) $this->fetchOnlyTestQueueId());
    }

    public function testEnsurePendingIgnoresFailedRowsAndQueuesFreshPendingRow(): void
    {
        Craft::$app->getQueue()->delay(300)->push(new RecurringQueueTestJob());
        Craft::$app->getDb()->createCommand()
            ->update('{{%queue}}', ['fail' => true], $this->testQueueWhere())
            ->execute();

        self::assertSame(1, $this->countTestQueueRows());
        self::assertSame(0, $this->countPendingTestQueueRows());

        $result = RecurringQueueHelper::ensurePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
            delay: 300,
            jobFactory: fn() => new RecurringQueueTestJob(),
        );

        self::assertSame(RecurringQueueResult::STATUS_CREATED, $result->status);
        self::assertNotNull($result->jobId);
        self::assertSame(0, $result->duplicatesDeleted);
        self::assertSame(2, $this->countTestQueueRows());
        self::assertSame(1, $this->countPendingTestQueueRows());
    }

    public function testEnsurePendingSkipsNonPositiveDelay(): void
    {
        $result = RecurringQueueHelper::ensurePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
            delay: 0,
            jobFactory: fn() => new RecurringQueueTestJob(),
        );

        self::assertSame(RecurringQueueResult::STATUS_SKIPPED, $result->status);
        self::assertTrue($result->wasSkipped());
        self::assertFalse($result->hasPending());
        self::assertNull($result->jobId);
        self::assertSame(0, $this->countTestQueueRows());
    }

    public function testDeletePendingRemovesOnlyPendingRows(): void
    {
        Craft::$app->getQueue()->delay(300)->push(new RecurringQueueTestJob());
        Craft::$app->getQueue()->delay(300)->push(new RecurringQueueTestJob());

        Craft::$app->getDb()->createCommand()
            ->update('{{%queue}}', ['fail' => true], [
                'id' => $this->fetchOnlyTestQueueIds()[0],
            ])
            ->execute();

        self::assertSame(2, $this->countTestQueueRows());
        self::assertSame(1, $this->countPendingTestQueueRows());

        $deleted = RecurringQueueHelper::deletePending(
            pluginToken: 'lindemannrockbase',
            jobClass: RecurringQueueTestJob::class,
        );

        self::assertSame(1, $deleted);
        self::assertSame(1, $this->countTestQueueRows());
        self::assertSame(0, $this->countPendingTestQueueRows());
    }

    private function countTestQueueRows(): int
    {
        return (int) $this->testQueueQuery()->count();
    }

    private function countPendingTestQueueRows(): int
    {
        return (int) $this->testQueueQuery()
            ->andWhere(['fail' => false])
            ->andWhere(['timeUpdated' => null])
            ->count();
    }

    private function fetchOnlyTestQueueId(): int
    {
        $ids = $this->fetchOnlyTestQueueIds();
        self::assertCount(1, $ids);

        return $ids[0];
    }

    /**
     * @return list<int>
     */
    private function fetchOnlyTestQueueIds(): array
    {
        return array_map(
            static fn($id): int => (int) $id,
            $this->testQueueQuery()
                ->select('id')
                ->column()
        );
    }

    private function deleteTestQueueRows(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}', $this->testQueueWhere())
            ->execute();
    }

    private function testQueueQuery(): Query
    {
        return (new Query())
            ->from('{{%queue}}')
            ->where($this->testQueueWhere());
    }

    /**
     * @return array<int, mixed>
     */
    private function testQueueWhere(): array
    {
        return [
            'and',
            ['like', 'job', 'lindemannrockbase'],
            ['like', 'job', 'RecurringQueueTestJob'],
        ];
    }
}

/**
 * Test-only recurring job fixture.
 */
final class RecurringQueueTestJob extends BaseJob
{
    public string $pluginToken = 'lindemannrockbase';

    public function execute($queue): void
    {
    }

    protected function defaultDescription(): ?string
    {
        return 'Base recurring queue test job';
    }
}
