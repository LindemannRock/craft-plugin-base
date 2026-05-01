<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * JSON helpers used by base assets and templates.
 *
 * @since 5.19.0
 */
class JsonHelper
{
    /**
     * Encode JSON safely for inline HTML/JS.
     *
     * @param mixed $value
     * @return string
     */
    public static function htmlSafeJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '{}';
    }
}
