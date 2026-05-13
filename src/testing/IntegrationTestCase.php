<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\testing;

use Craft;
use craft\db\Query;
use craft\queue\BaseJob;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Abstract base for PHPUnit integration tests that run against a live Craft
 * install.
 *
 * Design constraints, intentional:
 *  - No transactional fixtures. Craft services do enough internal writes that
 *    transactional rollback doesn't compose cleanly. Tests clean up by marker.
 *  - No plugin-specific knowledge. This base knows nothing about any plugin's
 *    tables, services, or external state. Each plugin subclasses and adds its
 *    own helpers / cleanup.
 *  - No fixture factories. Plugins seed real rows through their normal model
 *    APIs and tag them with a recognisable name prefix for cleanup.
 *
 * Provided helpers:
 *  - {@see swapPluginComponent()} — swap a plugin service for a stub, restored
 *    automatically in {@see tearDown()}
 *  - {@see countRows()}, {@see fetchRow()} — generic Query wrappers
 *  - {@see purgeRowsByMarker()} — delete rows whose marker column starts with
 *    a given prefix; the canonical cleanup pattern
 *  - {@see drainQueueJob()} — run a queueable job in a loop until a condition
 *    holds, capped to surface hangs
 *  - {@see cleanupExternalState()} — override hook for non-DB cleanup (Redis
 *    keys, filesystem state, external search backends, etc.)
 *
 * @since 5.25.0
 */
abstract class IntegrationTestCase extends PhpUnitTestCase
{
    /**
     * Snapshot of components swapped during the current test, restored in
     * reverse order in {@see tearDown()}.
     *
     * @var list<array{handle: string, id: string, original: ?object}>
     */
    private array $swappedComponents = [];

    protected function tearDown(): void
    {
        // External-state cleanup runs first, while stubbed components are
        // still installed — a stub that recorded calls during the test may
        // need to be consulted by cleanup logic.
        $this->cleanupExternalState();
        $this->restoreSwappedComponents();
        parent::tearDown();
    }

    /**
     * Override in subclasses to clean up non-DB state (Redis cache keys,
     * filesystem artefacts, external search-backend indices, etc.).
     *
     * Called automatically from {@see tearDown()}. Default is a no-op so
     * subclasses can opt in without ceremony. Subclasses that override do
     * NOT need to call parent::cleanupExternalState() (base is a no-op).
     */
    protected function cleanupExternalState(): void
    {
        // No-op. Override me.
    }

    /**
     * Swap a plugin component (Yii ServiceLocator slot) for a test double.
     *
     * The original component is captured and restored automatically in
     * {@see tearDown()}. Safe to call multiple times for the same slot in
     * one test — restoration walks the snapshot stack in reverse.
     *
     * @throws \RuntimeException if $pluginHandle is not installed/enabled
     */
    protected function swapPluginComponent(string $pluginHandle, string $componentId, object $stub): void
    {
        $plugin = Craft::$app->plugins->getPlugin($pluginHandle);
        if ($plugin === null) {
            throw new \RuntimeException(
                "swapPluginComponent: plugin '$pluginHandle' is not installed or enabled.",
            );
        }

        // Capture the original *before* swapping. We use $plugin->get() rather
        // than reading $plugin->{$componentId} so lazy components are resolved
        // and we restore the same instance the rest of the system was using.
        $original = $plugin->has($componentId) ? $plugin->get($componentId) : null;

        $this->swappedComponents[] = [
            'handle' => $pluginHandle,
            'id' => $componentId,
            'original' => $original,
        ];

        $plugin->set($componentId, $stub);
    }

    /**
     * Restore swapped components in reverse order. Idempotent.
     */
    private function restoreSwappedComponents(): void
    {
        foreach (array_reverse($this->swappedComponents) as $snap) {
            $plugin = Craft::$app->plugins->getPlugin($snap['handle']);
            if ($plugin === null) {
                continue;
            }
            if ($snap['original'] === null) {
                $plugin->clear($snap['id']);
                continue;
            }
            $plugin->set($snap['id'], $snap['original']);
        }
        $this->swappedComponents = [];
    }

    /**
     * Generic row count. Pass the table reference in Yii's `{{%table}}` form
     * so the table prefix resolves automatically.
     *
     * @param array<string, mixed>|array<int, mixed> $where
     */
    protected function countRows(string $table, array $where = []): int
    {
        $query = (new Query())->from($table);
        if (!empty($where)) {
            $query->where($where);
        }

        return (int) $query->count();
    }

    /**
     * Fetch a single row matching $where, or null. Caller is responsible for
     * narrowing $where enough that "first row" is meaningful.
     *
     * @param array<string, mixed>|array<int, mixed> $where
     * @return array<string, mixed>|null
     */
    protected function fetchRow(string $table, array $where): ?array
    {
        $row = (new Query())->from($table)->where($where)->one();

        return $row !== false ? $row : null;
    }

    /**
     * Delete rows in $table whose $column starts with $prefix.
     *
     * Canonical cleanup pattern for tests that seed real rows: tag every
     * test-created row with a recognisable prefix (e.g. `__myplugin_test_`)
     * and purge by that prefix in setUp / tearDown. CP-created rows that
     * don't share the prefix are never touched.
     *
     * Wildcard handling: the third element `false` disables Yii's special-
     * character escaping so the literal `%` we append acts as the SQL LIKE
     * wildcard. The third argument is the prefix itself, untouched, so
     * any `%` or `_` characters that happen to appear in $prefix would
     * match literally — pick a marker prefix that doesn't contain them.
     */
    protected function purgeRowsByMarker(string $table, string $column, string $prefix): void
    {
        Craft::$app->getDb()
            ->createCommand()
            ->delete($table, ['like', $column, $prefix . '%', false])
            ->execute();
    }

    /**
     * Run a queueable job in a loop until $isDone returns true. Generalises
     * the BatchSyncJob "drain until empty" pattern — works for any job whose
     * single execution makes incremental progress against a queue / buffer.
     *
     * Capped at $maxIterations to surface infinite loops as a test failure
     * rather than a hang.
     *
     * @param callable(): bool $isDone Predicate evaluated before each iteration.
     */
    protected function drainQueueJob(BaseJob $job, callable $isDone, int $maxIterations = 50): void
    {
        $iterations = 0;
        while (!$isDone()) {
            $job->execute(Craft::$app->queue);
            $iterations++;
            if ($iterations > $maxIterations) {
                self::fail(
                    "drainQueueJob exceeded {$maxIterations} iterations without reaching the done condition.",
                );
            }
        }
    }
}
