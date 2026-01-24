<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\geo;

use Craft;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * Geo Lookup
 *
 * Performs geo IP lookups using configurable providers.
 * Returns normalized geo data regardless of provider used.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\geo\GeoLookup;
 *
 * $lookup = new GeoLookup([
 *     'provider' => 'ipapi.co',
 *     'apiKey' => null,
 * ]);
 *
 * $geoData = $lookup->lookup('8.8.8.8');
 * // Returns: ['countryCode' => 'US', 'country' => 'United States', 'city' => 'Mountain View', ...]
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class GeoLookup
{
    use LoggingTrait;

    /**
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * Track if HTTP warning has been logged this request
     */
    private static bool $httpWarningLogged = false;

    /**
     * @param array<string, mixed> $config Configuration options:
     *   - provider: Provider name (default: 'ip-api.com')
     *   - apiKey: API key for paid tiers (default: null)
     *   - timeout: Request timeout in seconds (default: 2)
     * @since 5.0.0
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'provider' => 'ip-api.com',
            'apiKey' => null,
            'timeout' => 2,
        ], $config);

        $this->setLoggingHandle('base');
    }

    /**
     * Lookup geo data for an IP address
     *
     * @param string $ip IP address to lookup
     * @return array<string, mixed>|null Normalized geo data or null on failure
     * @since 5.0.0
     */
    public function lookup(string $ip): ?array
    {
        if ($this->isPrivateIp($ip)) {
            return null;
        }

        try {
            $url = $this->buildUrl($ip);

            if ($url === null) {
                return null;
            }

            // Warn once per request if using HTTP
            if (str_starts_with($url, 'http://') && !self::$httpWarningLogged) {
                $this->logWarning('Geo lookup using HTTP - IP addresses exposed in transit. Consider HTTPS provider.', [
                    'provider' => $this->config['provider'],
                ]);
                self::$httpWarningLogged = true;
            }

            $response = $this->fetch($url);

            if ($response === null) {
                return null;
            }

            return $this->normalizeResponse($response);
        } catch (\Throwable $e) {
            $this->logWarning('Geo lookup failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Build the API URL for the configured provider
     */
    private function buildUrl(string $ip): ?string
    {
        $provider = $this->config['provider'];
        $providerConfig = GeoProvider::getProvider($provider);

        if ($providerConfig === null) {
            return null;
        }

        // Use HTTPS URL if API key provided and provider supports it
        if (!empty($this->config['apiKey']) && isset($providerConfig['httpsUrl'])) {
            $url = $providerConfig['httpsUrl'];
        } else {
            $url = $providerConfig['url'];
        }

        // Replace {ip} placeholder
        $url = str_replace('{ip}', urlencode($ip), $url);

        // Add API key if configured
        if (!empty($this->config['apiKey'])) {
            $keyParam = $providerConfig['keyParam'] ?? 'key';
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . $keyParam . '=' . urlencode($this->config['apiKey']);
        }

        return $url;
    }

    /**
     * Fetch data from URL
     *
     * @return array<string, mixed>|null
     */
    private function fetch(string $url): ?array
    {
        $client = Craft::createGuzzleClient([
            'timeout' => $this->config['timeout'],
            'headers' => [
                'User-Agent' => 'CraftCMS-Plugin/1.0',
            ],
        ]);

        try {
            $response = $client->get($url);
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            $this->logDebug('Geo fetch failed', ['url' => $this->sanitizeUrl($url), 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Remove API key from URL for logging
     */
    private function sanitizeUrl(string $url): string
    {
        // Remove common API key parameter patterns
        return preg_replace('/([?&])(key|token|apiKey)=[^&]+/', '$1$2=***', $url) ?? $url;
    }

    /**
     * Normalize response to standard format
     *
     * @param array<string, mixed> $data Raw API response
     * @return array<string, mixed>|null Normalized data or null on error
     */
    private function normalizeResponse(array $data): ?array
    {
        $provider = $this->config['provider'];
        $providerConfig = GeoProvider::getProvider($provider);

        // Check for errors
        if ($providerConfig !== null) {
            if (isset($providerConfig['errorField']) && isset($data[$providerConfig['errorField']])) {
                return null;
            }
            if (isset($providerConfig['successField'])) {
                $successValue = $providerConfig['successValue'] ?? true;
                if (($data[$providerConfig['successField']] ?? null) !== $successValue) {
                    return null;
                }
            }
        }

        // Map fields to standard format
        $fieldMap = $providerConfig['fieldMap'] ?? [];

        $result = [
            'countryCode' => null,
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        foreach ($result as $key => $value) {
            $sourceField = $fieldMap[$key] ?? $key;
            $result[$key] = $data[$sourceField] ?? null;
        }

        // Special handling for ipinfo.io "loc" field (lat,lon format)
        if ($provider === 'ipinfo.io' && isset($data['loc'])) {
            $parts = explode(',', $data['loc']);
            if (count($parts) === 2) {
                $result['latitude'] = (float) $parts[0];
                $result['longitude'] = (float) $parts[1];
            }
        }

        // Cast lat/lon to float
        if ($result['latitude'] !== null) {
            $result['latitude'] = (float) $result['latitude'];
        }
        if ($result['longitude'] !== null) {
            $result['longitude'] = (float) $result['longitude'];
        }

        return $result;
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
