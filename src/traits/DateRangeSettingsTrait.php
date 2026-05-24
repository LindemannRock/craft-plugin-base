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
 * Date Range Settings Trait
 *
 * Adds a single nullable `defaultDateRange` property to a plugin's Settings
 * model and exposes the matching validation rule + attribute label so each
 * plugin can surface per-plugin overrides of the base default analytics /
 * dashboard date range.
 *
 * `defaultDateRange` is nullable. Null = "inherit from base config /
 * hardcoded default". A non-null value overrides the base default for this
 * plugin only.
 *
 * Cascade applied by `DateRangeHelper::getDefaultDateRange()` (high → low):
 *   1. Plugin Settings model: $settings->defaultDateRange (this property)
 *   2. Plugin config file:    'defaultDateRange' (root)
 *   3. Plugin config file:    'analytics.defaultDateRange' (legacy)
 *   4. Base config file:      'defaultDateRange' (root)
 *   5. Base config file:      'analytics.defaultDateRange' (legacy)
 *   6. Hardcoded fallback:    'last30days'
 *
 * Companion CP partial: `lindemannrock-base/_partials/cascade-date-range-settings.twig`
 * renders the form field via shared `lindemannrock-base` translations. Plugins
 * include it via the `_partials/cascade-base-overrides` umbrella's `sections.dateRange`
 * branch.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\DateRangeSettingsTrait;
 *
 * class Settings extends Model
 * {
 *     use DateRangeSettingsTrait;
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->dateRangeSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->dateRangeSettingsLabel());
 *     }
 * }
 * ```
 *
 * Schema (Install.php or migration):
 * ```php
 * 'defaultDateRange' => $this->string(15)->null(),
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait DateRangeSettingsTrait
{
    /**
     * @var string|null Default date range for analytics / dashboard pages. Null = inherit base default.
     */
    public ?string $defaultDateRange = null;

    /**
     * Validation rules for the date range setting.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function dateRangeSettingsRules(): array
    {
        return [
            [['defaultDateRange'], 'in', 'range' => [
                'today',
                'yesterday',
                'thisWeek',
                'lastWeek',
                'last7days',
                'last14days',
                'last30days',
                'last90days',
                'thisMonth',
                'lastMonth',
                'thisQuarter',
                'lastQuarter',
                'thisYear',
                'lastYear',
                'last12months',
                'all',
            ], 'skipOnEmpty' => true],
        ];
    }

    /**
     * Attribute label for the date range setting.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function dateRangeSettingsLabel(): array
    {
        return [
            'defaultDateRange' => Craft::t('lindemannrock-base', 'Default Date Range'),
        ];
    }
}
