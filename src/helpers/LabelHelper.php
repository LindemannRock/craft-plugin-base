<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Helpers for formatting user-facing labels (form fields, dropdowns, etc.).
 *
 * @since 5.22.0
 */
class LabelHelper
{
    /**
     * Shorten a label for compact UI display (dropdowns, table headers, chips).
     *
     * Strips leading numbering like "1. " or "10) ", collapses whitespace,
     * and truncates to a maximum length with an ellipsis. A trailing
     * parenthetical suffix (e.g. " (Geely Service)") is preserved when
     * present so entries remain distinguishable after truncation.
     *
     * @param string $label The full label to shorten.
     * @param int $maxLength Target max length of the shortened label body, excluding the ellipsis; the body is kept one char under this. Default 60.
     * @return string
     */
    public static function shorten(string $label, int $maxLength = 60): string
    {
        $label = trim(preg_replace('/\s+/', ' ', $label) ?? '');

        if ($label === '') {
            return '';
        }

        $label = preg_replace('/^\s*\d+\s*[\.\)]\s*/u', '', $label) ?? $label;

        if (mb_strlen($label) <= $maxLength) {
            return $label;
        }

        $suffix = '';
        if (preg_match('/\s*(\([^()]+\))\s*$/u', $label, $matches)) {
            $suffix = ' ' . trim($matches[1]);
            $label = trim(mb_substr($label, 0, -mb_strlen($matches[0])));
        }

        $available = $maxLength - mb_strlen($suffix) - 1; // keep the body one char under the target length
        if ($available < 10) {
            // Suffix too long to preserve meaningfully; drop it and truncate body.
            return mb_substr($label, 0, $maxLength - 1) . '...';
        }

        return rtrim(mb_substr($label, 0, $available)) . '...' . $suffix;
    }
}
