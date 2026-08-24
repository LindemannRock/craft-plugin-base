# DeviceDetectionTrait @since(5.14.0)

User-agent parsing for device type, browser, and OS detection. Uses the [Matomo Device Detector](https://github.com/matomo-org/device-detector) library with optional caching.

## Setup

Add the trait to your analytics service and implement `getDeviceDetectionConfig()`:

```php
use yii\base\Component;
use lindemannrock\base\traits\DeviceDetectionTrait;
use lindemannrock\base\helpers\PluginHelper;

class AnalyticsService extends Component
{
    use DeviceDetectionTrait;

    protected function getDeviceDetectionConfig(): array
    {
        $settings = MyPlugin::$plugin->getSettings();

        return [
            'cacheEnabled' => $settings->cacheDeviceDetection,
            'cacheStorageMethod' => $settings->cacheStorageMethod,
            'cacheDuration' => $settings->deviceDetectionCacheDuration,
            'pluginHandle' => MyPlugin::$plugin->id,
            'cachePath' => PluginHelper::getCachePath(MyPlugin::$plugin, 'device'),
            'cacheKeyPrefix' => PluginHelper::getCacheKeyPrefix('my-plugin', 'device'),
            'includeLanguage' => true,
            'includePlatform' => true,
        ];
    }
}
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `cacheEnabled` | `bool` | `false` | Enable detection result caching |
| `cacheStorageMethod` | `string` | `'file'` | Cache storage token: `'file'`, `'redis'`, or `'craft'`; File automatically uses a suitable application cache on ephemeral hosts |
| `cacheDuration` | `int` | `3600` | Cache TTL in seconds |
| `pluginHandle` | `string` | `'lindemannrock-base'` | Plugin context used for Redis cache-component warning logs |
| `cachePath` | `string` | `''` | File cache directory path |
| `cacheKeyPrefix` | `string` | `''` | Cache key prefix |
| `includeLanguage` | `bool` | `false` | Include browser language detection |
| `includePlatform` | `bool` | `false` | Include detailed platform info |
| `includeClientHints` | `bool` | `true` | Read browser Client Hints from the current request and pass them to Matomo Device Detector |
| `clientHints` | `array\|ClientHints` | `null` | Explicit Client Hints for non-request contexts or tests |
| `systemAgents` | `array` | Includes `CacheManager/1.0` | First-party service agents to classify separately from human and external bot traffic |

## Portable cache behavior @since(5.38.0)

Device detection uses Base's application-cache contract rather than raw Redis commands or a Redis key registry:

- `redis` and `craft` both select Craft's exposed application cache.
- `file` uses the configured `cachePath` on durable hosts.
- `file` automatically switches to a suitable application cache when `App::isEphemeral()` is true and never touches the local cache path there.
- Application-cache values use [`ScopedCache`](scoped-cache.md) with the consumer plugin handle and the `device` family.
- Missing, unsuitable, malformed, or failing cache backends degrade to recomputation.

Use a positive `cacheDuration` for application-cache writes. `cacheKeyPrefix` remains part of the hashed device identity for compatibility, but `cacheKeySet` is no longer consumed.

## Detecting Device Info

```php
$info = $this->detectDeviceInfo($userAgent);
// Returns: [
//     'userAgent' => 'Mozilla/5.0 ...',
//     'deviceType' => 'desktop',       // desktop, smartphone, tablet, bot, etc.
//     'deviceBrand' => 'Apple',
//     'deviceModel' => 'MacBook Pro',
//     'osName' => 'Windows',
//     'osVersion' => '11',
//     'browser' => 'Chrome',
//     'browserVersion' => '120.0',
//     'browserEngine' => 'Blink',
//     'clientType' => 'browser',
//     'isRobot' => false,
//     'isMobileApp' => false,
//     'botName' => null,
//     'botCategory' => null,
//     'botUrl' => null,
//     'botProducerName' => null,
//     'botProducerUrl' => null,
//     'isSystemAgent' => false,
//     'trafficType' => 'human',       // human, bot, or system
//     'isMobile' => false,
//     'isTablet' => false,
//     'isDesktop' => true,
//     'platform' => 'x64',            // If includePlatform is true
//     'vendor' => 'Apple',
//     'language' => 'en',             // If includeLanguage is true
//     'clientHints' => [],
//     'clientHintsUsed' => false,
//     'architecture' => null,
//     'bitness' => null,
//     'formFactors' => [],
//     'appId' => null,
// ]

// Without user agent (uses current request)
$info = $this->detectDeviceInfo();

// Override config for a single call
$info = $this->detectDeviceInfo($userAgent, [
    'includeLanguage' => true,
]);
```

## Bot and System-Agent Classification

Matomo Device Detector identifies known crawler and service-agent user agents.
Base preserves the bot name, category, source URL, and producer metadata when
Matomo provides it.

First-party service agents can be configured separately from public bots. Base
ships with `CacheManager/1.0` classified as a system agent so analytics
consumers can separate cache warming from human traffic:

```php
$info = $this->detectDeviceInfo('CacheManager/1.0');
// $info['trafficType'] === 'system'
// $info['isSystemAgent'] === true
```

Add plugin-specific service agents with exact user-agent strings or trusted
regular expressions:

```php
return [
    'systemAgents' => [
        'MyWarmup/1.0' => [
            'name' => 'My Warmup',
            'category' => 'Service Agent',
            'producerName' => 'My Plugin',
        ],
        [
            'pattern' => '/^MyWorker\/\d+\.\d+/',
            'name' => 'My Worker',
            'category' => 'Service Agent',
            'producer' => [
                'name' => 'My Plugin',
                'url' => 'https://example.com',
            ],
        ],
    ],
];
```

## Client Hints

When available, base passes browser Client Hints to Matomo Device Detector and
also exposes the normalized hint data in the returned array. This improves
device, OS, browser, architecture, app, and form-factor detection for modern
reduced user-agent strings.

In normal web requests no extra setup is required. For queued jobs, imports, or
tests, pass explicit hint data:

```php
$info = $this->detectDeviceInfo($userAgent, [
    'clientHints' => [
        'Sec-CH-UA-Model' => '"Pixel 8"',
        'Sec-CH-UA-Platform' => '"Android"',
        'Sec-CH-UA-Mobile' => '?1',
        'Sec-CH-UA-Arch' => '"arm"',
        'Sec-CH-UA-Bitness' => '"64"',
        'Sec-CH-UA-Form-Factors' => '"Mobile"',
        'X-Requested-With' => 'com.example.app',
    ],
]);
```

Client Hints are included in device-detection cache keys, so the same reduced
user-agent string can cache different results for different hint sets.

## Detecting Language

```php
$language = $this->detectLanguageFromConfig();
// Returns: 'en', 'de', 'fr', etc.
```

## Building Device Models

Map detection data to a plugin-specific model class:

```php
$model = $this->buildDeviceModel($data, MyDeviceInfo::class, [
    'device' => 'deviceType',
    'browserName' => 'browser',
]);
```

## Direct class API

Most consumers use `DeviceDetectionTrait`, but `lindemannrock\base\device\DeviceDetection` is also public:

| Method | Purpose |
|---|---|
| `detect(?string $userAgent = null, array $overrideConfig = [])` | Return normalized device, client, bot, platform, language, and Client Hints data. |
| `isMobileDevice(array $deviceInfo)` | Check phone/tablet-style device types. |
| `isTablet(array $deviceInfo)` | Check the normalized tablet type. |
| `isDesktop(array $deviceInfo)` | Check the normalized desktop type. |
| `isBot(array $deviceInfo)` | Check the normalized robot flag. |
| `detectLanguage(array $config = [])` | Resolve a supported language from query, browser, optional IP mapping, and Craft site fallbacks. |
| `toModel(array $data, string $class, array $map = [])` | Map normalized data to a consumer model. |

## Next Steps

- [PluginHelper](plugin-helper.md) — cache path helpers for device detection storage
- [Disposable cache storage](disposable-cache-storage.md) — choose and present effective cache storage
- [Scoped cache](scoped-cache.md) — application-cache isolation and invalidation
- [GeoLookupTrait](geo-lookup.md) — IP geolocation (often used alongside device detection)
