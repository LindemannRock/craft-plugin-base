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
| `primaryHexFromSvg(?string $svg)` | `string\|null` | First non-black/white hex in an SVG (accent colour) @since(5.27.0) |
| `iconColorRoles(?string $svg)` | `array\|null` | `{accent, ink}` brand roles from icon SVG @since(5.27.0) |
| `mix(string $a, string $b, float $weight = 0.5)` | `string` | Blend two hex colours by weight (darken/lighten) @since(5.27.0) |
| `luminance(string $hex)` | `int` | Perceived luminance on 0–255 (Rec. 601) @since(5.27.0) |
| `withAlpha(string $hex, float $alpha)` | `string` | Append alpha, returns `#RRGGBBAA` @since(5.27.0) |

### PluginThemeStyleHelper

`lindemannrock\base\helpers\PluginThemeStyleHelper`
[Full docs](../feature-tour/plugin-theme-style-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `heroCssVarsFromSvg(?string $svg, string $style = 'lighter', ?string $fallbackAccent = null)` | `string` | CSS custom properties for plugin setup/documentation heroes @since(5.34.0) |
| `docsShellCssVarsFromSvg(?string $svg, ?string $fallbackAccent = null)` | `string` | CSS custom properties for docs shell surfaces @since(5.34.0) |
| `docsCssVarsFromSvg(?string $svg, string $style = 'lighter', ?string $fallbackAccent = null)` | `string` | Combined hero and docs shell CSS custom properties @since(5.34.0) |

**Twig functions:**

| Function | Maps to |
|----------|---------|
| `lrPluginHeroCssVars(svg, style='lighter', fallbackAccent=null)` | `heroCssVarsFromSvg()` |
| `lrPluginDocsShellCssVars(svg, fallbackAccent=null)` | `docsShellCssVarsFromSvg()` |
| `lrPluginDocsCssVars(svg, style='lighter', fallbackAccent=null)` | `docsCssVarsFromSvg()` |

### DateFormatHelper

`lindemannrock\base\helpers\DateFormatHelper`
[Full docs](../feature-tour/date-format-helper.md)

**Display formatting:**

| Method | Returns | Description |
|--------|---------|-------------|
| `formatDatetime($dt, $style, $showSeconds, $year, $isUtc)` | `string` | Full datetime |
| `formatCompactDatetime($dt, $showSeconds, $isUtc)` | `string` | Datetime without year |
| `formatCompactDatetimeFromSettings($dt, $settings, $showSeconds, $isUtc, $includeYear, $pluginHandle)` | `string` | Compact datetime for serialized labels such as queue descriptions; pass a plugin handle so "Use global default" cascades correctly |
| `formatDate($dt, $style, $year, $isUtc)` | `string` | Date only |
| `formatTime($dt, $style, $showSeconds, $isUtc)` | `string` | Time only |
| `formatRelative($dt, $isUtc)` | `string` | Relative time |

Display `$style` accepts `cascade` (default), `short`, `medium`, or `long`. `cascade` respects the active date-format settings; the other styles force numeric, abbreviated month, or full month date display respectively.

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
| `clearConfigCache($pluginHandle = null)` | `void` | Clear cached config for one plugin, or all cached config when omitted |

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
| `getWeekStartIsoDay()` | `int` | Craft week start converted to ISO weekday |

### ExportHelper

`lindemannrock\base\helpers\ExportHelper`
[Full docs](../feature-tour/export-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `getConfig(?string $pluginHandle)` | `array` | Resolved export config (plugin overrides base) |
| `isFormatEnabled(string $format, ?string $handle)` | `bool` | Check if format is enabled |
| `getEnabledFormats(?string $handle)` | `array` | List enabled format keys |
| `getFormatOptions()` | `array` | Options for select fields |
| `filename($settings, $parts, ?string $ext)` | `string` | Generate sanitized export filename |
| `assertNotEmpty(array $data, ?string $message = null)` | `void` | Throw if data is empty |
| `toCsv(array $rows, array $headers, string $file, array $dateCols)` | `Response` | CSV download response |
| `csvContent(array $rows, array $headers, array $dateCols, string $delimiter = ',', string $enclosure = '"')` | `string` | Build CSV string without sending response (delimiter/enclosure @since(5.25.0)) |
| `toJson(array $rows, string $file, array $dateCols, bool $pretty = true)` | `Response` | JSON download response |
| `toExcel(array $rows, array $headers, string $file, array $dateCols, array $opts)` | `Response` | Excel download response |
| `excelContent(array $rows, array $headers, array $dateCols, array $opts)` | `string` | Build XLSX bytes without sending response @since(5.25.0) |
| `isDangerousValue(mixed $value)` | `bool` | Check if a cell value would trigger formula injection (for callers building their own spreadsheet writer) @since(5.25.0) |
| `toExcelMulti(array $sheets, string $file)` | `Response` | Multi-sheet Excel workbook |
| `toZip(array $files, string $file)` | `Response` | ZIP archive download with sanitized member names |
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
| `getIconSvg(PluginInterface $plugin)` | `?string` | Read the plugin's `src/icon.svg` (located by reflection) @since(5.27.0) |
| `readIconSvg(string $srcDir)` | `?string` | Read `icon.svg` from a source dir — no plugin instance needed @since(5.27.0) |
| `lrLogoFile()` | `string` | Absolute path to the canonical LindemannRock logo SVG @since(5.27.0) |
| `lrLogoPaths()` | `string` | The logo's two `<path>` elements, no `<svg>`/`<g>` wrapper @since(5.27.0) |

### CacheHelper

`lindemannrock\base\helpers\CacheHelper`
[Full docs](../feature-tour/cache-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `clearTrackedRedisKeys(string $pluginHandle, string $keyType, int $batchSize = 500)` | `int` | Clear cache entries tracked in a plugin-owned Redis set using `SSCAN` batches @since(5.31.0) |
| `clearCacheFiles(string $directory, string $suffix = '.cache')` | `int` | Delete matching local cache files from a directory with `DirectoryIterator` @since(5.31.0) |
| `countCacheFiles(string $directory, string $suffix = '.cache')` | `int` | Count matching local cache files in a directory with `DirectoryIterator` @since(5.31.0) |

### JsonHelper

`lindemannrock\base\helpers\JsonHelper`

Small helper for safely embedding JSON into inline HTML/JS contexts.

| Method | Returns | Description |
|--------|---------|-------------|
| `htmlSafeJson(mixed $value)` | `string` | JSON-encode a value using HTML-safe flags for inline script/template output |

### GqlHelper

`lindemannrock\base\helpers\GqlHelper`
[Full docs](../feature-tour/gql-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `canQuery(string $component, ?GqlSchema $schema = null)` | `bool` | Check whether the active schema includes a plugin-owned read scope, such as `redirectManager.all:read` |
| `resolveSiteId(array $arguments, ?int $fallbackSiteId = null)` | `int\|null` | Resolve Craft-style GraphQL `site` / `siteId` arguments to a concrete site ID |
| `siteHandle(?int $siteId)` | `string\|null` | Resolve a site ID into a handle for virtual GraphQL `site` fields |
| `nullIfEmptyString(mixed $value)` | `mixed` | Return `null` for empty strings while preserving `0`, `'0'`, `false`, arrays, and other values |

### SettingsPostHelper

`lindemannrock\base\helpers\SettingsPostHelper`
[Full docs](../feature-tour/settings-post-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `apply(Model $model, array $postedValues, array $allowedAttributes, ?callable $shouldSkipAttribute = null, array $adapters = [])` | `SettingsPostResult` | Apply section-scoped typed settings POST values to a settings model, optionally skipping config-overridden attributes on explicit POST-save paths |

### SlugHandleHelper

`lindemannrock\base\helpers\SlugHandleHelper`
[Full docs](../feature-tour/slug-handle-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `normalizeHandle(?string $value, string $fallback = 'item')` | `string` | Normalize a value to a Craft-style handle |
| `normalizeSlug(?string $value, string $fallback = 'item')` | `string` | Normalize a value to a lowercase URL-style slug |
| `normalizePathSlug(?string $value, string $fallback = 'item')` | `string` | Normalize a slash-preserving path slug |
| `exists(string $table, string $column, string $candidate, array $options = [])` | `bool` | Check whether a slug/handle candidate exists, with optional scope and excluded ID |
| `makeUnique(string $table, string $column, string $base, array $options = [])` | `string` | Return `base`, `base-1`, `base-2`, etc. using DB-backed uniqueness checks |

### SafeSegmentHelper

`lindemannrock\base\helpers\SafeSegmentHelper`
[Full docs](../feature-tour/safe-segment-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `filenamePart(?string $value, string $fallback = 'file', array $options = [])` | `string` | Normalize a safe filename/cache/ZIP segment |
| `tokenKey(?string $value, string $fallback = 'token', int $maxLength = 64)` | `string` | Normalize a local token/config key |

### AssetVolumeHelper

`lindemannrock\base\helpers\AssetVolumeHelper`
[Full docs](../feature-tour/asset-volume-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `validateAssetId(mixed $assetId, ?string $allowedVolumeUid = null)` | `int|null` | Validate a submitted asset ID against an optional volume restriction and the current user's `viewAssets:` permission; returns the ID or `null` |

### UrlSafetyHelper

`lindemannrock\base\helpers\UrlSafetyHelper`
[Full docs](../feature-tour/url-safety-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `sanitizeRedirectUrl(string $url, string $fallback = '/')` | `string` | Return the URL if it's a safe redirect target (relative path or `http(s)`), otherwise the fallback |
| `isSafeRedirectUrl(string $url)` | `bool` | Whether the URL is a safe redirect target |
| `hasDangerousScheme(string $url)` | `bool` | Whether the URL uses an executable scheme (`javascript:`/`vbscript:`/`data:`/`file:`), incl. obfuscated variants — a denylist guard that leaves custom app deep links (`myapp://`) alone |

### ContentSafetyHelper

`lindemannrock\base\helpers\ContentSafetyHelper`
[Full docs](../feature-tour/content-safety-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `containsMaliciousMarkup(string $content, &$threats = [])` | `bool` | Whether free text contains dangerous HTML/script markup (`<script>`, `<iframe>`, `on*=`, `javascript:`, …) anywhere in the value, incl. entity-encoded; `$threats` is populated by reference with matched labels |

### StoragePathHelper

`lindemannrock\base\helpers\StoragePathHelper`
[Full docs](../feature-tour/storage-path-validator.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `resolve(string $path)` | `string` | Resolve environment variables and Craft aliases in a storage path value |
| `validatePath(string $path, array $options = [])` | `array` | Validate a raw storage path value without requiring a Yii model attribute |

### StoragePathValidator

`lindemannrock\base\validators\StoragePathValidator`
[Full docs](../feature-tour/storage-path-validator.md)

| Option | Type | Description |
|--------|------|-------------|
| `allowedAliases` | `array` | Alias prefixes allowed for literal aliases and env-resolved absolute paths |
| `preventWebroot` | `bool` | Reject paths that resolve inside `@webroot` |
| `requireAlias` | `bool` | Require a literal alias, env var, or absolute path that resolves inside an allowed alias root |
| `allowEnvVars` | `bool` | Allow `$VARIABLE` values and validate their resolved path |

### StorageVolumeHelper

`lindemannrock\base\helpers\StorageVolumeHelper`
[Full docs](../feature-tour/storage-path-validator.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `validateVolume(?string $volumeUid, array $options = [])` | `array` | Validate an optional asset volume UID for plugin-managed storage |
| `displayPath(?string $volumeUid, string $subpath)` | `string|null` | Return a CP-friendly `Volume: Name / path` label |
| `localRootPath(?string $volumeUid)` | `string|null` | Return the resolved root path for local filesystems, or `null` for remote/non-local volumes |

### StorageVolumeValidator

`lindemannrock\base\validators\StorageVolumeValidator`
[Full docs](../feature-tour/storage-path-validator.md)

| Option | Type | Description |
|--------|------|-------------|
| `preventLocalWebroot` | `bool` | Reject local volumes that resolve inside `@webroot` |
| `requireLocal` | `bool` | Require the selected volume to use a local filesystem |

### BooleanHelper

`lindemannrock\base\helpers\BooleanHelper`
[Full docs](../feature-tour/boolean-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `normalize(mixed $value, bool $default = false)` | `bool` | Normalize boolean-like values from config, env, POST, and HTML attributes |
| `isBooleanLike(mixed $value)` | `bool` | Check whether a value is a recognized boolean-like value |
| `toStyleValue(mixed $value, bool $default = false)` | `string` | Normalize a boolean-like value to `'1'` or `'0'` for style config |

### ExperimentalFeatureHelper

`lindemannrock\base\helpers\ExperimentalFeatureHelper`
[Full docs](../feature-tour/experimental-feature-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `isEnabled(string $envFlag, bool $requireDevMode = false)` | `bool` | Check whether an internal feature is explicitly enabled by env flag, optionally also requiring Craft dev mode |
| `requireEnabled(string $envFlag, bool $requireDevMode = false)` | `void` | Throw a 404 when the internal feature is disabled |

### ConsoleHelpHelper

`lindemannrock\base\helpers\ConsoleHelpHelper`
[Full docs](../feature-tour/console-help.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `renderOverview(array $manifest)` | `string` | Render the top-level plugin command catalog |
| `renderCommand(array $manifest, string $command)` | `string` | Render focused command help, or an unknown-command suggestion |
| `hasCommand(array $manifest, ?string $command)` | `bool` | Whether the manifest contains the requested command; `null` is the overview |

### DbHelper

`lindemannrock\base\helpers\DbHelper`
[Full docs](../feature-tour/db-helper.md)

| Method | Returns | Description |
|--------|---------|-------------|
| `jsonExtract(string $column, string\|string[] $path)` | `string` | Raw SQL string for JSON extraction. Pass an array for nested paths. Supports aliases and Craft table-prefix references such as `{{%table}}.content` |
| `jsonExtractExpression(string $column, string\|string[] $path, ?string $alias)` | `Expression` | Yii Expression for JSON extraction, with optional alias. Pass an array for nested paths |
| `groupConcat(string $expression, string $separator)` | `string` | DB-agnostic GROUP_CONCAT / STRING_AGG |
| `castToText(string\|Expression $expression)` @since(5.25.0) | `string` | DB-agnostic CAST to text — `CAST(expr AS CHAR)` on MySQL, `(expr)::text` on PostgreSQL |
| `existingColumn(string $table, string $column)` @since(5.35.0) | `string` | `{{%table}}.[[column]]` reference to the existing row inside an upsert's ON CONFLICT DO UPDATE expression — a bare column there is ambiguous on PostgreSQL |
| `boolToInt(string $column)` @since(5.35.0) | `string` | `CASE WHEN [[column]] THEN 1 ELSE 0 END` — 0/1 projection of a boolean column for MAX/MIN/SUM (PostgreSQL has no boolean max/min) |

Column-taking helpers wrap bare column references in Yii's `[[...]]` placeholder @since(5.35.0) so camelCase columns stay dialect-quoted on PostgreSQL; composed expressions pass through unchanged.

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

## Console Controllers

### AbstractHelpController

`lindemannrock\base\console\controllers\AbstractHelpController`
[Full docs](../feature-tour/console-help.md)

Extend this controller in a plugin's console namespace to expose `plugin-handle/help` and `plugin-handle/help group/action`.

| Method | Description |
|--------|-------------|
| `actionIndex(?string $command = null)` | Render overview or focused command help |
| `helpManifest(): array` | **(abstract)** Return the command manifest consumed by `ConsoleHelpHelper` |

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
| `detectLanguageFromConfig(array $overrideConfig = [])` | `string` | Detect browser language |
| `buildDeviceModel(array $data, string $class, array $map)` | `object` | Map detection data to model |
| `getDeviceDetectionConfig(): array` | `array` | Return config (default: `[]`) |

### QueueTtrTrait

[Full docs](../feature-tour/queue-ttr.md) — Shared queue TTR for long-running jobs.

| Method | Returns | Description |
|--------|---------|-------------|
| `queueTtrSeconds()` | `int` | Override per job TTR in seconds (default `1800`) |
| `getTtr()` | `int` | Returns queue TTR used by retryable jobs |

> `getTtr()` is used by yii2-queue when the job implements `RetryableJobInterface`.

### RecurringQueueHelper

[Full docs](../feature-tour/recurring-queue-helper.md) — Deployment-safe recurring queue-row ownership.

| Method | Returns | Description |
|--------|---------|-------------|
| `ensurePending(string $pluginToken, string $jobClass, int $delay, callable $jobFactory, array $extraLikeTokens = [], ?string $mutexName = null, int $mutexTimeout = 5)` | `RecurringQueueResult` | Atomically ensure one pending recurring row exists, returning `created`, `existing`, `skipped`, or `lock-missed` status plus duplicate-collapse metadata. |
| `deletePending(string $pluginToken, string $jobClass, array $extraLikeTokens = [])` | `int` | Delete pending rows for a recurring job identity. |
| `hasPending(string $pluginToken, string $jobClass, array $extraLikeTokens = [])` | `bool` | Check whether a pending row exists for a recurring job identity. |

### RecurringQueueResult

| Property / Method | Returns | Description |
|-------------------|---------|-------------|
| `$status` | `string` | One of `created`, `existing`, `skipped`, or `lock-missed`. |
| `$jobId` | `string|null` | Existing or newly queued job ID. |
| `$duplicatesDeleted` | `int` | Number of duplicate pending rows removed. |
| `wasCreated()` | `bool` | Whether this call pushed a new queue row. |
| `hasPending()` | `bool` | Whether the result has an existing or newly queued pending row. |

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

### SqlDialectLinter @since(5.35.0)

`lindemannrock\base\testing\SqlDialectLinter` (final)

| Method | Returns | Description |
|--------|---------|-------------|
| `scanDirectory(string $directory, array $excludeSuffixes = [], array $booleanColumns = [])` | `string[]` | Scan every `.php` file under a directory for PostgreSQL-unsafe raw SQL: unbracketed camelCase columns/aliases, bare identifiers in raw SQL statements, and MAX/MIN over declared boolean columns. Empty array when clean. |
| `scanFile(string $absolutePath, array $booleanColumns = [])` | `string[]` | Scan a single file. |

### bootstrap()

`lindemannrock\base\testing\bootstrap(?string $projectRoot = null): void` — initialise Craft as a console application from a test bootstrap file. Auto-detects the project root when `$projectRoot` is null.

See [Testing](../feature-tour/testing.md) for setup details and examples.

---

## Next Steps

- [Bootstrapping](bootstrapping.md) — how to initialize the base module
- [Twig Globals](twig-globals.md) — all Twig functions and filters
- [JavaScript API](javascript-api.md) — global JS functions and events
- [Front-End CSS](front-end-css.md) — CSS classes reference
- [Configuration](../get-started/configuration.md) — config file reference
