# Twig Filters & Functions

All Twig filters and functions provided by LindemannRock Base. These are available in all CP templates after any LindemannRock plugin calls `PluginHelper::bootstrap()`.

## Date/Time Filters

### Display Formatting

```twig
{# Full datetime — output depends on config (timeFormat, dateOrder, monthFormat) #}
{{ entry.dateCreated|lrDatetime }}                    {# "24 Jan 2026 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('medium') }}           {# "24 Jan 2026 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('long') }}             {# "24 January 2026 at 3:45 PM" #}
{{ entry.dateCreated|lrDatetime('short', true) }}      {# "24 Jan 2026 3:45:32 PM" (seconds) #}

{# Compact datetime (no year) — for dashboards #}
{{ entry.dateCreated|lrCompactDatetime }}              {# "24 Jan 3:45 PM" #}

{# Date only #}
{{ entry.dateCreated|lrDate }}                         {# "24 Jan 2026" #}
{{ entry.dateCreated|lrDate('long') }}                 {# "24 January 2026" #}
{{ entry.dateCreated|lrDate('short', false) }}         {# "24 Jan" (no year) #}

{# Time only #}
{{ entry.dateCreated|lrTime }}                         {# "3:45 PM" or "15:45" #}
{{ entry.dateCreated|lrTime('short', true) }}          {# "3:45:32 PM" (seconds) #}

{# Short date — for charts #}
{{ entry.dateCreated|lrShortDate }}                    {# "Jan 24" #}

{# Relative time #}
{{ entry.dateCreated|lrRelative }}                     {# "2 hours ago" #}
```

### Machine Formatting

```twig
{# Database format #}
{{ entry.dateCreated|lrForDatabase }}                  {# "2026-01-24 15:45:32" #}

{# API format (ISO 8601) #}
{{ entry.dateCreated|lrForApi }}                       {# "2026-01-24T15:45:32+00:00" #}

{# Filename format #}
{{ entry.dateCreated|lrForFilename }}                  {# "2026-01-24-154532" #}
```

### Filter Parameters

| Filter | Parameters | Description |
|--------|------------|-------------|
| `lrDatetime` | `length='short'`, `showSeconds=null`, `includeYear=true`, `isUtc=true` | Full datetime |
| `lrCompactDatetime` | `showSeconds=null`, `isUtc=true` | Datetime without year |
| `lrDate` | `length='short'`, `includeYear=true`, `isUtc=true` | Date only |
| `lrTime` | `length='short'`, `showSeconds=null`, `isUtc=true` | Time only |
| `lrShortDate` | `isUtc=true` | Short date for charts |
| `lrRelative` | `isUtc=true` | Relative time |
| `lrForDatabase` | (none) | `Y-m-d H:i:s` format |
| `lrForApi` | (none) | ISO 8601 format |
| `lrForFilename` | `includeTime=true` | `Y-m-d-His` format |

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
{% set enabledColor = lrColor('status', 'enabled') %}  {# Alias for lrSetColor #}

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

## Plugin Helper Variable

Each plugin registers a Twig global via `PluginHelper::bootstrap()`. This variable provides display name methods from [SettingsDisplayNameTrait](../feature-tour/settings-display-name.md):

```twig
{# Available via the helper variable name set in bootstrap() #}
{{ myHelper.displayName }}          {# "Redirect" #}
{{ myHelper.fullName }}             {# "Redirect Manager" #}
{{ myHelper.pluralDisplayName }}    {# "Redirects" #}
{{ myHelper.lowerDisplayName }}     {# "redirect" #}
```

## Next Steps

- [DateFormatHelper](../feature-tour/date-format-helper.md) — PHP API for date formatting
- [ColorHelper](../feature-tour/color-helper.md) — PHP API for colors
- [Components](components.md) — Twig components that use these functions
- [Configuration](../get-started/configuration.md) — settings that control date/time and export formatting
