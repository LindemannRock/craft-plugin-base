<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

use Craft;

/**
 * Plugin Name Settings Trait
 *
 * Centralizes the validation rule and attribute label for the `$pluginName`
 * property that every LindemannRock plugin's Settings model carries. Each
 * plugin keeps its own property declaration (the default value is
 * plugin-specific — "Search Manager", "Logging Library", etc.) while the
 * shared validation + label live here.
 *
 * Pairs with `lindemannrock-base/_partials/field-plugin-name.twig`, which
 * renders the form field with the shared label and a generic config-override
 * warning using base translations.
 *
 * Also pairs with `SettingsDisplayNameTrait`, which provides the
 * `getDisplayName()` / `getFullName()` / `getPluralDisplayName()` helpers
 * built on top of `$pluginName`. The two traits are independent — adopt one,
 * both, or neither.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\PluginNameSettingsTrait;
 *
 * class Settings extends Model
 * {
 *     use PluginNameSettingsTrait;
 *
 *     // Plugin keeps its own property + default value:
 *     public string $pluginName = 'Search Manager';
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->pluginNameSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->pluginNameSettingsLabel());
 *     }
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait PluginNameSettingsTrait
{
    /**
     * Validation rules for `$pluginName`.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function pluginNameSettingsRules(): array
    {
        return [
            [['pluginName'], 'filter', 'filter' => 'trim'],
            [['pluginName'], 'required'],
            [['pluginName'], 'string', 'max' => 255],
            [
                ['pluginName'],
                'match',
                'pattern' => '/^[^\p{Cc}<>]+$/u',
                'message' => Craft::t('lindemannrock-base', 'Plugin name cannot contain HTML or control characters.'),
            ],
        ];
    }

    /**
     * Attribute label for `$pluginName`.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function pluginNameSettingsLabel(): array
    {
        return [
            'pluginName' => Craft::t('lindemannrock-base', 'Plugin Name'),
        ];
    }
}
