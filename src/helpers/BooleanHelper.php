<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Boolean Helper
 *
 * Normalizes boolean-like values from config files, environment variables,
 * canonical form values, and HTML boolean attributes.
 *
 * Empty strings are true to match valueless HTML attribute presence semantics
 * such as `disabled=""`. Raw Craft lightswitch POST values use a different
 * contract (`''` means off); typed settings forms should use
 * {@see SettingsPostHelper} instead.
 *
 * @author    LindemannRock
 * @package   Base
 * @since     5.24.0
 */
class BooleanHelper
{
    /**
     * @var string[]
     */
    private const TRUE_VALUES = ['1', 'true', 'on', 'yes'];

    /**
     * @var string[]
     */
    private const FALSE_VALUES = ['0', 'false', 'off', 'no'];

    /**
     * Normalize a boolean-like value.
     *
     * An empty string is true for valueless HTML boolean attributes. It is not
     * the off-value normalizer for raw Craft lightswitch POST data; use
     * {@see SettingsPostHelper} for typed settings forms.
     */
    public static function normalize(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value != 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ($normalized === '') {
                return true;
            }
            if (in_array($normalized, self::TRUE_VALUES, true)) {
                return true;
            }
            if (in_array($normalized, self::FALSE_VALUES, true)) {
                return false;
            }
        }

        return $default;
    }

    /**
     * Check whether a value can be interpreted as a boolean.
     */
    public static function isBooleanLike(mixed $value): bool
    {
        if ($value === null || is_bool($value)) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            return $value == 0 || $value == 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return $normalized === ''
                || in_array($normalized, self::TRUE_VALUES, true)
                || in_array($normalized, self::FALSE_VALUES, true);
        }

        return false;
    }

    /**
     * Normalize a boolean-like value to a style config string.
     */
    public static function toStyleValue(mixed $value, bool $default = false): string
    {
        return self::normalize($value, $default) ? '1' : '0';
    }
}
