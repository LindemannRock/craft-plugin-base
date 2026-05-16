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

    public function testFormatDateUnsupportedLengthSilentlyFallsBackToShort(): void
    {
        $date = new DateTime('2026-05-16 15:45:00', new DateTimeZone('UTC'));

        $short = DateFormatHelper::formatDate($date, 'short');
        $full = DateFormatHelper::formatDate($date, 'full');

        // The display length set is exactly 'short' / 'medium' / 'long'.
        // Anything else falls into the `default` arm of the inner `match`, so
        // 'full' silently produces 'short' output. Pin that behaviour so a
        // future maintainer thinking about adding 'full' notices the contract.
        self::assertNotNull($short);
        self::assertSame($short, $full, "passing an unsupported length must silently fall back to 'short'");
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
        // no-config-file path.
        $cache = new ReflectionClass(DateFormatHelper::class);
        $configProperty = $cache->getProperty('config');
        $configProperty->setValue(null, []);

        self::assertSame('24', DateFormatHelper::getTimeFormat(), 'timeFormat default must be 24');
        self::assertSame('ymd', DateFormatHelper::getDateOrder(), 'dateOrder default must be ymd');
        self::assertSame('numeric', DateFormatHelper::getMonthFormat(), 'monthFormat default must be numeric');
        self::assertSame('/', DateFormatHelper::getDateSeparator(), 'dateSeparator default must be /');
        self::assertFalse(DateFormatHelper::getShowSeconds(), 'showSeconds default must be false');

        // tearDown clears the cache so subsequent tests re-read the real file.
    }
}
