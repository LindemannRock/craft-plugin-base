<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\geo;

/**
 * Geo Provider
 *
 * Built-in provider configurations for geo IP lookup services.
 * Provides standardized field mappings for different providers.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\geo\GeoProvider;
 *
 * $config = GeoProvider::getProvider('ipapi.co');
 * $options = GeoProvider::getProviderOptions(); // For dropdowns
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class GeoProvider
{
    /**
     * Built-in provider configurations
     *
     * @var array<string, array<string, mixed>>
     */
    public const PROVIDERS = [
        'ipapi.co' => [
            'name' => 'ipapi.co',
            'url' => 'https://ipapi.co/{ip}/json/',
            'https' => true,
            'rateLimit' => '1,000/day',
            'requiresKey' => false,
            'keyParam' => 'key',
            'fieldMap' => [
                'countryCode' => 'country_code',
                'country' => 'country_name',
                'region' => 'region',
                'city' => 'city',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
            ],
            'errorField' => 'error',
        ],
        'ipinfo.io' => [
            'name' => 'ipinfo.io',
            'url' => 'https://ipinfo.io/{ip}/json',
            'https' => true,
            'rateLimit' => '50,000/month',
            'requiresKey' => false,
            'keyParam' => 'token',
            'fieldMap' => [
                'countryCode' => 'country',
                'country' => 'country',
                'region' => 'region',
                'city' => 'city',
            ],
            'errorField' => 'error',
        ],
        'ip-api.com' => [
            'name' => 'ip-api.com',
            'url' => 'http://ip-api.com/json/{ip}?fields=status,countryCode,country,city,regionName,lat,lon',
            'https' => false,
            'httpsUrl' => 'https://pro.ip-api.com/json/{ip}?fields=status,countryCode,country,city,regionName,lat,lon',
            'rateLimit' => '45/minute',
            'requiresKey' => false,
            'keyParam' => 'key',
            'fieldMap' => [
                'countryCode' => 'countryCode',
                'country' => 'country',
                'region' => 'regionName',
                'city' => 'city',
                'latitude' => 'lat',
                'longitude' => 'lon',
            ],
            'successField' => 'status',
            'successValue' => 'success',
        ],
    ];

    /**
     * Get provider config by name
     *
     * @param string $name Provider name (e.g., 'ipapi.co', 'ipinfo.io', 'ip-api.com')
     * @return array<string, mixed>|null Provider configuration or null if not found
     * @since 5.0.0
     */
    public static function getProvider(string $name): ?array
    {
        return self::PROVIDERS[$name] ?? null;
    }

    /**
     * Get all providers for dropdown
     *
     * @return array<string, string> Provider options with labels
     * @since 5.0.0
     */
    public static function getProviderOptions(): array
    {
        return [
            'ip-api.com' => 'ip-api.com (HTTP free, HTTPS paid)',
            'ipapi.co' => 'ipapi.co (HTTPS, 1k/day free)',
            'ipinfo.io' => 'ipinfo.io (HTTPS, 50k/month free)',
        ];
    }
}
