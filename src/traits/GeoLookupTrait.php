<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

use lindemannrock\base\geo\GeoLookup;

/**
 * Geo Lookup Trait
 *
 * Provides geo IP lookup functionality for plugin services.
 * Uses the centralized GeoLookup class from the base module.
 *
 * Usage:
 * ```php
 * class AnalyticsService extends Component
 * {
 *     use GeoLookupTrait;
 *
 *     protected function getGeoConfig(): array
 *     {
 *         $settings = MyPlugin::$plugin->getSettings();
 *         return [
 *             'provider' => $settings->geoProvider,
 *             'apiKey' => $settings->geoApiKey,
 *         ];
 *     }
 *
 *     public function trackVisitor(string $ip): void
 *     {
 *         $geoData = $this->lookupGeoIp($ip, $this->getGeoConfig());
 *         if ($geoData) {
 *             // Use $geoData['countryCode'], $geoData['city'], etc.
 *         }
 *     }
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
trait GeoLookupTrait
{
    private ?GeoLookup $geoLookup = null;

    /**
     * Get geo data for an IP address
     *
     * @param string $ip IP address
     * @param array<string, mixed> $config Config override (provider, apiKey)
     * @return array<string, mixed>|null Normalized geo data or null on failure/private IP
     */
    protected function lookupGeoIp(string $ip, array $config = []): ?array
    {
        if ($this->geoLookup === null || !empty($config)) {
            $this->geoLookup = new GeoLookup($config);
        }

        return $this->geoLookup->lookup($ip);
    }

    /**
     * Get geo config from plugin settings
     *
     * Override this method in your service to provide plugin-specific settings.
     *
     * @return array<string, mixed> Config array with provider, apiKey
     */
    protected function getGeoConfig(): array
    {
        return [
            'provider' => 'ip-api.com',
            'apiKey' => null,
        ];
    }
}
