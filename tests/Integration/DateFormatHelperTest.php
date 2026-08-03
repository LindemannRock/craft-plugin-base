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
use craft\base\Model;
use craft\base\PluginInterface;
use craft\services\Config;
use craft\services\Plugins;
use DateTime;
use DateTimeZone;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use yii\db\Expression;

/**
 * Pins the contract for {@see DateFormatHelper}.
 *
 * @since 5.25.0
 */
final class DateFormatHelperTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Tests in this file may stub the static config cache via reflection;
        // make sure each test starts from the on-disk config.
        DateFormatHelper::clearConfigCache();
    }

    protected function tearDown(): void
    {
        DateFormatHelper::clearConfigCache();
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setDateFormatConfig(array $config, string $cacheKey = '__base__'): void
    {
        $cache = new ReflectionClass(DateFormatHelper::class);
        $configProperty = $cache->getProperty('configCache');
        $existing = $configProperty->getValue();
        $existing[$cacheKey] = $config;
        $configProperty->setValue(null, $existing);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getDateFormatConfigCache(): array
    {
        $cache = new ReflectionClass(DateFormatHelper::class);
        return $cache->getProperty('configCache')->getValue();
    }

    /**
     * Put Craft's real Plugins service into a deterministic point in its load lifecycle.
     *
     * @param array<string, PluginInterface> $plugins
     */
    private function setPluginLifecycleState(Plugins $service, bool $loading, bool $loaded, array $plugins = []): void
    {
        $reflection = new ReflectionClass(Plugins::class);
        $reflection->getProperty('_loadingPlugins')->setValue($service, $loading);
        $reflection->getProperty('_pluginsLoaded')->setValue($service, $loaded);
        $reflection->getProperty('_plugins')->setValue($service, $plugins);
    }

    /**
     * Run an assertion block with deterministic config and plugin services.
     *
     * @param array<string, array<string, mixed>> $configFiles
     * @param callable(Plugins): void $assertions
     */
    private function withDateFormatServices(array &$configFiles, callable $assertions): void
    {
        $originalConfig = Craft::$app->getConfig();
        $originalPlugins = Craft::$app->getPlugins();
        $config = $this->createMock(Config::class);
        $config->method('getConfigFromFile')->willReturnCallback(
            static function(string $handle) use (&$configFiles): array {
                return $configFiles[$handle] ?? [];
            },
        );
        $plugins = new Plugins();

        Craft::$app->set('config', $config);
        Craft::$app->set('plugins', $plugins);

        try {
            $assertions($plugins);
        } finally {
            DateFormatHelper::clearConfigCache();
            Craft::$app->set('plugins', $originalPlugins);
            Craft::$app->set('config', $originalConfig);
        }
    }

    public function testPluginLoadingResultIsNotCachedBeforeDatabaseSettingsBecomeAvailable(): void
    {
        $configFiles = [
            'lindemannrock-base' => [
                'timeFormat' => '24',
                'monthFormat' => 'long',
                'dateOrder' => 'dmy',
                'dateSeparator' => '/',
                'showSeconds' => false,
            ],
            'lifecycle-plugin' => [
                'timeFormat' => '12',
                'dateSeparator' => '-',
            ],
        ];

        $this->withDateFormatServices($configFiles, function(Plugins $plugins): void {
            // This is the supported recursive state created by Plugins::loadPlugins():
            // the plugin object is running init(), but _registerPlugin() has not run.
            $this->setPluginLifecycleState($plugins, loading: true, loaded: false);

            $startupConfig = DateFormatHelper::getConfig('lifecycle-plugin');

            self::assertSame('12', $startupConfig['timeFormat'], 'startup still receives plugin-file overrides');
            self::assertSame('long', $startupConfig['monthFormat'], 'startup still receives global Base config');
            self::assertSame('-', $startupConfig['dateSeparator']);
            self::assertArrayNotHasKey(
                'lifecycle-plugin',
                $this->getDateFormatConfigCache(),
                'a pre-registration result must not poison the request cache',
            );

            $settings = new class extends Model {
                public ?string $timeFormat = '24';
                public ?string $monthFormat = 'numeric';
                public ?string $dateOrder = null;
                public ?string $dateSeparator = null;
                public ?bool $showSeconds = true;
            };
            $plugin = $this->createMock(PluginInterface::class);
            $plugin->method('getSettings')->willReturn($settings);
            $this->setPluginLifecycleState(
                $plugins,
                loading: false,
                loaded: true,
                plugins: ['lifecycle-plugin' => $plugin],
            );

            $registeredConfig = DateFormatHelper::getConfig('lifecycle-plugin');

            self::assertSame('12', $registeredConfig['timeFormat'], 'plugin config must continue to override database settings');
            self::assertSame('numeric', $registeredConfig['monthFormat'], 'database settings must override global Base config');
            self::assertSame('dmy', $registeredConfig['dateOrder'], 'null database values must inherit global Base config');
            self::assertSame('-', $registeredConfig['dateSeparator'], 'null database values must inherit before plugin config is applied');
            self::assertTrue($registeredConfig['showSeconds']);
            self::assertSame($registeredConfig, $this->getDateFormatConfigCache()['lifecycle-plugin']);

            // A complete resolution is cached normally after registration.
            $settings->monthFormat = 'short';
            self::assertSame($registeredConfig, DateFormatHelper::getConfig('lifecycle-plugin'));
        });
    }

    #[DataProvider('unavailablePluginCases')]
    public function testUnavailablePluginAfterLoadingRetainsCacheableFallbackBehavior(string $case): void
    {
        $handle = 'unavailable-' . $case;
        $configFiles = [
            'lindemannrock-base' => ['timeFormat' => '24', 'monthFormat' => 'long'],
            $handle => ['timeFormat' => '12'],
        ];

        $this->withDateFormatServices($configFiles, function(Plugins $plugins) use (&$configFiles, $handle): void {
            // getPlugin() returns null for both a genuinely absent plugin and an
            // installed-but-disabled plugin once loading has completed.
            $this->setPluginLifecycleState($plugins, loading: false, loaded: true);

            $resolved = DateFormatHelper::getConfig($handle);
            self::assertSame(['timeFormat' => '12', 'monthFormat' => 'long'], $resolved);
            self::assertSame($resolved, $this->getDateFormatConfigCache()[$handle]);

            $configFiles[$handle]['timeFormat'] = '24';
            self::assertSame($resolved, DateFormatHelper::getConfig($handle), 'the complete fallback remains cached');
        });
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unavailablePluginCases(): iterable
    {
        yield 'absent plugin' => ['absent'];
        yield 'disabled plugin' => ['disabled'];
    }

    public function testGlobalAndHandleSpecificCacheClearingRemainIsolated(): void
    {
        $configFiles = [
            'lindemannrock-base' => ['timeFormat' => '24'],
            'cache-plugin' => ['monthFormat' => 'short'],
        ];

        $this->withDateFormatServices($configFiles, function(Plugins $plugins) use (&$configFiles): void {
            $this->setPluginLifecycleState($plugins, loading: false, loaded: true);

            self::assertSame(['timeFormat' => '24'], DateFormatHelper::getConfig());
            self::assertSame(
                ['timeFormat' => '24', 'monthFormat' => 'short'],
                DateFormatHelper::getConfig('cache-plugin'),
            );

            $configFiles['lindemannrock-base']['timeFormat'] = '12';
            $configFiles['cache-plugin']['monthFormat'] = 'long';
            DateFormatHelper::clearConfigCache('cache-plugin');

            self::assertSame(['timeFormat' => '24'], DateFormatHelper::getConfig(), 'handle clearing must preserve global cache');
            self::assertSame(
                ['timeFormat' => '12', 'monthFormat' => 'long'],
                DateFormatHelper::getConfig('cache-plugin'),
                'handle clearing must refresh only that handle',
            );

            DateFormatHelper::clearConfigCache();
            self::assertSame(['timeFormat' => '12'], DateFormatHelper::getConfig(), 'global clearing must refresh all entries');
            self::assertArrayNotHasKey('cache-plugin', $this->getDateFormatConfigCache());
        });
    }

    public function testLocalDateExpressionParameterizesTimezoneOffsetAgainstSqlInjection(): void
    {
        $expr = DateFormatHelper::localDateExpression('dateCreated');

        self::assertInstanceOf(Expression::class, $expr);

        // The injection-fix contract: the timezone offset is bound as
        // `:tzOffset`, never interpolated into the SQL string. If a future
        // refactor regresses to string interpolation this assertion fires.
        self::assertStringContainsString(':tzOffset', $expr->expression);
        self::assertArrayHasKey(':tzOffset', $expr->params);
        self::assertMatchesRegularExpression(
            '/^[+\-]\d{2}:\d{2}$/',
            (string) $expr->params[':tzOffset'],
            'tzOffset must be in ±HH:MM format'
        );

        // The actual offset string must NOT appear inline in the SQL.
        $offset = (string) $expr->params[':tzOffset'];
        self::assertStringNotContainsString("'$offset'", $expr->expression);

        // Driver-appropriate dialect.
        if (Craft::$app->getDb()->getIsMysql()) {
            self::assertStringContainsString('CONVERT_TZ', $expr->expression);
            self::assertStringContainsString('[[dateCreated]]', $expr->expression);
        } else {
            self::assertStringContainsString('AT TIME ZONE', $expr->expression);
        }
    }

    public function testFormatDateStylesSeparateCascadeFromFixedPresets(): void
    {
        $this->setDateFormatConfig([
            'timeFormat' => '12',
            'dateOrder' => 'dmy',
            'monthFormat' => 'long',
            'dateSeparator' => '/',
            'showSeconds' => true,
        ]);

        $date = new DateTime('2026-05-30 00:30:00', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame('30 May 2026', DateFormatHelper::formatDate($date));
        self::assertSame('30 May 2026', DateFormatHelper::formatDate($date, 'cascade'));
        self::assertSame('30/05/2026', DateFormatHelper::formatDate($date, 'short'));
        self::assertSame('30 May 2026', DateFormatHelper::formatDate($date, 'medium'));
        self::assertSame('30 May 2026', DateFormatHelper::formatDate($date, 'long'));
        self::assertSame('30 May 2026 at 12:30:00 AM', DateFormatHelper::formatDatetime($date, 'long'));
    }

    public function testExplicitPluginHandleSelectsPluginCascadeConfig(): void
    {
        $this->setDateFormatConfig([
            'timeFormat' => '12',
            'dateOrder' => 'mdy',
            'monthFormat' => 'long',
            'dateSeparator' => '/',
            'showSeconds' => true,
        ], 'test-plugin');

        $date = new DateTime('2026-01-07 14:30:25', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame('January 7, 2026', DateFormatHelper::formatDate($date, pluginHandle: 'test-plugin'));
        self::assertSame('2:30:25 PM', DateFormatHelper::formatTime($date, pluginHandle: 'test-plugin'));
        self::assertSame(
            'January 7, 2026 2:30:25 PM',
            DateFormatHelper::formatDatetime($date, pluginHandle: 'test-plugin'),
        );
    }

    /**
     * @since 5.26.0
     */
    public function testCompactDatetimeCanFormatFromResolvedSettingsObject(): void
    {
        $settings = (object) [
            'timeFormat' => '24',
            'dateOrder' => 'ymd',
            'monthFormat' => 'long',
            'dateSeparator' => '-',
            'showSeconds' => false,
        ];

        $date = new DateTime('2026-05-25 18:57:00', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame(
            'May 25 18:57',
            DateFormatHelper::formatCompactDatetimeFromSettings($date, $settings, false, false),
        );
    }

    /**
     * @since 5.27.0
     */
    public function testCompactDatetimeFromSettingsTreatsLongMonthsAsShort(): void
    {
        $settings = (object) [
            'timeFormat' => '24',
            'dateOrder' => 'dmy',
            'monthFormat' => 'long',
            'dateSeparator' => '/',
            'showSeconds' => false,
        ];

        $date = new DateTime('2026-06-16 00:00:00', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame(
            '16 Jun 00:00',
            DateFormatHelper::formatCompactDatetimeFromSettings($date, $settings, false, false),
        );
    }

    /**
     * @since 5.26.0
     */
    public function testCompactDatetimeFromSettingsCanShowNumericDatesAndSeconds(): void
    {
        $settings = (object) [
            'timeFormat' => '12',
            'dateOrder' => 'dmy',
            'monthFormat' => 'numeric',
            'dateSeparator' => '-',
            'showSeconds' => true,
        ];

        $date = new DateTime('2026-05-25 18:57:14', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame(
            '25-05 6:57:14 PM',
            DateFormatHelper::formatCompactDatetimeFromSettings($date, $settings, null, false),
        );
    }

    /**
     * @since 5.27.0
     */
    public function testCompactDatetimeFromSettingsFallsBackToPluginCascadeConfig(): void
    {
        $this->setDateFormatConfig([
            'timeFormat' => '24',
            'dateOrder' => 'dmy',
            'monthFormat' => 'short',
            'dateSeparator' => '/',
            'showSeconds' => true,
        ], 'test-plugin');

        $settings = (object) [
            'timeFormat' => null,
            'dateOrder' => null,
            'monthFormat' => null,
            'dateSeparator' => null,
            'showSeconds' => null,
        ];

        $date = new DateTime('2026-06-16 00:00:00', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame(
            '16 Jun 00:00:00',
            DateFormatHelper::formatCompactDatetimeFromSettings(
                $date,
                $settings,
                null,
                false,
                pluginHandle: 'test-plugin',
            ),
        );
    }

    /**
     * @since 5.26.0
     */
    public function testCompactDatetimeFromSettingsCanIncludeYear(): void
    {
        $settings = (object) [
            'timeFormat' => '24',
            'dateOrder' => 'ymd',
            'monthFormat' => 'long',
            'dateSeparator' => '-',
            'showSeconds' => false,
        ];

        $date = new DateTime('2027-05-27 11:14:00', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame(
            '2027 May 27 11:14',
            DateFormatHelper::formatCompactDatetimeFromSettings($date, $settings, false, false, true),
        );
    }

    /**
     * @since 5.26.0
     */
    public function testCanClearOnePluginConfigCacheEntry(): void
    {
        $cache = new ReflectionClass(DateFormatHelper::class);
        $configProperty = $cache->getProperty('configCache');

        $this->setDateFormatConfig([
            'timeFormat' => '12',
            'dateOrder' => 'mdy',
            'monthFormat' => 'short',
            'dateSeparator' => '/',
            'showSeconds' => false,
        ], 'plugin-a');

        $this->setDateFormatConfig([
            'timeFormat' => '24',
            'dateOrder' => 'dmy',
            'monthFormat' => 'long',
            'dateSeparator' => '-',
            'showSeconds' => true,
        ], 'plugin-b');

        DateFormatHelper::clearConfigCache('plugin-a');

        $config = $configProperty->getValue();
        self::assertArrayNotHasKey('plugin-a', $config);
        self::assertArrayHasKey('plugin-b', $config);
        self::assertSame('24', $config['plugin-b']['timeFormat']);
    }

    public function testShowSecondsStaysOrthogonalToDisplayStyle(): void
    {
        $this->setDateFormatConfig([
            'timeFormat' => '24',
            'dateOrder' => 'ymd',
            'monthFormat' => 'numeric',
            'dateSeparator' => '-',
            'showSeconds' => true,
        ]);

        $date = new DateTime('2026-05-30 15:45:32', new DateTimeZone(Craft::$app->getTimeZone()));

        self::assertSame('15:45:32', DateFormatHelper::formatTime($date, 'cascade'));
        self::assertSame('15:45:32', DateFormatHelper::formatTime($date, 'short'));
        self::assertSame('15:45:32', DateFormatHelper::formatTime($date, 'medium'));
        self::assertSame('15:45:32', DateFormatHelper::formatTime($date, 'long'));
        self::assertSame('15:45', DateFormatHelper::formatTime($date, 'long', false));
    }

    public function testFormatDateUnsupportedStyleSilentlyFallsBackToCascade(): void
    {
        $this->setDateFormatConfig([
            'timeFormat' => '24',
            'dateOrder' => 'dmy',
            'monthFormat' => 'long',
            'dateSeparator' => '/',
            'showSeconds' => false,
        ]);

        $date = new DateTime('2026-05-16 15:45:00', new DateTimeZone(Craft::$app->getTimeZone()));
        $cascade = DateFormatHelper::formatDate($date, 'cascade');
        $full = DateFormatHelper::formatDate($date, 'full');

        // The display style set is exactly 'cascade' / 'short' / 'medium' / 'long'.
        // Unsupported values remain non-fatal in Twig-heavy display contexts and
        // fall back to cascade-driven output.
        self::assertNotNull($cascade);
        self::assertSame($cascade, $full, "passing an unsupported style must silently fall back to 'cascade'");
    }

    public function testToFilenameStringFormatIsYearMonthDayHis(): void
    {
        // Without time → just the date.
        $dateOnly = DateFormatHelper::toFilenameString(
            new DateTime('2026-05-16 15:45:32', new DateTimeZone('UTC')),
            includeTime: false,
        );
        self::assertSame('2026-05-16', $dateOnly);

        // With time → filename-safe `Y-m-d-His`. Hours/minutes/seconds depend
        // on the active Craft timezone, so assert the shape and the date,
        // not the exact hour digits.
        $withTime = DateFormatHelper::toFilenameString(
            new DateTime('2026-05-16 15:45:32', new DateTimeZone('UTC')),
        );
        self::assertMatchesRegularExpression('/^2026-05-1[56]-\d{6}$/', $withTime);

        // Default (no args) emits the same shape, anchored on "now".
        $now = DateFormatHelper::toFilenameString();
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}-\d{6}$/', $now);
    }

    public function testCodeDefaultsKickInWhenConfigFileMissing(): void
    {
        // The on-disk config sets monthFormat=short / dateOrder=dmy, so the
        // documented "code defaults" only show through when the config is
        // absent. Force-empty the static cache via reflection to simulate the
        // no-config-file path. The cache key is '__base__' when no plugin is
        // active (auto-detected from the controller, which is null in tests).
        $this->setDateFormatConfig([]);

        self::assertSame('24', DateFormatHelper::getTimeFormat(), 'timeFormat default must be 24');
        self::assertSame('ymd', DateFormatHelper::getDateOrder(), 'dateOrder default must be ymd');
        self::assertSame('numeric', DateFormatHelper::getMonthFormat(), 'monthFormat default must be numeric');
        self::assertSame('/', DateFormatHelper::getDateSeparator(), 'dateSeparator default must be /');
        self::assertFalse(DateFormatHelper::getShowSeconds(), 'showSeconds default must be false');

        // tearDown clears the cache so subsequent tests re-read the real file.
    }
}
