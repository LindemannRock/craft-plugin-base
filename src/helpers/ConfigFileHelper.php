<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;

/**
 * Helpers for reading handle-keyed plugin config sections.
 *
 * @since 5.26.0
 */
class ConfigFileHelper
{
    /**
     * @var array<string, array>
     */
    private static array $_configCache = [];

    /**
     * Get the full config array for a plugin handle.
     *
     * @param string $pluginHandle
     * @return array
     */
    public static function getConfig(string $pluginHandle): array
    {
        if (!array_key_exists($pluginHandle, self::$_configCache)) {
            try {
                $config = Craft::$app->getConfig()->getConfigFromFile($pluginHandle);
            } catch (\Throwable) {
                $config = [];
            }

            self::$_configCache[$pluginHandle] = is_array($config) ? $config : [];
        }

        return self::$_configCache[$pluginHandle];
    }

    /**
     * Get a named section from a plugin config file.
     *
     * @param string $pluginHandle
     * @param string $section
     * @return array
     */
    public static function getConfigSection(string $pluginHandle, string $section): array
    {
        $config = self::getConfig($pluginHandle);
        $sectionConfig = $config[$section] ?? [];

        return is_array($sectionConfig) ? $sectionConfig : [];
    }

    /**
     * Check whether a handle exists in a handle-keyed config section.
     *
     * @param string $pluginHandle
     * @param string $section
     * @param string $handle
     * @return bool
     */
    public static function handleExistsInConfig(string $pluginHandle, string $section, string $handle): bool
    {
        $configs = self::getConfigSection($pluginHandle, $section);
        return array_key_exists($handle, $configs);
    }

    /**
     * Get one config item from a handle-keyed config section.
     *
     * @param string $pluginHandle
     * @param string $section
     * @param string $handle
     * @return array|null
     */
    public static function getConfigByHandle(string $pluginHandle, string $section, string $handle): ?array
    {
        $configs = self::getConfigSection($pluginHandle, $section);
        $config = $configs[$handle] ?? null;

        return is_array($config) ? $config : null;
    }

    /**
     * Get all handles from a handle-keyed config section.
     *
     * @param string $pluginHandle
     * @param string $section
     * @return array
     */
    public static function getHandles(string $pluginHandle, string $section): array
    {
        return array_keys(self::getConfigSection($pluginHandle, $section));
    }

    /**
     * Merge config-sourced items with database items.
     *
     * Config items take precedence over database items with the same handle.
     * Returns an array keyed by handle.
     *
     * @param array $configItems Items from config file, keyed by handle
     * @param array $databaseItems Database items, each with a `handle` property or key
     * @return array
     */
    public static function mergeConfigAndDatabase(array $configItems, array $databaseItems): array
    {
        $merged = $configItems;
        $configHandles = array_keys($configItems);

        foreach ($databaseItems as $item) {
            $handle = null;

            if (is_object($item) && isset($item->handle)) {
                $handle = $item->handle;
            } elseif (is_array($item) && isset($item['handle'])) {
                $handle = $item['handle'];
            }

            if (is_string($handle) && $handle !== '' && !in_array($handle, $configHandles, true)) {
                $merged[$handle] = $item;
            }
        }

        return $merged;
    }

    /**
     * Clear cached config for one plugin handle, or for all plugin handles.
     *
     * @param string|null $pluginHandle
     */
    public static function clearCache(?string $pluginHandle = null): void
    {
        if ($pluginHandle === null) {
            self::$_configCache = [];
            return;
        }

        unset(self::$_configCache[$pluginHandle]);
    }
}
