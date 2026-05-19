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
 * Export Format Settings Trait
 *
 * Adds three nullable per-format toggles to a plugin's Settings model and
 * exposes the matching validation rules + attribute labels so each plugin can
 * surface per-plugin overrides of the base export-format defaults (whether
 * the CSV / JSON / Excel options appear in this plugin's export menus).
 *
 * Each property is nullable. Null = "inherit from base config /
 * `ExportHelper::DEFAULT_FORMATS`". A non-null value overrides the base
 * default for this plugin only.
 *
 * Cascade applied by `ExportHelper::getConfig()` (high → low):
 *   1. Plugin Settings model: $settings->exportsCsv / exportsJson / exportsExcel
 *   2. Plugin config file:    'exports' hash (merged over base)
 *   3. Base config file:      'exports' hash
 *   4. Hardcoded fallback:    ExportHelper::DEFAULT_FORMATS
 *
 * Companion CP partial: `lindemannrock-base/_partials/cascade-export-format-settings.twig`
 * renders three selectFields (mirroring the `showSeconds` 3-state pattern: "Use
 * global default" / "Disabled" / "Enabled"). Plugins include it via the
 * `_partials/cascade-base-overrides` umbrella's `sections.exports` branch.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\ExportFormatSettingsTrait;
 *
 * class Settings extends Model
 * {
 *     use ExportFormatSettingsTrait;
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->exportFormatSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->exportFormatSettingsLabels());
 *     }
 * }
 * ```
 *
 * Schema (Install.php or migration):
 * ```php
 * 'exportsCsv'   => $this->boolean()->null(),
 * 'exportsJson'  => $this->boolean()->null(),
 * 'exportsExcel' => $this->boolean()->null(),
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait ExportFormatSettingsTrait
{
    /**
     * @var bool|null Whether the CSV export option is enabled. Null = inherit base default.
     */
    public ?bool $exportsCsv = null;

    /**
     * @var bool|null Whether the JSON export option is enabled. Null = inherit base default.
     */
    public ?bool $exportsJson = null;

    /**
     * @var bool|null Whether the Excel export option is enabled. Null = inherit base default.
     */
    public ?bool $exportsExcel = null;

    /**
     * Validation rules for the export format settings.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function exportFormatSettingsRules(): array
    {
        return [
            [['exportsCsv', 'exportsJson', 'exportsExcel'], 'boolean', 'skipOnEmpty' => true],
        ];
    }

    /**
     * Attribute labels for the export format settings.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function exportFormatSettingsLabels(): array
    {
        return [
            'exportsCsv' => Craft::t('lindemannrock-base', 'CSV Export'),
            'exportsJson' => Craft::t('lindemannrock-base', 'JSON Export'),
            'exportsExcel' => Craft::t('lindemannrock-base', 'Excel Export'),
        ];
    }
}
