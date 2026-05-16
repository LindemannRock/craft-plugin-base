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

/**
 * Schedule Helper
 *
 * Centralizes recurring-job scheduling. Maps a schedule identifier
 * (e.g. 'daily2am', 'weekly') to its next-run DateTime using fixed time
 * slots, so successive runs don't drift.
 *
 * Schedule identifiers:
 * - 'disabled'       — no automatic schedule
 * - 'every6hours'    — 00:00, 06:00, 12:00, 18:00
 * - 'every12hours'   — 00:00, 12:00
 * - 'daily'          — 00:00
 * - 'daily2am'       — 02:00
 * - 'weekly'         — configured Craft week-start day at 00:00
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
     * Get standard schedule options for dropdowns.
     *
     * Returns an array of options suitable for Twig templates.
     *
     * @param string $format 'array' returns [{value, label}], 'assoc' returns {value: label}
     * @return array
     */
    public static function getOptions(string $format = 'array'): array
    {
        $options = [
            'disabled' => Craft::t('lindemannrock-base', 'Disabled'),
            'every6hours' => Craft::t('lindemannrock-base', 'Every 6 Hours'),
            'every12hours' => Craft::t('lindemannrock-base', 'Every 12 Hours'),
            'daily' => Craft::t('lindemannrock-base', 'Daily'),
            'daily2am' => Craft::t('lindemannrock-base', 'Daily at 2:00 AM'),
            'weekly' => Craft::t('lindemannrock-base', 'Weekly'),
            'monthly' => Craft::t('lindemannrock-base', 'Monthly'),
            'every2months' => Craft::t('lindemannrock-base', 'Every 2 Months'),
            'quarterly' => Craft::t('lindemannrock-base', 'Quarterly'),
            'every6months' => Craft::t('lindemannrock-base', 'Every 6 Months'),
            'yearly' => Craft::t('lindemannrock-base', 'Yearly'),
        ];

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
     * Get the list of valid schedule identifiers (excluding 'disabled').
     *
     * Useful for `in` validators on Settings models.
     *
     * @return string[]
     */
    public static function getValidValues(): array
    {
        return array_keys(self::getOptions('assoc'));
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
            'every6hours' => self::getNextFixedHour($from, [0, 6, 12, 18]),
            'every12hours' => self::getNextFixedHour($from, [0, 12]),
            'daily' => self::getNextFixedHour($from, [0]),
            'daily2am' => self::getNextFixedHour($from, [2]),
            'weekly' => self::getNextWeekday($from, self::getWeekStartIsoDay()),
            'monthly' => self::addMonthsClamped($from, 1),
            'every2months' => self::addMonthsClamped($from, 2),
            'quarterly' => self::addMonthsClamped($from, 3),
            'every6months' => self::addMonthsClamped($from, 6),
            'yearly' => self::addMonthsClamped($from, 12),
            default => null,
        };
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

    /**
     * Get the configured Craft week start day as an ISO weekday.
     *
     * Craft stores week start as 0=Sunday, 1=Monday, ..., 6=Saturday.
     * PHP's ISO weekday format uses 1=Monday, ..., 7=Sunday.
     */
    private static function getWeekStartIsoDay(): int
    {
        $craftWeekday = (int) Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay;
        $craftWeekday = max(0, min(6, $craftWeekday));

        return $craftWeekday === 0 ? 7 : $craftWeekday;
    }
}
