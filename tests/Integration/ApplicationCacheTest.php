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
use craft\cachecascade\CascadeCache;
use lindemannrock\base\cache\CacheBackendStatus;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\caching\DbCache;
use yii\caching\FileCache;
use yii\log\Logger;
use yii\redis\Cache as RedisCache;

require_once dirname(__DIR__) . '/Fixtures/CascadeCache.php';

/**
 * @since 5.38.0
 */
final class ApplicationCacheTest extends IntegrationTestCase
{
    private CacheInterface $originalCache;
    private array $originalApplicationCacheDiagnostics;

    protected function setUp(): void
    {
        parent::setUp();
        $cache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $cache);
        $this->originalCache = $cache;
        $this->originalApplicationCacheDiagnostics = $this->applicationCacheDiagnosticsProperty()->getValue();
    }

    protected function tearDown(): void
    {
        Craft::$app->set('cache', $this->originalCache);
        $this->applicationCacheDiagnosticsProperty()->setValue(null, $this->originalApplicationCacheDiagnostics);
        parent::tearDown();
    }

    public function testDirectRedisIsResolvedAndClassifiedPersistent(): void
    {
        $cache = (new \ReflectionClass(RedisCache::class))->newInstanceWithoutConstructor();
        self::assertInstanceOf(RedisCache::class, $cache);
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_REDIS, $status->backend);
        self::assertTrue($status->crossRequestPersistent);
        self::assertTrue($status->ephemeralSuitable);
    }

    public function testDirectRedisSubclassUsesTheRedisClassification(): void
    {
        $cache = (new \ReflectionClass(ApplicationRedisCache::class))->newInstanceWithoutConstructor();
        self::assertInstanceOf(ApplicationRedisCache::class, $cache);
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        self::assertSame(CacheBackendStatus::BACKEND_REDIS, CacheBackendStatus::fromCache($cache)->backend);
    }

    public function testCascadeCacheIsAcceptedWithoutInspectingItsPrimary(): void
    {
        $cache = new CascadeCache();
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_MANAGED, $status->backend);
        self::assertNull($status->crossRequestPersistent);
        self::assertTrue($status->ephemeralSuitable);
    }

    public function testDatabaseCacheIsResolvedAndClassifiedPersistent(): void
    {
        $cache = new DbCache();
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_DATABASE, $status->backend);
        self::assertTrue($status->crossRequestPersistent);
        self::assertTrue($status->supportsCrossRequest(true));
    }

    public function testFileCacheIsAcceptedButRejectedForEphemeralCrossRequestUse(): void
    {
        $cache = new FileCache();
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_FILESYSTEM, $status->backend);
        self::assertTrue($status->crossRequestPersistent);
        self::assertFalse($status->ephemeralSuitable);
        self::assertTrue($status->supportsCrossRequest(false));
        self::assertFalse($status->supportsCrossRequest(true));
    }

    public function testArrayCacheIsAcceptedButClassifiedRequestLocal(): void
    {
        $cache = new ArrayCache();
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_MEMORY, $status->backend);
        self::assertFalse($status->crossRequestPersistent);
        self::assertFalse($status->supportsCrossRequest(false));
        self::assertFalse($status->supportsCrossRequest(true));
    }

    public function testUnknownCacheKeepsPersistenceUnknownAndIsAvailableBestEffort(): void
    {
        $cache = new UnknownApplicationCache();
        $this->installCache($cache);

        self::assertSame($cache, PluginHelper::getApplicationCacheOrLog(__METHOD__));
        $status = CacheBackendStatus::fromCache($cache);
        self::assertSame(CacheBackendStatus::BACKEND_UNKNOWN, $status->backend);
        self::assertNull($status->crossRequestPersistent);
        self::assertNull($status->ephemeralSuitable);
        self::assertTrue($status->supportsCrossRequest(true));
    }

    public function testMissingAndThrowingComponentsFailSoftly(): void
    {
        Craft::$app->clear('cache');
        self::assertNull(PluginHelper::getApplicationCacheOrLog(__METHOD__ . ':missing'));

        Craft::$app->set('cache', static function(): never {
            throw new \RuntimeException('Injected component resolution failure.');
        });
        self::assertNull(PluginHelper::getApplicationCacheOrLog(__METHOD__ . ':throwing'));

        $status = CacheBackendStatus::fromCache(null);
        self::assertSame(CacheBackendStatus::BACKEND_UNAVAILABLE, $status->backend);
        self::assertFalse($status->available);
        self::assertFalse($status->supportsCrossRequest(false));
    }

    public function testFailureDiagnosticsAreBoundedPerContext(): void
    {
        Craft::$app->set('cache', static function(): never {
            throw new \RuntimeException('Injected component resolution failure.');
        });
        $contextA = __METHOD__ . ':a';
        $contextB = __METHOD__ . ':b';
        $messageOffset = count(Craft::getLogger()->messages);

        PluginHelper::getApplicationCacheOrLog($contextA);
        PluginHelper::getApplicationCacheOrLog($contextA);
        PluginHelper::getApplicationCacheOrLog($contextB);

        $messages = array_filter(
            array_slice(Craft::getLogger()->messages, $messageOffset),
            static fn(array $message): bool => ($message[1] ?? null) === Logger::LEVEL_WARNING
                && ($message[2] ?? null) === 'lindemannrock-base'
                && (str_contains((string)($message[0] ?? ''), $contextA)
                    || str_contains((string)($message[0] ?? ''), $contextB)),
        );
        self::assertCount(2, $messages);
    }

    public function testStatusSourceContainsNoTranslatedOrConsumerFacingLabels(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/cache/CacheBackendStatus.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('Craft::t(', $source);
        self::assertStringNotContainsString('configuredSetting', $source);
        self::assertStringNotContainsString('effectiveSetting', $source);
    }

    private function installCache(CacheInterface $cache): void
    {
        Craft::$app->set('cache', $cache);
    }

    private function applicationCacheDiagnosticsProperty(): \ReflectionProperty
    {
        return new \ReflectionProperty(PluginHelper::class, 'applicationCacheDiagnostics');
    }
}

/**
 * @since 5.38.0
 */
final class UnknownApplicationCache extends Cache
{
    private array $values = [];

    protected function getValue($key)
    {
        return $this->values[$key] ?? false;
    }

    protected function getValues($keys)
    {
        return array_map(fn(string $key): mixed => $this->getValue($key), $keys);
    }

    protected function setValue($key, $value, $duration)
    {
        $this->values[$key] = $value;
        return true;
    }

    protected function setValues($data, $duration)
    {
        foreach ($data as $key => $value) {
            $this->values[$key] = $value;
        }
        return [];
    }

    protected function addValue($key, $value, $duration)
    {
        if (array_key_exists($key, $this->values)) {
            return false;
        }
        $this->values[$key] = $value;
        return true;
    }

    protected function deleteValue($key)
    {
        unset($this->values[$key]);
        return true;
    }

    protected function flushValues()
    {
        $this->values = [];
        return true;
    }
}

/**
 * @since 5.38.0
 */
final class ApplicationRedisCache extends RedisCache
{
}
