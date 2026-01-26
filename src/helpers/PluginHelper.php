<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\base\PluginInterface;
use lindemannrock\base\Base;
use lindemannrock\base\twigextensions\PluginNameExtension;
use lindemannrock\base\twigextensions\PluginNameHelper;
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
 *     PluginHelper::bootstrap(
 *         $this,
 *         'redirectHelper',
 *         ['redirectManager:viewLogs'],
 *         ['redirectManager:downloadLogs']
 *     );
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
     * - Color set registration for badges/filters
     *
     * @param PluginInterface $plugin The plugin instance
     * @param string $helperVariableName Twig global variable name (e.g., 'redirectHelper')
     * @param array $viewPermissions Permissions required to view logs (e.g., ['redirectManager:viewLogs'])
     * @param array $downloadPermissions Permissions required to download logs (e.g., ['redirectManager:downloadLogs'])
     * @param array $options Additional options:
     *   - 'colorSets': array of color sets to register for badges/filters
     *     Example: ['myStatus' => ['active' => ['color' => '#10b981', 'rgb' => '16, 185, 129', 'text' => '#065f46']]]
     * @since 5.0.0
     */
    public static function bootstrap(
        PluginInterface $plugin,
        string $helperVariableName,
        array $viewPermissions = [],
        array $downloadPermissions = [],
        array $options = [],
    ): void {
        // Register base module (idempotent - safe to call multiple times)
        Base::register();

        // Register global variable directly via Twig (avoids extension class name conflicts)
        \yii\base\Event::on(
            \craft\web\View::class,
            \craft\web\View::EVENT_BEFORE_RENDER_TEMPLATE,
            function() use ($plugin, $helperVariableName) {
                static $registered = [];
                if (!isset($registered[$helperVariableName])) {
                    $twig = Craft::$app->view->getTwig();
                    $twig->addGlobal($helperVariableName, new PluginNameHelper($plugin));
                    $registered[$helperVariableName] = true;
                }
            }
        );

        // Configure logging library (if available and viewPermissions provided)
        // Only plugins that explicitly pass viewPermissions will have logging enabled
        if (!empty($viewPermissions) && class_exists(LoggingLibrary::class)) {
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
                'viewPermissions' => $viewPermissions,
                'downloadPermissions' => $downloadPermissions,
            ]);
        }

        // Register plugin-specific color sets for badges/filters
        if (!empty($options['colorSets']) && is_array($options['colorSets'])) {
            foreach ($options['colorSets'] as $setName => $colors) {
                if (is_string($setName) && is_array($colors)) {
                    ColorHelper::registerColorSet($setName, $colors);
                }
            }
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
     * @since 5.0.0
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
     * @since 5.0.0
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

    // =========================================================================
    // CACHE PATH HELPERS
    // =========================================================================

    /**
     * Get the cache base path for a plugin
     *
     * Returns: storage/runtime/{plugin-handle}/cache/
     *
     * @param PluginInterface $plugin The plugin instance
     * @return string
     * @since 5.0.0
     */
    public static function getCacheBasePath(PluginInterface $plugin): string
    {
        return Craft::$app->getPath()->getRuntimePath() . '/' . $plugin->handle . '/cache/';
    }

    /**
     * Get a specific cache path for a plugin
     *
     * Returns: storage/runtime/{plugin-handle}/cache/{type}/
     *
     * @param PluginInterface $plugin The plugin instance
     * @param string $type Cache type (e.g., 'search', 'autocomplete', 'device')
     * @return string
     * @since 5.0.0
     */
    public static function getCachePath(PluginInterface $plugin, string $type): string
    {
        return self::getCacheBasePath($plugin) . $type . '/';
    }

    // =========================================================================
    // PLUGIN DETECTION HELPERS
    // =========================================================================

    /**
     * Check if a plugin is installed and enabled
     *
     * Use this to check for optional plugin dependencies before using their APIs.
     *
     * @param string $handle Plugin handle (e.g., 'redirect-manager', 'formie')
     * @return bool True if plugin is installed and enabled
     * @since 5.9.0
     */
    public static function isPluginEnabled(string $handle): bool
    {
        return Craft::$app->plugins->isPluginEnabled($handle);
    }

    /**
     * Check if a plugin is installed (may not be enabled)
     *
     * @param string $handle Plugin handle
     * @return bool True if plugin is installed (regardless of enabled state)
     * @since 5.9.0
     */
    public static function isPluginInstalled(string $handle): bool
    {
        return Craft::$app->plugins->isPluginInstalled($handle);
    }

    /**
     * Get a plugin instance
     *
     * Returns the plugin instance if installed and enabled, null otherwise.
     * Useful when you need to access the plugin's services or settings.
     *
     * @param string $handle Plugin handle
     * @return PluginInterface|null Plugin instance or null
     * @since 5.9.0
     */
    public static function getPlugin(string $handle): ?PluginInterface
    {
        return Craft::$app->plugins->getPlugin($handle);
    }

    /**
     * Get a plugin's display name (respects custom pluginName setting)
     *
     * Returns the plugin's display name, checking for a custom `pluginName`
     * in settings first, then falling back to the default plugin name.
     *
     * @param string $handle Plugin handle
     * @param string|null $fallback Fallback name if plugin not found (defaults to handle)
     * @return string The plugin's display name
     * @since 5.9.0
     */
    public static function getPluginName(string $handle, ?string $fallback = null): string
    {
        $plugin = self::getPlugin($handle);

        if (!$plugin) {
            return $fallback ?? $handle;
        }

        // Check for custom pluginName in settings
        $settings = $plugin->getSettings();
        if ($settings && property_exists($settings, 'pluginName') && !empty($settings->pluginName)) {
            return $settings->pluginName;
        }

        // Fall back to default plugin name
        return $plugin->name;
    }
}
