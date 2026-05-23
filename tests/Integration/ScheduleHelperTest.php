<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use lindemannrock\base\helpers\ScheduleHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see ScheduleHelper}.
 *
 * ScheduleHelper is NOT a cron expression evaluator — it maps a fixed
 * allowlist of identifiers (`daily2am`, `weekly`, `monthly`, …) to the
 * next concrete DateTime. The two contracts worth pinning:
 *
 *   1. The next-run DateTime respects the wall clock of the supplied
 *      `$from`'s timezone — fixed-hour slots like `daily2am` must land at
 *      02:00 local, not 02:00 UTC.
 *   2. Anything outside the allowlist (free-form strings, cron expressions)
 *      yields null, so callers can't sneak arbitrary schedules in.
 *
 * @since 5.25.0
 */
final class ScheduleHelperTest extends IntegrationTestCase
{
    public function testCalculateNextRespectsSuppliedTimezone(): void
    {
        $tz = new DateTimeZone('Asia/Riyadh');
        $from = new DateTime('2026-05-16 01:00:00', $tz);

        $next = ScheduleHelper::calculateNext('daily2am', $from);

        self::assertNotNull($next);
        // Next 02:00 after 01:00 same day is 02:00 same day in Riyadh.
        self::assertSame('Asia/Riyadh', $next->getTimezone()->getName());
        self::assertSame('2026-05-16 02:00:00', $next->format('Y-m-d H:i:s'));

        // After 02:00 (say 03:00) the next 02:00 slot is the following day.
        $afterSlot = new DateTime('2026-05-16 03:00:00', $tz);
        $nextDay = ScheduleHelper::calculateNext('daily2am', $afterSlot);
        self::assertNotNull($nextDay);
        self::assertSame('2026-05-17 02:00:00', $nextDay->format('Y-m-d H:i:s'));
    }

    public function testCalculateNextReturnsNullForDisabledAndAnythingOutsideTheAllowlist(): void
    {
        $from = new DateTime('2026-05-16 12:00:00', new DateTimeZone('UTC'));

        // Documented "no schedule" identifier.
        self::assertNull(ScheduleHelper::calculateNext('disabled', $from));

        // Free-form / cron-style strings are NOT accepted — a future caller
        // can't pipe `* * * * *` or arbitrary identifiers through here.
        self::assertNull(ScheduleHelper::calculateNext('* * * * *', $from));
        self::assertNull(ScheduleHelper::calculateNext('0 2 * * *', $from));
        self::assertNull(ScheduleHelper::calculateNext('arbitrary', $from));
        self::assertNull(ScheduleHelper::calculateNext('', $from));

        // calculateDelaySeconds wraps calculateNext; same contract — anything
        // unknown yields 0 ("do not enqueue") rather than a fallback delay.
        self::assertSame(0, ScheduleHelper::calculateDelaySeconds('disabled', $from));
        self::assertSame(0, ScheduleHelper::calculateDelaySeconds('arbitrary', $from));

        // The valid set is the documented allowlist. Pin the count so a future
        // addition shows up in this test and can be evaluated as an intentional
        // schedule, not a typo.
        $valid = ScheduleHelper::getValidValues();
        self::assertContains('every15minutes', $valid);
        self::assertContains('hourly', $valid);
        self::assertContains('daily2am', $valid);
        self::assertContains('disabled', $valid);
        self::assertContains('every2weeks', $valid);
        self::assertContains('yearly', $valid);
        self::assertCount(18, $valid);
    }

    public function testMinuteAndHourIntervalsUseTheNextFixedSlot(): void
    {
        $from = new DateTime('2026-05-16 10:15:00', new DateTimeZone('UTC'));

        $nextQuarter = ScheduleHelper::calculateNext('every15minutes', $from);
        self::assertNotNull($nextQuarter);
        self::assertSame('2026-05-16 10:30:00', $nextQuarter->format('Y-m-d H:i:s'));

        $afterQuarter = new DateTime('2026-05-16 10:16:00', new DateTimeZone('UTC'));
        $nextHalf = ScheduleHelper::calculateNext('every30minutes', $afterQuarter);
        self::assertNotNull($nextHalf);
        self::assertSame('2026-05-16 10:30:00', $nextHalf->format('Y-m-d H:i:s'));

        $nextTwoHours = ScheduleHelper::calculateNext('every2hours', $from);
        self::assertNotNull($nextTwoHours);
        self::assertSame('2026-05-16 12:00:00', $nextTwoHours->format('Y-m-d H:i:s'));
    }

    public function testCuratedOptionsPreserveRequestedOrderAndRejectUnknownValues(): void
    {
        $options = ScheduleHelper::getOptions(['daily', 'disabled', 'weekly']);

        self::assertSame([
            ['value' => 'daily', 'label' => 'Daily'],
            ['value' => 'disabled', 'label' => 'Disabled'],
            ['value' => 'weekly', 'label' => 'Weekly'],
        ], $options);

        self::assertSame(
            ['hourly' => 'Hourly', 'monthly' => 'Monthly'],
            ScheduleHelper::getOptions(['hourly', 'monthly'], 'assoc')
        );

        self::assertSame(
            ['every15minutes', 'daily2am'],
            ScheduleHelper::getValidValues(['every15minutes', 'daily2am'])
        );

        $this->expectException(InvalidArgumentException::class);
        ScheduleHelper::getOptions(['daily', 'manual']);
    }
}
