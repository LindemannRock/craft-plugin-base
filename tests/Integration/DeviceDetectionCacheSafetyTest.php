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
 * @since 5.38.0
 */
#[CoversClass(DeviceDetection::class)]
final class DeviceDetectionCacheSafetyTest extends IntegrationTestCase
{
    public function testDeviceCacheUsesOnlyThePortableScopedContract(): void
    {
        $sourceFile = dirname(__DIR__, 2) . '/src/device/DeviceDetection.php';
        $source = file_get_contents($sourceFile);

        self::assertIsString($source);
        self::assertStringContainsString('PluginHelper::getApplicationCacheOrLog', $source);
        self::assertStringContainsString('new ScopedCache(', $source);
        self::assertStringNotContainsString('PluginHelper::getRedisCacheOrLog', $source);
        self::assertStringNotContainsString('executeCommand(', $source);
        self::assertDoesNotMatchRegularExpression('/\\b(SADD|SMEMBERS|SSCAN|KEYS)\\b/', $source);
        self::assertStringNotContainsString("['cacheKeySet']", $source);
    }
}
