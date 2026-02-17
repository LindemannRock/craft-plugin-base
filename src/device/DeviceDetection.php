<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\device;

use Craft;
use DeviceDetector\DeviceDetector;

/**
 * Device Detection
 *
 * Centralized user-agent parsing and device normalization.
 * Designed to be reused by multiple plugins via DeviceDetectionTrait.
 *
 * @since 5.2.0
 */
class DeviceDetection
{
    private const DEFAULT_COUNTRY_LANGUAGE_MAP = [
        'SA' => 'ar',
        'AE' => 'ar',
        'KW' => 'ar',
        'QA' => 'ar',
        'BH' => 'ar',
        'OM' => 'ar',
        'EG' => 'ar',
        'JO' => 'ar',
        'LB' => 'ar',
        'IQ' => 'ar',
        'SY' => 'ar',
        'YE' => 'ar',
        'LY' => 'ar',
        'TN' => 'ar',
        'DZ' => 'ar',
        'MA' => 'ar',
        'US' => 'en',
        'GB' => 'en',
        'CA' => 'en',
        'AU' => 'en',
        'NZ' => 'en',
        'IE' => 'en',
    ];
    private array $config;
    private ?DeviceDetector $detector = null;
    private static bool $redisFallbackLogged = false;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Detect device information from a user agent.
     *
     * @param string|null $userAgent
     * @param array<string, mixed> $overrideConfig
     * @return array<string, mixed>
     */
    public function detect(?string $userAgent = null, array $overrideConfig = []): array
    {
        $config = array_replace($this->config, $overrideConfig);
        $userAgent = $userAgent ?? Craft::$app->getRequest()->getUserAgent() ?? '';

        if (!empty($config['cacheEnabled']) && $userAgent) {
            $cached = $this->getCachedDeviceInfo($userAgent, $config);
            if ($cached !== null) {
                return $cached;
            }
        }

        $detector = $this->getDetector();
        $detector->setUserAgent($userAgent);
        $detector->parse();

        $deviceInfo = [
            'userAgent' => $userAgent,
            'deviceType' => null,
            'deviceBrand' => null,
            'deviceModel' => null,
            'osName' => null,
            'osVersion' => null,
            'browser' => null,
            'browserVersion' => null,
            'browserEngine' => null,
            'clientType' => null,
            'isRobot' => false,
            'isMobileApp' => false,
            'botName' => null,
            'isMobile' => null,
            'isTablet' => null,
            'isDesktop' => null,
            'platform' => null,
            'vendor' => null,
            'language' => null,
        ];

        if ($detector->isBot()) {
            $deviceInfo['isRobot'] = true;
            $botInfo = $detector->getBot();
            $deviceInfo['botName'] = $botInfo['name'] ?? null;
            if (!empty($config['includePlatform'])) {
                $deviceInfo['platform'] = 'other';
            }
            if (!empty($config['cacheEnabled']) && $userAgent) {
                $this->cacheDeviceInfo($userAgent, $deviceInfo, $config);
            }
            return $deviceInfo;
        }

        $deviceType = $detector->getDeviceName();
        $deviceInfo['deviceType'] = strtolower($deviceType ?: 'desktop');
        $deviceInfo['isMobile'] = $detector->isMobile();
        $deviceInfo['isTablet'] = $detector->isTablet();
        $deviceInfo['isDesktop'] = $detector->isDesktop();
        $deviceInfo['isMobileApp'] = $detector->isMobileApp();

        $deviceInfo['deviceBrand'] = $detector->getBrandName() ?: null;
        $deviceInfo['deviceModel'] = $detector->getModel() ?: null;

        $osInfo = $detector->getOs();
        if ($osInfo) {
            $deviceInfo['osName'] = $osInfo['name'] ?? null;
            $deviceInfo['osVersion'] = $osInfo['version'] ?? null;
        }

        $clientInfo = $detector->getClient();
        if ($clientInfo) {
            $deviceInfo['clientType'] = $clientInfo['type'] ?? null;
            $deviceInfo['browser'] = $clientInfo['name'] ?? null;
            $deviceInfo['browserVersion'] = $clientInfo['version'] ?? null;
            $deviceInfo['browserEngine'] = $clientInfo['engine'] ?? null;
        }

        if (!empty($config['includePlatform'])) {
            $platformInfo = $this->normalizePlatformVendor(
                $deviceInfo['osName'],
                $userAgent,
                $deviceInfo['deviceBrand']
            );
            $deviceInfo['platform'] = $platformInfo['platform'];
            $deviceInfo['vendor'] = $platformInfo['vendor'];
        }

        if (!empty($config['includeLanguage'])) {
            $deviceInfo['language'] = $this->detectLanguage($config);
        }

        if (!empty($config['cacheEnabled']) && $userAgent) {
            $this->cacheDeviceInfo($userAgent, $deviceInfo, $config);
        }

        return $deviceInfo;
    }

    /**
     * Check if device is mobile (phone or tablet).
     *
     * @param array<string, mixed> $deviceInfo
     */
    public function isMobileDevice(array $deviceInfo): bool
    {
        return in_array($deviceInfo['deviceType'] ?? '', ['mobile', 'tablet', 'smartphone', 'phablet'], true);
    }

    /**
     * Check if device is a tablet.
     *
     * @param array<string, mixed> $deviceInfo
     */
    public function isTablet(array $deviceInfo): bool
    {
        return ($deviceInfo['deviceType'] ?? '') === 'tablet';
    }

    /**
     * Check if device is desktop.
     *
     * @param array<string, mixed> $deviceInfo
     */
    public function isDesktop(array $deviceInfo): bool
    {
        return ($deviceInfo['deviceType'] ?? 'desktop') === 'desktop';
    }

    /**
     * Check if device is a bot.
     *
     * @param array<string, mixed> $deviceInfo
     */
    public function isBot(array $deviceInfo): bool
    {
        return (bool)($deviceInfo['isRobot'] ?? false);
    }

    /**
     * Detect language from request.
     *
     * @param array<string, mixed> $config
     */
    public function detectLanguage(array $config = []): string
    {
        $request = Craft::$app->getRequest();
        $detectedLang = null;

        $langParam = $request->getQueryParam('lang') ?? $request->getQueryParam('locale');
        if ($langParam) {
            $detectedLang = substr($langParam, 0, 2);
        }

        if (!$detectedLang) {
            $method = $config['languageDetectionMethod'] ?? 'browser';
            switch ($method) {
                case 'ip':
                    $detectedLang = $this->detectFromIp($config);
                    break;
                case 'both':
                    $detectedLang = $this->detectFromBrowser();
                    if (!$detectedLang) {
                        $detectedLang = $this->detectFromIp($config);
                    }
                    break;
                case 'browser':
                default:
                    $detectedLang = $this->detectFromBrowser();
                    break;
            }
        }

        if (!$detectedLang) {
            $detectedLang = substr(Craft::$app->language, 0, 2);
        }

        $supportedLanguages = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $supportedLanguages[] = substr($site->language, 0, 2);
        }
        $supportedLanguages = array_unique($supportedLanguages);

        if (!in_array($detectedLang, $supportedLanguages, true)) {
            $detectedLang = substr(Craft::$app->getSites()->getPrimarySite()->language, 0, 2);
        }

        return $detectedLang;
    }

    /**
     * Normalize platform/vendor from OS and user agent.
     *
     * @param string|null $osName
     * @param string $userAgent
     * @param string|null $brand
     * @return array{platform: string, vendor: string|null}
     */
    private function normalizePlatformVendor(?string $osName, string $userAgent, ?string $brand): array
    {
        $platform = 'other';
        $vendor = null;
        $os = strtolower($osName ?? '');

        if (str_contains($os, 'ios') || str_contains($os, 'iphone') || str_contains($os, 'ipad')) {
            $platform = 'ios';
            $vendor = 'Apple';
        } elseif (str_contains($os, 'android')) {
            $ua = strtolower($userAgent);
            if (str_contains($ua, 'harmonyos') || str_contains($ua, 'huawei') || str_contains($ua, 'honor')) {
                $platform = 'huawei';
                $vendor = 'Huawei';
            } else {
                $platform = 'android';
            }
        } elseif (str_contains($os, 'windows phone')) {
            $platform = 'windows';
            $vendor = 'Microsoft';
        } elseif (str_contains($os, 'windows')) {
            $platform = 'windows';
        } elseif (str_contains($os, 'mac') || str_contains($os, 'os x')) {
            $platform = 'macos';
            $vendor = 'Apple';
        } elseif (str_contains($os, 'linux') || str_contains($os, 'ubuntu')) {
            $platform = 'linux';
        }

        if (!$vendor && $brand) {
            $vendor = $brand;
        }

        return [
            'platform' => $platform,
            'vendor' => $vendor,
        ];
    }

    private function detectFromBrowser(): ?string
    {
        $acceptLanguage = Craft::$app->getRequest()->getHeaders()->get('Accept-Language');
        if (!$acceptLanguage) {
            return null;
        }

        $languages = [];
        $parts = explode(',', $acceptLanguage);
        foreach ($parts as $part) {
            $lang = explode(';', $part);
            $code = substr(trim($lang[0]), 0, 2);
            $quality = isset($lang[1]) ? (float) str_replace('q=', '', $lang[1]) : 1.0;
            $languages[$code] = $quality;
        }

        arsort($languages);
        return array_key_first($languages);
    }

    private function detectFromIp(array $config): ?string
    {
        if (empty($config['enableGeoDetection'])) {
            return null;
        }

        $ip = Craft::$app->getRequest()->getUserIP();
        if (!$ip) {
            return null;
        }

        $lookup = $config['geoLookupCallback'] ?? null;
        if (!is_callable($lookup)) {
            return null;
        }

        $location = $lookup($ip);
        $countryCode = null;

        if (is_array($location)) {
            $countryCode = $location['countryCode'] ?? null;
        } elseif (is_string($location)) {
            $countryCode = $location;
        }

        if (!$countryCode) {
            return null;
        }

        $map = $config['countryLanguageMap'] ?? self::DEFAULT_COUNTRY_LANGUAGE_MAP;
        return $map[$countryCode] ?? null;
    }

    private function getDetector(): DeviceDetector
    {
        if ($this->detector === null) {
            $this->detector = new DeviceDetector();
        }

        return $this->detector;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getCachedDeviceInfo(string $userAgent, array $config): ?array
    {
        $cacheKey = $this->getCacheKey($userAgent, $config);
        $cacheStorage = $config['cacheStorageMethod'] ?? 'file';

        if ($cacheStorage === 'redis') {
            $cache = Craft::$app->cache;
            if ($cache instanceof \yii\redis\Cache) {
                $cached = $cache->get($cacheKey);
                return $cached !== false ? $cached : null;
            }

            if (!self::$redisFallbackLogged) {
                $this->logWarning(
                    'Redis cache selected but Craft cache is not Redis; falling back to file cache',
                    $config
                );
                self::$redisFallbackLogged = true;
            }
        }

        $cachePath = $config['cachePath'] ?? null;
        if (!$cachePath) {
            return null;
        }

        $cacheFile = rtrim($cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($userAgent) . '.cache';
        if (!file_exists($cacheFile)) {
            return null;
        }

        $duration = (int)($config['cacheDuration'] ?? 0);
        if ($duration > 0) {
            $mtime = filemtime($cacheFile);
            if ($mtime && (time() - $mtime > $duration)) {
                @unlink($cacheFile);
                return null;
            }
        }

        $data = file_get_contents($cacheFile);
        $decoded = json_decode((string)$data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            @unlink($cacheFile);
            return null;
        }

        return $decoded;
    }

    private function cacheDeviceInfo(string $userAgent, array $data, array $config): void
    {
        $cacheKey = $this->getCacheKey($userAgent, $config);
        $cacheStorage = $config['cacheStorageMethod'] ?? 'file';
        $duration = (int)($config['cacheDuration'] ?? 0);

        if ($cacheStorage === 'redis') {
            $cache = Craft::$app->cache;
            if ($cache instanceof \yii\redis\Cache) {
                $cache->set($cacheKey, $data, $duration);

                $cacheKeySet = $config['cacheKeySet'] ?? null;
                if ($cacheKeySet) {
                    $cache->redis->executeCommand('SADD', [$cacheKeySet, $cacheKey]);
                }
                return;
            }
        }

        $cachePath = $config['cachePath'] ?? null;
        if (!$cachePath) {
            return;
        }

        try {
            if (!is_dir($cachePath)) {
                \craft\helpers\FileHelper::createDirectory($cachePath);
            }

            $cacheFile = rtrim($cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($userAgent) . '.cache';
            file_put_contents($cacheFile, json_encode($data), LOCK_EX);
        } catch (\Throwable $e) {
            $this->logError('Failed to cache device info', $config, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getCacheKey(string $userAgent, array $config): string
    {
        $prefix = $config['cacheKeyPrefix'] ?? 'device:';
        return $prefix . md5($userAgent);
    }

    /**
     * Map device info array to a model instance.
     *
     * @param array<string, mixed> $data
     * @param class-string $class
     * @param array<string, string> $map Map of target => source keys
     */
    public function toModel(array $data, string $class, array $map = []): object
    {
        $model = new $class();

        if (!empty($map)) {
            $mapped = [];
            foreach ($map as $target => $source) {
                $mapped[$target] = $data[$source] ?? null;
            }
            $data = $mapped;
        }

        if (method_exists($model, 'setAttributes')) {
            $model->setAttributes($data, false);
            return $model;
        }

        foreach ($data as $key => $value) {
            if (property_exists($model, $key)) {
                $model->{$key} = $value;
            }
        }

        return $model;
    }

    private function logWarning(string $message, array $config, array $context = []): void
    {
        $logger = $config['logWarning'] ?? null;
        if (is_callable($logger)) {
            $logger($message, $context);
            return;
        }

        Craft::warning($message, __METHOD__);
    }

    private function logError(string $message, array $config, array $context = []): void
    {
        $logger = $config['logError'] ?? null;
        if (is_callable($logger)) {
            $logger($message, $context);
            return;
        }

        Craft::error($message, __METHOD__);
    }
}
