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
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.26.0
 */
#[CoversClass(DeviceDetection::class)]
#[CoversClass(PluginHelper::class)]
final class DeviceDetectionRedisSafeguardTest extends IntegrationTestCase
{
    public function testDirectRedisCommandUsesRedisSafeguardHelper(): void
    {
        $sourceFile = dirname(__DIR__, 2) . '/src/device/DeviceDetection.php';
        $source = file_get_contents($sourceFile);

        self::assertIsString($source);
        self::assertStringContainsString('executeCommand(', $source);
        self::assertStringContainsString('PluginHelper::getRedisCacheOrLog', $source);
        self::assertDoesNotMatchRegularExpression(
            '/instanceof\s+\\\\yii\\\\redis\\\\Cache(?:(?!PluginHelper::getRedisCacheOrLog).){0,500}executeCommand\(/s',
            $source,
        );
    }
}
