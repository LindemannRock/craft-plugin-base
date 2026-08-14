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
use lindemannrock\base\cache\ScopedCache;
use lindemannrock\base\testing\IntegrationTestCase;
use yii\caching\ArrayCache;

/**
 * @since 5.38.0
 */
final class ScopedCacheTest extends IntegrationTestCase
{
    public function testStringAndAssociativeArrayIdentitiesAreDeterministic(): void
    {
        $cache = new RecordingScopedCache();
        $first = new ScopedCache($cache, 'example-plugin', 'statistics');
        $second = new ScopedCache($cache, 'example-plugin', 'statistics');

        self::assertTrue($first->set('same-item', false, 120));
        $stringResult = $second->get('same-item');
        self::assertTrue($stringResult->isHit());
        self::assertFalse($stringResult->value);

        self::assertTrue($first->set([
            'site' => 1,
            'options' => ['language' => 'en', 'limit' => 20],
        ], 'array-value', 120));
        $arrayResult = $second->get([
            'options' => ['limit' => 20, 'language' => 'en'],
            'site' => 1,
        ]);
        self::assertTrue($arrayResult->isHit());
        self::assertSame('array-value', $arrayResult->value);
    }

    public function testNamespaceSeparatesApplicationsPluginsAndFamilies(): void
    {
        $cache = new RecordingScopedCache();
        $originalApplicationId = Craft::$app->id;
        try {
            Craft::$app->id = 'application-a';
            $pluginA = new ScopedCache($cache, 'plugin-a', 'family-a');
            $pluginB = new ScopedCache($cache, 'plugin-b', 'family-a');
            $familyB = new ScopedCache($cache, 'plugin-a', 'family-b');
            Craft::$app->id = 'application-b';
            $applicationB = new ScopedCache($cache, 'plugin-a', 'family-a');
        } finally {
            Craft::$app->id = $originalApplicationId;
        }

        self::assertTrue($pluginA->set('item', 'plugin-a', 60));
        self::assertTrue($pluginB->set('item', 'plugin-b', 60));
        self::assertTrue($familyB->set('item', 'family-b', 60));
        self::assertTrue($applicationB->set('item', 'application-b', 60));

        self::assertSame('plugin-a', $pluginA->get('item')->value);
        self::assertSame('plugin-b', $pluginB->get('item')->value);
        self::assertSame('family-b', $familyB->get('item')->value);
        self::assertSame('application-b', $applicationB->get('item')->value);

        $itemKeys = $cache->itemSetKeys();
        self::assertCount(4, array_unique($itemKeys));
        self::assertTrue($this->containsKeyFragment($itemKeys, ':app:' . hash('sha256', 'application-a') . ':'));
        self::assertTrue($this->containsKeyFragment($itemKeys, ':app:' . hash('sha256', 'application-b') . ':'));
        self::assertTrue($this->containsKeyFragment($itemKeys, ':plugin:plugin-a:family:family-a:'));
        self::assertTrue($this->containsKeyFragment($itemKeys, ':plugin:plugin-b:family:family-a:'));
    }

    public function testKeysUseTheVersionedGenerationAndIdentityFormat(): void
    {
        $cache = new RecordingScopedCache();
        $scoped = new ScopedCache($cache, 'example-plugin', 'search-results');
        self::assertTrue($scoped->set(['query' => 'sensitive value'], 'value', 60, ['index' => 'main']));

        $keys = array_merge($cache->addKeys, $cache->setKeys);
        self::assertTrue($this->containsKeyFragment($keys, 'lr-cache:v1:app:' . hash('sha256', Craft::$app->id)));
        self::assertTrue($this->containsKeyFragment($keys, ':plugin:example-plugin:family:search-results:generation:global'));
        self::assertTrue($this->containsKeyFragment($keys, ':generation:scope:'));
        self::assertTrue($this->containsKeyFragment($keys, ':item:global:'));
        self::assertTrue($this->containsKeyFragment($keys, ':scope:'));
        self::assertTrue($this->containsKeyFragment($keys, ':identity:'));
        self::assertFalse($this->containsKeyFragment($keys, 'sensitive value'));
        self::assertFalse($this->containsKeyFragment($keys, ':index:main'));
    }

    public function testFamilyInvalidationHidesPriorItemsAndPreservesAnotherPlugin(): void
    {
        $cache = new RecordingScopedCache();
        $target = new ScopedCache($cache, 'target-plugin', 'results');
        $sentinel = new ScopedCache($cache, 'sentinel-plugin', 'results');

        self::assertTrue($target->set('one', 'first', 60));
        self::assertTrue($target->set('two', 'second', 60, 'scope-a'));
        self::assertTrue($sentinel->set('sentinel', 'safe', 60));
        self::assertTrue($target->invalidateFamily());

        self::assertTrue($target->get('one')->isMiss());
        self::assertTrue($target->get('two', 'scope-a')->isMiss());
        self::assertSame('safe', $sentinel->get('sentinel')->value);
    }

    public function testScopeInvalidationHidesOnlyThatScope(): void
    {
        $cache = new RecordingScopedCache();
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');

        self::assertTrue($scoped->set('item', 'a', 60, ['index' => 'a']));
        self::assertTrue($scoped->set('item', 'b', 60, ['index' => 'b']));
        self::assertTrue($scoped->set('item', 'global-item', 60));
        self::assertTrue($scoped->invalidateScope(['index' => 'a']));

        self::assertTrue($scoped->get('item', ['index' => 'a'])->isMiss());
        self::assertSame('b', $scoped->get('item', ['index' => 'b'])->value);
        self::assertSame('global-item', $scoped->get('item')->value);
    }

    public function testConcurrentGenerationInitializationSharesTheWinner(): void
    {
        $cache = new RecordingScopedCache();
        $first = new ScopedCache($cache, 'example-plugin', 'results');
        $second = new ScopedCache($cache, 'example-plugin', 'results');

        self::assertTrue($first->set('one', 1, 60));
        self::assertTrue($second->set('two', 2, 60));

        self::assertSame(1, $cache->addCalls);
        self::assertSame(1, $second->get('one')->value);
        self::assertSame(2, $first->get('two')->value);
    }

    public function testFailedGenerationAddUsesThePersistedWinnerAfterReread(): void
    {
        $cache = new RecordingScopedCache();
        $cache->failNextAddWithWinner = true;
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');

        self::assertTrue($scoped->set('item', 'value', 60));
        self::assertSame('value', $scoped->get('item')->value);
        self::assertTrue($this->containsKeyFragment($cache->itemSetKeys(), ':global:0123456789abcdef0123456789abcdef:'));
    }

    public function testFailedGenerationInitializationNeverUsesAnUnpersistedToken(): void
    {
        $cache = new RecordingScopedCache();
        $cache->returnAddFalse = true;
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');

        self::assertFalse($scoped->set('item', 'value', 60));
        self::assertSame([], $cache->itemSetKeys());
    }

    public function testWriteUsingAnOldGenerationRemainsUnreachable(): void
    {
        $cache = new RecordingScopedCache();
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');
        $cache->beforeNextItemSet = static function() use ($scoped): void {
            self::assertTrue($scoped->invalidateFamily());
        };

        self::assertTrue($scoped->set('item', 'old-generation', 60));
        self::assertTrue($scoped->get('item')->isMiss());
    }

    public function testEveryStoredValueReceivesAFinitePositiveTtl(): void
    {
        $cache = new RecordingScopedCache();
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');

        self::assertFalse($scoped->set('zero', 'value', 0));
        self::assertFalse($scoped->set('negative', 'value', -1));
        self::assertTrue($scoped->set('positive', 'value', 123));

        self::assertNotEmpty($cache->durations);
        foreach ($cache->durations as $duration) {
            self::assertIsInt($duration);
            self::assertGreaterThan(0, $duration);
        }
        self::assertSame(123, $cache->itemSetDurations()[0]);
    }

    public function testCacheFailuresAndFalseResultsFailSoftly(): void
    {
        $getCache = new RecordingScopedCache();
        $getCache->throwGet = true;
        self::assertTrue((new ScopedCache($getCache, 'example-plugin', 'get'))->get('item')->isFailure());

        $setCache = new RecordingScopedCache();
        $setCache->returnSetFalse = true;
        self::assertFalse((new ScopedCache($setCache, 'example-plugin', 'set'))->set('item', 'value', 60));

        $throwSetCache = new RecordingScopedCache();
        $throwSetCache->throwSet = true;
        self::assertFalse((new ScopedCache($throwSetCache, 'example-plugin', 'throw-set'))->set('item', 'value', 60));

        $deleteCache = new RecordingScopedCache();
        $deleteScoped = new ScopedCache($deleteCache, 'example-plugin', 'delete');
        self::assertTrue($deleteScoped->set('item', 'value', 60));
        $deleteCache->throwDelete = true;
        self::assertFalse($deleteScoped->delete('item'));

        $falseDeleteCache = new RecordingScopedCache();
        $falseDeleteScoped = new ScopedCache($falseDeleteCache, 'example-plugin', 'false-delete');
        self::assertTrue($falseDeleteScoped->set('item', 'value', 60));
        $falseDeleteCache->returnDeleteFalse = true;
        self::assertFalse($falseDeleteScoped->delete('item'));

        $invalidateCache = new RecordingScopedCache();
        $invalidateCache->returnSetFalse = true;
        self::assertFalse((new ScopedCache($invalidateCache, 'example-plugin', 'invalidate'))->invalidateFamily());
    }

    public function testExactItemDeletePreservesSiblingAndSentinelValues(): void
    {
        $cache = new RecordingScopedCache();
        $target = new ScopedCache($cache, 'target-plugin', 'results');
        $sentinel = new ScopedCache($cache, 'sentinel-plugin', 'results');
        self::assertTrue($target->set('delete-me', 'delete', 60));
        self::assertTrue($target->set('keep-me', 'keep', 60));
        self::assertTrue($sentinel->set('sentinel', 'safe', 60));

        self::assertTrue($target->delete('delete-me'));
        self::assertTrue($target->get('delete-me')->isMiss());
        self::assertSame('keep', $target->get('keep-me')->value);
        self::assertSame('safe', $sentinel->get('sentinel')->value);
    }

    public function testImplementationNeverFlushesOrEnumeratesSharedCacheState(): void
    {
        $cache = new RecordingScopedCache();
        $scoped = new ScopedCache($cache, 'example-plugin', 'results');
        self::assertTrue($scoped->set('item', 'value', 60));
        self::assertTrue($scoped->invalidateFamily());
        self::assertSame(0, $cache->flushCalls);

        $source = file_get_contents(dirname(__DIR__, 2) . '/src/cache/ScopedCache.php');
        self::assertIsString($source);
        self::assertStringNotContainsString('->flush(', $source);
        self::assertDoesNotMatchRegularExpression('/\\b(KEYS|SMEMBERS|SSCAN)\\b/', $source);
        self::assertStringNotContainsString('executeCommand(', $source);
    }

    private function containsKeyFragment(array $keys, string $fragment): bool
    {
        foreach ($keys as $key) {
            if (str_contains($key, $fragment)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * @since 5.38.0
 */
final class RecordingScopedCache extends ArrayCache
{
    public array $addKeys = [];
    public array $setKeys = [];
    public array $setDurations = [];
    public array $durations = [];
    public int $addCalls = 0;
    public int $flushCalls = 0;
    public bool $failNextAddWithWinner = false;
    public bool $returnAddFalse = false;
    public bool $returnSetFalse = false;
    public bool $throwSet = false;
    public bool $throwGet = false;
    public bool $throwDelete = false;
    public bool $returnDeleteFalse = false;
    public $beforeNextItemSet = null;

    public function get($key)
    {
        if ($this->throwGet) {
            throw new \RuntimeException('Injected get failure.');
        }
        return parent::get($key);
    }

    public function set($key, $value, $duration = null, $dependency = null)
    {
        $this->setKeys[] = (string)$key;
        $this->setDurations[] = $duration;
        $this->durations[] = $duration;
        if (str_contains((string)$key, ':item:') && is_callable($this->beforeNextItemSet)) {
            $callback = $this->beforeNextItemSet;
            $this->beforeNextItemSet = null;
            $callback();
        }
        if ($this->returnSetFalse) {
            return false;
        }
        if ($this->throwSet) {
            throw new \RuntimeException('Injected set failure.');
        }
        return parent::set($key, $value, $duration, $dependency);
    }

    public function add($key, $value, $duration = 0, $dependency = null)
    {
        $this->addCalls++;
        $this->addKeys[] = (string)$key;
        $this->durations[] = $duration;
        if ($this->failNextAddWithWinner) {
            $this->failNextAddWithWinner = false;
            parent::set($key, '0123456789abcdef0123456789abcdef', $duration);
            return false;
        }
        if ($this->returnAddFalse) {
            return false;
        }
        return parent::add($key, $value, $duration, $dependency);
    }

    public function delete($key)
    {
        if ($this->throwDelete) {
            throw new \RuntimeException('Injected delete failure.');
        }
        if ($this->returnDeleteFalse) {
            return false;
        }
        return parent::delete($key);
    }

    public function flush()
    {
        $this->flushCalls++;
        return parent::flush();
    }

    public function itemSetKeys(): array
    {
        return array_values(array_filter(
            $this->setKeys,
            static fn(string $key): bool => str_contains($key, ':item:'),
        ));
    }

    public function itemSetDurations(): array
    {
        $durations = [];
        foreach ($this->setKeys as $index => $key) {
            if (str_contains($key, ':item:')) {
                $durations[] = $this->setDurations[$index];
            }
        }
        return $durations;
    }
}
