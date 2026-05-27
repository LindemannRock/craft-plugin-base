<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

use lindemannrock\base\device\DeviceDetection;

/**
 * Device Detection Trait
 *
 * Provides shared user-agent parsing across plugins.
 *
 * Usage:
 * ```php
 * class AnalyticsService extends Component
 * {
 *     use DeviceDetectionTrait;
 *
 *     protected function getDeviceDetectionConfig(): array
 *     {
 *         $settings = MyPlugin::$plugin->getSettings();
 *         return [
 *             'cacheEnabled' => $settings->cacheDeviceDetection,
 *             'cacheStorageMethod' => $settings->cacheStorageMethod,
 *             'cacheDuration' => $settings->deviceDetectionCacheDuration,
 *             'pluginHandle' => MyPlugin::$plugin->id,
 *             'cachePath' => PluginHelper::getCachePath(MyPlugin::$plugin, 'device'),
 *             'cacheKeyPrefix' => 'myplugin:device:',
 *             'cacheKeySet' => 'myplugin-device-keys',
 *             'includeLanguage' => true,
 *             'includePlatform' => true,
 *         ];
 *     }
 * }
 * ```
 *
 * @since 5.14.0
 */
trait DeviceDetectionTrait
{
    private ?DeviceDetection $deviceDetection = null;

    /**
     * Detect device information from a user agent.
     *
     * @param string|null $userAgent
     * @param array<string, mixed> $overrideConfig
     * @return array<string, mixed>
     */
    protected function detectDeviceInfo(?string $userAgent = null, array $overrideConfig = []): array
    {
        $config = array_replace($this->getDeviceDetectionConfig(), $overrideConfig);

        // Prefer plugin logging if available — use closures to preserve protected visibility
        if (!isset($config['logWarning']) && method_exists($this, 'logWarning')) {
            $config['logWarning'] = fn(string $msg, array $ctx = []) => $this->logWarning($msg, $ctx);
        }
        if (!isset($config['logError']) && method_exists($this, 'logError')) {
            $config['logError'] = fn(string $msg, array $ctx = []) => $this->logError($msg, $ctx);
        }

        if ($this->deviceDetection === null || !empty($overrideConfig)) {
            $this->deviceDetection = new DeviceDetection($config);
        }

        return $this->deviceDetection->detect($userAgent, $config);
    }

    /**
     * Detect language based on device detection config.
     *
     * @param array<string, mixed> $overrideConfig
     */
    protected function detectLanguageFromConfig(array $overrideConfig = []): string
    {
        $config = array_replace($this->getDeviceDetectionConfig(), $overrideConfig);

        // Use closures to preserve protected visibility
        if (!isset($config['logWarning']) && method_exists($this, 'logWarning')) {
            $config['logWarning'] = fn(string $msg, array $ctx = []) => $this->logWarning($msg, $ctx);
        }
        if (!isset($config['logError']) && method_exists($this, 'logError')) {
            $config['logError'] = fn(string $msg, array $ctx = []) => $this->logError($msg, $ctx);
        }

        if ($this->deviceDetection === null || !empty($overrideConfig)) {
            $this->deviceDetection = new DeviceDetection($config);
        }

        return $this->deviceDetection->detectLanguage($config);
    }

    /**
     * Build a device info model from raw detection data.
     *
     * @param array<string, mixed> $data
     * @param class-string $class
     * @param array<string, string> $map Map of target => source keys
     */
    protected function buildDeviceModel(array $data, string $class, array $map = []): object
    {
        if ($this->deviceDetection === null) {
            $this->deviceDetection = new DeviceDetection();
        }

        return $this->deviceDetection->toModel($data, $class, $map);
    }

    /**
     * Override in your service to provide plugin-specific settings.
     *
     * @return array<string, mixed>
     */
    protected function getDeviceDetectionConfig(): array
    {
        return [];
    }
}
