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
     * Get the default date range from config.
     *
     * Resolution order:
     * 1. Plugin config: defaultDateRange (root level)
     * 2. Plugin config: analytics.defaultDateRange (legacy/backwards compat)
     * 3. Base config: defaultDateRange (root level)
     * 4. Base config: analytics.defaultDateRange (legacy)
     * 5. Hardcoded default: 'last30days'
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return string The default date range (e.g., 'last30days')
     * @since 5.3.0
     */
    public static function getDefaultDateRange(?string $pluginHandle = null): string
    {
        // Check plugin-specific config first
        if ($pluginHandle) {
            $pluginConfig = Craft::$app->getConfig()->getConfigFromFile($pluginHandle);
            // Root level takes priority
            if (isset($pluginConfig['defaultDateRange'])) {
                return $pluginConfig['defaultDateRange'];
            }
            // Legacy: analytics.defaultDateRange
            if (isset($pluginConfig['analytics']['defaultDateRange'])) {
                return $pluginConfig['analytics']['defaultDateRange'];
            }
        }

        // Fall back to base config
        $config = Craft::$app->getConfig()->getConfigFromFile('lindemannrock-base');
        // Root level takes priority
        if (isset($config['defaultDateRange'])) {
            return $config['defaultDateRange'];
        }
        // Legacy: analytics.defaultDateRange
        return $config['analytics']['defaultDateRange'] ?? 'last30days';
    }

    /**
     * Get standard date range options for dropdowns.
     *
     * Returns an array of options suitable for Twig templates.
     *
     * @param string $format 'array' returns [{value, label}], 'assoc' returns {value: label}
     * @return array
     * @since 5.3.0
     */
    public static function getOptions(string $format = 'array'): array
    {
        $options = [
            'today' => Craft::t('app', 'Today'),
            'yesterday' => Craft::t('app', 'Yesterday'),
            'last7days' => Craft::t('app', 'Last 7 days'),
            'last30days' => Craft::t('app', 'Last 30 days'),
            'last90days' => Craft::t('app', 'Last 90 days'),
            'thisMonth' => Craft::t('app', 'This month'),
            'lastMonth' => Craft::t('app', 'Last month'),
            'thisYear' => Craft::t('app', 'This year'),
            'lastYear' => Craft::t('app', 'Last year'),
            'all' => Craft::t('app', 'All time'),
        ];

        if ($format === 'assoc') {
            return $options;
        }

        // Return as array of {value, label} objects
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    /**
     * Normalize a date range value.
     *
     * @param string|null $dateRange The date range to normalize
     * @param string|null $default The default value (if null, uses config default)
     * @return string The normalized date range
     */
    public static function normalize(?string $dateRange, ?string $default = null): string
    {
        $default = $default ?? self::getDefaultDateRange();
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
            case 'thisMonth':
                $start = new \DateTime('now', $tz);
                $start->modify('first day of this month')->setTime(0, 0, 0);
                break;
            case 'lastMonth':
                $start = new \DateTime('now', $tz);
                $start->modify('first day of last month')->setTime(0, 0, 0);

                $end = new \DateTime('now', $tz);
                $end->modify('first day of this month')->setTime(0, 0, 0);
                break;
            case 'thisYear':
                $start = new \DateTime('now', $tz);
                $start->modify('first day of January this year')->setTime(0, 0, 0);
                break;
            case 'lastYear':
                $start = new \DateTime('now', $tz);
                $start->modify('first day of January last year')->setTime(0, 0, 0);

                $end = new \DateTime('now', $tz);
                $end->modify('first day of January this year')->setTime(0, 0, 0);
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
     */
    public static function getDaysCount(string $dateRange): int
    {
        $normalized = self::normalize($dateRange);

        // For dynamic ranges, calculate actual days using Craft's timezone
        $tz = new \DateTimeZone(\Craft::$app->getTimeZone());

        if ($normalized === 'thisMonth') {
            $now = new \DateTime('now', $tz);
            return (int) $now->format('j'); // Day of month (1-31)
        }

        if ($normalized === 'lastMonth') {
            $lastMonth = new \DateTime('first day of last month', $tz);
            return (int) $lastMonth->format('t'); // Days in month
        }

        if ($normalized === 'thisYear') {
            $now = new \DateTime('now', $tz);
            $startOfYear = new \DateTime('first day of January this year', $tz);
            return (int) $now->diff($startOfYear)->days + 1;
        }

        if ($normalized === 'lastYear') {
            $now = new \DateTime('now', $tz);
            $lastYear = (int) $now->format('Y') - 1;
            return ((($lastYear % 4 === 0) && ($lastYear % 100 !== 0)) || ($lastYear % 400 === 0)) ? 366 : 365;
        }

        return match ($normalized) {
            'today' => 1,
            'yesterday' => 1,
            'last7days' => 7,
            'last30days' => 30,
            'last90days' => 90,
            default => 30,
        };
    }
}
