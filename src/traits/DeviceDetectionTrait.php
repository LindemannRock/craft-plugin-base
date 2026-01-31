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
 * @since 5.2.0
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

        if ($this->deviceDetection === null || !empty($overrideConfig)) {
            $this->deviceDetection = new DeviceDetection($config);
        }

        return $this->deviceDetection->detect($userAgent, $config);
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
