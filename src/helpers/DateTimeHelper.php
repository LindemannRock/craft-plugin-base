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
use DateTimeZone;

/**
 * DateTime Helper
 *
 * Provides centralized date/time formatting for all LindemannRock plugins.
 * Respects Craft's timezone and configurable format preferences.
 *
 * Configuration via config/lindemannrock-base.php:
 * ```php
 * return [
 *     'timeFormat' => '24',        // '12' (AM/PM) or '24' (military)
 *     'monthFormat' => 'numeric',  // 'numeric' (01), 'short' (Jan), 'long' (January)
 *     'dateOrder' => 'dmy',        // 'dmy', 'mdy', 'ymd'
 *     'dateSeparator' => '/',      // '/', '-', '.' (only used with numeric months)
 *     'showSeconds' => false,      // Default for time display
 * ];
 * ```
 *
 * Usage:
 * ```php
 * use lindemannrock\base\helpers\DateTimeHelper;
 *
 * // Display formatting (uses Craft timezone + config)
 * DateTimeHelper::formatDatetime($date);              // "22/01/2026 15:45"
 * DateTimeHelper::formatDate($date);                  // "22/01/2026"
 * DateTimeHelper::formatTime($date);                  // "15:45"
 * DateTimeHelper::formatTime($date, showSeconds: true); // "15:45:32"
 *
 * // For database/API/filenames
 * DateTimeHelper::forDatabase($date);   // "2026-01-22 15:45:32"
 * DateTimeHelper::forApi($date);        // "2026-01-22T15:45:32+00:00"
 * DateTimeHelper::forFilename();        // "2026-01-22-154532"
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class DateTimeHelper
{
    /**
     * @var array|null Cached configuration
     */
    private static ?array $config = null;

    // =========================================================================
    // CONFIGURATION
    // =========================================================================

    /**
     * Get configuration from config/lindemannrock-base.php
     *
     * @return array
     * @since 5.8.0
     */
    public static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = Craft::$app->config->getConfigFromFile('lindemannrock-base') ?: [];
        }

        return self::$config;
    }

    /**
     * Get time format preference
     *
     * @return string '12' or '24'
     * @since 5.8.0
     */
    public static function getTimeFormat(): string
    {
        return self::getConfig()['timeFormat'] ?? '24';
    }

    /**
     * Get date order preference
     *
     * @return string 'dmy', 'mdy', or 'ymd'
     * @since 5.8.0
     */
    public static function getDateOrder(): string
    {
        return self::getConfig()['dateOrder'] ?? 'ymd';
    }

    /**
     * Get date separator preference
     *
     * @return string '/', '-', or '.'
     * @since 5.8.0
     */
    public static function getDateSeparator(): string
    {
        return self::getConfig()['dateSeparator'] ?? '/';
    }

    /**
     * Get default showSeconds preference
     *
     * @return bool
     * @since 5.8.0
     */
    public static function getShowSeconds(): bool
    {
        return self::getConfig()['showSeconds'] ?? false;
    }

    /**
     * Get month format preference
     *
     * @return string 'numeric', 'short', or 'long'
     * @since 5.8.0
     */
    public static function getMonthFormat(): string
    {
        return self::getConfig()['monthFormat'] ?? 'numeric';
    }

    /**
     * Clear cached config (useful for testing)
     *
     * @since 5.8.0
     */
    public static function clearConfigCache(): void
    {
        self::$config = null;
    }

    // =========================================================================
    // TIMEZONE CONVERSION
    // =========================================================================

    /**
     * Convert a date to Craft's configured timezone
     *
     * Handles:
     * - DateTime objects (assumes UTC if no timezone set)
     * - Date strings (assumes UTC)
     * - Null values
     *
     * @param DateTime|string|null $date
     * @return DateTime|null
     * @since 5.8.0
     */
    public static function toCraftTimezone(DateTime|string|null $date): ?DateTime
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date, new DateTimeZone('UTC'));
            } catch (\Exception) {
                return null;
            }
        }

        try {
            $craftTimezone = new DateTimeZone(Craft::$app->getTimeZone());
            $date->setTimezone($craftTimezone);
        } catch (\Exception) {
            // If timezone conversion fails, return date as-is
        }

        return $date;
    }

    // =========================================================================
    // DISPLAY FORMATTING
    // =========================================================================

    /**
     * Format datetime for display
     *
     * @param DateTime|string|null $date
     * @param string $length 'short', 'medium', 'long'
     * @param bool|null $showSeconds Override config default (null = use config)
     * @param bool $includeYear Whether to include year in output
     * @return string|null
     * @since 5.8.0
     */
    public static function formatDatetime(
        DateTime|string|null $date,
        string $length = 'short',
        ?bool $showSeconds = null,
        bool $includeYear = true,
    ): ?string {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return null;
        }

        $datePart = self::formatDate($date, $length, $includeYear);
        $timePart = self::formatTime($date, $length, $showSeconds);

        if ($length === 'long') {
            return $datePart . ' at ' . $timePart;
        }

        return $datePart . ' ' . $timePart;
    }

    /**
     * Format compact datetime (no year) for dashboards/recent activity
     *
     * @param DateTime|string|null $date
     * @param bool|null $showSeconds Override config default (null = use config)
     * @return string|null Example: "Jan 23, 15:45" or "23 Jan 15:45"
     * @since 5.8.0
     */
    public static function formatCompactDatetime(
        DateTime|string|null $date,
        ?bool $showSeconds = null,
    ): ?string {
        return self::formatDatetime($date, 'short', $showSeconds, false);
    }

    /**
     * Format date only for display
     *
     * @param DateTime|string|null $date
     * @param string $length 'short', 'medium', 'long'
     * @param bool $includeYear Whether to include year in output
     * @return string|null
     * @since 5.8.0
     */
    public static function formatDate(
        DateTime|string|null $date,
        string $length = 'short',
        bool $includeYear = true,
    ): ?string {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return null;
        }

        $order = self::getDateOrder();
        $sep = self::getDateSeparator();

        if ($length === 'long') {
            if ($includeYear) {
                $format = match ($order) {
                    'dmy' => 'j F Y',      // 22 January 2026
                    'mdy' => 'F j, Y',     // January 22, 2026
                    'ymd' => 'Y F j',      // 2026 January 22
                    default => 'j F Y',
                };
            } else {
                $format = match ($order) {
                    'dmy' => 'j F',        // 22 January
                    'mdy' => 'F j',        // January 22
                    'ymd' => 'F j',        // January 22 (no year = mdy style)
                    default => 'j F',
                };
            }
        } elseif ($length === 'medium') {
            if ($includeYear) {
                $format = match ($order) {
                    'dmy' => 'j M Y',      // 22 Jan 2026
                    'mdy' => 'M j, Y',     // Jan 22, 2026
                    'ymd' => 'Y M j',      // 2026 Jan 22
                    default => 'j M Y',
                };
            } else {
                $format = match ($order) {
                    'dmy' => 'j M',        // 22 Jan
                    'mdy' => 'M j',        // Jan 22
                    'ymd' => 'M j',        // Jan 22 (no year = mdy style)
                    default => 'j M',
                };
            }
        } else {
            // short - respects monthFormat config
            $monthFormat = self::getMonthFormat();

            if ($monthFormat === 'long') {
                // Full month name
                if ($includeYear) {
                    $format = match ($order) {
                        'dmy' => 'j F Y',      // 22 January 2026
                        'mdy' => 'F j, Y',     // January 22, 2026
                        'ymd' => 'Y F j',      // 2026 January 22
                        default => 'j F Y',
                    };
                } else {
                    $format = match ($order) {
                        'dmy' => 'j F',        // 22 January
                        'mdy' => 'F j',        // January 22
                        'ymd' => 'F j',        // January 22
                        default => 'j F',
                    };
                }
            } elseif ($monthFormat === 'short') {
                // Short month name
                if ($includeYear) {
                    $format = match ($order) {
                        'dmy' => 'j M Y',      // 22 Jan 2026
                        'mdy' => 'M j, Y',     // Jan 22, 2026
                        'ymd' => 'Y M j',      // 2026 Jan 22
                        default => 'j M Y',
                    };
                } else {
                    $format = match ($order) {
                        'dmy' => 'j M',        // 22 Jan
                        'mdy' => 'M j',        // Jan 22
                        'ymd' => 'M j',        // Jan 22
                        default => 'j M',
                    };
                }
            } else {
                // numeric (default)
                if ($includeYear) {
                    $format = match ($order) {
                        'dmy' => "d{$sep}m{$sep}Y",  // 22/01/2026
                        'mdy' => "m{$sep}d{$sep}Y",  // 01/22/2026
                        'ymd' => "Y{$sep}m{$sep}d",  // 2026/01/22
                        default => "d{$sep}m{$sep}Y",
                    };
                } else {
                    $format = match ($order) {
                        'dmy' => "d{$sep}m",         // 22/01
                        'mdy' => "m{$sep}d",         // 01/22
                        'ymd' => "m{$sep}d",         // 01/22
                        default => "d{$sep}m",
                    };
                }
            }
        }

        return $date->format($format);
    }

    /**
     * Format time only for display
     *
     * @param DateTime|string|null $date
     * @param string $length 'short', 'medium', 'long' (medium/long include seconds if showSeconds)
     * @param bool|null $showSeconds Override config default (null = use config)
     * @return string|null
     * @since 5.8.0
     */
    public static function formatTime(
        DateTime|string|null $date,
        string $length = 'short',
        ?bool $showSeconds = null,
    ): ?string {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return null;
        }

        $is12Hour = self::getTimeFormat() === '12';
        $seconds = $showSeconds ?? self::getShowSeconds();

        // For 'short' length, only show seconds if explicitly requested
        // For 'medium'/'long', show seconds based on config/param
        if ($length === 'short' && $showSeconds === null) {
            $seconds = false;
        }

        if ($is12Hour) {
            $format = $seconds ? 'g:i:s A' : 'g:i A';  // 3:45:32 PM or 3:45 PM
        } else {
            $format = $seconds ? 'H:i:s' : 'H:i';      // 15:45:32 or 15:45
        }

        return $date->format($format);
    }

    /**
     * Format short date for charts/compact display
     *
     * @param DateTime|string|null $date
     * @return string|null Example: "Jan 22"
     * @since 5.8.0
     */
    public static function formatShortDate(DateTime|string|null $date): ?string
    {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return null;
        }

        return $date->format('M j');  // Jan 22
    }

    /**
     * Format relative time (e.g., "2 hours ago")
     *
     * @param DateTime|string|null $date
     * @return string|null
     * @since 5.8.0
     */
    public static function formatRelative(DateTime|string|null $date): ?string
    {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return null;
        }

        return Craft::$app->getFormatter()->asRelativeTime($date);
    }

    // =========================================================================
    // DATABASE FORMATTING
    // =========================================================================

    /**
     * Format for database storage (MySQL datetime)
     *
     * @param DateTime|string|null $date
     * @return string|null "2026-01-22 15:45:32"
     * @since 5.8.0
     */
    public static function forDatabase(DateTime|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                return null;
            }
        }

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Format date only for database
     *
     * @param DateTime|string|null $date
     * @return string|null "2026-01-22"
     * @since 5.8.0
     */
    public static function forDatabaseDate(DateTime|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                return null;
            }
        }

        return $date->format('Y-m-d');
    }

    /**
     * Format for database date range start (beginning of day)
     *
     * @param DateTime|string|null $date
     * @return string|null "2026-01-22 00:00:00"
     * @since 5.8.0
     */
    public static function forDatabaseDayStart(DateTime|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                return null;
            }
        }

        return $date->format('Y-m-d 00:00:00');
    }

    /**
     * Format for database date range end (end of day)
     *
     * @param DateTime|string|null $date
     * @return string|null "2026-01-22 23:59:59"
     * @since 5.8.0
     */
    public static function forDatabaseDayEnd(DateTime|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                return null;
            }
        }

        return $date->format('Y-m-d 23:59:59');
    }

    // =========================================================================
    // API FORMATTING
    // =========================================================================

    /**
     * Format for API responses (ISO 8601)
     *
     * @param DateTime|string|null $date
     * @return string|null "2026-01-22T15:45:32+00:00"
     * @since 5.8.0
     */
    public static function forApi(DateTime|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                return null;
            }
        }

        return $date->format('c');
    }

    // =========================================================================
    // FILENAME FORMATTING
    // =========================================================================

    /**
     * Format for filenames (safe characters)
     *
     * @param DateTime|string|null $date Defaults to now if null
     * @param bool $includeTime Whether to include time portion
     * @return string "2026-01-22-154532" or "2026-01-22"
     * @since 5.8.0
     */
    public static function forFilename(
        DateTime|string|null $date = null,
        bool $includeTime = true,
    ): string {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (\Exception) {
                $date = new DateTime();
            }
        }

        if ($includeTime) {
            return $date->format('Y-m-d-His');  // 2026-01-22-154532
        }

        return $date->format('Y-m-d');  // 2026-01-22
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get current datetime in Craft timezone
     *
     * @return DateTime
     * @since 5.8.0
     */
    public static function now(): DateTime
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        return self::toCraftTimezone($now) ?? $now;
    }

    /**
     * Check if a date is today
     *
     * @param DateTime|string|null $date
     * @return bool
     * @since 5.8.0
     */
    public static function isToday(DateTime|string|null $date): bool
    {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return false;
        }

        $today = self::now();
        return $date->format('Y-m-d') === $today->format('Y-m-d');
    }

    /**
     * Check if a date is in the past
     *
     * @param DateTime|string|null $date
     * @return bool
     * @since 5.8.0
     */
    public static function isPast(DateTime|string|null $date): bool
    {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return false;
        }

        return $date < self::now();
    }

    /**
     * Check if a date is in the future
     *
     * @param DateTime|string|null $date
     * @return bool
     * @since 5.8.0
     */
    public static function isFuture(DateTime|string|null $date): bool
    {
        $date = self::toCraftTimezone($date);
        if ($date === null) {
            return false;
        }

        return $date > self::now();
    }
}
