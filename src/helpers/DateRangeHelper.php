<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\db\Query;
use craft\helpers\Db;

/**
 * Date Range Helper
 *
 * Centralizes date range parsing for analytics queries.
 *
 * @since 5.2.0
 */
class DateRangeHelper
{
    /**
     * Normalize a date range value.
     */
    public static function normalize(?string $dateRange, string $default = 'last7days'): string
    {
        $dateRange = $dateRange ?: $default;

        if ($dateRange === 'alltime') {
            return 'all';
        }

        return $dateRange;
    }

    /**
     * Return UTC date bounds for a date range.
     *
     * @return array{start: \DateTime|null, end: \DateTime|null}
     */
    public static function getBounds(string $dateRange, ?\DateTimeZone $tz = null): array
    {
        $dateRange = self::normalize($dateRange);
        $tz = $tz ?? new \DateTimeZone(Craft::$app->getTimeZone());

        $start = null;
        $end = null;

        switch ($dateRange) {
            case 'today':
                $start = new \DateTime('now', $tz);
                $start->setTime(0, 0, 0);
                break;
            case 'yesterday':
                $start = new \DateTime('now', $tz);
                $start->modify('-1 day')->setTime(0, 0, 0);

                $end = new \DateTime('now', $tz);
                $end->setTime(0, 0, 0);
                break;
            case 'last7days':
                $start = new \DateTime('now', $tz);
                $start->modify('-7 days');
                break;
            case 'last30days':
                $start = new \DateTime('now', $tz);
                $start->modify('-30 days');
                break;
            case 'last90days':
                $start = new \DateTime('now', $tz);
                $start->modify('-90 days');
                break;
            case 'all':
            case 'alltime':
            default:
                return ['start' => null, 'end' => null];
        }

        $start->setTimezone(new \DateTimeZone('UTC'));
        if ($end) {
            $end->setTimezone(new \DateTimeZone('UTC'));
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Apply a date range filter to a query.
     */
    public static function applyToQuery(Query $query, string $dateRange, string $column = 'dateCreated', ?\DateTimeZone $tz = null): void
    {
        $bounds = self::getBounds($dateRange, $tz);

        if ($bounds['start']) {
            $query->andWhere(['>=', $column, Db::prepareDateForDb($bounds['start'])]);
        }
        if ($bounds['end']) {
            $query->andWhere(['<', $column, Db::prepareDateForDb($bounds['end'])]);
        }
    }

    /**
     * Get number of days in a date range.
     *
     * Useful for calculating averages (e.g., "average clicks per day").
     *
     * @param string $dateRange
     * @return int
     * @since 5.2.0
     */
    public static function getDaysCount(string $dateRange): int
    {
        return match (self::normalize($dateRange)) {
            'today' => 1,
            'yesterday' => 1,
            'last7days' => 7,
            'last30days' => 30,
            'last90days' => 90,
            default => 30,
        };
    }
}
