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
 * Detects dangerous HTML/script markup in free-text values that will be
 * rendered into a page (titles, descriptions, translation strings, etc.).
 *
 * This is the content counterpart to {@see UrlSafetyHelper}: where that helper
 * asks "is this *URL* a safe place to send a browser?" (scheme, anchored at the
 * start of the value), this one asks "does this *text* contain markup that
 * executes when injected into HTML?" — matched anywhere in the value, because
 * an embedded `<script>` or `href="javascript:"` is dangerous wherever it sits.
 *
 * It is a precise denylist (detect-and-reject), not a sanitizer: it flags only
 * known-dangerous patterns, so legitimate text containing a lone `<` (e.g.
 * `price < $5`) is NOT a match. Strip-based cleaning (`strip_tags()`) would
 * mangle such text — callers should reject the value instead.
 *
 * @since 5.27.0
 */
class ContentSafetyHelper
{
    /**
     * Patterns that indicate executable or page-controlling markup. Safe tags
     * such as `<p>`/`<br>`/`<strong>` are intentionally not listed.
     *
     * @var array<string, string> pattern => human-readable threat label
     * @since 5.27.0
     */
    private const DANGEROUS_MARKUP_PATTERNS = [
        '/<script[^>]*>/i' => 'Script tag',
        '/<svg[^>]*>/i' => 'SVG tag',
        '/<iframe[^>]*>/i' => 'Iframe tag',
        '/<object[^>]*>/i' => 'Object tag',
        '/<embed[^>]*>/i' => 'Embed tag',
        '/<form[^>]*>/i' => 'Form tag',
        '/<meta[^>]*http-equiv/i' => 'Meta refresh',
        '/<base[^>]*href/i' => 'Base tag',
        '/javascript:/i' => 'JavaScript protocol',
        '/vbscript:/i' => 'VBScript protocol',
        '#data:text/html#i' => 'Data URL',
        '/on\w+\s*=/i' => 'Event handler',
    ];

    /**
     * Whether the text contains dangerous HTML/script markup.
     *
     * Scans both the raw value and its HTML-entity-decoded form, so an encoded
     * payload such as `&#106;avascript:` or `&lt;script&gt;` cannot slip past
     * the raw match. Encoded hits are labelled with a ` (encoded)` suffix.
     *
     * @param string $content The free-text value to inspect.
     * @param array<int, string> $threats Populated by reference with the
     *     human-readable labels of every pattern that matched (empty when safe).
     *     Useful for logging which threats were found. Untyped so callers can
     *     pass a fresh, undeclared variable.
     * @return bool True when at least one dangerous pattern matched.
     * @since 5.27.0
     */
    public static function containsMaliciousMarkup(string $content, &$threats = []): bool
    {
        $threats = [];

        foreach (self::DANGEROUS_MARKUP_PATTERNS as $pattern => $label) {
            if (preg_match($pattern, $content)) {
                $threats[] = $label;
            }
        }

        // Re-scan the entity-decoded form to catch encoded payloads.
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded !== $content) {
            foreach (self::DANGEROUS_MARKUP_PATTERNS as $pattern => $label) {
                if (preg_match($pattern, $decoded)) {
                    $threats[] = $label . ' (encoded)';
                }
            }
        }

        return $threats !== [];
    }
}
