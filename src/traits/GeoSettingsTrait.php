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
 * Geo Settings Trait
 *
 * Centralizes the validation rules and attribute labels for the `$geoProvider`
 * and `$geoApiKey` properties that plugins using `_partials/geo-settings.twig`
 * carry. Each plugin keeps its own property declarations (defaults are
 * conventionally `'ip-api.com'` and `null`) while the shared validation +
 * labels live here.
 *
 * Pairs with `lindemannrock-base/_partials/geo-settings.twig`, which renders
 * the form fields with the shared labels and the generic config-override
 * warnings using base translations.
 *
 * The trait does NOT declare the underlying properties — each plugin's
 * Settings model declares its own `public string $geoProvider` and
 * `public ?string $geoApiKey`. This matches the convention used by
 * `PluginNameSettingsTrait` and friends, where the trait owns the shared
 * behaviour (rules + labels) but not the property declaration.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\GeoSettingsTrait;
 *
 * class Settings extends Model
 * {
 *     use GeoSettingsTrait;
 *
 *     // Plugin keeps its own property declarations:
 *     public string $geoProvider = 'ip-api.com';
 *     public ?string $geoApiKey = null;
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->geoSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->geoSettingsLabel());
 *     }
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait GeoSettingsTrait
{
    /**
     * Validation rules for `$geoProvider` and `$geoApiKey`.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function geoSettingsRules(): array
    {
        return [
            [['geoProvider'], 'in', 'range' => ['ip-api.com', 'ipapi.co', 'ipinfo.io']],
            [['geoApiKey'], 'string', 'max' => 255, 'skipOnEmpty' => true],
        ];
    }

    /**
     * Attribute labels for `$geoProvider` and `$geoApiKey`.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function geoSettingsLabel(): array
    {
        return [
            'geoProvider' => Craft::t('lindemannrock-base', 'Geo Provider'),
            'geoApiKey' => Craft::t('lindemannrock-base', 'API Key'),
        ];
    }
}
