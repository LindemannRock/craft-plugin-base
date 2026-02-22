# GeoHelper @since(5.3.0)

Country names, dial codes, phone number validation, and country select options. Uses ISO 3166-1 alpha-2 country codes.

## Country Names

```php
use lindemannrock\base\helpers\GeoHelper;

GeoHelper::getCountryName('US');   // "United States"
GeoHelper::getCountryName('GB');   // "United Kingdom"
GeoHelper::getCountryName('XX');   // "XX" (returns original code if unknown)

// Get all countries
$countries = GeoHelper::getAllCountries();
// ['AD' => 'Andorra', 'AE' => 'United Arab Emirates', ...]

// Validate a country code
GeoHelper::isValidCountryCode('US');   // true
GeoHelper::isValidCountryCode('ZZ');   // false
```

## Dial Codes @since(5.7.0)

Dial codes are returned as strings without the `+` prefix. The `+` is added only in display formatting.

```php
GeoHelper::getDialCode('US');   // "1"
GeoHelper::getDialCode('DE');   // "49"
GeoHelper::getDialCode('KW');   // "965"
GeoHelper::getDialCode('XX');   // null (unknown)

// Get all dial codes
$codes = GeoHelper::getAllDialCodes();
// ['AD' => '376', 'AE' => '971', ...]

// Formatted display string
GeoHelper::getCountryWithDialCode('KW');  // "Kuwait (+965)"
```

> [!NOTE]
> Some dial codes are shared by multiple countries (e.g., `1` for US and CA, `44` for GB and GG).

## Structured Country Data @since(5.16.0)

Get all countries with their dial codes as structured objects, sorted by country name.

```php
$data = GeoHelper::getCountryDialCodeData();
// [
//     ['countryCode' => 'AF', 'dialCode' => '93', 'countryName' => 'Afghanistan'],
//     ['countryCode' => 'AL', 'dialCode' => '355', 'countryName' => 'Albania'],
//     ...
// ]
```

## Country Select Options @since(5.7.0)

Generate options for dropdown fields with country name and dial code.

```php
$options = GeoHelper::getCountryDialCodeOptions();
// [
//     ['label' => 'Afghanistan (+93)', 'value' => 'AF'],
//     ['label' => 'Albania (+355)', 'value' => 'AL'],
//     ...
// ]

// With "All Countries" option at the top
$options = GeoHelper::getCountryDialCodeOptions(true);
// [['label' => 'All Countries', 'value' => '*'], ['label' => 'Afghanistan (+93)', ...], ...]
```

## Phone Number Validation @since(5.7.0)

Check if a phone number matches an allowed list of country dial codes.

```php
// Allow only Kuwait and Saudi Arabia
$allowed = ['KW', 'SA'];

GeoHelper::isPhoneNumberAllowed('+96512345678', $allowed);  // true (Kuwait)
GeoHelper::isPhoneNumberAllowed('96612345678', $allowed);   // true (Saudi Arabia)
GeoHelper::isPhoneNumberAllowed('+1234567890', $allowed);   // false (US)

// Allow all countries
GeoHelper::isPhoneNumberAllowed('+1234567890', ['*']);       // true
```

The method strips the `+` prefix and checks if the number starts with any of the allowed countries' dial codes.

## Twig Usage

Geo functions are available in templates after bootstrap:

```twig
{# Structured data for all countries with dial codes #}
{% set data = lrCountryDialCodeData() %}
{# [{countryCode: 'KW', dialCode: '965', countryName: 'Kuwait'}, ...] #}
{% for item in data %}
    {{ item.countryCode }} +{{ item.dialCode }} - {{ item.countryName }}
{% endfor %}

{# Single lookups #}
{{ lrCountryName('US') }}                  {# "United States" #}
{{ lrDialCode('DE') }}                     {# "49" #}
{{ lrCountryWithDialCode('KW') }}          {# "Kuwait (+965)" #}

{# All countries including those without dial codes #}
{% set countries = lrCountries() %}

{# Validation #}
{% if lrValidCountryCode('US') %}...{% endif %}
```

## Next Steps

- [GeoLookupTrait](geo-lookup.md) — IP geolocation in service classes
- [Components](../template-guides/components.md) — phone-input component with country code selector
