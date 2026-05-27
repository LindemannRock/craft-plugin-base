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
use DateTime;
use DateTimeZone;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\testing\IntegrationTestCase;
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
