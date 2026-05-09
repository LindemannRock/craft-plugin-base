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
 * @since 5.14.0
 */
class DateRangeHelper
{
    /**
     * Get the default date range from config.
     *
     * Resolution order:
     * 1. Plugin Settings model: $settings->defaultDateRange (when the property
     *    exists). Plugins using SettingsConfigTrait already merge config-file
     *    overrides onto the model, so this single read encodes config-wins →
     *    UI value → property default.
     * 2. Plugin config file: defaultDateRange (root level) — fallback for
     *    plugins that don't yet expose the setting on their Settings model.
     * 3. Plugin config file: analytics.defaultDateRange (legacy/backwards compat)
     * 4. Base config: defaultDateRange (root level)
     * 5. Base config: analytics.defaultDateRange (legacy)
     * 6. Hardcoded default: 'last30days'
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return string The default date range (e.g., 'last30days')
     */
    public static function getDefaultDateRange(?string $pluginHandle = null): string
    {
        if ($pluginHandle) {
            // Prefer the plugin's Settings model when the property exists.
            // SettingsConfigTrait ensures config-file values already override
            // the saved/UI value on the model.
            $plugin = Craft::$app->plugins->getPlugin($pluginHandle);
            if ($plugin !== null) {
                $settings = $plugin->getSettings();
                if ($settings !== null && property_exists($settings, 'defaultDateRange')) {
                    $value = $settings->defaultDateRange;
                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }
            }

            // Fallback for plugins that haven't added defaultDateRange to
            // their Settings model yet — read directly from the config file.
            $pluginConfig = Craft::$app->getConfig()->getConfigFromFile($pluginHandle);
            if (isset($pluginConfig['defaultDateRange'])) {
                return $pluginConfig['defaultDateRange'];
            }
            if (isset($pluginConfig['analytics']['defaultDateRange'])) {
                return $pluginConfig['analytics']['defaultDateRange'];
            }
        }

        // Fall back to base config
        $config = Craft::$app->getConfig()->getConfigFromFile('lindemannrock-base');
        if (isset($config['defaultDateRange'])) {
            return $config['defaultDateRange'];
        }
        return $config['analytics']['defaultDateRange'] ?? 'last30days';
    }

    /**
     * Get standard date range options for dropdowns.
     *
     * Returns an array of options suitable for Twig templates.
     *
     * @param string $format 'array' returns [{value, label}], 'assoc' returns {value: label}
     * @param bool $includeCustom Whether to include a Custom Range option
     * @return array
     */
    public static function getOptions(string $format = 'array', bool $includeCustom = false): array
    {
        $options = [
            'today' => Craft::t('lindemannrock-base', 'Today'),
            'yesterday' => Craft::t('lindemannrock-base', 'Yesterday'),
            'last7days' => Craft::t('lindemannrock-base', 'Last 7 days'),
            'last30days' => Craft::t('lindemannrock-base', 'Last 30 days'),
            'last90days' => Craft::t('lindemannrock-base', 'Last 90 days'),
            'thisMonth' => Craft::t('lindemannrock-base', 'This month'),
            'lastMonth' => Craft::t('lindemannrock-base', 'Last month'),
            'thisYear' => Craft::t('lindemannrock-base', 'This year'),
            'lastYear' => Craft::t('lindemannrock-base', 'Last year'),
            'all' => Craft::t('lindemannrock-base', 'All time'),
        ];

        if ($includeCustom) {
            $options['custom'] = Craft::t('lindemannrock-base', 'Custom Range');
        }

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
    public static function getBounds(
        string $dateRange,
        ?\DateTimeZone $tz = null,
        \DateTime|string|null $customStart = null,
        \DateTime|string|null $customEnd = null,
    ): array {
        $dateRange = self::normalize($dateRange);
        $tz = $tz ?? new \DateTimeZone(Craft::$app->getTimeZone());

        $start = null;
        $end = null;

        switch ($dateRange) {
            case 'custom':
                $start = self::normalizeCustomDate($customStart, $tz);
                $start?->setTime(0, 0, 0);

                $end = self::normalizeCustomDate($customEnd, $tz);
                $end?->modify('+1 day')->setTime(0, 0, 0);
                break;
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

        if (!$start && !$end) {
            return ['start' => null, 'end' => null];
        }

        if ($start) {
            $start->setTimezone(new \DateTimeZone('UTC'));
        }
        if ($end) {
            $end->setTimezone(new \DateTimeZone('UTC'));
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Apply a date range filter to a query.
     */
    public static function applyToQuery(
        Query $query,
        string $dateRange,
        string $column = 'dateCreated',
        ?\DateTimeZone $tz = null,
        \DateTime|string|null $customStart = null,
        \DateTime|string|null $customEnd = null,
    ): void {
        $bounds = self::getBounds($dateRange, $tz, $customStart, $customEnd);

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

    private static function normalizeCustomDate(\DateTime|string|null $date, \DateTimeZone $tz): ?\DateTime
    {
        if ($date instanceof \DateTime) {
            $date = clone $date;
            $date->setTimezone($tz);

            return $date;
        }

        if (!is_string($date) || trim($date) === '') {
            return null;
        }

        return new \DateTime($date, $tz);
    }
}
