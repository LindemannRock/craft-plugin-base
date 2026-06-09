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
     * Schemes that can execute script or read local resources when handed to a
     * browser. These are never a legitimate stored link target.
     *
     * @since 5.27.0
     */
    private const DANGEROUS_SCHEMES = ['javascript', 'vbscript', 'data', 'file'];

    /**
     * Whether the URL uses a dangerous executable scheme (`javascript:`,
     * `vbscript:`, `data:`, `file:`), including whitespace- or entity-obfuscated
     * variants such as `java\tscript:` or `&#106;avascript:`.
     *
     * This is a denylist primitive: callers keep their own allowed-scheme rules
     * and add this as an extra guard, so custom app deep links (`myapp://`,
     * `fb://`) still pass while script-bearing URLs are blocked. Use it where a
     * permissive validator (e.g. `filter_var(..., FILTER_VALIDATE_URL)`) would
     * otherwise let an executable scheme through.
     *
     * @param string $url The candidate URL.
     * @return bool True when the URL resolves to a dangerous scheme.
     * @since 5.27.0
     */
    public static function hasDangerousScheme(string $url): bool
    {
        // Browsers ignore control chars/whitespace inside a scheme and decode
        // HTML entities — normalize both before anchoring the prefix check.
        $normalized = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $normalized) ?? '');

        foreach (self::DANGEROUS_SCHEMES as $scheme) {
            if (str_starts_with($normalized, $scheme . ':')) {
                return true;
            }
        }

        return false;
    }

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

        // Allow relative URLs — but not protocol-relative (`//host`), which the
        // browser resolves to an external origin.
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
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

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $url);
    }
}
