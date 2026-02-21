# GeoLookupTrait

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

## Next Steps

- [GeoHelper](geo-helper.md) — country names, dial codes, and phone validation
- [DeviceDetectionTrait](device-detection.md) — user-agent parsing (often used alongside geo lookup)
