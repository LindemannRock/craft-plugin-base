<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Analytics IP Helper
 *
 * Shared IP preprocessing for analytics tracking across plugins.
 *
 * @author LindemannRock
 * @since 5.20.0
 */
class AnalyticsIpHelper
{
    /**
     * Prepare an IP for analytics storage and optional geo lookup.
     *
     * @param string|null $rawIp
     * @param bool $anonymizeIp
     * @param bool $enableGeoDetection
     * @param callable(string): string $hashIp
     * @return array{
     *   processedIp: string|null,
     *   hashedIp: string|null,
     *   geoLookupIp: string|null,
     *   hashError: \Throwable|null
     * }
     */
    public static function prepare(?string $rawIp, bool $anonymizeIp, bool $enableGeoDetection, callable $hashIp): array
    {
        $processedIp = $rawIp;
        $hashedIp = null;
        $geoLookupIp = null;
        $hashError = null;

        if ($anonymizeIp) {
            $processedIp = self::anonymize($processedIp);
        }

        if ($processedIp) {
            try {
                $hashedIp = $hashIp($processedIp);

                if ($enableGeoDetection) {
                    $geoLookupIp = $processedIp;
                }
            } catch (\Throwable $e) {
                $processedIp = null;
                $hashError = $e;
            }
        }

        return [
            'processedIp' => $processedIp,
            'hashedIp' => $hashedIp,
            'geoLookupIp' => $geoLookupIp,
            'hashError' => $hashError,
        ];
    }

    /**
     * Anonymize an IP address with subnet masking.
     *
     * IPv4: Mask last octet (192.168.1.123 -> 192.168.1.0)
     * IPv6: Mask last 80 bits (keep first 48 bits)
     *
     * @param string|null $ip
     * @return string|null
     */
    public static function anonymize(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $binary = inet_pton($ip);
            if ($binary === false) {
                return $ip;
            }

            $anonymized = substr($binary, 0, 6) . str_repeat("\0", 10);
            $result = inet_ntop($anonymized);

            return $result !== false ? $result : $ip;
        }

        return $ip;
    }
}
