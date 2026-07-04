# Twig Filters & Functions

All Twig filters and functions provided by LindemannRock Base. These are available in all CP templates after any LindemannRock plugin calls `PluginHelper::bootstrap()`.

## Date/Time Filters

### Display Formatting

```twig
{# Full datetime — default output depends on config (timeFormat, dateOrder, monthFormat) #}
{{ entry.dateCreated|lrDatetime }}                    {# "24 January 2026 3:45 PM" if monthFormat='long' #}
{{ entry.dateCreated|lrDatetime('short') }}           {# "24/01/2026 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('medium') }}           {# "24 Jan 2026 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('long') }}             {# "24 January 2026 at 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('cascade', true) }}    {# "24 January 2026 3:45:32 PM" (seconds) #}

{# Compact datetime (no year) — for dashboards #}
{{ entry.dateCreated|lrCompactDatetime }}              {# "24 Jan 3:45 PM" #}

{# Date only #}
{{ entry.dateCreated|lrDate }}                         {# cascade-driven #}
{{ entry.dateCreated|lrDate('short') }}                {# "24/01/2026" #}
{{ entry.dateCreated|lrDate('long') }}                 {# "24 January 2026" #}
{{ entry.dateCreated|lrDate('medium', false) }}        {# "24 Jan" (no year) #}

{# Time only #}
{{ entry.dateCreated|lrTime }}                         {# "3:45 PM" or "15:45" #}
{{ entry.dateCreated|lrTime('cascade', true) }}        {# "3:45:32 PM" (seconds) #}

{# Relative time #}
{{ entry.dateCreated|lrRelative }}                     {# "2 hours ago" #}
```

### Machine Formatting

```twig
{# Datetime string format #}
{{ entry.dateCreated|lrToDateTimeString }}             {# "2026-01-24 15:45:32" #}

{# API format (ISO 8601) #}
{{ entry.dateCreated|lrToApiString }}                  {# "2026-01-24T15:45:32+00:00" #}

{# Filename format #}
{{ entry.dateCreated|lrToFilenameString }}             {# "2026-01-24-154532" #}
```

### Filter Parameters

| Filter | Parameters | Description |
|--------|------------|-------------|
| `lrDatetime` | `style='cascade'`, `showSeconds=null`, `includeYear=true`, `isUtc=true` | Full datetime |
| `lrCompactDatetime` | `showSeconds=null`, `isUtc=true` | Datetime without year |
| `lrDate` | `style='cascade'`, `includeYear=true`, `isUtc=true` | Date only |
| `lrTime` | `style='cascade'`, `showSeconds=null`, `isUtc=true` | Time only |
| `lrRelative` | `isUtc=true` | Relative time |
| `lrToDateTimeString` | (none) | `Y-m-d H:i:s` format |
| `lrToApiString` | (none) | ISO 8601 format |
| `lrToFilenameString` | `includeTime=true` | `Y-m-d-His` format |

> [!NOTE]
> `cascade` is the default style and respects the active plugin/base date-format settings. `short`, `medium`, and `long` are fixed display styles. When `showSeconds=true` is set in `config/lindemannrock-base.php` or via a plugin's [`DateFormatSettingsTrait`](../feature-tour/date-format-helper.md#cascade-order-since5100), seconds appear in all styles. Pass an explicit `true`/`false` as the `showSeconds` arg to override the cascade for a single call site.

## Date/Time Functions

```twig
{% set now = lrNow() %}                              {# Current DateTime in Craft timezone #}
{% if lrIsToday(entry.dateCreated) %}Today{% endif %}
{% if lrIsPast(entry.expiryDate) %}Expired{% endif %}
{% if lrIsFuture(entry.postDate) %}Scheduled{% endif %}

{# Default date range from config #}
{% set defaultRange = lrDefaultDateRange() %}         {# "last30days" #}
{% set defaultRange = lrDefaultDateRange('my-plugin') %} {# Check plugin-specific config first #}

{# Date range options for dropdowns #}
{% set options = lrDateRangeOptions() %}
{# [{'value': 'today', 'label': 'Today'}, ...] #}

{% set options = lrDateRangeOptions('assoc') %}
{# {'today': 'Today', 'yesterday': 'Yesterday', ...} #}

{% set options = lrDateRangeOptions('array', true) %}
{# Includes {'value': 'custom', 'label': 'Custom Range'} as the final option #}

{# Resolved date/time format config (cascade: plugin config > plugin settings > base config > defaults) #}
{% set fmt = lrDateFormatConfig() %}                  {# {timeFormat, dateOrder, dateSeparator, monthFormat, showSeconds} #}
{% set fmt = lrDateFormatConfig('my-plugin') %}       {# Check plugin-specific config first #}
```

## Color Functions

```twig
{# Palette colors #}
{% set color = lrPaletteColor('teal') %}
{# {'class': 'teal', 'color': '#14b8a6', 'rgb': '20, 184, 166', 'text': '#115e59'} #}

{% set names = lrPaletteColorNames() %}
{# ['teal', 'cyan', 'gray', 'orange', ...] #}

{# Color sets #}
{% set statusColors = lrColorSet('status') %}
{# {'enabled': {...}, 'disabled': {...}, 'pending': {...}, ...} #}

{% set enabledColor = lrSetColor('status', 'enabled') %}

{# Check if a color set exists #}
{% if lrHasColorSet('myCustomSet') %}...{% endif %}

{# List all color set names #}
{% set setNames = lrAvailableColorSets() %}

{# Filter display colors #}
{{ lrFilterColor('status', 'enabled', currentFilter) }}
{# Returns '#14b8a6' if selected, '#aab6c1' (neutral) if not #}

{# Special colors #}
{{ lrNeutralColor() }}     {# '#aab6c1' #}
{% set defaultColor = lrDefaultColor() %}
```

## Plugin Theme Style Functions

Use these when a Twig template needs plugin-branded CSS custom properties from an icon SVG. They wrap [PluginThemeStyleHelper](../feature-tour/plugin-theme-style-helper.md).

```twig
{# Setup/documentation hero variables only #}
{% set heroStyle = lrPluginHeroCssVars(plugin.iconSvg ?? null) %}
{% set deepHeroStyle = lrPluginHeroCssVars(plugin.iconSvg ?? null, 'deeper', '#0F766E') %}

{# Docs shell variables only #}
{% set shellStyle = lrPluginDocsShellCssVars(plugin.iconSvg ?? null, '#0F766E') %}

{# Combined hero + docs shell variables #}
{% set docsStyle = lrPluginDocsCssVars(plugin.iconSvg ?? null, 'lighter', '#0F766E') %}
```

| Function | Parameters | Description |
|----------|------------|-------------|
| `lrPluginHeroCssVars` | `svg`, `style='lighter'`, `fallbackAccent=null` | Returns `--plugin-hero-*` variables for branded hero surfaces |
| `lrPluginDocsShellCssVars` | `svg`, `fallbackAccent=null` | Returns `--plugin-shell-*` variables for docs page chrome |
| `lrPluginDocsCssVars` | `svg`, `style='lighter'`, `fallbackAccent=null` | Returns combined hero and docs shell variables |

## Export Functions

```twig
{# Check if a format is enabled #}
{% if lrExportEnabled('excel') %}...{% endif %}
{% if lrExportEnabled('json') %}...{% endif %}

{# Get all enabled formats #}
{% set formats = lrExportFormats() %}
{# ['csv', 'excel'] (depends on config) #}

{# Get format options for select fields #}
{% set options = lrExportFormatOptions() %}
{# [{'value': 'xlsx', 'label': 'Excel (.xlsx)'}, {'value': 'csv', 'label': 'CSV (.csv)'}] #}
```

## Geo Functions

```twig
{# Structured data for all countries with dial codes #}
{% set data = lrCountryDialCodeData() %}
{# [{countryCode: 'KW', dialCode: '965', countryName: 'Kuwait'}, ...] #}

{# Build any dropdown format from the data #}
{% for item in data %}
    <option value="{{ item.countryCode }}">{{ item.countryName }} (+{{ item.dialCode }})</option>
{% endfor %}

{# Single lookups #}
{{ lrCountryName('US') }}              {# "United States" #}
{{ lrDialCode('US') }}                 {# "1" (no + prefix) #}
{{ lrCountryWithDialCode('KW') }}      {# "Kuwait (+965)" #}

{# All countries including those without dial codes #}
{% for code, name in lrCountries() %}
    <option value="{{ code }}">{{ name }}</option>
{% endfor %}

{# Validation #}
{% if lrValidCountryCode('US') %}Valid{% endif %}
```

## Plugin Functions

```twig
{# Check if a plugin is installed (may be disabled) #}
{% if lrPluginInstalled('formie') %}...{% endif %}

{# Check if a plugin is installed AND enabled #}
{% if lrPluginEnabled('formie') %}...{% endif %}

{# Get a plugin's display name (respects custom pluginName setting) #}
{{ lrPluginName('search-manager') }}                  {# "Search Manager" or custom name #}
{{ lrPluginName('missing-plugin', 'Fallback Name') }} {# "Fallback Name" if not found #}
```

## Label Filters

### |lrShortLabel @since(5.22.0)

Shortens a long label for compact UI contexts — dropdown options, table headers, tabs. Strips leading numbering, collapses whitespace, and truncates with an ellipsis. A trailing parenthetical suffix is preserved so entries stay distinguishable.

```twig
{{ field.label|lrShortLabel }}        {# default 60 chars #}
{{ field.label|lrShortLabel(40) }}    {# custom max length #}
```

| Filter | Parameters | Description |
|--------|------------|-------------|
| `lrShortLabel` | `maxLength=60` | Strip numbering and truncate label |

See [LabelHelper](../feature-tour/label-helper.md) for full details.

## Plugin Helper Variable

Each plugin registers a Twig global via `PluginHelper::bootstrap()`. This variable provides display name methods from [SettingsDisplayNameTrait](../feature-tour/settings-display-name.md):

```twig
{# Available via the helper variable name set in bootstrap() #}
{{ myHelper.displayName }}          {# "Redirect" #}
{{ myHelper.fullName }}             {# "Redirect Manager" #}
{{ myHelper.pluralDisplayName }}    {# "Redirects" #}
{{ myHelper.lowerDisplayName }}     {# "redirect" #}
{{ myHelper.pluralLowerDisplayName }} {# "redirects" #}
```

## Next Steps

- [DateFormatHelper](../feature-tour/date-format-helper.md) — PHP API for date formatting
- [ColorHelper](../feature-tour/color-helper.md) — PHP API for colors
- [Components](components.md) — Twig components that use these functions
- [Configuration](../get-started/configuration.md) — settings that control date/time and export formatting
