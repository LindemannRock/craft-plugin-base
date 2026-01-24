<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use craft\base\PluginInterface;

/**
 * Plugin Name Helper
 *
 * Helper class that exposes Settings methods as properties for clean Twig syntax.
 * Used by PluginNameExtension to provide plugin name helpers in templates.
 *
 * This class acts as a proxy to the plugin's Settings model, delegating
 * calls to the displayName methods provided by SettingsDisplayNameTrait.
 *
 * Allows property-style access in Twig:
 * ```twig
 * {{ helper.displayName }}  {# Instead of {{ helper.getDisplayName() }} #}
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class PluginNameHelper
{
    /**
     * @var PluginInterface The plugin instance
     */
    private PluginInterface $plugin;

    /**
     * Constructor
     *
     * @param PluginInterface $plugin The plugin instance
     * @since 5.0.0
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get display name (singular, without "Manager")
     *
     * @return string
     * @since 5.0.0
     */
    public function getDisplayName(): string
    {
        $settings = $this->plugin->getSettings();
        if ($settings && method_exists($settings, 'getDisplayName')) {
            return $settings->getDisplayName();
        }
        return $this->plugin->name;
    }

    /**
     * Get plural display name (without "Manager")
     *
     * @return string
     * @since 5.0.0
     */
    public function getPluralDisplayName(): string
    {
        $settings = $this->plugin->getSettings();
        if ($settings && method_exists($settings, 'getPluralDisplayName')) {
            return $settings->getPluralDisplayName();
        }
        return $this->plugin->name . 's';
    }

    /**
     * Get full plugin name (as configured)
     *
     * @return string
     * @since 5.0.0
     */
    public function getFullName(): string
    {
        $settings = $this->plugin->getSettings();
        if ($settings && method_exists($settings, 'getFullName')) {
            return $settings->getFullName();
        }
        return $this->plugin->name;
    }

    /**
     * Get lowercase display name (singular)
     *
     * @return string
     * @since 5.0.0
     */
    public function getLowerDisplayName(): string
    {
        $settings = $this->plugin->getSettings();
        if ($settings && method_exists($settings, 'getLowerDisplayName')) {
            return $settings->getLowerDisplayName();
        }
        return strtolower($this->plugin->name);
    }

    /**
     * Get lowercase plural display name
     *
     * @return string
     * @since 5.0.0
     */
    public function getPluralLowerDisplayName(): string
    {
        $settings = $this->plugin->getSettings();
        if ($settings && method_exists($settings, 'getPluralLowerDisplayName')) {
            return $settings->getPluralLowerDisplayName();
        }
        return strtolower($this->plugin->name) . 's';
    }

    /**
     * Get cache base path for the plugin
     *
     * Returns the display path (relative): storage/runtime/{handle}/cache/
     *
     * @return string
     * @since 5.5.0
     */
    public function getCacheBasePath(): string
    {
        return 'storage/runtime/' . $this->plugin->handle . '/cache/';
    }

    /**
     * Get cache path for a specific type
     *
     * Returns the display path (relative): storage/runtime/{handle}/cache/{type}/
     *
     * @param string $type Cache type (e.g., 'device', 'qr', 'search')
     * @return string
     * @since 5.5.0
     */
    public function getCachePath(string $type): string
    {
        return $this->getCacheBasePath() . $type . '/';
    }

    /**
     * Magic getter to allow property-style access in Twig
     *
     * Enables: {{ helper.displayName }} instead of {{ helper.getDisplayName() }}
     *
     * @param string $name Property name
     * @return string|null
     * @since 5.0.0
     */
    public function __get(string $name): ?string
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }
}
