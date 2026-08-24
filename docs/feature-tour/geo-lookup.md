# GeoLookupTrait @since(5.7.0)

IP geolocation for service classes. Resolves IP addresses to country, city, and region data using configurable providers.

## Setup

Add the trait to your service. Override `getGeoConfig()` to pull settings from your plugin (optional — defaults to `ip-api.com` with no API key):

```php
use yii\base\Component;
use lindemannrock\base\traits\GeoLookupTrait;

class AnalyticsService extends Component
{
    use GeoLookupTrait;

    protected function getGeoConfig(): array
    {
        $settings = MyPlugin::$plugin->getSettings();

        return [
            'provider' => $settings->geoProvider,
            'apiKey' => $settings->geoApiKey,
        ];
    }
}
```

## Looking Up an IP

```php
$geoData = $this->lookupGeoIp($ipAddress);

if ($geoData) {
    $geoData['countryCode'];  // 'US'
    $geoData['country'];      // 'United States'
    $geoData['region'];       // 'New York'
    $geoData['city'];         // 'New York'
    $geoData['latitude'];     // 40.7128
    $geoData['longitude'];    // -74.0060
}
```

Returns `null` for private/reserved IP addresses and on lookup failure.

### With Config Override

Pass config directly for one-off lookups:

```php
$geoData = $this->lookupGeoIp($ip, [
    'provider' => 'ip-api.com',
    'apiKey' => null,
]);
```

## Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `provider` | `string` | `'ip-api.com'` | Geo lookup provider |
| `apiKey` | `string\|null` | `null` | API key (if provider requires one) |

### Available Providers

| Provider | Free Tier | Protocol | Notes |
|----------|-----------|----------|-------|
| `ip-api.com` | 45 requests/min | HTTP (free), HTTPS (with API key) | Default provider |
| `ipapi.co` | 1,000 requests/day | HTTPS | No API key required for free tier |
| `ipinfo.io` | 50,000 requests/month | HTTPS | Base can call it without a key; pass `apiKey` when your account or tier requires a token |

## Typical Usage

```php
class AnalyticsService extends Component
{
    use GeoLookupTrait;

    public function trackVisitor(string $ip): void
    {
        $geoData = $this->lookupGeoIp($ip, $this->getGeoConfig());

        if ($geoData) {
            // Store country and city with analytics data
            $record->countryCode = $geoData['countryCode'];
            $record->city = $geoData['city'];
            $record->save();
        }
    }
}
```

## Direct class API

The trait delegates to two public classes:

```php
use lindemannrock\base\geo\GeoLookup;
use lindemannrock\base\geo\GeoProvider;

$lookup = new GeoLookup([
    'provider' => 'ipapi.co',
    'apiKey' => null,
    'timeout' => 2,
]);

$geoData = $lookup->lookup('8.8.8.8');
$providerOptions = GeoProvider::getProviderOptions();
```

`GeoProvider::getProvider($name)` returns the built-in URL, field mapping, rate-limit label, and response markers for one provider, or `null` for an unknown name.

## Next Steps

- [GeoHelper](geo-helper.md) — country names, dial codes, and phone validation
- [DeviceDetectionTrait](device-detection.md) — user-agent parsing (often used alongside geo lookup)
