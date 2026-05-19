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
 * Items Per Page Settings Trait
 *
 * Adds a standardized `itemsPerPage` property to a plugin's Settings model and
 * exposes the matching validation rule + attribute label so each plugin can
 * surface a consistent paging control in its CP settings page.
 *
 * Unlike `DateFormatSettingsTrait`, the value here does NOT cascade from
 * `config/lindemannrock-base.php` — each plugin owns its own value and column.
 * What's centralized is the shared default (100), the shared validation bounds
 * (min 10, max 500), the shared label translation, and the form-field markup.
 *
 * Companion CP partial: `lindemannrock-base/_partials/field-items-per-page.twig`
 * renders the form field with shared label and a generic config-override
 * warning — plugins include it once and pass plugin-specific instructions.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\ItemsPerPageSettingsTrait;
 * use lindemannrock\base\traits\SettingsPersistenceTrait;
 *
 * class Settings extends Model
 * {
 *     use ItemsPerPageSettingsTrait;
 *     use SettingsPersistenceTrait;
 *
 *     // ... plugin-specific properties ...
 *
 *     protected static function integerFields(): array
 *     {
 *         return ['itemsPerPage', 'otherInt'];  // plugin still owns DB cast
 *     }
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->itemsPerPageSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->itemsPerPageSettingsLabel());
 *     }
 * }
 * ```
 *
 * Schema (Install.php or migration):
 * ```php
 * $this->createTable($table, [
 *     // ... other columns ...
 *     'itemsPerPage' => $this->integer()->notNull()->defaultValue(100),
 *     // ...
 * ]);
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait ItemsPerPageSettingsTrait
{
    /**
     * @var int Number of items per page in CP listings.
     */
    public int $itemsPerPage = 100;

    /**
     * Validation rules for the items-per-page setting.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function itemsPerPageSettingsRules(): array
    {
        return [
            [['itemsPerPage'], 'integer', 'min' => 10, 'max' => 500],
            [['itemsPerPage'], 'default', 'value' => 100],
        ];
    }

    /**
     * Attribute label for the items-per-page setting.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function itemsPerPageSettingsLabel(): array
    {
        return [
            'itemsPerPage' => Craft::t('lindemannrock-base', 'Items Per Page'),
        ];
    }
}
