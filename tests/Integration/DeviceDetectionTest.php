<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\device\DeviceDetection;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.26.0
 */
#[CoversClass(DeviceDetection::class)]
final class DeviceDetectionTest extends IntegrationTestCase
{
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
}
