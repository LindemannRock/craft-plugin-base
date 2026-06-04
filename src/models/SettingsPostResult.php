<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\models;

/**
 * Result returned after applying settings POST values to a settings model.
 *
 * @since 5.26.0
 */
final class SettingsPostResult
{
    /**
     * @param array<int, string> $attributesToValidate
     * @param array<int, string> $assignedAttributes
     * @param array<int, string> $ignoredAttributes
     */
    public function __construct(
        public readonly array $attributesToValidate,
        public readonly array $assignedAttributes,
        public readonly array $ignoredAttributes,
        public readonly bool $hasErrors,
    ) {
    }
}
