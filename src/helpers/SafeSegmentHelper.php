<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

/**
 * Helpers for safe non-DB string segments.
 *
 * Use this for filename fragments and local token/config keys. Do not use it
 * for persisted slug/handle uniqueness; use {@see SlugHandleHelper} instead.
 *
 * @since 5.26.0
 */
class SafeSegmentHelper
{
    /**
     * Normalize a value to a filename-safe segment.
     *
     * Supported options:
     * - allowDots: preserve dots inside the segment, default `false`
     * - lowercase: lowercase the result, default `true`
     * - maxLength: truncate to this length when positive, default `120`
     *
     * @param string|null $value Raw segment value.
     * @param string $fallback Fallback used when the segment normalizes empty.
     * @param array<string, mixed> $options
     * @return string
     * @since 5.26.0
     */
    public static function filenamePart(?string $value, string $fallback = 'file', array $options = []): string
    {
        $allowDots = (bool)($options['allowDots'] ?? false);
        $lowercase = (bool)($options['lowercase'] ?? true);
        $maxLength = (int)($options['maxLength'] ?? 120);

        $segment = trim((string)$value);
        $segment = preg_replace('/[\x00-\x1f\x7f"\'\\\\\/]+/', '-', $segment) ?? '';
        $segment = $allowDots
            ? (preg_replace('/[^A-Za-z0-9._-]+/', '-', $segment) ?? '')
            : (preg_replace('/[^A-Za-z0-9_-]+/', '-', $segment) ?? '');
        $segment = preg_replace('/-+/', '-', $segment) ?? '';
        $segment = trim($segment, '-_.');

        if ($lowercase) {
            $segment = mb_strtolower($segment);
        }

        $segment = self::truncate($segment, $maxLength);
        if ($segment !== '') {
            return $segment;
        }

        if ($fallback === '') {
            return '';
        }

        $fallbackSegment = self::filenamePart($fallback, 'file', $options);

        return $fallbackSegment !== '' ? $fallbackSegment : 'file';
    }

    /**
     * Normalize a value to a local token/config key.
     *
     * Token keys are lowercase dash-separated fragments with no path or file
     * semantics. They are intended for JSON/config/CSS-ish local keys such as
     * Canvas Studio theme token keys.
     *
     * @param string|null $value Raw key value.
     * @param string $fallback Fallback used when the key normalizes empty.
     * @param int $maxLength Maximum key length; set to `0` to disable truncation.
     * @return string
     * @since 5.26.0
     */
    public static function tokenKey(?string $value, string $fallback = 'token', int $maxLength = 64): string
    {
        $key = mb_strtolower(trim((string)$value));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key) ?? '';
        $key = preg_replace('/-+/', '-', $key) ?? '';
        $key = trim($key, '-');
        $key = self::truncate($key, $maxLength);

        if ($key !== '') {
            return $key;
        }

        if ($fallback === '') {
            return '';
        }

        $fallbackKey = self::tokenKey($fallback, 'token', $maxLength);

        return $fallbackKey !== '' ? $fallbackKey : 'token';
    }

    private static function truncate(string $value, int $maxLength): string
    {
        if ($maxLength <= 0 || mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $maxLength), '-_.');
    }
}
