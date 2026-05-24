<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\DateRangeHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * DateTime Twig Extension
 *
 * Provides Twig filters and functions for date/time formatting.
 * All filters use the centralized DateFormatHelper which respects
 * config/lindemannrock-base.php settings.
 *
 * Filters:
 * ```twig
 * {{ date|lrDatetime }}              {# 22/01/2026 15:45 #}
 * {{ date|lrDatetime('long') }}      {# 22 January 2026 at 15:45 #}
 * {{ date|lrDate }}                  {# 22/01/2026 #}
 * {{ date|lrDate('long') }}          {# 22 January 2026 #}
 * {{ date|lrTime }}                  {# 15:45 #}
 * {{ date|lrTime('cascade', true) }} {# 15:45:32 (with seconds) #}
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
            new TwigFilter('lrRelative', [$this, 'formatRelative']),

            // Machine formatting
            new TwigFilter('lrToDateTimeString', [$this, 'toDateTimeString']),
            new TwigFilter('lrToApiString', [$this, 'toApiString']),
            new TwigFilter('lrToFilenameString', [$this, 'toFilenameString']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrNow', [DateFormatHelper::class, 'now']),
            new TwigFunction('lrIsToday', [DateFormatHelper::class, 'isToday']),
            new TwigFunction('lrIsPast', [DateFormatHelper::class, 'isPast']),
            new TwigFunction('lrIsFuture', [DateFormatHelper::class, 'isFuture']),
            new TwigFunction('lrDateFormatConfig', [DateFormatHelper::class, 'getConfig']),
            new TwigFunction('lrDefaultDateRange', [DateRangeHelper::class, 'getDefaultDateRange']),
            new TwigFunction('lrDateRangeOptions', [DateRangeHelper::class, 'getOptions']),
        ];
    }

    // =========================================================================
    // FILTER METHODS
    // =========================================================================

    /**
     * Format datetime for display
     *
     * @param mixed $date
     * @param string $style 'cascade', 'short', 'medium', 'long'
     * @param bool|null $showSeconds
     * @param bool $includeYear Whether to include year in output
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     */
    public function formatDatetime(
        mixed $date,
        string $style = 'cascade',
        ?bool $showSeconds = null,
        bool $includeYear = true,
        bool $isUtc = true,
    ): ?string {
        return DateFormatHelper::formatDatetime($date, $style, $showSeconds, $includeYear, $isUtc);
    }

    /**
     * Format compact datetime (no year) for dashboards/recent activity
     *
     * @param mixed $date
     * @param bool|null $showSeconds
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     */
    public function formatCompactDatetime(mixed $date, ?bool $showSeconds = null, bool $isUtc = true): ?string
    {
        return DateFormatHelper::formatCompactDatetime($date, $showSeconds, $isUtc);
    }

    /**
     * Format date for display
     *
     * @param mixed $date
     * @param string $style 'cascade', 'short', 'medium', 'long'
     * @param bool $includeYear Whether to include year in output
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     */
    public function formatDate(
        mixed $date,
        string $style = 'cascade',
        bool $includeYear = true,
        bool $isUtc = true,
    ): ?string {
        return DateFormatHelper::formatDate($date, $style, $includeYear, $isUtc);
    }

    /**
     * Format time for display
     *
     * @param mixed $date
     * @param string $style 'cascade', 'short', 'medium', 'long'
     * @param bool|null $showSeconds
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     */
    public function formatTime(
        mixed $date,
        string $style = 'cascade',
        ?bool $showSeconds = null,
        bool $isUtc = true,
    ): ?string {
        return DateFormatHelper::formatTime($date, $style, $showSeconds, $isUtc);
    }

    /**
     * Format relative time
     *
     * @param mixed $date
     * @param bool $isUtc Whether string timestamps are in UTC (true) or already in local time (false)
     * @return string|null
     */
    public function formatRelative(mixed $date, bool $isUtc = true): ?string
    {
        return DateFormatHelper::formatRelative($date, $isUtc);
    }

    /**
     * Format as datetime string (Y-m-d H:i:s)
     *
     * @param mixed $date
     * @return string|null
     */
    public function toDateTimeString(mixed $date): ?string
    {
        return DateFormatHelper::toDateTimeString($date);
    }

    /**
     * Format as ISO 8601 string
     *
     * @param mixed $date
     * @return string|null
     */
    public function toApiString(mixed $date): ?string
    {
        return DateFormatHelper::toApiString($date);
    }

    /**
     * Format as filename-safe string
     *
     * @param mixed $date
     * @param bool $includeTime
     * @return string
     */
    public function toFilenameString(mixed $date = null, bool $includeTime = true): string
    {
        return DateFormatHelper::toFilenameString($date, $includeTime);
    }
}
