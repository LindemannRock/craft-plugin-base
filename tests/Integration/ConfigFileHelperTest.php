<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\ConfigFileHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use ReflectionClass;

/**
 * Pins the contract for {@see ConfigFileHelper}.
 *
 * @since 5.26.0
 */
final class ConfigFileHelperTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        ConfigFileHelper::clearCache();
        parent::tearDown();
    }

    public function testConfigSectionsAreScopedByPluginHandle(): void
    {
        $this->seedConfigCache([
            'alpha-plugin' => [
                'providers' => [
                    'primary' => ['type' => 'alpha'],
                ],
            ],
            'beta-plugin' => [
                'providers' => [
                    'primary' => ['type' => 'beta'],
                ],
            ],
        ]);

        self::assertSame(['type' => 'alpha'], ConfigFileHelper::getConfigByHandle('alpha-plugin', 'providers', 'primary'));
        self::assertSame(['type' => 'beta'], ConfigFileHelper::getConfigByHandle('beta-plugin', 'providers', 'primary'));
    }

    public function testSectionHandleAndExistenceLookups(): void
    {
        $this->seedConfigCache([
            'example-plugin' => [
                'externalHandlers' => [
                    'sentry-production' => ['type' => 'sentry'],
                    'webhook-alerts' => ['type' => 'webhook'],
                ],
                'notAnArray' => 'ignored',
            ],
        ]);

        self::assertSame(
            [
                'sentry-production' => ['type' => 'sentry'],
                'webhook-alerts' => ['type' => 'webhook'],
            ],
            ConfigFileHelper::getConfigSection('example-plugin', 'externalHandlers'),
        );
        self::assertSame(['sentry-production', 'webhook-alerts'], ConfigFileHelper::getHandles('example-plugin', 'externalHandlers'));
        self::assertTrue(ConfigFileHelper::handleExistsInConfig('example-plugin', 'externalHandlers', 'webhook-alerts'));
        self::assertFalse(ConfigFileHelper::handleExistsInConfig('example-plugin', 'externalHandlers', 'missing'));
        self::assertNull(ConfigFileHelper::getConfigByHandle('example-plugin', 'externalHandlers', 'missing'));
        self::assertSame([], ConfigFileHelper::getConfigSection('example-plugin', 'notAnArray'));
    }

    public function testMergeConfigAndDatabaseLetsConfigWinForObjectRecords(): void
    {
        $databasePrimary = (object)[
            'handle' => 'primary',
            'name' => 'Database Primary',
        ];
        $databaseSecondary = (object)[
            'handle' => 'secondary',
            'name' => 'Database Secondary',
        ];

        $merged = ConfigFileHelper::mergeConfigAndDatabase(
            [
                'primary' => ['name' => 'Config Primary'],
            ],
            [$databasePrimary, $databaseSecondary],
        );

        self::assertSame(['primary', 'secondary'], array_keys($merged));
        self::assertSame(['name' => 'Config Primary'], $merged['primary']);
        self::assertSame($databaseSecondary, $merged['secondary']);
    }

    public function testMergeConfigAndDatabaseLetsConfigWinForArrayRecords(): void
    {
        $merged = ConfigFileHelper::mergeConfigAndDatabase(
            [
                'primary' => ['name' => 'Config Primary'],
            ],
            [
                ['handle' => 'primary', 'name' => 'Database Primary'],
                ['handle' => 'secondary', 'name' => 'Database Secondary'],
                ['name' => 'Missing Handle'],
            ],
        );

        self::assertSame(['primary', 'secondary'], array_keys($merged));
        self::assertSame(['name' => 'Config Primary'], $merged['primary']);
        self::assertSame(['handle' => 'secondary', 'name' => 'Database Secondary'], $merged['secondary']);
    }

    public function testClearCacheCanTargetOnePluginOrAllPlugins(): void
    {
        $this->seedConfigCache([
            'alpha-plugin' => ['section' => ['one' => []]],
            'beta-plugin' => ['section' => ['two' => []]],
        ]);

        ConfigFileHelper::clearCache('alpha-plugin');

        self::assertSame([
            'beta-plugin' => ['section' => ['two' => []]],
        ], $this->getSeededConfigCache());

        ConfigFileHelper::clearCache();

        self::assertSame([], $this->getSeededConfigCache());
    }

    private function seedConfigCache(array $config): void
    {
        $reflection = new ReflectionClass(ConfigFileHelper::class);
        $property = $reflection->getProperty('_configCache');
        $property->setValue(null, $config);
    }

    private function getSeededConfigCache(): array
    {
        $reflection = new ReflectionClass(ConfigFileHelper::class);
        $property = $reflection->getProperty('_configCache');
        return $property->getValue();
    }
}
