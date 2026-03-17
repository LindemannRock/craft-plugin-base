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
use craft\events\CreateTwigEvent;
use lindemannrock\base\Base;
use lindemannrock\base\twigextensions\PluginNameHelper;
use lindemannrock\logginglibrary\LoggingLibrary;
use yii\base\Event;

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
 *         ['redirectManager:viewSystemLogs'],
 *         ['redirectManager:downloadSystemLogs']
 *     );
 *
 *     // Optional: register translations automatically (if translations/ exists)
 *     // PluginHelper::bootstrap(..., options: ['registerTranslations' => true]);
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
     * Resolve the plugin version from the plugin package metadata.
     *
     * The canonical source is the plugin's composer.json.
     *
     * @param PluginInterface $plugin
     * @return string|null
     */
    public static function getPluginVersion(PluginInterface $plugin): ?string
    {
        $metadata = self::getPluginComposerMetadata($plugin);
        if (!is_array($metadata)) {
            return null;
        }

        $version = trim((string)($metadata['version'] ?? ''));
        return $version !== '' ? $version : null;
    }

    /**
     * Read the plugin package composer metadata.
     *
     * @param PluginInterface $plugin
     * @return array|null
     */
    public static function getPluginComposerMetadata(PluginInterface $plugin): ?array
    {
        try {
            $reflection = new \ReflectionClass($plugin);
            $pluginFile = $reflection->getFileName();
            if ($pluginFile === false) {
                return null;
            }

            $composerPath = dirname(dirname($pluginFile)) . '/composer.json';
            if (!is_file($composerPath) || !is_readable($composerPath)) {
                return null;
            }

            $json = file_get_contents($composerPath);
            if (!is_string($json) || trim($json) === '') {
                return null;
            }

            $data = json_decode($json, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

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
     * @param array $viewSystemLogsPermissions Permissions required to view system logs (e.g., ['redirectManager:viewSystemLogs'])
     * @param array $downloadSystemLogsPermissions Permissions required to download system logs (e.g., ['redirectManager:downloadSystemLogs'])
     * @param array $options Additional options:
     *   - 'colorSets': array of color sets to register for badges/filters
     *     Example: ['myStatus' => ['active' => ['color' => '#10b981', 'rgb' => '16, 185, 129', 'text' => '#065f46']]]
     *   - 'logMenu': array to customize log sidebar menu (label/items)
     *     Example: ['label' => 'Logs', 'items' => ['system' => ['label' => 'System', 'url' => 'my-plugin/logs/system']]]
     *   - 'registerTranslations': bool to auto-register translations (default true)
     *   - 'translationCategory': string override for translation category (default plugin id)
     *   - 'translationBasePath': string override for translation base path (default {plugin}/translations)
     *   - 'installExperience': bool|array to enable the one-time CP install welcome experience
     *     Dev preview URL: ?lrInstallPreview={plugin-handle} on any plugin CP page
     *     Example: [
     *       'headline' => 'Canvas Studio is ready',
     *       'body' => 'Start by creating your first document or theme.',
     *       'redirectUri' => 'canvas-studio',
     *       'ctaLabel' => 'Open Canvas Studio',
     *       'ctaUrl' => 'canvas-studio',
     *       'accent' => '#0f766e',
     *     ]
     */
    public static function bootstrap(
        PluginInterface $plugin,
        string $helperVariableName,
        array $viewSystemLogsPermissions = [],
        array $downloadSystemLogsPermissions = [],
        array $options = [],
    ): void {
        // Register base module (idempotent - safe to call multiple times)
        Base::register();

        // Register the helper global when each Twig environment is created.
        // This avoids late addGlobal() timing issues and avoids extension
        // class-name collisions between multiple plugins.
        Event::on(
            \craft\web\View::class,
            \craft\web\View::EVENT_AFTER_CREATE_TWIG,
            static function(CreateTwigEvent $event) use ($plugin, $helperVariableName) {
                $event->twig->addGlobal($helperVariableName, new PluginNameHelper($plugin));
            }
        );

        // Configure logging library (if available and viewSystemLogsPermissions provided)
        // Only plugins that explicitly pass viewSystemLogsPermissions will have logging enabled
        if (!empty($viewSystemLogsPermissions) && class_exists(LoggingLibrary::class)) {
            $settings = $plugin->getSettings();

            // Get settings values with fallbacks
            $pluginName = $settings->pluginName ?? $plugin->name;
            $logLevel = $settings->logLevel ?? 'error';
            $itemsPerPage = $settings->itemsPerPage ?? 100;

            $logMenu = $options['logMenu'] ?? null;
            $logMenuItems = null;
            $logMenuLabel = null;

            if (is_array($logMenu)) {
                $logMenuItems = is_array($logMenu['items'] ?? null) ? $logMenu['items'] : null;
                $logMenuLabel = is_string($logMenu['label'] ?? null) ? $logMenu['label'] : null;
            }

            LoggingLibrary::configure([
                'pluginHandle' => $plugin->id,
                'pluginName' => $pluginName,
                'logLevel' => $logLevel,
                'itemsPerPage' => $itemsPerPage,
                'viewSystemLogsPermissions' => $viewSystemLogsPermissions,
                'downloadSystemLogsPermissions' => $downloadSystemLogsPermissions,
                'logMenuItems' => $logMenuItems,
                'logMenuLabel' => $logMenuLabel,
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

        // Register translations (if enabled and translations/ exists)
        $registerTranslations = $options['registerTranslations'] ?? true;
        if ($registerTranslations) {
            $category = is_string($options['translationCategory'] ?? null) ? $options['translationCategory'] : null;
            $basePath = is_string($options['translationBasePath'] ?? null) ? $options['translationBasePath'] : null;
            self::registerTranslations($plugin, $basePath, $category);
        }

        // Register one-time CP install experience
        if (($options['installExperience'] ?? true) !== false) {
            $installOptions = is_array($options['installExperience'] ?? null) ? $options['installExperience'] : [];
            InstallExperienceHelper::register($plugin, $installOptions);
        }
    }

    /**
     * Apply custom plugin name from config file or database settings
     *
     * Priority order:
     * 1. Config file (config/{plugin-handle}.php) — highest priority
     * 2. Database settings ($plugin->getSettings()->pluginName) — fallback
     * 3. Default plugin name — if neither is set
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
        // 1. Check config file first (highest priority)
        try {
            $safeId = basename($plugin->id);
            $configPath = Craft::$app->getPath()->getConfigPath() . '/' . $safeId . '.php';

            if (file_exists($configPath)) {
                $config = require $configPath;

                if (is_array($config)) {
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
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore config errors
        }

        // 2. Fall back to database settings
        try {
            $settings = $plugin->getSettings();
            if ($settings && property_exists($settings, 'pluginName')) {
                $dbName = trim($settings->pluginName);
                if ($dbName !== '' && $dbName !== $plugin->name) {
                    $plugin->name = $dbName;
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore - plugin continues with default name
        }
    }

    /**
     * Build a cache key prefix from a plugin handle and key type.
     *
     * Example: redirect-manager + device -> redirectmanager:device:
     *
     * @param string $handle Plugin handle
     * @param string $type Cache type (e.g., 'device')
     * @return string
     * @since 5.14.0
     */
    public static function getCacheKeyPrefix(string $handle, string $type): string
    {
        $safeHandle = str_replace('-', '', $handle);
        return $safeHandle . ':' . $type . ':';
    }

    /**
     * Build a Redis cache key set name for a plugin handle and key type.
     *
     * Example: redirect-manager + device -> redirectmanager-device-keys
     *
     * @param string $handle Plugin handle
     * @param string $type Cache type (e.g., 'device')
     * @return string
     * @since 5.14.0
     */
    public static function getCacheKeySet(string $handle, string $type): string
    {
        $safeHandle = str_replace('-', '', $handle);
        return $safeHandle . '-' . $type . '-keys';
    }

    /**
     * Register translations for a plugin
     *
     * Convenience method to register translation messages.
     * Alternative to doing it manually in each plugin.
     *
     * @param PluginInterface|string $pluginOrHandle Plugin instance or translation category handle
     * @param string|null $basePath Path to translations directory (required for handle-only usage)
     * @param string|null $category Translation category override (defaults to plugin id)
     * @since 5.14.0
     */
    public static function registerTranslations(PluginInterface|string $pluginOrHandle, ?string $basePath = null, ?string $category = null): void
    {
        if ($pluginOrHandle instanceof PluginInterface) {
            $category = $category ?: $pluginOrHandle->id;
            if ($basePath === null) {
                if ($pluginOrHandle instanceof \craft\base\Plugin) {
                    $basePath = $pluginOrHandle->getBasePath() . DIRECTORY_SEPARATOR . 'translations';
                } else {
                    $ref = new \ReflectionClass($pluginOrHandle);
                    $basePath = dirname($ref->getFileName()) . DIRECTORY_SEPARATOR . 'translations';
                }
            }
        } else {
            $category = $category ?: $pluginOrHandle;
        }

        if (!$category || !$basePath) {
            return;
        }

        $alreadyRegistered = isset(Craft::$app->i18n->translations[$category]);
        $hasTranslationsDir = is_dir($basePath);

        if ($alreadyRegistered || !$hasTranslationsDir) {
            return;
        }

        Craft::$app->i18n->translations[$category] = [
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
     * @since 5.5.0
     */
    public static function getCacheBasePath(PluginInterface $plugin): string
    {
        return Craft::$app->getPath()->getRuntimePath() . '/' . $plugin->id . '/cache/';
    }

    /**
     * Get a specific cache path for a plugin
     *
     * Returns: storage/runtime/{plugin-handle}/cache/{type}/
     *
     * @param PluginInterface $plugin The plugin instance
     * @param string $type Cache type (e.g., 'search', 'autocomplete', 'device')
     * @return string
     * @since 5.5.0
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
