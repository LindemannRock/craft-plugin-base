# API Reference

Quick reference for all public PHP classes, methods, and traits in the base module. Each entry links to its detailed documentation page.

## Helpers

### ColorHelper

`lindemannrock\base\helpers\ColorHelper`
[Full docs](../feature-tour/color-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getPaletteColor(string $name)` | `array` | Get palette color by name |
| `getPaletteColorNames()` | `array` | List all 18 palette color names |
| `getColorSet(string $name)` | `array` | Get all colors in a set |
| `getSetColor(string $set, string $key)` | `array` | Get one color from a set |
| `registerColorSet(string $name, array $colors)` | `void` | Register a custom color set |
| `hasColorSet(string $name)` | `bool` | Check if a color set exists |
| `getAvailableColorSets()` | `array` | List all color set names |
| `getNeutralColor()` | `string` | Neutral gray hex (`#aab6c1`) |
| `getDefaultColor()` | `array` | Default fallback color array |
| `getFilterColor(string $set, string $key, ?string $current)` | `string` | Hex color for filter display |

### DateFormatHelper

`lindemannrock\base\helpers\DateFormatHelper`
[Full docs](../feature-tour/date-format-helper.md)

**Display formatting:**

| Method | Returns | Description |
|--------|---------|-------------|
| `formatDatetime($dt, $length, $showSeconds, $year, $isUtc)` | `string` | Full datetime |
| `formatCompactDatetime($dt, $showSeconds, $isUtc)` | `string` | Datetime without year |
| `formatDate($dt, $length, $year, $isUtc)` | `string` | Date only |
| `formatTime($dt, $length, $showSeconds, $isUtc)` | `string` | Time only |
| `formatShortDate($dt, $isUtc)` | `string` | Short date for charts |
| `formatRelative($dt, $isUtc)` | `string` | Relative time |

**Machine formatting:**

| Method | Returns | Description |
|--------|---------|-------------|
| `toDateTimeString($dt)` | `string` | `Y-m-d H:i:s` |
| `toDateString($dt)` | `string` | `Y-m-d` |
| `toDayStartString($dt)` | `string` | `Y-m-d 00:00:00` |
| `toDayEndString($dt)` | `string` | `Y-m-d 23:59:59` |
| `toApiString($dt)` | `string` | ISO 8601 |
| `toFilenameString($dt, $includeTime)` | `string` | `Y-m-d-His` |

**Utilities:**

| Method | Returns | Description |
|--------|---------|-------------|
| `now()` | `DateTime` | Current time in Craft timezone |
| `isToday($dt, $isUtc)` | `bool` | Check if today |
| `isPast($dt, $isUtc)` | `bool` | Check if in past |
| `isFuture($dt, $isUtc)` | `bool` | Check if in future |

**SQL expressions:**

| Method | Returns | Description |
|--------|---------|-------------|
| `localDateExpression(string $column, Query $query)` | `string` | DB-agnostic timezone date expression |
| `localHourExpression(string $column, Query $query)` | `string` | DB-agnostic timezone hour expression |
| `getCraftTimezoneOffset()` | `string` | Timezone offset string (e.g., `'+02:00'`) |

### DateRangeHelper

`lindemannrock\base\helpers\DateRangeHelper`
[Full docs](../feature-tour/date-range-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getDefaultDateRange(?string $handle)` | `string` | Default date range from config |
| `getOptions(string $format)` | `array` | Date range options for dropdowns |
| `normalize(?string $range, ?string $handle)` | `string` | Normalize range key with fallback |
| `getBounds(string $range)` | `array` | `[DateTime $start, DateTime $end]` |
| `applyToQuery(Query $query, string $column, string $range)` | `void` | Add date range WHERE to query |
| `getDaysCount(string $range)` | `int` | Number of days in range |

### ExportHelper

`lindemannrock\base\helpers\ExportHelper`
[Full docs](../feature-tour/export-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `isFormatEnabled(string $format, ?string $handle)` | `bool` | Check if format is enabled |
| `getEnabledFormats(?string $handle)` | `array` | List enabled format keys |
| `getFormatOptions()` | `array` | Options for select fields |
| `filename($settings, $parts, ?string $ext)` | `string` | Generate export filename |
| `assertNotEmpty(array $data)` | `void` | Throw if data is empty |
| `toCsv(array $rows, array $headers, string $file, array $dateCols)` | `Response` | CSV download response |
| `toJson(array $rows, string $file, array $dateCols)` | `Response` | JSON download response |
| `toExcel(array $rows, array $headers, string $file, array $dateCols, array $opts)` | `Response` | Excel download response |
| `toMultiSheetExcel(array $sheets, string $file)` | `Response` | Multi-sheet Excel |
| `toZip(array $files, string $file)` | `Response` | ZIP archive download |

### GeoHelper

`lindemannrock\base\helpers\GeoHelper`
[Full docs](../feature-tour/geo-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getCountryName(string $code)` | `string` | Country name by code |
| `getAllCountries()` | `array` | All countries (code => name) |
| `isValidCountryCode(string $code)` | `bool` | Validate country code |
| `getDialCode(string $code)` | `?string` | Dial code (e.g., `'1'`, no `+`) |
| `getAllDialCodes()` | `array` | All dial codes |
| `getCountryDialCodeOptions()` | `array` | Options for select fields |
| `getCountryDialCodeData()` | `array` | All countries with `{countryCode, dialCode, countryName}`, sorted by name |
| `getCountryWithDialCode(string $code)` | `string` | Formatted name with dial code (e.g., `"Kuwait (+965)"`) |
| `isPhoneNumberAllowed(string $phone, array $allowed)` | `bool` | Validate against allowed countries |

### PluginHelper

`lindemannrock\base\helpers\PluginHelper`
[Full docs](../feature-tour/plugin-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `bootstrap($plugin, $helper, $viewPerms, $dlPerms, $opts)` | `void` | Initialize base module |
| `applyPluginNameFromConfig(PluginInterface $plugin)` | `void` | Override name from config |
| `isPluginEnabled(string $handle)` | `bool` | Plugin installed and enabled? |
| `isPluginInstalled(string $handle)` | `bool` | Plugin installed? |
| `getPlugin(string $handle)` | `?PluginInterface` | Get plugin instance |
| `getPluginName(string $handle, ?string $fallback)` | `string` | Plugin display name |
| `getCacheBasePath(PluginInterface $plugin)` | `string` | Cache base directory |
| `getCachePath(PluginInterface $plugin, string $type)` | `string` | Typed cache directory |
| `getCacheKeyPrefix(string $handle, string $type)` | `string` | Cache key prefix |
| `getCacheKeySet(string $handle, string $type)` | `string` | Redis key set name |
| `registerTranslations($plugin, ?string $path, ?string $cat)` | `void` | Register translation source |

### DbHelper

`lindemannrock\base\helpers\DbHelper`
[Full docs](../feature-tour/db-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `jsonExtract(Query $query, string $column, string $path)` | `string` | JSON extract with bound param |
| `jsonExtractExpression(string $column, string $path)` | `string` | Raw JSON extract SQL |
| `groupConcat(string $column, string $separator, bool $distinct)` | `string` | DB-agnostic GROUP_CONCAT |

### CsvImportHelper

`lindemannrock\base\helpers\CsvImportHelper`
[Full docs](../feature-tour/csv-import-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `parseUpload(UploadedFile $file, array $options)` | `array` | Parse CSV file into rows |

### CpNavHelper

`lindemannrock\base\helpers\CpNavHelper`
[Full docs](../feature-tour/cp-nav-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `buildSubnav(PluginInterface $plugin, array $sections)` | `array` | Build subnav array |
| `firstAccessibleRoute(array $sections)` | `?string` | First route user can access |

---

## Traits

### SettingsPersistenceTrait

[Full docs](../feature-tour/settings-persistence.md) — Save/load settings to a database table.

| Method | Description |
|--------|-------------|
| `tableName(): string` | **(abstract)** Return table name |
| `booleanFields(): array` | Fields to cast as boolean |
| `integerFields(): array` | Fields to cast as integer |
| `floatFields(): array` | Fields to cast as float |
| `jsonFields(): array` | Fields to JSON encode/decode |
| `excludeFromSave(): array` | Properties to exclude |
| `loadFromDatabase(): static` | Load settings from DB |
| `saveToDatabase(): bool` | Save settings to DB |

### SettingsConfigTrait

[Full docs](../feature-tour/settings-config.md) — Detect config file overrides.

| Method | Description |
|--------|-------------|
| `pluginHandle(): string` | **(abstract)** Return plugin handle |
| `isOverriddenByConfig(string $key): bool` | Check if setting is defined in config file |
| `validateLogLevel(): void` | Validate log level (debug requires devMode) |

### SettingsDisplayNameTrait

[Full docs](../feature-tour/settings-display-name.md) — Plugin display name methods.

| Method | Returns | Description |
|--------|---------|-------------|
| `getDisplayName()` | `string` | Singular name without "Manager" |
| `getFullName()` | `string` | Full plugin name |
| `getPluralDisplayName()` | `string` | Plural form |
| `getLowerDisplayName()` | `string` | Lowercase singular |
| `getPluralLowerDisplayName()` | `string` | Lowercase plural |

### EditionTrait

[Full docs](../feature-tour/edition-support.md) — Plugin edition checking.

| Method | Returns | Description |
|--------|---------|-------------|
| `isLite()` | `bool` | Check if Lite edition |
| `isStandard()` | `bool` | Check if Standard edition |
| `isPro()` | `bool` | Check if Pro edition |
| `isAtLeast(string $edition)` | `bool` | At least this edition? |
| `isBelow(string $edition)` | `bool` | Below this edition? |
| `requireEdition(string $edition, string $feature)` | `void` | Throw if below edition |
| `getEditionName()` | `string` | Current edition display name |
| `hasMultipleEditions()` | `bool` | Plugin has multiple editions? |
| `hasFeature(string $feature)` | `bool` | Check feature availability |
| `getEditionFeatures()` | `array` | Override for feature list |

### DeviceDetectionTrait

[Full docs](../feature-tour/device-detection.md) — User-agent parsing.

| Method | Returns | Description |
|--------|---------|-------------|
| `detectDeviceInfo(?string $ua, array $config)` | `array` | Detect device/browser/OS |
| `detectLanguageFromConfig()` | `string` | Detect browser language |
| `buildDeviceModel(array $data, string $class, array $map)` | `object` | Map detection data to model |
| `getDeviceDetectionConfig(): array` | `array` | **(abstract)** Return config |

### GeoLookupTrait

[Full docs](../feature-tour/geo-lookup.md) — IP geolocation.

| Method | Returns | Description |
|--------|---------|-------------|
| `lookupGeoIp(string $ip, ?array $config)` | `?array` | Look up IP location |
| `getGeoConfig(): array` | `array` | **(abstract)** Return config |

---

## Next Steps

- [Bootstrapping](bootstrapping.md) — how to initialize the base module
- [Twig Globals](twig-globals.md) — all Twig functions and filters
- [JavaScript API](javascript-api.md) — global JS functions and events
- [Front-End CSS](front-end-css.md) — CSS classes reference
- [Configuration](../get-started/configuration.md) — config file reference
