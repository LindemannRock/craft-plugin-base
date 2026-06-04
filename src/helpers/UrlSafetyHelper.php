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
 * Safety checks for URLs that are about to be used as redirect targets.
 *
 * Any value handed to a redirect response should be constrained to a relative
 * path or an `http(s)` absolute URL — never an executable scheme such as
 * `javascript:`, `data:`, or `vbscript:`. Controllers across plugins were each
 * carrying an identical private `_sanitizeUrl()`; this helper centralizes the
 * rule so redirect, fallback, and not-found paths all enforce it the same way.
 *
 * @since 5.26.0
 */
class UrlSafetyHelper
{
    /**
     * Return the URL if it is a safe redirect target, otherwise the fallback.
     *
     * Safe means a relative path (starts with `/`) or an `http`/`https` absolute
     * URL. Everything else — `javascript:`, `data:`, `vbscript:`, bare words —
     * collapses to `$fallback`.
     *
     * @param string $url The candidate redirect URL.
     * @param string $fallback Returned when the candidate is not a safe target.
     * @return string The original URL when safe, otherwise the fallback.
     * @since 5.26.0
     */
    public static function sanitizeRedirectUrl(string $url, string $fallback = '/'): string
    {
        $url = trim($url);

        // Allow relative URLs.
        if (str_starts_with($url, '/')) {
            return $url;
        }

        // Allow http and https absolute URLs.
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        // Reject everything else (javascript:, data:, vbscript:, etc.).
        return $fallback;
    }

    /**
     * Whether the given URL is a safe redirect target (relative path or http(s)).
     *
     * Useful when the caller wants to log or branch on a blocked value rather
     * than silently fall back.
     *
     * @param string $url The candidate redirect URL.
     * @return bool
     * @since 5.26.0
     */
    public static function isSafeRedirectUrl(string $url): bool
    {
        $url = trim($url);

        if (str_starts_with($url, '/')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $url);
    }
}
