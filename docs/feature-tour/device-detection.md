# DeviceDetectionTrait

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
            'cachePath' => PluginHelper::getCachePath(MyPlugin::$plugin, 'device'),
            'cacheKeyPrefix' => PluginHelper::getCacheKeyPrefix('my-plugin', 'device'),
            'cacheKeySet' => PluginHelper::getCacheKeySet('my-plugin', 'device'),
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
| `cacheStorageMethod` | `string` | `'file'` | Cache storage: `'file'` or `'redis'` |
| `cacheDuration` | `int` | `3600` | Cache TTL in seconds |
| `cachePath` | `string` | `''` | File cache directory path |
| `cacheKeyPrefix` | `string` | `''` | Cache key prefix |
| `cacheKeySet` | `string` | `''` | Redis key set name |
| `includeLanguage` | `bool` | `false` | Include browser language detection |
| `includePlatform` | `bool` | `false` | Include detailed platform info |

## Detecting Device Info

```php
$info = $this->detectDeviceInfo($userAgent);
// Returns: [
//     'deviceType' => 'desktop',     // desktop, smartphone, tablet, bot, etc.
//     'browser' => 'Chrome',
//     'browserVersion' => '120.0',
//     'osName' => 'Windows',
//     'osVersion' => '11',
//     'isRobot' => false,
//     'botName' => null,
//     'language' => 'en',            // If includeLanguage is true
//     'platform' => 'x64',          // If includePlatform is true
// ]

// Without user agent (uses current request)
$info = $this->detectDeviceInfo();

// Override config for a single call
$info = $this->detectDeviceInfo($userAgent, [
    'includeLanguage' => true,
]);
```

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

## Next Steps

- [PluginHelper](plugin-helper.md) — cache path helpers for device detection storage
- [GeoLookupTrait](geo-lookup.md) — IP geolocation (often used alongside device detection)
