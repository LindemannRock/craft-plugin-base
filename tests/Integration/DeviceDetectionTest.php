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
use lindemannrock\base\device\DeviceDetection;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\caching\FileCache;

require_once dirname(__DIR__) . '/Fixtures/CascadeCache.php';

/**
 * @since 5.26.0
 */
#[CoversClass(DeviceDetection::class)]
final class DeviceDetectionTest extends IntegrationTestCase
{
    private CacheInterface $originalCache;
    private bool $hadEphemeralSetting;
    private mixed $originalEphemeralSetting = null;

    protected function setUp(): void
    {
        parent::setUp();
        $cache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $cache);
        $this->originalCache = $cache;
        $this->hadEphemeralSetting = array_key_exists('CRAFT_EPHEMERAL', $_SERVER);
        $this->originalEphemeralSetting = $_SERVER['CRAFT_EPHEMERAL'] ?? null;
        $_SERVER['CRAFT_EPHEMERAL'] = false;
    }

    protected function tearDown(): void
    {
        Craft::$app->set('cache', $this->originalCache);
        if ($this->hadEphemeralSetting) {
            $_SERVER['CRAFT_EPHEMERAL'] = $this->originalEphemeralSetting;
        } else {
            unset($_SERVER['CRAFT_EPHEMERAL']);
        }
        parent::tearDown();
    }

    public function testDefaultSystemAgentClassifiesCacheManagerTraffic(): void
    {
        $info = (new DeviceDetection())->detect('CacheManager/1.0', [
            'includeClientHints' => false,
            'includePlatform' => true,
        ]);

        self::assertTrue($info['isRobot']);
        self::assertTrue($info['isSystemAgent']);
        self::assertSame('system', $info['trafficType']);
        self::assertSame('Cache Manager', $info['botName']);
        self::assertSame('Service Agent', $info['botCategory']);
        self::assertSame('LindemannRock', $info['botProducerName']);
        self::assertSame('system', $info['platform']);
        self::assertSame('LindemannRock', $info['vendor']);
    }

    public function testCustomSystemAgentPatternCanBeConfigured(): void
    {
        $info = (new DeviceDetection())->detect('ExampleWarmup/2.0 (+https://example.test)', [
            'includeClientHints' => false,
            'systemAgents' => [
                [
                    'pattern' => '/^ExampleWarmup\/\d+\.\d+/',
                    'name' => 'Example Warmup',
                    'category' => 'Service Agent',
                    'producer' => [
                        'name' => 'Example',
                        'url' => 'https://example.test',
                    ],
                ],
            ],
        ]);

        self::assertTrue($info['isSystemAgent']);
        self::assertSame('system', $info['trafficType']);
        self::assertSame('Example Warmup', $info['botName']);
        self::assertSame('Example', $info['botProducerName']);
        self::assertSame('https://example.test', $info['botProducerUrl']);
    }

    public function testKnownMatomoBotExposesRichBotMetadata(): void
    {
        $info = (new DeviceDetection())->detect(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            ['includeClientHints' => false]
        );

        self::assertTrue($info['isRobot']);
        self::assertFalse($info['isSystemAgent']);
        self::assertSame('bot', $info['trafficType']);
        self::assertSame('Googlebot', $info['botName']);
        self::assertSame('Search bot', $info['botCategory']);
        self::assertNotEmpty($info['botUrl']);
        self::assertSame('Google Inc.', $info['botProducerName']);
    }

    public function testClientHintsAreExposedForConsumers(): void
    {
        $info = (new DeviceDetection())->detect('Mozilla/5.0 AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', [
            'clientHints' => [
                'Sec-CH-UA-Model' => '"Pixel 8"',
                'Sec-CH-UA-Platform' => '"Android"',
                'Sec-CH-UA-Platform-Version' => '"14.0.0"',
                'Sec-CH-UA-Mobile' => '?1',
                'Sec-CH-UA-Arch' => '"arm"',
                'Sec-CH-UA-Bitness' => '"64"',
                'Sec-CH-UA-Form-Factors' => '"Mobile"',
                'X-Requested-With' => 'com.example.app',
            ],
        ]);

        self::assertTrue($info['clientHintsUsed']);
        self::assertSame('Pixel 8', $info['clientHints']['model']);
        self::assertSame('Android', $info['clientHints']['platform']);
        self::assertSame('14.0.0', $info['clientHints']['platformVersion']);
        self::assertTrue($info['clientHints']['mobile']);
        self::assertSame('arm', $info['architecture']);
        self::assertSame('64', $info['bitness']);
        self::assertSame(['mobile'], $info['formFactors']);
        self::assertSame('com.example.app', $info['appId']);
    }

    public function testCacheIdentityIncludesClientHints(): void
    {
        $cachePath = $this->createTrackedTempDirectory('__base_device_detection_');
        $detector = new DeviceDetection();
        $userAgent = 'Mozilla/5.0 AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36';
        $config = [
            'cacheEnabled' => true,
            'cacheStorageMethod' => 'file',
            'cachePath' => $cachePath,
            'cacheDuration' => 3600,
        ];

        $pixel = $detector->detect($userAgent, $config + [
            'clientHints' => [
                'Sec-CH-UA-Model' => '"Pixel 8"',
                'Sec-CH-UA-Platform' => '"Android"',
            ],
        ]);
        $iphone = $detector->detect($userAgent, $config + [
            'clientHints' => [
                'Sec-CH-UA-Model' => '"iPhone"',
                'Sec-CH-UA-Platform' => '"iOS"',
            ],
        ]);

        self::assertSame('Pixel 8', $pixel['clientHints']['model']);
        self::assertSame('iPhone', $iphone['clientHints']['model']);
        self::assertCount(2, glob($cachePath . DIRECTORY_SEPARATOR . '*.cache') ?: []);
    }

    public function testDurableFileCacheIsReusedAcrossDetectorInstances(): void
    {
        $cachePath = $this->createTrackedTempDirectory('__base_device_file_');
        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'first') + [
            'cachePath' => $cachePath,
        ]);
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'second') + [
            'cachePath' => $cachePath,
        ]);

        self::assertSame('first', $first['botName']);
        self::assertSame('first', $second['botName']);
        self::assertCount(1, glob($cachePath . DIRECTORY_SEPARATOR . '*.cache') ?: []);
    }

    public function testFileReadAndWriteFailuresRecomputeWithoutWarningsEscaping(): void
    {
        $cachePath = $this->createTrackedTempDirectory('__base_device_file_failure_');
        $cacheFile = $cachePath . DIRECTORY_SEPARATOR . md5('ExampleDevice/1.0') . '.cache';
        mkdir($cacheFile);
        $errors = [];
        set_error_handler(static function(int $severity, string $message) use (&$errors): never {
            $errors[] = [$severity, $message];
            throw new \ErrorException($message, 0, $severity);
        });

        try {
            $info = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'recomputed') + [
                'cachePath' => $cachePath,
                'logError' => static function(): void {
                },
            ]);
        } finally {
            restore_error_handler();
        }

        self::assertSame('recomputed', $info['botName']);
    }

    public function testLegacyRedisTokenUsesManagedApplicationCache(): void
    {
        $this->installCache(new CascadeCache());

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('redis', 'first'));
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('redis', 'second'));

        self::assertSame('first', $first['botName']);
        self::assertSame('first', $second['botName']);
    }

    public function testCraftTokenUsesUnknownApplicationCacheBestEffort(): void
    {
        $cache = new DevicePersistentCache();
        $this->installCache($cache);

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'first'));
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'second'));

        self::assertSame('first', $first['botName']);
        self::assertSame('first', $second['botName']);
        self::assertContains(77, $cache->setDurations);
    }

    public function testEphemeralFileTokenUsesSuitableApplicationCacheWithoutRuntimeFiles(): void
    {
        $_SERVER['CRAFT_EPHEMERAL'] = true;
        $this->installCache(new CascadeCache());
        $cachePath = $this->createTrackedTempDirectory('__base_device_ephemeral_');

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'first') + [
            'cachePath' => $cachePath,
        ]);
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'second') + [
            'cachePath' => $cachePath,
        ]);

        self::assertSame('first', $first['botName']);
        self::assertSame('first', $second['botName']);
        self::assertSame([], glob($cachePath . DIRECTORY_SEPARATOR . '*.cache') ?: []);
    }

    public function testEphemeralFileCacheBackendIsRejectedAndRecomputed(): void
    {
        $_SERVER['CRAFT_EPHEMERAL'] = true;
        $applicationCachePath = $this->createTrackedTempDirectory('__base_application_file_');
        $cache = new RecordingDeviceFileCache(['cachePath' => $applicationCachePath]);
        $this->installCache($cache);
        $runtimeCachePath = $this->createTrackedTempDirectory('__base_device_runtime_');

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'first') + [
            'cachePath' => $runtimeCachePath,
        ]);
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('file', 'second') + [
            'cachePath' => $runtimeCachePath,
        ]);

        self::assertSame('first', $first['botName']);
        self::assertSame('second', $second['botName']);
        self::assertSame(0, $cache->setCalls);
        self::assertSame([], glob($runtimeCachePath . DIRECTORY_SEPARATOR . '*.cache') ?: []);
    }

    public function testArrayCacheDoesNotClaimCrossRequestReuse(): void
    {
        $_SERVER['CRAFT_EPHEMERAL'] = true;
        $cache = new RecordingDeviceArrayCache();
        $this->installCache($cache);

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'first'));
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'second'));

        self::assertSame('first', $first['botName']);
        self::assertSame('second', $second['botName']);
        self::assertSame(0, $cache->setCalls);
    }

    public function testUnavailableApplicationCacheRecomputes(): void
    {
        Craft::$app->set('cache', static function(): never {
            throw new \RuntimeException('Injected component failure.');
        });

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'first'));
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'second'));

        self::assertSame('first', $first['botName']);
        self::assertSame('second', $second['botName']);
    }

    public function testApplicationCacheOperationFailureRecomputes(): void
    {
        $cache = new DevicePersistentCache();
        $cache->throwGet = true;
        $this->installCache($cache);

        $first = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'first'));
        $second = (new DeviceDetection())->detect('ExampleDevice/1.0', $this->deviceConfig('craft', 'second'));

        self::assertSame('first', $first['botName']);
        self::assertSame('second', $second['botName']);
    }

    public function testApplicationCacheKeepsPluginNamespacesIsolated(): void
    {
        $this->installCache(new DevicePersistentCache());
        $pluginA = array_replace($this->deviceConfig('craft', 'plugin-a'), [
            'pluginHandle' => 'plugin-a',
            'cacheKeyPrefix' => 'plugina:device:',
        ]);
        $pluginB = array_replace($this->deviceConfig('craft', 'plugin-b'), [
            'pluginHandle' => 'plugin-b',
            'cacheKeyPrefix' => 'pluginb:device:',
        ]);

        $firstA = (new DeviceDetection())->detect('ExampleDevice/1.0', $pluginA);
        $firstB = (new DeviceDetection())->detect('ExampleDevice/1.0', $pluginB);
        $cachedA = (new DeviceDetection())->detect('ExampleDevice/1.0', array_replace($pluginA, [
            'systemAgents' => $this->systemAgent('changed-a'),
        ]));

        self::assertSame('plugin-a', $firstA['botName']);
        self::assertSame('plugin-b', $firstB['botName']);
        self::assertSame('plugin-a', $cachedA['botName']);
    }

    private function deviceConfig(string $storage, string $agentName): array
    {
        return [
            'cacheEnabled' => true,
            'cacheStorageMethod' => $storage,
            'cacheDuration' => 77,
            'pluginHandle' => 'example-plugin',
            'cacheKeyPrefix' => 'exampleplugin:device:',
            'cacheKeySet' => 'exampleplugin-device-keys',
            'includeClientHints' => false,
            'systemAgents' => $this->systemAgent($agentName),
        ];
    }

    private function systemAgent(string $agentName): array
    {
        return [[
            'userAgent' => 'ExampleDevice/1.0',
            'name' => $agentName,
            'category' => 'Service Agent',
        ]];
    }

    private function installCache(CacheInterface $cache): void
    {
        Craft::$app->set('cache', $cache);
    }
}

/**
 * @since 5.38.0
 */
final class DevicePersistentCache extends Cache
{
    private array $values = [];
    public array $setDurations = [];
    public bool $throwGet = false;

    protected function getValue($key)
    {
        if ($this->throwGet) {
            throw new \RuntimeException('Injected get failure.');
        }
        return $this->values[$key] ?? false;
    }

    protected function getValues($keys)
    {
        return array_map(fn(string $key): mixed => $this->getValue($key), $keys);
    }

    protected function setValue($key, $value, $duration)
    {
        $this->setDurations[] = $duration;
        $this->values[$key] = $value;
        return true;
    }

    protected function setValues($data, $duration)
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value, $duration);
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
final class RecordingDeviceFileCache extends FileCache
{
    public int $setCalls = 0;

    public function set($key, $value, $duration = null, $dependency = null)
    {
        $this->setCalls++;
        return parent::set($key, $value, $duration, $dependency);
    }
}

/**
 * @since 5.38.0
 */
final class RecordingDeviceArrayCache extends ArrayCache
{
    public int $setCalls = 0;

    public function set($key, $value, $duration = null, $dependency = null)
    {
        $this->setCalls++;
        return parent::set($key, $value, $duration, $dependency);
    }
}
