<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\device;

use Craft;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use lindemannrock\base\helpers\PluginHelper;

/**
 * Device Detection
 *
 * Centralized user-agent parsing and device normalization.
 * Designed to be reused by multiple plugins via DeviceDetectionTrait.
 *
 * @since 5.14.0
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

    /**
     * @var array<string, array<string, mixed>>
     */
    private const DEFAULT_SYSTEM_AGENTS = [
        'CacheManager/1.0' => [
            'name' => 'Cache Manager',
            'category' => 'Service Agent',
            'producerName' => 'LindemannRock',
        ],
    ];

    private array $config;
    private ?DeviceDetector $detector = null;

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
        $clientHints = $this->buildClientHints($config);
        $clientHintsData = $this->normalizeClientHints($clientHints);

        if (!empty($config['cacheEnabled']) && $userAgent) {
            $cached = $this->getCachedDeviceInfo($userAgent, $config, $clientHintsData);
            if ($cached !== null) {
                return $cached;
            }
        }

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
            'botCategory' => null,
            'botUrl' => null,
            'botProducerName' => null,
            'botProducerUrl' => null,
            'isSystemAgent' => false,
            'trafficType' => 'human',
            'isMobile' => null,
            'isTablet' => null,
            'isDesktop' => null,
            'platform' => null,
            'vendor' => null,
            'language' => null,
            'clientHints' => $clientHintsData,
            'clientHintsUsed' => $clientHints !== null,
            'architecture' => $clientHintsData['architecture'] ?? null,
            'bitness' => $clientHintsData['bitness'] ?? null,
            'formFactors' => $clientHintsData['formFactors'] ?? [],
            'appId' => $clientHintsData['app'] ?? null,
        ];

        $systemAgent = $this->matchSystemAgent($userAgent, $config);
        if ($systemAgent !== null) {
            $deviceInfo['isRobot'] = true;
            $deviceInfo['isSystemAgent'] = true;
            $deviceInfo['trafficType'] = 'system';
            $deviceInfo['botName'] = $systemAgent['name'] ?? null;
            $deviceInfo['botCategory'] = $systemAgent['category'] ?? null;
            $deviceInfo['botUrl'] = $systemAgent['url'] ?? null;
            $deviceInfo['botProducerName'] = $systemAgent['producerName'] ?? null;
            $deviceInfo['botProducerUrl'] = $systemAgent['producerUrl'] ?? null;
            if (!empty($config['includePlatform'])) {
                $deviceInfo['platform'] = 'system';
                $deviceInfo['vendor'] = $systemAgent['producerName'] ?? null;
            }
            if (!empty($config['cacheEnabled']) && $userAgent) {
                $this->cacheDeviceInfo($userAgent, $deviceInfo, $config, $clientHintsData);
            }
            return $deviceInfo;
        }

        $detector = $this->getDetector();
        $detector->setUserAgent($userAgent);
        $detector->setClientHints($clientHints);
        $detector->parse();

        if ($detector->isBot()) {
            $deviceInfo['isRobot'] = true;
            $deviceInfo['trafficType'] = 'bot';
            $botInfo = $detector->getBot();
            if (is_array($botInfo)) {
                $deviceInfo['botName'] = $botInfo['name'] ?? null;
                $deviceInfo['botCategory'] = $botInfo['category'] ?? null;
                $deviceInfo['botUrl'] = $botInfo['url'] ?? null;
                $producer = $botInfo['producer'] ?? null;
                if (is_array($producer)) {
                    $deviceInfo['botProducerName'] = $producer['name'] ?? null;
                    $deviceInfo['botProducerUrl'] = $producer['url'] ?? null;
                }
            }
            if (!empty($config['includePlatform'])) {
                $deviceInfo['platform'] = 'other';
            }
            if (!empty($config['cacheEnabled']) && $userAgent) {
                $this->cacheDeviceInfo($userAgent, $deviceInfo, $config, $clientHintsData);
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
            $this->cacheDeviceInfo($userAgent, $deviceInfo, $config, $clientHintsData);
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
        if (is_string($langParam) && $langParam !== '') {
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
    private function getCachedDeviceInfo(string $userAgent, array $config, array $clientHintsData = []): ?array
    {
        $cacheKey = $this->getCacheKey($userAgent, $config, $clientHintsData);
        $cacheStorage = $config['cacheStorageMethod'] ?? 'file';

        if ($cacheStorage === 'redis') {
            $cache = PluginHelper::getRedisCacheOrLog($this->getPluginContext($config));
            if ($cache !== null) {
                $cached = $cache->get($cacheKey);
                return $cached !== false ? $cached : null;
            }
        }

        $cachePath = $config['cachePath'] ?? null;
        if (!$cachePath) {
            return null;
        }

        $cacheFile = rtrim($cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($this->getCacheIdentity($userAgent, $clientHintsData)) . '.cache';
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

    private function cacheDeviceInfo(string $userAgent, array $data, array $config, array $clientHintsData = []): void
    {
        $cacheKey = $this->getCacheKey($userAgent, $config, $clientHintsData);
        $cacheStorage = $config['cacheStorageMethod'] ?? 'file';
        $duration = (int)($config['cacheDuration'] ?? 0);

        if ($cacheStorage === 'redis') {
            $cache = PluginHelper::getRedisCacheOrLog($this->getPluginContext($config));
            if ($cache !== null) {
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

            $cacheFile = rtrim($cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($this->getCacheIdentity($userAgent, $clientHintsData)) . '.cache';
            file_put_contents($cacheFile, json_encode($data), LOCK_EX);
        } catch (\Throwable $e) {
            $this->logError('Failed to cache device info', $config, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getCacheKey(string $userAgent, array $config, array $clientHintsData = []): string
    {
        $prefix = $config['cacheKeyPrefix'] ?? 'device:';
        return $prefix . md5($this->getCacheIdentity($userAgent, $clientHintsData));
    }

    /**
     * @param array<string, mixed> $clientHintsData
     */
    private function getCacheIdentity(string $userAgent, array $clientHintsData = []): string
    {
        if (empty($clientHintsData)) {
            return $userAgent;
        }

        $encoded = json_encode($clientHintsData);
        if (!is_string($encoded)) {
            return $userAgent;
        }

        return $userAgent . '|' . $encoded;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildClientHints(array $config): ?ClientHints
    {
        $configuredHints = $config['clientHints'] ?? null;
        if ($configuredHints instanceof ClientHints) {
            return $configuredHints;
        }
        if (is_array($configuredHints)) {
            return ClientHints::factory($configuredHints);
        }

        if (($config['includeClientHints'] ?? true) === false) {
            return null;
        }

        $request = Craft::$app->getRequest();
        if (!method_exists($request, 'getHeaders')) {
            return null;
        }

        $headers = $request->getHeaders();
        $hintHeaders = [];
        foreach ([
            'Sec-CH-UA',
            'Sec-CH-UA-Arch',
            'Sec-CH-UA-Bitness',
            'Sec-CH-UA-Full-Version',
            'Sec-CH-UA-Full-Version-List',
            'Sec-CH-UA-Mobile',
            'Sec-CH-UA-Model',
            'Sec-CH-UA-Platform',
            'Sec-CH-UA-Platform-Version',
            'Sec-CH-UA-Form-Factors',
            'X-Requested-With',
        ] as $name) {
            $value = $headers->get($name);
            if (is_string($value) && $value !== '') {
                $hintHeaders[$name] = $value;
            }
        }

        return !empty($hintHeaders) ? ClientHints::factory($hintHeaders) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeClientHints(?ClientHints $clientHints): array
    {
        if ($clientHints === null) {
            return [];
        }

        return array_filter([
            'model' => $clientHints->getModel() ?: null,
            'platform' => $clientHints->getOperatingSystem() ?: null,
            'platformVersion' => $clientHints->getOperatingSystemVersion() ?: null,
            'brandVersion' => $clientHints->getBrandVersion() ?: null,
            'brands' => $clientHints->getBrandList(),
            'mobile' => $clientHints->isMobile(),
            'architecture' => $clientHints->getArchitecture() ?: null,
            'bitness' => $clientHints->getBitness() ?: null,
            'app' => $clientHints->getApp() ?: null,
            'formFactors' => $clientHints->getFormFactors(),
        ], static fn($value): bool => $value !== null && $value !== [] && $value !== '');
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>|null
     */
    private function matchSystemAgent(string $userAgent, array $config): ?array
    {
        if ($userAgent === '') {
            return null;
        }

        $customAgents = $config['systemAgents'] ?? [];
        $agents = array_replace(self::DEFAULT_SYSTEM_AGENTS, is_array($customAgents) ? $customAgents : []);
        foreach ($agents as $key => $agent) {
            if (!is_array($agent)) {
                continue;
            }

            $match = $agent['userAgent'] ?? $key;
            if (is_string($match) && $match !== '' && $userAgent === $match) {
                return $this->normalizeSystemAgent($agent);
            }

            $pattern = $agent['pattern'] ?? null;
            if (is_string($pattern) && $pattern !== '' && @preg_match($pattern, $userAgent) === 1) {
                return $this->normalizeSystemAgent($agent);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $agent
     * @return array{name: string|null, category: string|null, url: string|null, producerName: string|null, producerUrl: string|null}
     */
    private function normalizeSystemAgent(array $agent): array
    {
        $producer = $agent['producer'] ?? null;

        return [
            'name' => is_string($agent['name'] ?? null) ? $agent['name'] : null,
            'category' => is_string($agent['category'] ?? null) ? $agent['category'] : null,
            'url' => is_string($agent['url'] ?? null) ? $agent['url'] : null,
            'producerName' => $this->normalizeSystemAgentProducerValue($agent, $producer, 'producerName', 'name'),
            'producerUrl' => $this->normalizeSystemAgentProducerValue($agent, $producer, 'producerUrl', 'url'),
        ];
    }

    /**
     * @param array<string, mixed> $agent
     * @param mixed $producer
     */
    private function normalizeSystemAgentProducerValue(
        array $agent,
        mixed $producer,
        string $agentKey,
        string $producerKey,
    ): ?string {
        if (is_string($agent[$agentKey] ?? null)) {
            return $agent[$agentKey];
        }

        if (is_array($producer) && is_string($producer[$producerKey] ?? null)) {
            return $producer[$producerKey];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getPluginContext(array $config): string
    {
        $pluginHandle = $config['pluginHandle'] ?? null;
        if (is_string($pluginHandle) && $pluginHandle !== '') {
            return $pluginHandle;
        }

        return 'lindemannrock-base';
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
