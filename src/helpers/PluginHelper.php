<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\base\PluginInterface;
use lindemannrock\base\Base;
use lindemannrock\base\twigextensions\PluginNameExtension;
use lindemannrock\logginglibrary\LoggingLibrary;

/**
 * Plugin Helper
 *
 * Provides common initialization utilities for LindemannRock plugins.
 * Consolidates repeated setup code into simple helper methods.
 *
 * Usage in Plugin::init():
 * ```php
 * use lindemannrock\base\helpers\PluginHelper;
 *
 * public function init(): void
 * {
 *     parent::init();
 *     self::$plugin = $this;
 *
 *     // One line replaces: base registration + twig extension + logging config
 *     PluginHelper::bootstrap($this, 'redirectHelper', ['redirectManager:viewLogs']);
 *
 *     // Apply plugin name from config file
 *     PluginHelper::applyPluginNameFromConfig($this);
 *
 *     // ... rest of plugin-specific init
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class PluginHelper
{
    /**
     * Bootstrap the base module and configure common functionality
     *
     * This single method replaces:
     * - Base module registration
     * - Twig extension registration (PluginNameExtension)
     * - Logging library configuration (if available)
     *
     * @param PluginInterface $plugin The plugin instance
     * @param string $helperVariableName Twig global variable name (e.g., 'redirectHelper')
     * @param array $loggingPermissions Permissions required to view logs (e.g., ['redirectManager:viewLogs'])
     */
    public static function bootstrap(
        PluginInterface $plugin,
        string $helperVariableName,
        array $loggingPermissions = [],
    ): void {
        // Register base module (idempotent - safe to call multiple times)
        Base::register();

        // Register plugin name Twig extension
        Craft::$app->view->registerTwigExtension(
            new PluginNameExtension($plugin, $helperVariableName)
        );

        // Configure logging library (if available)
        if (class_exists(LoggingLibrary::class)) {
            $settings = $plugin->getSettings();

            // Get settings values with fallbacks
            $pluginName = $settings->pluginName ?? $plugin->name;
            $logLevel = $settings->logLevel ?? 'error';
            $itemsPerPage = $settings->itemsPerPage ?? 100;

            LoggingLibrary::configure([
                'pluginHandle' => $plugin->handle,
                'pluginName' => $pluginName,
                'logLevel' => $logLevel,
                'itemsPerPage' => $itemsPerPage,
                'permissions' => $loggingPermissions,
            ]);
        }
    }

    /**
     * Override plugin name from config file
     *
     * Checks config/{plugin-handle}.php for a 'pluginName' setting
     * and applies it to the plugin instance. Supports environment-specific
     * and wildcard configurations.
     *
     * Config file examples:
     * ```php
     * // Root level (all environments)
     * return ['pluginName' => 'Custom Name'];
     *
     * // Environment-specific
     * return [
     *     'production' => ['pluginName' => 'Prod Name'],
     *     'dev' => ['pluginName' => 'Dev Name'],
     * ];
     *
     * // Wildcard
     * return ['*' => ['pluginName' => 'All Envs Name']];
     * ```
     *
     * @param PluginInterface $plugin The plugin instance
     */
    public static function applyPluginNameFromConfig(PluginInterface $plugin): void
    {
        $configPath = Craft::$app->getPath()->getConfigPath() . '/' . $plugin->handle . '.php';

        if (!file_exists($configPath)) {
            return;
        }

        try {
            $config = require $configPath;

            if (!is_array($config)) {
                return;
            }

            // Check root level first
            if (isset($config['pluginName']) && is_string($config['pluginName'])) {
                $plugin->name = $config['pluginName'];
                return;
            }

            // Check environment-specific
            $env = Craft::$app->getConfig()->env;
            if ($env && isset($config[$env]['pluginName']) && is_string($config[$env]['pluginName'])) {
                $plugin->name = $config[$env]['pluginName'];
                return;
            }

            // Check wildcard
            if (isset($config['*']['pluginName']) && is_string($config['*']['pluginName'])) {
                $plugin->name = $config['*']['pluginName'];
            }
        } catch (\Throwable $e) {
            // Silently ignore config errors - plugin continues with default name
        }
    }

    /**
     * Register translations for a plugin
     *
     * Convenience method to register translation messages.
     * Alternative to doing it manually in each plugin.
     *
     * @param string $handle Plugin handle (translation category)
     * @param string $basePath Path to translations directory
     */
    public static function registerTranslations(string $handle, string $basePath): void
    {
        Craft::$app->i18n->translations[$handle] = [
            'class' => \craft\i18n\PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => $basePath,
            'forceTranslation' => true,
            'allowOverrides' => true,
        ];
    }
}
