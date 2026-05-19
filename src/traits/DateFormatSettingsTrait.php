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
 * Date Format Settings Trait
 *
 * Adds the five date/time format properties to a plugin's Settings model and
 * exposes the matching validation rules + attribute labels so each plugin can
 * surface per-plugin date/time overrides in its CP Interface settings page.
 *
 * Each property is nullable. Null = "inherit from base config / hardcoded
 * default". A non-null value overrides the base default for this plugin only.
 *
 * Cascade applied by `DateFormatHelper::getConfig()` when the plugin is the
 * current request's active controller (high → low priority):
 *   1. Plugin config file (config/{handle}.php)
 *   2. Plugin DB settings (this trait's properties)
 *   3. Base config file (config/lindemannrock-base.php)
 *   4. Hardcoded defaults inside DateFormatHelper getters
 *
 * Companion CP partial: `lindemannrock-base/_partials/date-format-settings.twig`
 * renders the 5 form fields with `isOverriddenByConfig` warnings using shared
 * `lindemannrock-base` translations — plugins include it once and gain the
 * full UI without per-plugin string duplication.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\DateFormatSettingsTrait;
 * use lindemannrock\base\traits\SettingsPersistenceTrait;
 *
 * class Settings extends Model
 * {
 *     use DateFormatSettingsTrait;
 *     use SettingsPersistenceTrait;
 *
 *     // ... plugin-specific properties ...
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->dateFormatSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->dateFormatSettingsLabels());
 *     }
 *
 *     protected static function booleanFields(): array
 *     {
 *         return ['showSeconds', 'otherBool'];  // include showSeconds for persistence cast
 *     }
 * }
 * ```
 *
 * Schema (Install.php or migration — 5 nullable columns):
 * ```php
 * $this->createTable($table, [
 *     // ... other columns ...
 *     'timeFormat'    => $this->string(2)->null(),
 *     'monthFormat'   => $this->string(20)->null(),
 *     'dateOrder'     => $this->string(3)->null(),
 *     'dateSeparator' => $this->string(1)->null(),
 *     'showSeconds'   => $this->boolean()->null(),
 *     // ...
 * ]);
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait DateFormatSettingsTrait
{
    /**
     * @var string|null Time format ('12' or '24'). Null = inherit base default.
     */
    public ?string $timeFormat = null;

    /**
     * @var string|null Month format ('numeric', 'short', 'long'). Null = inherit base default.
     */
    public ?string $monthFormat = null;

    /**
     * @var string|null Date order ('dmy', 'mdy', 'ymd'). Null = inherit base default.
     */
    public ?string $dateOrder = null;

    /**
     * @var string|null Date separator ('/', '-', '.'). Null = inherit base default.
     */
    public ?string $dateSeparator = null;

    /**
     * @var bool|null Show seconds in time display. Null = inherit base default.
     */
    public ?bool $showSeconds = null;

    /**
     * Validation rules for the date format settings.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     *
     * @return array
     */
    public function dateFormatSettingsRules(): array
    {
        return [
            [['timeFormat'], 'in', 'range' => ['12', '24'], 'skipOnEmpty' => true],
            [['monthFormat'], 'in', 'range' => ['numeric', 'short', 'long'], 'skipOnEmpty' => true],
            [['dateOrder'], 'in', 'range' => ['dmy', 'mdy', 'ymd'], 'skipOnEmpty' => true],
            [['dateSeparator'], 'in', 'range' => ['/', '-', '.'], 'skipOnEmpty' => true],
            [['showSeconds'], 'boolean', 'skipOnEmpty' => true],
        ];
    }

    /**
     * Attribute labels for the date format settings.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function dateFormatSettingsLabels(): array
    {
        return [
            'timeFormat' => Craft::t('lindemannrock-base', 'Time Format'),
            'monthFormat' => Craft::t('lindemannrock-base', 'Month Format'),
            'dateOrder' => Craft::t('lindemannrock-base', 'Date Order'),
            'dateSeparator' => Craft::t('lindemannrock-base', 'Date Separator'),
            'showSeconds' => Craft::t('lindemannrock-base', 'Show Seconds'),
        ];
    }
}
