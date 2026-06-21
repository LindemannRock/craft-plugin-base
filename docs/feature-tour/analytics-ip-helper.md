# AnalyticsIpHelper @since(5.20.0)

`AnalyticsIpHelper` prepares a visitor IP for analytics tracking in one step — anonymizing it, hashing it for storage, and deciding which form (if any) is safe to send to a geo-lookup provider. Use it in any plugin that records request analytics so the privacy rules stay identical across the suite.

It is a pure transform: you pass in the raw IP and your privacy toggles, and it hands back the processed values. It never stores anything itself.

## prepare()

```php
use lindemannrock\base\helpers\AnalyticsIpHelper;

$result = AnalyticsIpHelper::prepare(
    $request->getUserIP(),
    anonymizeIp: $settings->anonymizeIp,
    enableGeoDetection: $settings->enableGeoDetection,
    hashIp: fn(string $ip): string => Craft::$app->getSecurity()->hashData($ip),
);

// $result:
// [
//     'processedIp'  => '203.0.113.0',   // anonymized when anonymizeIp = true
//     'hashedIp'     => '…',             // result of your hashIp callable, or null
//     'geoLookupIp'  => '203.0.113.0',   // ip to send to the geo provider, or null
//     'hashError'    => null,            // \Throwable if hashIp threw
// ]
```

The `$hashIp` callable is yours — typically Craft's security component or a salted hash. If it throws, `prepare()` does not bubble the exception: `processedIp` is set to `null` and the throwable is returned in `hashError` so the caller can log it and continue without dropping the request.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$rawIp` | `string\|null` | Raw client IP (e.g. from `$request->getUserIP()`) |
| `$anonymizeIp` | `bool` | Mask the host portion before processing |
| `$enableGeoDetection` | `bool` | Whether a `geoLookupIp` should be produced |
| `$hashIp` | `callable` | `fn(string $ip): string` — your hashing strategy |

When `$anonymizeIp` is `true`, `geoLookupIp` is the anonymized address — geo lookups still resolve to the right country/region, but the full address never leaves your server.

## anonymize()

```php
AnalyticsIpHelper::anonymize('203.0.113.42');
// "203.0.113.0"  — last IPv4 octet zeroed

AnalyticsIpHelper::anonymize('2001:db8:1234:5678:9abc:def0:1234:5678');
// "2001:db8:1234::"  — last 80 bits zeroed, first 48 kept
```

`anonymize()` masks the last octet of an IPv4 address and the last 80 bits of an IPv6 address (keeping the first 48). It returns `null` for an empty or `null` input, and returns a value that is neither valid IPv4 nor IPv6 unchanged.

## Next Steps

- [GeoLookupTrait](geo-lookup.md) — resolve an IP (anonymized or not) to country/region/city
- [GeoHelper](geo-helper.md) — country names, dial codes, and validation
