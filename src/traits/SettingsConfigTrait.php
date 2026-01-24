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
 * Settings Config Trait
 *
 * Provides config file override detection and log level validation for Settings models.
 * Used to determine if settings are coming from config files vs database,
 * and to enforce that debug logging only works with devMode enabled.
 *
 * Requirements:
 * - Using class must implement pluginHandle() method
 * - For validateLogLevel(): class must have a string $logLevel property
 *
 * Usage:
 * ```php
 * class Settings extends Model
 * {
 *     use SettingsConfigTrait;
 *     use SettingsPersistenceTrait; // For saveToDatabase() in validateLogLevel
 *
 *     public string $logLevel = 'error';
 *
 *     protected static function pluginHandle(): string { return 'my-plugin'; }
 *
 *     public function rules(): array
 *     {
 *         return [
 *             [['logLevel'], 'validateLogLevel'],
 *         ];
 *     }
 * }
 *
 * // Check if setting is from config
 * if ($settings->isOverriddenByConfig('pluginName')) {
 *     // Show "controlled by config file" message
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
trait SettingsConfigTrait
{
    /**
     * Get the plugin handle for config file lookup
     *
     * Used to locate the config file at config/{plugin-handle}.php
     * Example: 'redirect-manager' for config/redirect-manager.php
     *
     * @return string
     */
    abstract protected static function pluginHandle(): string;

    /**
     * Check if a setting is overridden by config file
     *
     * Checks if the specified attribute is defined in the plugin's config file.
     * Supports dot notation for nested settings (e.g., 'backends.algolia.enabled').
     *
     * Config file priority order:
     * 1. Root level: return ['setting' => 'value']
     * 2. Environment-specific: return ['production' => ['setting' => 'value']]
     * 3. Wildcard: return ['*' => ['setting' => 'value']]
     *
     * @param string $attribute The setting attribute name or dot-notation path
     * @return bool True if the setting is defined in config file
     * @since 5.0.0
     */
    public function isOverriddenByConfig(string $attribute): bool
    {
        $configPath = Craft::$app->getPath()->getConfigPath() . '/' . static::pluginHandle() . '.php';

        if (!file_exists($configPath)) {
            return false;
        }

        try {
            $rawConfig = require $configPath;
        } catch (\Throwable $e) {
            return false;
        }

        if (!is_array($rawConfig)) {
            return false;
        }

        // Handle dot notation for nested config (e.g., 'backends.algolia.enabled')
        if (str_contains($attribute, '.')) {
            $parts = explode('.', $attribute);
            $current = $rawConfig;

            foreach ($parts as $part) {
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    return false;
                }
                $current = $current[$part];
            }

            return true;
        }

        // Check root level (use array_key_exists to detect null values)
        if (array_key_exists($attribute, $rawConfig)) {
            return true;
        }

        // Check environment-specific config (e.g., 'production', 'dev')
        $env = Craft::$app->getConfig()->env;
        if ($env && is_array($rawConfig[$env] ?? null) && array_key_exists($attribute, $rawConfig[$env])) {
            return true;
        }

        // Check wildcard config ('*')
        if (is_array($rawConfig['*'] ?? null) && array_key_exists($attribute, $rawConfig['*'])) {
            return true;
        }

        return false;
    }

    /**
     * Validate log level (debug requires devMode)
     *
     * This validator ensures 'debug' log level is only used when devMode is enabled.
     * If devMode is disabled and logLevel is 'debug', it automatically falls back to 'info'.
     *
     * If the setting is NOT from a config file, it also saves the corrected value to database.
     *
     * Add to your rules():
     * ```php
     * public function rules(): array
     * {
     *     return [
     *         [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
     *         [['logLevel'], 'validateLogLevel'],
     *     ];
     * }
     * ```
     *
     * @param string $attribute The attribute being validated
     * @param array|null $params Validation parameters
     * @param mixed $validator The validator instance
     * @since 5.0.0
     */
    public function validateLogLevel($attribute, $params, $validator): void
    {
        $logLevel = $this->$attribute;

        // Only check if logLevel is 'debug'
        if ($logLevel !== 'debug') {
            return;
        }

        // Debug is allowed when devMode is enabled
        if (Craft::$app->getConfig()->getGeneral()->devMode) {
            return;
        }

        // Debug not allowed without devMode - fall back to 'info'
        $this->$attribute = 'info';

        // If the setting is from config file, just log a warning
        if ($this->isOverriddenByConfig('logLevel')) {
            // Only log once per session to avoid spam
            if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
                $sessionKey = static::pluginHandle() . '_debug_config_warning';
                if (Craft::$app->getSession()->get($sessionKey) === null) {
                    Craft::warning(
                        'Log level "debug" from config file changed to "info" because devMode is disabled. ' .
                        'Update your config/' . static::pluginHandle() . '.php file.',
                        static::pluginHandle()
                    );
                    Craft::$app->getSession()->set($sessionKey, true);
                }
            } else {
                Craft::warning(
                    'Log level "debug" from config file changed to "info" because devMode is disabled.',
                    static::pluginHandle()
                );
            }
            return;
        }

        // Setting is from database - save the correction
        Craft::warning(
            'Log level automatically changed from "debug" to "info" because devMode is disabled.',
            static::pluginHandle()
        );

        // Save to database if method exists (from SettingsPersistenceTrait)
        if (method_exists($this, 'saveToDatabase')) {
            $this->saveToDatabase();
        }
    }
}
