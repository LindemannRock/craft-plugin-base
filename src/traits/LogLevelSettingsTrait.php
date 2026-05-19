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
 * Log Level Settings Trait
 *
 * Adds the standardized `$logLevel` property (`'error'` default) to a plugin's
 * Settings model and exposes the matching validation rules + attribute label.
 *
 * Pairs with `lindemannrock-base/_partials/field-log-level.twig`, which renders
 * the form field with the shared label, the four option labels (Error /
 * Warning / Info / Debug), and the devMode-gating of the `debug` option using
 * base translations.
 *
 * Also pairs with `SettingsConfigTrait::validateLogLevel`, which provides the
 * `debug → info` fallback when devMode is disabled. The Settings model must
 * already `use SettingsConfigTrait;` for the `validateLogLevel` validator
 * referenced in `logLevelSettingsRules()` to resolve.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\traits\LogLevelSettingsTrait;
 * use lindemannrock\base\traits\SettingsConfigTrait;
 *
 * class Settings extends Model
 * {
 *     use LogLevelSettingsTrait;
 *     use SettingsConfigTrait;
 *
 *     public function rules(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific rules ...
 *         ], $this->logLevelSettingsRules());
 *     }
 *
 *     public function attributeLabels(): array
 *     {
 *         return array_merge([
 *             // ... plugin-specific labels ...
 *         ], $this->logLevelSettingsLabel());
 *     }
 * }
 * ```
 *
 * Schema (Install.php or migration):
 * ```php
 * 'logLevel' => $this->string(10)->notNull()->defaultValue('error'),
 * // or an enum column if your DB supports it:
 * 'logLevel' => $this->enum('logLevel', ['debug', 'info', 'warning', 'error'])->notNull()->defaultValue('error'),
 * ```
 *
 * @author LindemannRock
 * @since 5.25.0
 */
trait LogLevelSettingsTrait
{
    /**
     * @var string Log level for plugin operations. One of 'debug', 'info', 'warning', 'error'.
     */
    public string $logLevel = 'error';

    /**
     * Validation rules for `$logLevel`.
     *
     * Merge into the Settings model's `rules()` return value via `array_merge`.
     * The `validateLogLevel` validator comes from `SettingsConfigTrait` —
     * required for the devMode-gated `debug` fallback to work.
     *
     * @return array
     */
    public function logLevelSettingsRules(): array
    {
        return [
            [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
            [['logLevel'], 'validateLogLevel'],
        ];
    }

    /**
     * Attribute label for `$logLevel`.
     *
     * Merge into the Settings model's `attributeLabels()` return value via `array_merge`.
     *
     * @return array
     */
    public function logLevelSettingsLabel(): array
    {
        return [
            'logLevel' => Craft::t('lindemannrock-base', 'Log Level'),
        ];
    }
}
