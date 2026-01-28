<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\DateTimeHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * DateTime Twig Extension
 *
 * Provides Twig filters and functions for date/time formatting.
 * All filters use the centralized DateTimeHelper which respects
 * config/lindemannrock-base.php settings.
 *
 * Filters:
 * ```twig
 * {{ date|lrDatetime }}              {# 22/01/2026 15:45 #}
 * {{ date|lrDatetime('long') }}      {# 22 January 2026 at 15:45 #}
 * {{ date|lrDate }}                  {# 22/01/2026 #}
 * {{ date|lrDate('long') }}          {# 22 January 2026 #}
 * {{ date|lrTime }}                  {# 15:45 #}
 * {{ date|lrTime('short', true) }}   {# 15:45:32 (with seconds) #}
 * {{ date|lrShortDate }}             {# Jan 22 #}
 * {{ date|lrRelative }}              {# 2 hours ago #}
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class DateTimeExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock DateTime';
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            // Display formatting
            new TwigFilter('lrDatetime', [$this, 'formatDatetime']),
            new TwigFilter('lrCompactDatetime', [$this, 'formatCompactDatetime']),
            new TwigFilter('lrDate', [$this, 'formatDate']),
            new TwigFilter('lrTime', [$this, 'formatTime']),
            new TwigFilter('lrShortDate', [$this, 'formatShortDate']),
            new TwigFilter('lrRelative', [$this, 'formatRelative']),

            // Database/API formatting
            new TwigFilter('lrForDatabase', [$this, 'forDatabase']),
            new TwigFilter('lrForApi', [$this, 'forApi']),
            new TwigFilter('lrForFilename', [$this, 'forFilename']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrNow', [DateTimeHelper::class, 'now']),
            new TwigFunction('lrIsToday', [DateTimeHelper::class, 'isToday']),
            new TwigFunction('lrIsPast', [DateTimeHelper::class, 'isPast']),
            new TwigFunction('lrIsFuture', [DateTimeHelper::class, 'isFuture']),
        ];
    }

    // =========================================================================
    // FILTER METHODS
    // =========================================================================

    /**
     * Format datetime for display
     *
     * @param mixed $date
     * @param string $length 'short', 'medium', 'long'
     * @param bool|null $showSeconds
     * @param bool $includeYear Whether to include year in output
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatDatetime(
        mixed $date,
        string $length = 'short',
        ?bool $showSeconds = null,
        bool $includeYear = true,
        bool $isUtc = true,
    ): ?string {
        return DateTimeHelper::formatDatetime($date, $length, $showSeconds, $includeYear, $isUtc);
    }

    /**
     * Format compact datetime (no year) for dashboards/recent activity
     *
     * @param mixed $date
     * @param bool|null $showSeconds
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatCompactDatetime(mixed $date, ?bool $showSeconds = null, bool $isUtc = true): ?string
    {
        return DateTimeHelper::formatCompactDatetime($date, $showSeconds, $isUtc);
    }

    /**
     * Format date for display
     *
     * @param mixed $date
     * @param string $length 'short', 'medium', 'long'
     * @param bool $includeYear Whether to include year in output
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatDate(
        mixed $date,
        string $length = 'short',
        bool $includeYear = true,
        bool $isUtc = true,
    ): ?string {
        return DateTimeHelper::formatDate($date, $length, $includeYear, $isUtc);
    }

    /**
     * Format time for display
     *
     * @param mixed $date
     * @param string $length 'short', 'medium', 'long'
     * @param bool|null $showSeconds
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatTime(
        mixed $date,
        string $length = 'short',
        ?bool $showSeconds = null,
        bool $isUtc = true,
    ): ?string {
        return DateTimeHelper::formatTime($date, $length, $showSeconds, $isUtc);
    }

    /**
     * Format short date for charts
     *
     * @param mixed $date
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatShortDate(mixed $date, bool $isUtc = true): ?string
    {
        return DateTimeHelper::formatShortDate($date, $isUtc);
    }

    /**
     * Format relative time
     *
     * @param mixed $date
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     * @since 5.8.0
     */
    public function formatRelative(mixed $date, bool $isUtc = true): ?string
    {
        return DateTimeHelper::formatRelative($date, $isUtc);
    }

    /**
     * Format for database
     *
     * @param mixed $date
     * @return string|null
     * @since 5.8.0
     */
    public function forDatabase(mixed $date): ?string
    {
        return DateTimeHelper::forDatabase($date);
    }

    /**
     * Format for API (ISO 8601)
     *
     * @param mixed $date
     * @return string|null
     * @since 5.8.0
     */
    public function forApi(mixed $date): ?string
    {
        return DateTimeHelper::forApi($date);
    }

    /**
     * Format for filename
     *
     * @param mixed $date
     * @param bool $includeTime
     * @return string
     * @since 5.8.0
     */
    public function forFilename(mixed $date = null, bool $includeTime = true): string
    {
        return DateTimeHelper::forFilename($date, $includeTime);
    }
}
