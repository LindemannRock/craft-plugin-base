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
use lindemannrock\base\helpers\DateRangeHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see DateRangeHelper}.
 *
 * @since 5.26.0
 */
final class DateRangeHelperTest extends IntegrationTestCase
{
    private int $originalWeekStartDay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalWeekStartDay = (int) Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay;
    }

    protected function tearDown(): void
    {
        Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay = $this->originalWeekStartDay;
        parent::tearDown();
    }

    public function testWeekStartConvertsCraftSundayToIsoSunday(): void
    {
        Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay = 0;

        self::assertSame(7, DateRangeHelper::getWeekStartIsoDay());
    }

    public function testWeekStartKeepsCraftMondayAsIsoMonday(): void
    {
        Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay = 1;

        self::assertSame(1, DateRangeHelper::getWeekStartIsoDay());
    }

    public function testWeekBoundsRespectConfiguredCraftWeekStartDay(): void
    {
        Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay = 0;
        $tz = new DateTimeZone('Asia/Dubai');
        $expectedThisWeekStart = $this->expectedStartOfCurrentWeek($tz);
        $expectedLastWeekStart = (clone $expectedThisWeekStart)->modify('-7 days');

        $thisWeek = DateRangeHelper::getBounds('thisWeek', $tz);
        $lastWeek = DateRangeHelper::getBounds('lastWeek', $tz);

        self::assertNotNull($thisWeek['start']);
        self::assertNull($thisWeek['end']);
        self::assertSame($expectedThisWeekStart->format('Y-m-d H:i:s'), $thisWeek['start']->format('Y-m-d H:i:s'));

        self::assertNotNull($lastWeek['start']);
        self::assertNotNull($lastWeek['end']);
        self::assertSame($expectedLastWeekStart->format('Y-m-d H:i:s'), $lastWeek['start']->format('Y-m-d H:i:s'));
        self::assertSame($expectedThisWeekStart->format('Y-m-d H:i:s'), $lastWeek['end']->format('Y-m-d H:i:s'));
    }

    public function testAdditionalRangeDayCounts(): void
    {
        self::assertSame(7, DateRangeHelper::getDaysCount('lastWeek'));
        self::assertSame(14, DateRangeHelper::getDaysCount('last14days'));
        self::assertGreaterThanOrEqual(89, DateRangeHelper::getDaysCount('lastQuarter'));
        self::assertGreaterThanOrEqual(365, DateRangeHelper::getDaysCount('last12months'));
    }

    private function expectedStartOfCurrentWeek(DateTimeZone $tz): DateTime
    {
        $start = new DateTime('now', $tz);
        $currentWeekday = (int) $start->format('N');
        $daysSinceWeekStart = ($currentWeekday - DateRangeHelper::getWeekStartIsoDay() + 7) % 7;

        if ($daysSinceWeekStart > 0) {
            $start->modify("-{$daysSinceWeekStart} days");
        }

        return $start->setTime(0, 0, 0)->setTimezone(new DateTimeZone('UTC'));
    }
}
