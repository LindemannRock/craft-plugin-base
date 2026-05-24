<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use DateTime;
use InvalidArgumentException;

/**
 * Schedule Helper
 *
 * Centralizes recurring-job scheduling. Maps a schedule identifier
 * (e.g. 'daily2am', 'weekly') to its next-run DateTime using fixed time
 * slots, so successive runs don't drift.
 *
 * Schedule identifiers:
 * - 'disabled'       — no automatic schedule
 * - 'every15minutes' — every 15 minutes
 * - 'every30minutes' — every 30 minutes
 * - 'hourly'         — every hour
 * - 'every2hours'    — 00:00, 02:00, 04:00, ...
 * - 'every3hours'    — 00:00, 03:00, 06:00, ...
 * - 'every4hours'    — 00:00, 04:00, 08:00, ...
 * - 'every6hours'    — 00:00, 06:00, 12:00, 18:00
 * - 'every12hours'   — 00:00, 12:00
 * - 'daily'          — 00:00
 * - 'daily2am'       — 02:00
 * - 'weekly'         — configured Craft week-start day at 00:00
 * - 'every2weeks'    — same weekday + time as the starting point, +2 weeks
 * - 'monthly'        — same day-of-month + time as the starting point
 * - 'every2months'   — same as monthly, +2
 * - 'quarterly'      — same as monthly, +3
 * - 'every6months'   — same as monthly, +6
 * - 'yearly'         — same as monthly, +12
 *
 * @since 5.25.0
 */
class ScheduleHelper
{
    /**
     * Schedule labels keyed by canonical schedule identifier.
     */
    private const LABELS = [
        'disabled' => 'Disabled',
        'every15minutes' => 'Every 15 Minutes',
        'every30minutes' => 'Every 30 Minutes',
        'hourly' => 'Hourly',
        'every2hours' => 'Every 2 Hours',
        'every3hours' => 'Every 3 Hours',
        'every4hours' => 'Every 4 Hours',
        'every6hours' => 'Every 6 Hours',
        'every12hours' => 'Every 12 Hours',
        'daily' => 'Daily',
        'daily2am' => 'Daily at 2:00 AM',
        'weekly' => 'Weekly',
        'every2weeks' => 'Every 2 Weeks',
        'monthly' => 'Monthly',
        'every2months' => 'Every 2 Months',
        'quarterly' => 'Quarterly',
        'every6months' => 'Every 6 Months',
        'yearly' => 'Yearly',
    ];

    /**
     * Get standard schedule options for dropdowns.
     *
     * Returns an array of options suitable for Twig templates. Pass an
     * explicit value list for curated plugin UIs; omit it to get every
     * canonical option.
     *
     * @param array<string>|string $valuesOrFormat Schedule values, or 'array'/'assoc' for backwards compatibility
     * @param string $format 'array' returns [{value, label}], 'assoc' returns {value: label}
     * @return array
     */
    public static function getOptions(array|string $valuesOrFormat = 'array', string $format = 'array'): array
    {
        if (is_string($valuesOrFormat)) {
            $format = $valuesOrFormat;
            $values = array_keys(self::LABELS);
        } else {
            $values = $valuesOrFormat;
        }

        $options = [];
        foreach ($values as $value) {
            if (!array_key_exists($value, self::LABELS)) {
                throw new InvalidArgumentException(sprintf('Unknown schedule value "%s".', $value));
            }

            $options[$value] = Craft::t('lindemannrock-base', self::LABELS[$value]);
        }

        if ($format === 'assoc') {
            return $options;
        }

        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    /**
     * Get the list of valid schedule identifiers.
     *
     * Useful for `in` validators on Settings models.
     *
     * @param array<string>|null $values Optional curated values to validate
     * @return string[]
     */
    public static function getValidValues(?array $values = null): array
    {
        if ($values === null) {
            return array_keys(self::LABELS);
        }

        self::getOptions($values, 'assoc');
        return $values;
    }

    /**
     * Calculate the next scheduled DateTime for a schedule identifier.
     *
     * Returns null when the schedule is 'disabled' or unknown. When
     * `$from` is omitted, "now" is taken in Craft's app timezone so
     * fixed-hour schedules (`daily2am` etc.) land on the wall-clock
     * hour the user expects, not on PHP's `date.timezone` default.
     *
     * @param string $schedule Schedule identifier (see class docblock)
     * @param DateTime|null $from Starting point (defaults to now in app TZ)
     * @return DateTime|null
     */
    public static function calculateNext(string $schedule, ?DateTime $from = null): ?DateTime
    {
        if ($schedule === 'disabled') {
            return null;
        }

        $from = $from ?? DateFormatHelper::now();

        return match ($schedule) {
            'every15minutes' => self::getNextMinuteInterval($from, 15),
            'every30minutes' => self::getNextMinuteInterval($from, 30),
            'hourly' => self::getNextMinuteInterval($from, 60),
            'every2hours' => self::getNextFixedHour($from, [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22]),
            'every3hours' => self::getNextFixedHour($from, [0, 3, 6, 9, 12, 15, 18, 21]),
            'every4hours' => self::getNextFixedHour($from, [0, 4, 8, 12, 16, 20]),
            'every6hours' => self::getNextFixedHour($from, [0, 6, 12, 18]),
            'every12hours' => self::getNextFixedHour($from, [0, 12]),
            'daily' => self::getNextFixedHour($from, [0]),
            'daily2am' => self::getNextFixedHour($from, [2]),
            'weekly' => self::getNextWeekday($from, DateRangeHelper::getWeekStartIsoDay()),
            'every2weeks' => (clone $from)->modify('+2 weeks'),
            'monthly' => self::addMonthsClamped($from, 1),
            'every2months' => self::addMonthsClamped($from, 2),
            'quarterly' => self::addMonthsClamped($from, 3),
            'every6months' => self::addMonthsClamped($from, 6),
            'yearly' => self::addMonthsClamped($from, 12),
            default => null,
        };
    }

    /**
     * Get the next occurrence of a minute interval.
     */
    private static function getNextMinuteInterval(DateTime $from, int $minutes): DateTime
    {
        $next = (clone $from)->setTime(
            (int) $from->format('G'),
            (int) floor((int) $from->format('i') / $minutes) * $minutes,
            0
        );

        while ($next <= $from) {
            $next->modify("+{$minutes} minutes");
        }

        return $next;
    }

    /**
     * Calculate seconds from $from (or now) until the next scheduled run.
     *
     * Returns 0 when the schedule is 'disabled' or unknown — callers
     * should treat 0 as "do not enqueue".
     *
     * @param string $schedule Schedule identifier
     * @param DateTime|null $from Starting point (defaults to now)
     * @return int Seconds until next run, or 0 to skip
     */
    public static function calculateDelaySeconds(string $schedule, ?DateTime $from = null): int
    {
        $next = self::calculateNext($schedule, $from);
        if ($next === null) {
            return 0;
        }

        $from = $from ?? DateFormatHelper::now();
        $delay = $next->getTimestamp() - $from->getTimestamp();

        return max(0, $delay);
    }

    /**
     * Add months while keeping end-of-month schedules valid.
     *
     * Example: starting from Jan 31, +1 month lands on Feb 28/29 instead
     * of overflowing into March.
     */
    private static function addMonthsClamped(DateTime $from, int $months): DateTime
    {
        $target = clone $from;
        $day = (int) $target->format('j');
        $time = $target->format('H:i:s');

        $target->modify('first day of this month');
        $target->modify("+{$months} months");

        $lastDay = (int) $target->format('t');
        $target->setDate(
            (int) $target->format('Y'),
            (int) $target->format('n'),
            min($day, $lastDay)
        );

        [$hour, $minute, $second] = array_map('intval', explode(':', $time));
        $target->setTime($hour, $minute, $second);

        return $target;
    }

    /**
     * Get next occurrence of one of the given fixed hours.
     *
     * @param int[] $hours Valid hours (0-23)
     */
    private static function getNextFixedHour(DateTime $from, array $hours): DateTime
    {
        $currentHour = (int) $from->format('G');

        foreach ($hours as $hour) {
            if ($hour > $currentHour || ($hour === $currentHour && (int) $from->format('i') === 0 && (int) $from->format('s') === 0)) {
                if ($hour === $currentHour) {
                    continue;
                }
                return (clone $from)->setTime($hour, 0, 0);
            }
        }

        return (clone $from)->modify('+1 day')->setTime($hours[0], 0, 0);
    }

    /**
     * Get next occurrence of an ISO weekday (1=Monday, 7=Sunday).
     */
    private static function getNextWeekday(DateTime $from, int $weekday): DateTime
    {
        $next = (clone $from)->setTime(0, 0, 0);
        $currentWeekday = (int) $from->format('N');

        if ($currentWeekday === $weekday && $from->format('H:i:s') === '00:00:00') {
            $next->modify('+1 week');
        } elseif ($currentWeekday >= $weekday) {
            $daysUntil = 7 - $currentWeekday + $weekday;
            $next->modify("+{$daysUntil} days");
        } else {
            $daysUntil = $weekday - $currentWeekday;
            $next->modify("+{$daysUntil} days");
        }

        return $next;
    }
}
