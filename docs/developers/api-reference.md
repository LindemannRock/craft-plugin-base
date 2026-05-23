# API Reference

Quick reference for all public PHP classes, methods, and traits in the base module. Each entry links to its detailed documentation page.

## Helpers

### ColorHelper

`lindemannrock\base\helpers\ColorHelper`
[Full docs](../feature-tour/color-helper.md)

**Constants:**

| Constant | Type | Value |
|----------|------|-------|
| `NEUTRAL_COLOR` | `string` | `'#aab6c1'` |
| `DEFAULT_COLOR` | `array` | `['class' => 'default', 'color' => '#9aa5b1', ...]` |
| `PALETTE` | `array` | Full 18-color palette keyed by name |

**Methods:**

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

**Configuration:**

| Method | Returns | Description |
|--------|---------|-------------|
| `getConfig()` | `array` | Full config from `lindemannrock-base.php` |
| `getTimeFormat()` | `string` | `'12'` or `'24'` |
| `getDateOrder()` | `string` | `'dmy'`, `'mdy'`, or `'ymd'` |
| `getDateSeparator()` | `string` | `'/'`, `'-'`, or `'.'` |
| `getShowSeconds()` | `bool` | Default showSeconds preference |
| `getMonthFormat()` | `string` | `'numeric'`, `'short'`, or `'long'` |
| `clearConfigCache()` | `void` | Clear cached config (useful for testing) |

**Utilities:**

| Method | Returns | Description |
|--------|---------|-------------|
| `now()` | `DateTime` | Current time in Craft timezone |
| `toCraftTimezone($dt, bool $isUtc)` | `?DateTime` | Convert date to Craft timezone |
| `isToday(DateTime\|string\|null $dt)` | `bool` | Check if today |
| `isPast(DateTime\|string\|null $dt)` | `bool` | Check if in past |
| `isFuture(DateTime\|string\|null $dt)` | `bool` | Check if in future |

**SQL expressions:**

| Method | Returns | Description |
|--------|---------|-------------|
| `localDateExpression(string $column)` | `Expression` | DB-agnostic timezone date expression |
| `localHourExpression(string $column)` | `Expression` | DB-agnostic timezone hour expression |
| `getCraftTimezoneOffset()` | `string` | Timezone offset string (e.g., `'+02:00'`) |

### DateRangeHelper

`lindemannrock\base\helpers\DateRangeHelper`
[Full docs](../feature-tour/date-range-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getDefaultDateRange(?string $handle)` | `string` | Default date range from config |
| `getOptions(string $format = 'array', bool $includeCustom = false)` | `array` | Date range options for dropdowns |
| `normalize(?string $range, ?string $default)` | `string` | Normalize range key; `$default` falls back to config |
| `getBounds(string $range, ?DateTimeZone $tz, DateTime|string|null $customStart, DateTime|string|null $customEnd)` | `array` | `['start' => ?DateTime, 'end' => ?DateTime]` in UTC |
| `applyToQuery(Query $query, string $dateRange, string $column, ?DateTimeZone $tz, DateTime|string|null $customStart, DateTime|string|null $customEnd)` | `void` | Add date range WHERE to query |
| `getDaysCount(string $range)` | `int` | Number of days in range |

### ExportHelper

`lindemannrock\base\helpers\ExportHelper`
[Full docs](../feature-tour/export-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getConfig(?string $pluginHandle)` | `array` | Resolved export config (plugin overrides base) |
| `isFormatEnabled(string $format, ?string $handle)` | `bool` | Check if format is enabled |
| `getEnabledFormats(?string $handle)` | `array` | List enabled format keys |
| `getFormatOptions()` | `array` | Options for select fields |
| `filename($settings, $parts, ?string $ext)` | `string` | Generate export filename |
| `assertNotEmpty(array $data, ?string $message = null)` | `void` | Throw if data is empty |
| `toCsv(array $rows, array $headers, string $file, array $dateCols)` | `Response` | CSV download response |
| `csvContent(array $rows, array $headers, array $dateCols, string $delimiter = ',', string $enclosure = '"')` | `string` | Build CSV string without sending response (delimiter/enclosure @since(5.25.0)) |
| `toJson(array $rows, string $file, array $dateCols, bool $pretty = true)` | `Response` | JSON download response |
| `toExcel(array $rows, array $headers, string $file, array $dateCols, array $opts)` | `Response` | Excel download response |
| `excelContent(array $rows, array $headers, array $dateCols, array $opts)` | `string` | Build XLSX bytes without sending response @since(5.25.0) |
| `isDangerousValue(mixed $value)` | `bool` | Check if a cell value would trigger formula injection (for callers building their own spreadsheet writer) @since(5.25.0) |
| `toExcelMulti(array $sheets, string $file)` | `Response` | Multi-sheet Excel workbook |
| `toZip(array $files, string $file)` | `Response` | ZIP archive download |
| `formatDateColumns(array $rows, array $dateCols)` | `array` | Format dates for CSV/Excel (Craft TZ, `Y-m-d H:i:s`) |
| `formatDateColumnsForApi(array $rows, array $dateCols)` | `array` | Format dates for JSON export (ISO 8601) |

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
| `getCountryDialCodeOptions(bool $includeAll = false)` | `array` | Options for select fields |
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
| `applyConfigOverridesToSettings(Model $settings, string $handle, array $skipKeys = [])` | `Model` | Merge config file values into a DB-backed Settings model |
| `isPluginEnabled(string $handle)` | `bool` | Plugin installed and enabled? |
| `isPluginInstalled(string $handle)` | `bool` | Plugin installed? |
| `getPlugin(string $handle)` | `?PluginInterface` | Get plugin instance |
| `getPluginName(string $handle, ?string $fallback)` | `string` | Plugin display name |
| `getPluginVersion(PluginInterface $plugin)` | `?string` | Get plugin version from `composer.json` |
| `getPluginComposerMetadata(PluginInterface $plugin)` | `?array` | Read plugin package metadata from `composer.json` |
| `getCacheBasePath(PluginInterface $plugin)` | `string` | Cache base directory |
| `getCachePath(PluginInterface $plugin, string $type)` | `string` | Typed cache directory |
| `getCacheKeyPrefix(string $handle, string $type)` | `string` | Cache key prefix |
| `getCacheKeySet(string $handle, string $type)` | `string` | Redis key set name |
| `registerTranslations($plugin, ?string $path, ?string $cat)` | `void` | Register translation source |

### JsonHelper

`lindemannrock\base\helpers\JsonHelper`

Small helper for safely embedding JSON into inline HTML/JS contexts.

| Method | Returns | Description |
|--------|---------|-------------|
| `htmlSafeJson(mixed $value)` | `string` | JSON-encode a value using HTML-safe flags for inline script/template output |

### BooleanHelper

`lindemannrock\base\helpers\BooleanHelper`
[Full docs](../feature-tour/boolean-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `normalize(mixed $value, bool $default = false)` | `bool` | Normalize boolean-like values from config, env, POST, and HTML attributes |
| `isBooleanLike(mixed $value)` | `bool` | Check whether a value is a recognized boolean-like value |
| `toStyleValue(mixed $value, bool $default = false)` | `string` | Normalize a boolean-like value to `'1'` or `'0'` for style config |

### DbHelper

`lindemannrock\base\helpers\DbHelper`
[Full docs](../feature-tour/db-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `jsonExtract(string $column, string\|string[] $path)` | `string` | Raw SQL string for JSON extraction. Pass an array for nested paths. Supports aliases and Craft table-prefix references such as `{{%table}}.content` |
| `jsonExtractExpression(string $column, string\|string[] $path, ?string $alias)` | `Expression` | Yii Expression for JSON extraction, with optional alias. Pass an array for nested paths |
| `groupConcat(string $expression, string $separator)` | `string` | DB-agnostic GROUP_CONCAT / STRING_AGG |
| `castToText(string\|Expression $expression)` @since(5.25.0) | `string` | DB-agnostic CAST to text — `CAST(expr AS CHAR)` on MySQL, `(expr)::text` on PostgreSQL |

### CsvImportHelper

`lindemannrock\base\helpers\CsvImportHelper`
[Full docs](../feature-tour/csv-import-helper.md)

**Constants:**

| Constant | Value | Description |
|----------|-------|-------------|
| `DEFAULT_MAX_ROWS` | `4000` | Default row limit for imports |
| `DEFAULT_MAX_BYTES` | `5242880` | Default file size limit (5 MB) |

**Methods:**

| Method | Returns | Description |
|--------|---------|-------------|
| `parseUpload(UploadedFile $file, array $options)` | `array` | Parse CSV file into rows |
| `stripFormulaEscapePrefix(string $value)` | `string` | Strip leading `'` added by ExportHelper for formula-safe CSV values |

### CpNavHelper

`lindemannrock\base\helpers\CpNavHelper`
[Full docs](../feature-tour/cp-nav-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `buildSubnav(User $user, ?Model $settings, array $sections)` | `array` | Build subnav array from section definitions |
| `firstAccessibleRoute(User $user, ?Model $settings, array $sections)` | `?string` | First route the user can access |

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

**Constants:**

| Constant | Value | Description |
|----------|-------|-------------|
| `EDITION_STANDARD` | `'standard'` | Free tier |
| `EDITION_LITE` | `'lite'` | Entry-level paid tier |
| `EDITION_PRO` | `'pro'` | Full-featured paid tier |

**Methods:**

| Method | Returns | Description |
|--------|---------|-------------|
| `editions()` | `string[]` | **(static)** Editions this plugin supports |
| `isLite()` | `bool` | Check if Lite edition |
| `isStandard()` | `bool` | Check if Standard edition |
| `isPro()` | `bool` | Check if Pro edition |
| `isAtLeast(string $edition)` | `bool` | At least this edition? |
| `isBelow(string $edition)` | `bool` | Below this edition? |
| `requireEdition(string $edition, ?string $feature)` | `void` | Throw if below edition |
| `getEditionHandle()` | `string` | Current edition handle (e.g., `'pro'`) |
| `getEditionName(?string $edition)` | `string` | Edition display name (e.g., `'Pro'`) |
| `hasMultipleEditions()` | `bool` | Plugin has multiple editions? |
| `hasFeature(string $feature)` | `bool` | Check feature availability |
| `getEditionFeatures(string $edition)` | `array` | Override for feature list |

### DeviceDetectionTrait

[Full docs](../feature-tour/device-detection.md) — User-agent parsing.

| Method | Returns | Description |
|--------|---------|-------------|
| `detectDeviceInfo(?string $ua, array $config)` | `array` | Detect device/browser/OS |

### QueueTtrTrait

[Full docs](../feature-tour/queue-ttr.md) — Shared queue TTR for long-running jobs.

| Method | Returns | Description |
|--------|---------|-------------|
| `queueTtrSeconds()` | `int` | Override per job TTR in seconds (default `1800`) |
| `getTtr()` | `int` | Returns queue TTR used by retryable jobs |

> `getTtr()` is used by yii2-queue when the job implements `RetryableJobInterface`.
| `detectLanguageFromConfig(array $overrideConfig = [])` | `string` | Detect browser language |
| `buildDeviceModel(array $data, string $class, array $map)` | `object` | Map detection data to model |
| `getDeviceDetectionConfig(): array` | `array` | Return config (default: `[]`) |

### GeoLookupTrait

[Full docs](../feature-tour/geo-lookup.md) — IP geolocation.

| Method | Returns | Description |
|--------|---------|-------------|
| `lookupGeoIp(string $ip, array $config = [])` | `?array` | Look up IP location |
| `getGeoConfig(): array` | `array` | Return config (default: `['provider' => 'ip-api.com', 'apiKey' => null]`) |

---

## Testing Utilities

`lindemannrock\base\testing\`
[Full docs](../feature-tour/testing.md) — PHPUnit integration test scaffolding.

### IntegrationTestCase

`lindemannrock\base\testing\IntegrationTestCase` (abstract)

| Method | Returns | Description |
|--------|---------|-------------|
| `cleanupExternalState()` | `void` | Override hook for non-DB cleanup (Redis, filesystem, external backends). |
| `swapPluginComponent(string $handle, string $componentId, object $stub)` | `void` | Swap a plugin service component for a stub, auto-restored in `tearDown` (LIFO). |
| `countRows(string $table, array $where = [])` | `int` | Generic row count by table + where. |
| `fetchRow(string $table, array $where)` | `?array` | Single-row fetch, or null. |
| `purgeRowsByMarker(string $table, string $column, string $prefix)` | `void` | Delete rows whose marker column starts with the given prefix. |
| `drainQueueJob(BaseJob $job, callable $isDone, int $maxIterations = 50)` | `void` | Run a queueable job until `$isDone()` is true, capped to surface hangs. |

### StubConsoleRequest

`lindemannrock\base\testing\StubConsoleRequest` (final, extends `craft\console\Request`)

Test double that adds web-only accessors (`getUserIP`, `getUserAgent`, `getReferrer`) to Craft's console request. **Install via `Craft::$app->set('request', new StubConsoleRequest(...))`** in `setUp` and restore manually in `tearDown` — it's a Craft-level component, not a plugin component, so `swapPluginComponent()` doesn't apply. Keeps `getIsConsoleRequest()` honest under the harness.

### StubWebRequest

`lindemannrock\base\testing\StubWebRequest` (final, extends `yii\web\Request`)

Same three accessors as `StubConsoleRequest`, but extends Yii's web request. **Pass directly as a method argument** when the service under test type-hints `yii\web\Request` (or `craft\web\Request`). Never install on `Craft::$app->set('request', …)` — a web request on a console-bootstrapped Craft fools mode-detection.

### bootstrap()

`lindemannrock\base\testing\bootstrap(?string $projectRoot = null): void` — initialise Craft as a console application from a test bootstrap file. Auto-detects the project root when `$projectRoot` is null.

For the cross-plugin workflow recipe, see [`plugins/_docs/workflows/testing.md`](../../../_docs/workflows/testing.md).

---

## Next Steps

- [Bootstrapping](bootstrapping.md) — how to initialize the base module
- [Twig Globals](twig-globals.md) — all Twig functions and filters
- [JavaScript API](javascript-api.md) — global JS functions and events
- [Front-End CSS](front-end-css.md) — CSS classes reference
- [Configuration](../get-started/configuration.md) — config file reference
