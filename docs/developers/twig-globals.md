# Twig Globals

All Twig variables, functions, and filters registered by the base module. These become available in CP templates after any plugin calls `PluginHelper::bootstrap()`.

## Plugin Helper Variable

Each plugin that calls `bootstrap()` registers a Twig global under the name it specifies. For example, if Redirect Manager passes `'redirectHelper'`, templates can use:

```twig
{{ redirectHelper.fullName }}             {# "Redirect Manager" (or custom name) #}
{{ redirectHelper.displayName }}          {# "Redirect" #}
{{ redirectHelper.pluralDisplayName }}    {# "Redirects" #}
{{ redirectHelper.lowerDisplayName }}     {# "redirect" #}
{{ redirectHelper.pluralLowerDisplayName }} {# "redirects" #}
```

Cache path helpers are also available:

```twig
{{ redirectHelper.cacheBasePath }}        {# "storage/runtime/redirect-manager/cache/" #}
{{ redirectHelper.getCachePath('device') }} {# "storage/runtime/redirect-manager/cache/device/" #}
```

The variable is a `PluginNameHelper` instance that proxies to the plugin's Settings model. If the Settings uses [SettingsDisplayNameTrait](../feature-tour/settings-display-name.md), custom plugin names are reflected automatically. Otherwise it falls back to the plugin's default name.

## Registered Extensions

The base module registers five Twig extensions. All functions and filters are prefixed with `lr` to avoid naming collisions.

### DateTimeExtension

**9 filters:**

| Filter | Purpose | Docs |
|--------|---------|------|
| `lrDatetime` | Full datetime | [Twig Filters](../template-guides/twig-filters-functions.md#display-formatting) |
| `lrCompactDatetime` | Datetime without year | " |
| `lrDate` | Date only | " |
| `lrTime` | Time only | " |
| `lrShortDate` | Short date for charts | " |
| `lrRelative` | Relative time ("2 hours ago") | " |
| `lrForDatabase` | `Y-m-d H:i:s` format | [Twig Filters](../template-guides/twig-filters-functions.md#machine-formatting) |
| `lrForApi` | ISO 8601 format | " |
| `lrForFilename` | `Y-m-d-His` format | " |

**6 functions:**

| Function | Purpose |
|----------|---------|
| `lrNow()` | Current DateTime in Craft timezone |
| `lrIsToday(date)` | Check if a date is today |
| `lrIsPast(date)` | Check if a date is in the past |
| `lrIsFuture(date)` | Check if a date is in the future |
| `lrDefaultDateRange(pluginHandle?)` | Default date range from config |
| `lrDateRangeOptions(format?)` | Date range options for dropdowns |

### ColorExtension

**10 functions:**

| Function | Purpose |
|----------|---------|
| `lrPaletteColor(name)` | Get palette color data |
| `lrPaletteColorNames()` | List all palette color names |
| `lrColorSet(name)` | Get entire color set |
| `lrSetColor(set, key)` | Get specific color from set |
| `lrColor(set, key)` | Alias for `lrSetColor` |
| `lrHasColorSet(name)` | Check if a color set exists |
| `lrAvailableColorSets()` | List all color set names |
| `lrNeutralColor()` | Neutral gray (`#aab6c1`) |
| `lrDefaultColor()` | Default fallback color |
| `lrFilterColor(set, key, current)` | Color for filter display (selected vs neutral) |

### ExportExtension

**3 functions:**

| Function | Purpose |
|----------|---------|
| `lrExportEnabled(format, pluginHandle?)` | Check if export format is enabled |
| `lrExportFormats(pluginHandle?)` | List enabled format keys |
| `lrExportFormatOptions()` | Format options for select fields |

### GeoExtension

**6 functions:**

| Function | Purpose |
|----------|---------|
| `lrCountryDialCodeData()` | All countries with dial codes as `{countryCode, dialCode, countryName}` — the main data source |
| `lrCountries()` | All countries (code => name), including those without dial codes |
| `lrCountryName(code)` | Country name by code (single lookup) |
| `lrDialCode(code)` | Dial code by country (single lookup, e.g., `'1'` for US, no `+` prefix) |
| `lrCountryWithDialCode(code)` | Formatted name with dial code (e.g., `"Kuwait (+965)"`) |
| `lrValidCountryCode(code)` | Validate a country code (returns bool) |

### PluginExtension

**3 functions:**

| Function | Purpose |
|----------|---------|
| `lrPluginInstalled(handle)` | Check if plugin is installed (may be disabled) |
| `lrPluginEnabled(handle)` | Check if plugin is installed and enabled |
| `lrPluginName(handle, fallback?)` | Plugin display name (respects custom `pluginName`) |

## Registration Flow

Extensions are registered when the base module initializes:

1. A plugin calls `PluginHelper::bootstrap()` in its `init()`
2. `Base::register()` is called (idempotent — only runs once even if multiple plugins call it)
3. The `Base` module registers all five extensions with Twig
4. The plugin's helper variable is registered via `View::EVENT_BEFORE_RENDER_TEMPLATE`

Since `Base::register()` is idempotent, the extensions are available regardless of how many plugins bootstrap the base module.

## Next Steps

- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — detailed usage examples for every filter and function
- [Bootstrapping](bootstrapping.md) — how `PluginHelper::bootstrap()` works
- [Color Helper](../feature-tour/color-helper.md) — PHP API behind the color functions
