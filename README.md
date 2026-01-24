# LindemannRock Plugin Base

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-plugin-base.svg)](https://packagist.org/packages/lindemannrock/craft-plugin-base)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-plugin-base.svg)](LICENSE.md)

Common utilities and building blocks for LindemannRock Craft CMS plugins.

## Overview

This package provides shared functionality for all LindemannRock plugins:

- **Edition Support** for Craft Plugin Store licensing with standardized tiers (Standard/Lite/Pro)
- **Traits** for Settings models (displayName, database persistence, config overrides)
- **DateTimeHelper** for centralized date/time formatting with timezone support
- **Twig Extensions** for plugin name helpers and datetime filters in templates
- **Helpers** for common plugin initialization tasks and geographic utilities
- **Templates** for shared components (plugin-credit, info-box, ip-salt-error)
- **GeoHelper** for ISO 3166-1 country code lookups (249 countries)

## Requirements

- Craft CMS 5.0+
- PHP 8.2+

## Installation

### Via Composer

```bash
cd /path/to/project
composer require lindemannrock/craft-plugin-base
```

### Using DDEV

```bash
cd /path/to/project
ddev composer require lindemannrock/craft-plugin-base
```

## Usage

### Edition Support in Plugin Class

```php
use lindemannrock\base\traits\EditionTrait;

class MyPlugin extends Plugin
{
    use EditionTrait;

    // Define your tier model (override default)
    public static function editions(): array
    {
        return [
            self::EDITION_LITE,  // Entry paid tier
            self::EDITION_PRO,   // Full featured tier
        ];
    }
}
```

**Available tier models:**

| Model | Editions | Use Case |
|-------|----------|----------|
| Free-only | `[STANDARD]` | Free plugins (default) |
| Two paid | `[LITE, PRO]` | Commercial plugins |
| Free + paid | `[STANDARD, PRO]` | Freemium plugins |
| Three tiers | `[STANDARD, LITE, PRO]` | Complex offerings |

**Checking editions:**

```php
// In controllers - gate entire actions
public function actionCloudBackup(): Response
{
    MyPlugin::getInstance()->requireEdition(MyPlugin::EDITION_PRO);
    // ... pro-only code
}

// In services - conditional logic
if (MyPlugin::getInstance()->isPro()) {
    // Pro feature
}

if (MyPlugin::getInstance()->isAtLeast(MyPlugin::EDITION_LITE)) {
    // Lite or Pro feature
}
```

**In templates:**

```twig
{% set plugin = craft.app.plugins.getPlugin('my-plugin') %}

{% if plugin.isPro() %}
    {# Pro-only UI #}
{% else %}
    <a href="#">Upgrade to Pro</a>
{% endif %}

{# Show current edition #}
<span class="edition-badge">{{ plugin.getEditionName() }}</span>
```

### In Settings Model

```php
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;

class Settings extends Model
{
    use SettingsDisplayNameTrait;
    use SettingsPersistenceTrait;
    use SettingsConfigTrait;

    public string $pluginName = 'My Plugin';

    protected static function tableName(): string
    {
        return 'myplugin_settings';
    }

    protected static function pluginHandle(): string
    {
        return 'my-plugin';
    }

    // Optional: specify field types for database persistence
    protected static function booleanFields(): array
    {
        return ['enableFeature', 'debugMode'];
    }

    protected static function integerFields(): array
    {
        return ['cacheTimeout', 'maxItems'];
    }

    protected static function jsonFields(): array
    {
        return ['excludePatterns', 'customSettings'];
    }
}
```

### In Main Plugin Class

```php
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();

    // Bootstrap base module (registers Twig extension + logging)
    PluginHelper::bootstrap($this, 'myPluginHelper', ['myPlugin:viewLogs']);

    // Apply plugin name from config file
    PluginHelper::applyPluginNameFromConfig($this);
}
```

### In Templates

```twig
{# Plugin name helpers (via Twig extension) #}
{{ myPluginHelper.displayName }}       {# "My Plugin" #}
{{ myPluginHelper.fullName }}          {# "My Plugin Manager" #}
{{ myPluginHelper.pluralDisplayName }} {# "My Plugins" #}
{{ myPluginHelper.lowerDisplayName }}  {# "my plugin" #}

{# Shared components #}
{% include 'lindemannrock-base/_components/plugin-credit' %}

{% include 'lindemannrock-base/_components/info-box' with {
    message: 'This is an informational message',
    type: 'info'  {# 'info', 'success', 'warning' #}
} %}

{% include 'lindemannrock-base/_components/ip-salt-error' with {
    pluginHandle: 'my-plugin',
    envVarName: 'MY_PLUGIN_IP_SALT'
} %}
```

## Components

### Traits

| Trait | Methods Provided |
|-------|------------------|
| `EditionTrait` | `editions()`, `isStandard()`, `isLite()`, `isPro()`, `isAtLeast()`, `isBelow()`, `requireEdition()`, `getEditionName()`, `hasMultipleEditions()` |
| `SettingsDisplayNameTrait` | `getDisplayName()`, `getFullName()`, `getPluralDisplayName()`, `getLowerDisplayName()`, `getPluralLowerDisplayName()` |
| `SettingsPersistenceTrait` | `loadFromDatabase()`, `saveToDatabase()` |
| `SettingsConfigTrait` | `isOverriddenByConfig()` |

### Templates

| Template | Purpose |
|----------|---------|
| `plugin-credit` | Footer credit with plugin name and developer link |
| `info-box` | Styled info/success/warning message box |
| `ip-salt-error` | Error banner for missing IP hash salt configuration |
| `badge` | Colored status badge with dot and text |
| `row-actions` | Action buttons/menus for table rows |
| `filter-status` | Status dropdown filter with colored indicators |
| `filter-dropdown` | Simple dropdown filter |
| `filter-daterange` | Date range picker filter |
| `export-menu` | Export dropdown with format checking (Excel/CSV/JSON) |
| `cp-table` (layout) | Reusable table/listing page layout |
| `cp-analytics` (layout) | Reusable analytics/dashboard page layout |

### Helpers

| Helper | Purpose |
|--------|---------|
| `PluginHelper::bootstrap()` | Registers base module, Twig extension, and logging |
| `PluginHelper::applyPluginNameFromConfig()` | Applies custom plugin name from config file |
| `PluginHelper::registerTranslations()` | Register translation messages for a plugin |
| `PluginHelper::getCacheBasePath()` | Get the cache base path for a plugin |
| `PluginHelper::getCachePath()` | Get a specific cache type path for a plugin |
| `GeoHelper::getCountryName()` | Convert ISO 3166-1 alpha-2 country code to name |
| `GeoHelper::getAllCountries()` | Get all 249 countries as code => name array |
| `GeoHelper::isValidCountryCode()` | Validate a country code |
| `DateTimeHelper::formatDatetime()` | Format datetime for display with timezone |
| `DateTimeHelper::formatDate()` | Format date only for display |
| `DateTimeHelper::formatTime()` | Format time only for display |
| `DateTimeHelper::forDatabase()` | Format for MySQL datetime storage |
| `DateTimeHelper::forApi()` | Format as ISO 8601 for APIs |
| `DateTimeHelper::forFilename()` | Format safe for filenames |
| `ColorHelper::getPaletteColor()` | Get a color from the palette by name |
| `ColorHelper::getPaletteColorNames()` | Get all available palette color names |
| `ColorHelper::getColorSet()` | Get entire color set by name |
| `ColorHelper::getSetColor()` | Get specific color from a set |
| `ColorHelper::getNeutralColor()` | Get neutral/unselected color |
| `ColorHelper::getDefaultColor()` | Get default fallback color |
| `ColorHelper::getFilterColor()` | Get color for filter display |
| `ColorHelper::hasColorSet()` | Check if a color set exists |
| `ColorHelper::getAvailableColorSets()` | Get all available color set names |
| `ColorHelper::registerColorSet()` | Register custom color set at runtime |

### Cache Path Helpers

Provides consistent cache directory structure across plugins: `storage/runtime/{plugin-handle}/cache/{type}/`

```php
use lindemannrock\base\helpers\PluginHelper;

// Get the base cache path for a plugin
$basePath = PluginHelper::getCacheBasePath($plugin);
// Returns: storage/runtime/my-plugin/cache/

// Get a specific cache type path
$searchCache = PluginHelper::getCachePath($plugin, 'search');
// Returns: storage/runtime/my-plugin/cache/search/

$autocompleteCache = PluginHelper::getCachePath($plugin, 'autocomplete');
// Returns: storage/runtime/my-plugin/cache/autocomplete/

$deviceCache = PluginHelper::getCachePath($plugin, 'device');
// Returns: storage/runtime/my-plugin/cache/device/
```

### GeoHelper Usage

```php
use lindemannrock\base\helpers\GeoHelper;

// Get country name from code
$name = GeoHelper::getCountryName('US');  // "United States"
$name = GeoHelper::getCountryName('GB');  // "United Kingdom"
$name = GeoHelper::getCountryName('XX');  // "XX" (returns code if unknown)

// Get all countries
$countries = GeoHelper::getAllCountries();  // ['AD' => 'Andorra', 'AE' => 'United Arab Emirates', ...]

// Validate country code
$valid = GeoHelper::isValidCountryCode('US');  // true
$valid = GeoHelper::isValidCountryCode('XX');  // false
```

### DateTimeHelper

Provides centralized date/time formatting for all plugins. Respects Craft's timezone and configurable format preferences.

#### Configuration

Create `config/lindemannrock-base.php` to set your preferences:

```php
<?php
return [
    // Time format: '12' (AM/PM) or '24' (military)
    'timeFormat' => '24',

    // Month format: 'numeric' (01), 'short' (Jan), 'long' (January)
    'monthFormat' => 'numeric',

    // Date order: 'dmy', 'mdy', 'ymd'
    'dateOrder' => 'dmy',

    // Date separator: '/', '-', '.' (only used with numeric month format)
    'dateSeparator' => '/',

    // Show seconds by default: true/false
    'showSeconds' => false,

    // Environment-specific overrides
    // 'production' => [
    //     'timeFormat' => '12',
    //     'monthFormat' => 'short',
    // ],
];
```

#### PHP Usage

```php
use lindemannrock\base\helpers\DateTimeHelper;

// Display formatting (respects config + Craft timezone)
DateTimeHelper::formatDatetime($date);                    // "22/01/2026 15:45"
DateTimeHelper::formatDatetime($date, 'long');            // "22 January 2026 at 15:45"
DateTimeHelper::formatDatetime($date, showSeconds: true); // "22/01/2026 15:45:32"

// Compact datetime (no year) - ideal for dashboards
DateTimeHelper::formatCompactDatetime($date);             // "22 Jan 15:45"

// Exclude year with includeYear parameter
DateTimeHelper::formatDatetime($date, includeYear: false); // "22/01 15:45"
DateTimeHelper::formatDate($date, includeYear: false);     // "22/01"

DateTimeHelper::formatDate($date);                        // "22/01/2026"
DateTimeHelper::formatDate($date, 'medium');              // "22 Jan 2026"
DateTimeHelper::formatDate($date, 'long');                // "22 January 2026"

DateTimeHelper::formatTime($date);                        // "15:45" or "3:45 PM"
DateTimeHelper::formatTime($date, showSeconds: true);     // "15:45:32"

DateTimeHelper::formatShortDate($date);                   // "Jan 22" (for charts)
DateTimeHelper::formatRelative($date);                    // "2 hours ago"

// Database formatting
DateTimeHelper::forDatabase($date);                       // "2026-01-22 15:45:32"
DateTimeHelper::forDatabaseDate($date);                   // "2026-01-22"
DateTimeHelper::forDatabaseDayStart($date);               // "2026-01-22 00:00:00"
DateTimeHelper::forDatabaseDayEnd($date);                 // "2026-01-22 23:59:59"

// API formatting (ISO 8601)
DateTimeHelper::forApi($date);                            // "2026-01-22T15:45:32+00:00"

// Filename formatting
DateTimeHelper::forFilename();                            // "2026-01-22-154532"
DateTimeHelper::forFilename($date, includeTime: false);   // "2026-01-22"

// Utilities
DateTimeHelper::now();                                    // Current DateTime in Craft timezone
DateTimeHelper::isToday($date);                           // true/false
DateTimeHelper::isPast($date);                            // true/false
DateTimeHelper::isFuture($date);                          // true/false
DateTimeHelper::toCraftTimezone($date);                   // Convert UTC to Craft timezone
```

#### Twig Usage

All filters automatically respect the config settings:

```twig
{# Display formatting #}
{{ entry.dateCreated|lrDatetime }}              {# 22/01/2026 15:45 #}
{{ entry.dateCreated|lrDatetime('long') }}      {# 22 January 2026 at 15:45 #}
{{ entry.dateCreated|lrDatetime('short', true) }} {# 22/01/2026 15:45:32 (with seconds) #}

{{ entry.dateCreated|lrDate }}                  {# 22/01/2026 #}
{{ entry.dateCreated|lrDate('long') }}          {# 22 January 2026 #}

{{ entry.dateCreated|lrTime }}                  {# 15:45 #}
{{ entry.dateCreated|lrTime('short', true) }}   {# 15:45:32 (with seconds) #}

{{ entry.dateCreated|lrShortDate }}             {# Jan 22 #}
{{ entry.dateCreated|lrRelative }}              {# 2 hours ago #}

{# Compact datetime (no year) - ideal for dashboards/recent activity #}
{{ entry.dateCreated|lrCompactDatetime }}       {# Jan 22 15:45 or 22 Jan 15:45 #}

{# Exclude year using includeYear parameter #}
{{ entry.dateCreated|lrDatetime('short', null, false) }}  {# 22/01 15:45 #}
{{ entry.dateCreated|lrDate('short', false) }}            {# 22/01 #}
{{ entry.dateCreated|lrDate('medium', false) }}           {# 22 Jan #}

{# Database/API formatting #}
{{ entry.dateCreated|lrForDatabase }}           {# 2026-01-22 15:45:32 #}
{{ entry.dateCreated|lrForApi }}                {# 2026-01-22T15:45:32+00:00 #}
{{ entry.dateCreated|lrForFilename }}           {# 2026-01-22-154532 #}

{# Utility functions #}
{% set now = lrNow() %}
{% if lrIsToday(entry.dateCreated) %}Today{% endif %}
{% if lrIsPast(entry.expiryDate) %}Expired{% endif %}
{% if lrIsFuture(entry.postDate) %}Scheduled{% endif %}
```

#### Example Configurations

**European Client (24-hour, DD/MM/YYYY numeric):**
```php
return [
    'timeFormat' => '24',
    'monthFormat' => 'numeric',
    'dateOrder' => 'dmy',
    'dateSeparator' => '/',
];
// Output: 22/01/2026 15:45
```

**US Client (12-hour AM/PM, Jan 22, 2026):**
```php
return [
    'timeFormat' => '12',
    'monthFormat' => 'short',
    'dateOrder' => 'mdy',
];
// Output: Jan 22, 2026 3:45 PM
```

**Formal Style (January 22, 2026):**
```php
return [
    'timeFormat' => '24',
    'monthFormat' => 'long',
    'dateOrder' => 'mdy',
];
// Output: January 22, 2026 15:45
```

**ISO Standard (24-hour, YYYY-MM-DD):**
```php
return [
    'timeFormat' => '24',
    'monthFormat' => 'numeric',
    'dateOrder' => 'ymd',
    'dateSeparator' => '-',
];
// Output: 2026-01-22 15:45
```

**Overriding in Templates:**

The `monthFormat` config sets the default, but you can always override per-call:

```twig
{# Uses config default (e.g., numeric → 22/01/2026) #}
{{ entry.dateCreated|lrDate }}

{# Force short month names regardless of config #}
{{ entry.dateCreated|lrDate('medium') }}  {# 22 Jan 2026 #}

{# Force full month names regardless of config #}
{{ entry.dateCreated|lrDate('long') }}    {# 22 January 2026 #}
```

#### Real-World Examples

**AJAX Response (Controller):**
```php
use lindemannrock\base\helpers\DateTimeHelper;

public function actionGetLogs(): Response
{
    $logs = $this->logsService->getLogs();

    foreach ($logs as &$log) {
        $log['dateFormatted'] = DateTimeHelper::formatDatetime($log['dateCreated']);
        $log['timeFormatted'] = DateTimeHelper::formatTime($log['dateCreated'], showSeconds: true);
    }

    return $this->asJson([
        'success' => true,
        'logs' => $logs,
        'exportedAt' => DateTimeHelper::forApi(DateTimeHelper::now()),
    ]);
}
```

**CSV Export:**
```php
use lindemannrock\base\helpers\DateTimeHelper;

public function actionExportCsv(): Response
{
    $data = $this->service->getData();
    $output = fopen('php://temp', 'r+');

    // Header row
    fputcsv($output, ['Date', 'Time', 'Message', 'Status']);

    // Data rows with formatted dates
    foreach ($data as $row) {
        fputcsv($output, [
            DateTimeHelper::formatDate($row['dateCreated']),
            DateTimeHelper::formatTime($row['dateCreated'], showSeconds: true),
            $row['message'],
            $row['status'],
        ]);
    }

    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);

    // Filename with timestamp
    $filename = 'export-' . DateTimeHelper::forFilename() . '.csv';

    return $this->response
        ->setHeader('Content-Type', 'text/csv')
        ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
        ->setContent($content);
}
```

**JSON Export:**
```php
use lindemannrock\base\helpers\DateTimeHelper;

public function actionExportJson(): Response
{
    $data = $this->service->getData();

    $export = [
        'exportedAt' => DateTimeHelper::forApi(DateTimeHelper::now()),
        'timezone' => Craft::$app->getTimeZone(),
        'records' => array_map(fn($row) => [
            'id' => $row['id'],
            'date' => DateTimeHelper::formatDate($row['dateCreated']),
            'time' => DateTimeHelper::formatTime($row['dateCreated']),
            'datetime' => DateTimeHelper::formatDatetime($row['dateCreated']),
            'iso' => DateTimeHelper::forApi($row['dateCreated']),
            'message' => $row['message'],
        ], $data),
    ];

    $filename = 'export-' . DateTimeHelper::forFilename() . '.json';

    return $this->response
        ->setHeader('Content-Type', 'application/json')
        ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
        ->setContent(json_encode($export, JSON_PRETTY_PRINT));
}
```

**Log Viewer Template (Twig):**
```twig
<table>
    <thead>
        <tr>
            <th>Time</th>
            <th>Level</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
        {% for entry in logEntries %}
            <tr>
                <td>
                    <time datetime="{{ entry.timestamp|lrForApi }}">
                        {{ entry.timestamp|lrTime('short', true) }}
                    </time>
                </td>
                <td>{{ entry.level|upper }}</td>
                <td>{{ entry.message }}</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```

#### Migration Guide

When updating existing code to use DateTimeHelper, replace the old patterns:

**Before (Manual timezone conversion):**
```php
$utcDate = new \DateTime($result['lastHit'], new \DateTimeZone('UTC'));
$utcDate->setTimezone(new \DateTimeZone(Craft::$app->getTimeZone()));
$result['lastHitFormatted'] = Craft::$app->getFormatter()->asDatetime($utcDate, 'short');
```

**After:**
```php
$result['lastHitFormatted'] = DateTimeHelper::formatDatetime($result['lastHit']);
```

---

**Before (Direct formatter):**
```php
$formatter = Craft::$app->getFormatter();
$log['datetimeFormatted'] = $formatter->asDatetime($log['dateCreated'], 'medium');
```

**After:**
```php
$log['datetimeFormatted'] = DateTimeHelper::formatDatetime($log['dateCreated'], 'medium');
```

---

**Before (Manual format strings):**
```php
$date->format('Y-m-d H:i:s');           // For database
$date->format('c');                      // For API
date('Y-m-d-His');                       // For filename
$date->format('M j, Y');                 // For display
```

**After:**
```php
DateTimeHelper::forDatabase($date);      // For database
DateTimeHelper::forApi($date);           // For API
DateTimeHelper::forFilename();           // For filename
DateTimeHelper::formatDate($date, 'medium');  // For display
```

---

**Before (Twig):**
```twig
{{ entry.timestamp|date('H:i:s') }}
{{ entry.timestamp|date('Y-m-d') }}
{{ entry.timestamp|date('M j, Y') }}
```

**After:**
```twig
{{ entry.timestamp|lrTime('short', true) }}
{{ entry.timestamp|lrDate }}
{{ entry.timestamp|lrDate('medium') }}
```

#### Quick Reference

| Method/Filter | Output Example | Use Case |
|---------------|----------------|----------|
| `formatDatetime()` / `\|lrDatetime` | 22/01/2026 15:45 | General display |
| `formatDatetime($d, 'long')` | 22 January 2026 at 15:45 | Detailed display |
| `formatCompactDatetime()` / `\|lrCompactDatetime` | 22 Jan 15:45 | Dashboards/recent activity |
| `formatDatetime($d, 'short', null, false)` | 22/01 15:45 | Datetime without year |
| `formatDate()` / `\|lrDate` | 22/01/2026 | Date only |
| `formatDate($d, 'long')` | 22 January 2026 | Long date |
| `formatDate($d, 'short', false)` | 22/01 | Date without year |
| `formatTime()` / `\|lrTime` | 15:45 | Time only |
| `formatTime($d, showSeconds: true)` | 15:45:32 | Time with seconds |
| `formatShortDate()` / `\|lrShortDate` | Jan 22 | Charts/compact |
| `formatRelative()` / `\|lrRelative` | 2 hours ago | Relative time |
| `forDatabase()` / `\|lrForDatabase` | 2026-01-22 15:45:32 | MySQL storage |
| `forDatabaseDate()` | 2026-01-22 | MySQL date only |
| `forDatabaseDayStart()` | 2026-01-22 00:00:00 | Date range start |
| `forDatabaseDayEnd()` | 2026-01-22 23:59:59 | Date range end |
| `forApi()` / `\|lrForApi` | 2026-01-22T15:45:32+00:00 | JSON APIs |
| `forFilename()` / `\|lrForFilename` | 2026-01-22-154532 | Export filenames |
| `now()` / `lrNow()` | DateTime object | Current time |
| `isToday()` / `lrIsToday()` | true/false | Check if today |
| `isPast()` / `lrIsPast()` | true/false | Check if past |
| `isFuture()` / `lrIsFuture()` | true/false | Check if future |

## ExportHelper

Provides centralized CSV, JSON, and Excel export functionality for all LindemannRock plugins. Handles date formatting, response headers, and consistent file naming.

### Configuration

Add to `config/lindemannrock-base.php` to control which export formats are available:

```php
return [
    // ... other settings ...

    // Export formats (all default to true if not specified)
    'exports' => [
        'csv' => true,
        'json' => true,
        'excel' => true,  // Set false to disable
    ],
];
```

### PHP Usage

```php
use lindemannrock\base\helpers\ExportHelper;

// Check enabled formats
if (ExportHelper::isFormatEnabled('excel')) {
    // Excel export available
}

$formats = ExportHelper::getEnabledFormats(); // ['csv', 'json', 'excel']

// Generate filename - 3 patterns supported:

// 1. Standard pattern with settings (recommended)
$settings = MyPlugin::$plugin->getSettings();
$filename = ExportHelper::filename($settings, ['logs', $dateRange], 'xlsx');
// → "my-plugin-logs-last30days-2026-01-24-153045.xlsx"

// 2. Simple with timestamp
$filename = ExportHelper::filename('sms-logs', 'csv');
// → "sms-logs-2026-01-24-153045.csv"

// 3. Exact name (no modification)
$filename = ExportHelper::filename('exact-name.csv');
// → "exact-name.csv"

// Check for empty data - redirect with flash message (recommended for CP)
if (empty($rows)) {
    Craft::$app->getSession()->setError(Craft::t('my-plugin', 'No logs to export.'));
    return $this->redirect(Craft::$app->getRequest()->getReferrer());
}

// Or throw exception for API exports
ExportHelper::assertNotEmpty($rows);  // Throws BadRequestHttpException: "Nothing to export."
ExportHelper::assertNotEmpty($rows, 'Custom message');  // Custom error message

// CSV export
return ExportHelper::toCsv($rows, $headers, $filename, ['dateCreated']);

// JSON export
return ExportHelper::toJson($rows, $filename, ['dateCreated']);

// Excel export with options
return ExportHelper::toExcel($rows, $headers, $filename, ['dateCreated'], [
    'sheetTitle' => 'SMS Logs',      // Sheet name (max 31 chars)
    'freezeHeader' => true,           // Freeze header row (default: true)
    'autoFilter' => true,             // Add filter dropdowns (default: true)
    'columnWidths' => ['A' => 20],    // Custom column widths (optional)
]);
```

### Twig Usage

```twig
{# Check if format is enabled #}
{% if lrExportEnabled('excel') %}
    <a href="{{ url('plugin/export', {format: 'xlsx'}) }}">Export as Excel</a>
{% endif %}

{# Build export menu from enabled formats #}
<div class="menu">
    {% for format in lrExportFormats() %}
        <a href="{{ url('plugin/export', {format: format}) }}">
            {{ format|upper }}
        </a>
    {% endfor %}
</div>

{# Get format options for select fields (form dropdowns) #}
{{ forms.selectField({
    label: 'Export Format',
    name: 'format',
    options: lrExportFormatOptions(),
}) }}
```

### Export Methods

| Method | Output | Use Case |
|--------|--------|----------|
| `toCsv($rows, $headers, $filename, $dateColumns)` | CSV file | Spreadsheet-compatible |
| `toJson($data, $filename, $dateColumns)` | JSON file | API/data exchange |
| `toExcel($rows, $headers, $filename, $dateColumns, $options)` | XLSX file | Professional reports |
| `filename($prefix, $extension)` | Timestamped filename | Consistent naming |
| `isFormatEnabled($format)` | boolean | Check availability |
| `getEnabledFormats()` | array | List all enabled formats |
| `getFormatOptions()` | array | Options for select fields |
| `formatDateColumns($rows, $dateColumns)` | Formatted rows | Database format dates |
| `formatDateColumnsForApi($rows, $dateColumns)` | Formatted rows | ISO 8601 dates |

### Excel Features

The `toExcel()` method creates professionally styled spreadsheets:

- **Header styling**: Bold white text on dark background
- **Frozen header row**: Stays visible while scrolling
- **Auto-filter dropdowns**: Easy data filtering
- **Auto-sized columns**: Fits content (or use custom widths)
- **Alternating row colors**: Improved readability
- **Thin borders**: Clean grid appearance

### Controller Example

```php
use lindemannrock\base\helpers\ExportHelper;

public function actionExport(): Response
{
    $this->requirePermission('myPlugin:export');

    $format = Craft::$app->getRequest()->getQueryParam('format', 'csv');

    // Check if format is enabled
    if (!ExportHelper::isFormatEnabled($format)) {
        throw new ForbiddenHttpException('Export format not available');
    }

    // Get data
    $logs = $this->logsService->getLogs();

    // Prepare rows for export
    $rows = array_map(fn($log) => [
        'dateCreated' => $log['dateCreated'],
        'recipient' => $log['recipient'],
        'message' => $log['message'],
        'status' => $log['status'],
    ], $logs);

    $headers = ['Date', 'Recipient', 'Message', 'Status'];
    $dateColumns = ['dateCreated'];
    $settings = MyPlugin::$plugin->getSettings();

    return match ($format) {
        'json' => ExportHelper::toJson($rows, ExportHelper::filename($settings, ['logs', $dateRange], 'json'), $dateColumns),
        'xlsx', 'excel' => ExportHelper::toExcel($rows, $headers, ExportHelper::filename($settings, ['logs', $dateRange], 'xlsx'), $dateColumns),
        default => ExportHelper::toCsv($rows, $headers, ExportHelper::filename($settings, ['logs', $dateRange], 'csv'), $dateColumns),
    };
}
```

## ColorHelper

Provides centralized color definitions for badges, filters, and status indicators across all LindemannRock plugins. Ensures consistent colors are used in both filter dropdowns and table badges.

### Color Palette

ColorHelper provides a unified `PALETTE` constant with all available colors. This includes Craft's Tailwind-based colors and can be extended with custom colors:

| Class | Hex Color | Use Case |
|-------|-----------|----------|
| `teal` | #14b8a6 | Enabled, live status |
| `cyan` | #06b6d4 | Information |
| `gray` | #6b7280 | Disabled, neutral |
| `orange` | #f97316 | Pending, warning |
| `red` | #ef4444 | Error, expired, off |
| `blue` | #3b82f6 | Production, redirect |
| `pink` | #ec4899 | Development |
| `purple` | #a855f7 | Debug |
| `green` | #22c55e | Success, yes, on |
| `yellow` | #eab308 | Caution |
| `amber` | #f59e0b | Alert |
| `emerald` | #10b981 | Positive |
| `indigo` | #6366f1 | Special |
| `violet` | #8b5cf6 | Alternative |
| `fuchsia` | #d946ef | Highlight |
| `rose` | #f43f5e | Client error |
| `lime` | #84cc16 | Active |
| `sky` | #0ea5e9 | Info logs |

### Available Color Sets

| Color Set | Values | Use Case |
|-----------|--------|----------|
| `status` | enabled, disabled, pending, expired, live, on, off | Craft status classes |
| `yesNo` | yes, no, true, false | Boolean (green/red) |
| `handled` | yes, no, true, false | Handled state (green/red) |
| `configSource` | config, database | Configuration source |
| `environmentType` | development, staging, production | Environment type |
| `priority` | low, normal, high, critical | Priority levels |
| `httpStatus` | success, redirect, client_error, server_error | HTTP response types |
| `logLevel` | debug, info, warning, error | Log severity levels |
| `exportStatus` | pending, processing, completed, failed | Export/job status |

### PHP Usage

```php
use lindemannrock\base\helpers\ColorHelper;

// Get a palette color by name (recommended for plugins)
$teal = ColorHelper::getPaletteColor('teal');
// Returns: ['class' => 'teal', 'color' => '#14b8a6', 'rgb' => '20, 184, 166', 'text' => '#115e59']

// Get all available palette color names
$colorNames = ColorHelper::getPaletteColorNames();
// Returns: ['teal', 'cyan', 'gray', 'orange', 'red', 'blue', 'pink', ...]

// Get entire color set
$colors = ColorHelper::getColorSet('status');
// Returns: ['enabled' => ['class' => 'teal', ...], 'disabled' => ['class' => 'gray', ...], ...]

// Get specific color from a set
$enabledColor = ColorHelper::getSetColor('status', 'enabled');
// Returns: ['class' => 'teal', 'color' => '#14b8a6', 'rgb' => '20, 184, 166', 'text' => '#115e59', 'dot' => 'enabled']

// Get neutral color (for unselected filter items)
$neutral = ColorHelper::getNeutralColor();
// Returns: '#aab6c1'

// Get filter color (shows actual color if selected, neutral if not)
$filterColor = ColorHelper::getFilterColor('status', 'enabled', $currentFilter);

// Check if color set exists
if (ColorHelper::hasColorSet('customSet')) { ... }

// Register custom color set at runtime (uses palette colors)
ColorHelper::registerColorSet('myCustomSet', [
    'active' => ColorHelper::getPaletteColor('teal'),
    'inactive' => ColorHelper::getPaletteColor('gray'),
]);
```

### Twig Usage

```twig
{# Get a palette color by name #}
{% set teal = lrPaletteColor('teal') %}

{# Get all palette color names #}
{% set colorNames = lrPaletteColorNames() %}

{# Get entire color set #}
{% set colors = lrColorSet('status') %}

{# Get specific color from a set #}
{% set enabledColor = lrSetColor('status', 'enabled') %}
<span style="color: {{ enabledColor.color }};">Enabled</span>

{# Get neutral color #}
{% set neutral = lrNeutralColor() %}

{# Get default color (fallback) #}
{% set default = lrDefaultColor() %}

{# Get filter color (colored if selected, neutral if not) #}
{% set filterColor = lrFilterColor('status', 'enabled', currentFilter) %}
<span class="status" style="background: {{ filterColor }};"></span>

{# Check if color set exists #}
{% if lrHasColorSet('customSet') %}...{% endif %}

{# Get all available color sets #}
{% set allSets = lrAvailableColorSets() %}
```

### Plugin Color Registration

Plugins should register their custom colors in their `init()` method using `PluginHelper::bootstrap()`:

```php
use lindemannrock\base\helpers\ColorHelper;
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();

    // Bootstrap with custom color sets
    PluginHelper::bootstrap(
        $this,
        'myPluginHelper',
        ['myPlugin:viewLogs'],
        ['myPlugin:downloadLogs'],
        [
            'colorSets' => [
                'myStatus' => [
                    'active' => ColorHelper::getPaletteColor('teal'),
                    'pending' => ColorHelper::getPaletteColor('orange'),
                    'failed' => ColorHelper::getPaletteColor('red'),
                ],
                'myType' => [
                    'typeA' => ColorHelper::getPaletteColor('purple'),
                    'typeB' => ColorHelper::getPaletteColor('blue'),
                ],
            ],
        ]
    );
}
```

### Adding Default Color Sets

To add new default color sets to the base module, edit `/plugins/base/src/helpers/ColorHelper.php` and add to the `initialize()` method using `PALETTE`:

```php
'myNewType' => [
    'value1' => self::PALETTE['teal'],
    'value2' => self::PALETTE['red'],
],

// For status sets with dot classes, use array_merge:
'myStatus' => [
    'active' => array_merge(self::PALETTE['teal'], ['dot' => 'enabled']),
    'inactive' => array_merge(self::PALETTE['gray'], ['dot' => 'disabled']),
],
```

Each color entry contains:
- `class` - CSS class name for status-label wrapper (matches Craft's classes)
- `color` - Solid hex color for dots/indicators
- `rgb` - RGB values for semi-transparent backgrounds
- `text` - Dark text color for readability
- `dot` - (optional) Inner status dot class (e.g., 'enabled', 'disabled')

## Template Components

### Badge Component

Renders colored badges with dot and text. Uses ColorHelper for consistent colors.

**Location:** `lindemannrock-base/_components/badge`

```twig
{# Using Craft's built-in status colors #}
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Enabled',
    status: 'green',  {# green, red, orange, blue, teal, gray, disabled, all #}
} only %}

{# Using ColorHelper color set (recommended) #}
{% include 'lindemannrock-base/_components/badge' with {
    label: item.status|capitalize,
    value: item.status,
    colorSet: 'smsStatus',
} only %}

{# Using custom colors directly #}
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Custom',
    color: '#6366f1',
    rgb: '99, 102, 241',
    textColor: '#312e81',
} only %}

{# With link #}
{% include 'lindemannrock-base/_components/badge' with {
    label: 'View',
    status: 'green',
    url: '/some/url',
    title: 'Click to view',
} only %}
```

### Row Actions Component

Renders action buttons or dropdown menus for table rows with permission handling.

**Location:** `lindemannrock-base/_components/row-actions`

```twig
{# Simple delete button #}
{% include 'lindemannrock-base/_components/row-actions' with {
    item: redirect,
    actions: {
        type: 'button',
        icon: 'delete',
        permission: 'pluginHandle:delete',
        class: 'delete',
        jsAction: 'delete',
    },
} only %}

{# Dropdown menu with multiple actions #}
{% include 'lindemannrock-base/_components/row-actions' with {
    item: item,
    actions: {
        type: 'menu',
        icon: 'settings',
        title: 'Actions'|t('app'),
        permission: 'pluginHandle:anyAction',
        items: [
            {
                label: 'Edit'|t('app'),
                url: url('plugin/edit/' ~ item.id),
                permission: 'plugin:edit',
            },
            {type: 'divider'},
            {
                label: 'Delete'|t('app'),
                class: 'error',
                permission: 'plugin:delete',
                jsAction: 'delete',
                confirm: 'Are you sure?',
            },
        ],
    },
} only %}
```

**Parameters:**
- `item` - The current row item (provides `item.id` for data attributes)
- `actions.type` - `'button'` or `'menu'`
- `actions.icon` - Icon name (delete, settings, etc.)
- `actions.permission` - Column-level permission (hides entire column if not allowed)
- `actions.items` - Array of menu items (for `type: 'menu'`)
  - `label` - Display text
  - `url` - Link URL
  - `permission` - Per-action permission check
  - `showIf` / `hideIf` - Conditional display
  - `class` - CSS class (e.g., `'error'` for destructive)
  - `jsAction` - JavaScript action name (triggers `lr:rowAction` event)
  - `confirm` - Confirmation message
  - `type: 'divider'` - Separator line

### Filter Components

#### Status Filter

Dropdown filter with colored status indicators. Supports ColorHelper integration.

**Location:** `lindemannrock-base/_components/filter-status`

```twig
{% include 'lindemannrock-base/_components/filter-status' with {
    filter: {
        param: 'status',
        current: statusFilter,
        label: 'All Status'|t('my-plugin'),
        colorSet: 'smsStatus',  {# Use ColorHelper colors #}
        options: [
            {value: 'all', label: 'All'|t('app'), status: 'all'},
            {value: 'sent', label: 'Sent'|t('my-plugin'), colorKey: 'sent'},
            {value: 'failed', label: 'Failed'|t('my-plugin'), colorKey: 'failed'},
            {value: 'pending', label: 'Pending'|t('my-plugin'), colorKey: 'pending'},
        ],
    },
    urlParams: {search: search, sort: sort, dir: dir},
} only %}
```

**Grouped filters with multiple sections:**

Each group can have its own `param` and `current` values, allowing multiple filter parameters in one dropdown:

```twig
{% include 'lindemannrock-base/_components/filter-status' with {
    filter: {
        param: 'status',        {# Default param for groups without their own #}
        current: statusFilter,  {# Default current for groups without their own #}
        label: 'All',
        groups: [
            {
                {# Uses default param/current from filter #}
                options: [
                    {value: 'all', label: 'All', status: 'all'},
                    {value: 'enabled', label: 'Enabled', status: 'green'},
                    {value: 'disabled', label: 'Disabled', status: 'disabled'},
                ],
            },
            {
                header: 'Source',
                param: 'source',           {# Different URL param #}
                current: sourceFilter,     {# Different current value #}
                colorSet: 'configSource',
                options: [
                    {value: 'all', label: 'All Sources', status: 'all'},
                    {value: 'config', label: 'Config', colorKey: 'config'},
                    {value: 'database', label: 'Database', colorKey: 'database'},
                ],
            },
            {
                header: 'Type',
                param: 'type',
                current: typeFilter,
                colorSet: 'environmentType',
                options: [
                    {value: 'all', label: 'All Types', status: 'all'},
                    {value: 'production', label: 'Production', colorKey: 'production'},
                    {value: 'development', label: 'Development', colorKey: 'development'},
                ],
            },
        ],
    },
    urlParams: urlParams,
} only %}
```

#### Dropdown Filter

Simple dropdown filter without status indicators.

**Location:** `lindemannrock-base/_components/filter-dropdown`

```twig
{% include 'lindemannrock-base/_components/filter-dropdown' with {
    filter: {
        param: 'language',
        current: languageFilter,
        label: 'All Languages'|t('my-plugin'),
        options: [
            {value: 'all', label: 'All Languages'},
            {value: 'en', label: 'English'},
            {value: 'de', label: 'German'},
        ],
    },
    urlParams: urlParams,
} only %}
```

#### Date Range Filter

Date range picker for filtering by date period.

**Location:** `lindemannrock-base/_components/filter-daterange`

```twig
{% include 'lindemannrock-base/_components/filter-daterange' with {
    filter: {
        param: 'dateRange',
        current: dateRange,
        label: 'Date Range'|t('my-plugin'),
    },
    urlParams: urlParams,
} only %}
```

### Export Menu Component

Reusable export dropdown menu that automatically shows only enabled formats based on `config/lindemannrock-base.php` settings.

**Location:** `lindemannrock-base/_components/export-menu`

```twig
{# Basic usage #}
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'sms-manager/sms-logs/export',
    permission: 'smsManager:downloadLogs',
} only %}

{# With extra parameters (filters, site, etc.) #}
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'my-plugin/export',
    permission: 'myPlugin:export',
    extraParams: {status: statusFilter, provider: providerFilter},
} only %}
```

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `action` | string | (required) | Controller action URL (e.g., `'sms-manager/logs/export'`) |
| `permission` | string | `null` | Permission required to show button (checks `currentUser.can()`) |
| `dateRangeParam` | string | `'dateRange'` | URL parameter name for date range |
| `extraParams` | object | `{}` | Additional parameters to pass to export URL |

**Features:**

- Automatically reads `dateRange` from the current URL
- Only shows formats enabled in config (`lrExportEnabled()`)
- Hides entire button if no formats are enabled
- Respects permission checks when `permission` is provided
- Format order: Excel → CSV → JSON

**Output Example:**

When all formats are enabled, renders:
```html
<div class="btngroup">
    <button type="button" class="btn menubtn" data-icon="download">Export</button>
    <div class="menu" data-align="right">
        <ul>
            <li><a href="...?format=excel">Export as Excel</a></li>
            <li><a href="...?format=csv">Export as CSV</a></li>
            <li><a href="...?format=json">Export as JSON</a></li>
        </ul>
    </div>
</div>
```

## CP Table Layout

A reusable layout for building consistent table/listing pages in the Control Panel.

**Location:** `lindemannrock-base/_layouts/cp-table`

### Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-table' %}

{% set tableConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'My Items'|t('my-plugin'),
        subnav: 'items',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
            { label: 'Items'|t('my-plugin'), url: url('my-plugin/items') }
        ],
    },
    filters: [
        {
            type: 'status',
            param: 'status',
            current: statusFilter,
            label: 'All Status'|t('my-plugin'),
            colorSet: 'enabledStatus',
            options: [
                {value: 'all', label: 'All', status: 'all'},
                {value: 'enabled', label: 'Enabled', colorKey: 'enabled'},
                {value: 'disabled', label: 'Disabled', colorKey: 'disabled'},
            ],
        },
        {
            type: 'dropdown',
            param: 'category',
            current: categoryFilter,
            label: 'All Categories',
            options: categoryOptions,
        },
        {
            type: 'dateRange',
            param: 'dateRange',
            current: dateRange,
            label: 'Date Range',
        },
    ],
    search: {
        placeholder: 'Search items...'|t('my-plugin'),
        value: search,
    },
    sort: {
        field: sort,
        direction: dir,
    },
    table: {
        columns: [
            {key: 'dateCreated', label: 'Created'|t('my-plugin'), sortable: true},
            {key: 'name', label: 'Name'|t('my-plugin'), sortable: true},
            {key: 'status', label: 'Status'|t('my-plugin'), sortable: true},
        ],
        items: items,
        emptyMessage: 'No items found.'|t('my-plugin'),
    },
    pagination: {
        page: page,
        limit: limit,
        totalCount: totalCount,
        itemLabel: {singular: 'item', plural: 'items'},
    },
    checkboxes: currentUser.can('myPlugin:deleteItems'),
    rowActions: true,  // Set to false to hide Actions column
} %}

{# Custom table row rendering #}
{% block tableRow %}
    <td class="light">{{ item.dateCreated|lrDatetime }}</td>
    <td><strong>{{ item.name }}</strong></td>
    <td>
        {% include 'lindemannrock-base/_components/badge' with {
            label: item.status|capitalize,
            value: item.status,
            colorSet: 'enabledStatus',
        } only %}
    </td>
{% endblock %}

{# Row actions (per-row buttons/menu) #}
{% block rowActions %}
    {% include 'lindemannrock-base/_components/row-actions' with {
        item: item,
        actions: {
            type: 'menu',
            icon: 'settings',
            permission: 'myPlugin:editItems',
            items: [
                {label: 'Edit', url: url('my-plugin/items/' ~ item.id)},
                {type: 'divider'},
                {label: 'Delete', class: 'error', jsAction: 'delete'},
            ],
        },
    } only %}
{% endblock %}

{# Toolbar actions (e.g., Export button) #}
{% block toolbarActions %}
    <div class="btngroup">
        <button type="button" class="btn menubtn" data-icon="download">Export</button>
        <div class="menu">
            <ul>
                <li><a href="{{ url('my-plugin/export', {format: 'csv'}) }}">CSV</a></li>
                <li><a href="{{ url('my-plugin/export', {format: 'json'}) }}">JSON</a></li>
            </ul>
        </div>
    </div>
{% endblock %}

{# Bulk actions (shown when items selected) #}
{% block bulkActions %}
    {% if currentUser.can('myPlugin:deleteItems') %}
        <button type="button" class="btn secondary" id="bulk-delete-btn">
            Delete (<span id="selected-count">0</span>)
        </button>
    {% endif %}
{% endblock %}
```

### Configuration Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `plugin.handle` | string | `''` | Plugin handle for URL building |
| `plugin.name` | string | `''` | Plugin display name |
| `page.title` | string | `'Listing'` | Page title |
| `page.subnav` | string | `''` | Active subnav item |
| `page.crumbs` | array | `[]` | Breadcrumb items |
| `filters` | array | `[]` | Filter configurations (status, dropdown, dateRange) |
| `search.placeholder` | string | `'Search...'` | Search input placeholder |
| `search.value` | string | `''` | Current search value |
| `sort.field` | string | `'dateCreated'` | Current sort field |
| `sort.direction` | string | `'desc'` | Sort direction (asc/desc) |
| `table.columns` | array | `[]` | Column definitions with `key`, `label`, `sortable`, `width` |
| `table.items` | array | `[]` | Data items to display |
| `table.emptyMessage` | string | `'No items found.'` | Message when no items |
| `table.expandable` | bool | `false` | Enable click-to-expand rows |
| `pagination.page` | int | `1` | Current page number |
| `pagination.limit` | int | `50` | Items per page |
| `pagination.totalCount` | int | `0` | Total item count |
| `pagination.itemLabel` | object | `{singular: 'item', plural: 'items'}` | Labels for pagination text |
| `checkboxes` | bool | `false` | Enable row selection checkboxes |
| `rowActions` | bool | `true` | Show Actions column (set `false` for read-only tables) |
| `newButton` | object/null | `null` | New button config: `{url, label, permission}` |
| `ajax.enabled` | bool | `false` | Enable auto-refresh |
| `ajax.interval` | int | `0` | Refresh interval in seconds |
| `ajax.endpoint` | string | `''` | AJAX endpoint URL |

### Expandable Rows

Enable click-to-expand rows for showing additional details:

```twig
{% set tableConfig = {
    table: {
        expandable: true,  // Enable expandable rows
        // ...
    },
} %}

{% block expandableRow %}
    <div class="context-label">{{ 'Details'|t('app') }}</div>
    <pre>{{ item.context }}</pre>
{% endblock %}
```

### New Button

Add a "New" button to the toolbar:

```twig
{% set tableConfig = {
    newButton: {
        url: url('my-plugin/items/new'),
        label: 'New Item'|t('my-plugin'),
        permission: 'myPlugin:createItems',  // Optional permission check
    },
} %}
```

### Available Blocks

| Block | Purpose |
|-------|---------|
| `tableRow` | Custom cell rendering for each row |
| `rowActions` | Per-row action button/menu |
| `toolbarActions` | Buttons in the toolbar (outside form) |
| `bulkActions` | Buttons shown when items are selected |
| `expandableRow` | Expandable detail content for each row |
| `sidebar` | Right sidebar content (uses Craft's details pane) |
| `beforeTable` | Content before table (warnings, info boxes) |
| `extraToolbar` | Additional toolbar items inside the toolbar form |
| `extraFooter` | Additional footer content |
| `scripts` | Custom JavaScript for the page |

### Sidebar Content

To add content to the right sidebar (details pane), use the `sidebarContent` block:

```twig
{% block sidebarContent %}
    <div class="meta" style="padding: 12px;">
        <div class="data">
            <div class="heading">{{ "Summary"|t('app') }}</div>
            <div class="value">{{ totalCount }} items</div>
        </div>
        <div class="data">
            <div class="heading">{{ "Status"|t('app') }}</div>
            <div class="value">Active</div>
        </div>
    </div>
{% endblock %}
```

The sidebar appears on the right side of the page using Craft's built-in details pane.

> **Note**: We use `sidebarContent` instead of `sidebar` to avoid collision with Craft's left sidebar block in `_layouts/cp`.

### JavaScript Events

The cp-table layout dispatches these events:

```javascript
// Selection changed
document.addEventListener('lr:selectionChanged', function(e) {
    console.log('Selected count:', e.detail.count);
    console.log('Selected IDs:', e.detail.ids);
});

// Row action triggered
document.addEventListener('lr:rowAction', function(e) {
    console.log('Action:', e.detail.action);
    console.log('Item ID:', e.detail.id);
});

// Access selection API
if (window.lrTableSelection) {
    const ids = window.lrTableSelection.getSelectedIds();
    const count = window.lrTableSelection.getCount();
}
```

### Column Sorting

The cp-table layout provides clickable column headers for sorting, but **you must implement the actual sorting logic** in your template.

**Step 1:** Mark columns as sortable in your config:

```twig
table: {
    columns: [
        {key: 'name', label: 'Name', sortable: true},
        {key: 'handle', label: 'Handle', sortable: true},
        {key: 'provider', label: 'Provider'},  {# not sortable #}
    ],
}
```

**Step 2:** Get sort parameters from the request:

```twig
{% set sort = craft.app.request.getParam('sort', 'name') %}
{% set dir = craft.app.request.getParam('dir', 'asc') %}
```

**Step 3:** Implement sorting logic before pagination:

```twig
{# Sort items #}
{% if sort == 'name' %}
    {% set items = items|sort((a, b) => dir == 'asc' ? a.name|lower <=> b.name|lower : b.name|lower <=> a.name|lower) %}
{% elseif sort == 'handle' %}
    {% set items = items|sort((a, b) => dir == 'asc' ? a.handle|lower <=> b.handle|lower : b.handle|lower <=> a.handle|lower) %}
{% elseif sort == 'dateCreated' %}
    {% set items = items|sort((a, b) => dir == 'asc' ? a.dateCreated <=> b.dateCreated : b.dateCreated <=> a.dateCreated) %}
{% endif %}

{# Then paginate #}
{% set totalCount = items|length %}
{% set paginatedItems = items|slice(offset, limit) %}
```

**Sorting tips:**
- Use `|lower` for case-insensitive string sorting
- Use `?? ''` for nullable fields: `(a.field ?? '') <=> (b.field ?? '')`
- The `<=>` spaceship operator returns -1, 0, or 1 for comparison
- Boolean fields sort directly: `a.enabled <=> b.enabled`

## CP Analytics Layout

A reusable layout for building consistent analytics/dashboard pages in the Control Panel.

**Location:** `lindemannrock-base/_layouts/cp-analytics`

### Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-analytics' %}

{% set analyticsConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Analytics'|t('my-plugin'),
        subnav: 'analytics',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
            { label: 'Analytics'|t('my-plugin'), url: url('my-plugin/analytics') },
        ],
    },
    tabs: {
        overview: { label: 'Overview'|t('my-plugin') },
        details: { label: 'Details'|t('my-plugin') },
    },
    filters: {
        dateRange: {
            default: 'last7days',
            current: dateRange,
        },
        sites: {
            enabled: true,
            current: siteId,
            sites: craft.app.sites.allSites,
        },
        custom: [
            {
                param: 'provider',
                current: providerId,
                allLabel: 'All Providers'|t('my-plugin'),
                options: providerOptions,
            },
        ],
    },
    export: {
        permission: 'myPlugin:exportAnalytics',
        action: 'my-plugin/analytics/export',
    },
    charts: {
        prefix: 'myPlugin',
        dataEndpoint: 'my-plugin/analytics/get-data',
    },
} %}

{# Tab content #}
{% block tabs %}
    <div id="overview" class="lr-tab-content">
        {% include 'my-plugin/analytics/_partials/overview' %}
    </div>
    <div id="details" class="lr-tab-content hidden">
        {% include 'my-plugin/analytics/_partials/details' %}
    </div>
{% endblock %}

{# Chart initialization #}
{% block scripts %}
<script>
document.addEventListener('lr:analyticsInit', function(e) {
    window.lrLoadChartData('daily', function(data) {
        window.lrCreateChart('daily-chart', 'line', {
            labels: data.labels,
            datasets: [{ label: 'Views', data: data.values, borderColor: '#0d78f2' }]
        });
    });
});
</script>
{% endblock %}
```

### Configuration Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `plugin.handle` | string | `''` | Plugin handle for URL building |
| `plugin.name` | string | `''` | Plugin display name |
| `page.title` | string | `'Analytics'` | Page title |
| `page.subnav` | string | `'analytics'` | Active subnav item |
| `tabs` | object | `{}` | Tab definitions: `{ tabId: { label: 'Label' } }` |
| `filters.dateRange.default` | string | `'last7days'` | Default date range |
| `filters.dateRange.current` | string | | Current date range value |
| `filters.sites.enabled` | bool | `false` | Enable site filter |
| `filters.sites.current` | string | `''` | Current site ID |
| `filters.sites.sites` | array | `[]` | Available sites |
| `filters.custom` | array | `[]` | Custom filter definitions |
| `export.permission` | string | `null` | Permission for export |
| `export.action` | string | `''` | Export action URL |
| `charts.prefix` | string | `'analytics'` | Window variable prefix for charts |
| `charts.dataEndpoint` | string | `''` | AJAX endpoint for chart data |

### CSS Classes

The layout provides these pre-styled CSS classes:

| Class | Description |
|-------|-------------|
| `.lr-tab-content` | Tab content wrapper (use `hidden` class for non-active) |
| `.lr-analytics-stats` | Grid container for stat boxes |
| `.lr-stat-box` | Individual stat box |
| `.lr-stat-value` | Large stat value |
| `.lr-stat-label` | Stat description label |
| `.lr-analytics-charts` | Grid container for charts |
| `.lr-analytics-charts.two-columns` | Two-column chart grid |
| `.lr-chart-container` | Individual chart wrapper |
| `.lr-chart-container.full-width` | Full-width chart (spans grid) |
| `.lr-table-scroll` | Scrollable table wrapper |
| `.lr-section-heading` | Section heading style |

### JavaScript Helpers

The layout provides global helpers for chart operations:

```javascript
// Load chart data via AJAX
window.lrLoadChartData('chartType', function(data) {
    // Process data
}, { extraParam: 'value' });

// Create a chart using Chart.js
window.lrCreateChart('canvas-id', 'line', {
    labels: [...],
    datasets: [...]
}, { /* Chart.js options */ });

// Access chart colors
const colors = window.lrChartColors;

// Access config
const config = window.lrAnalyticsConfig;
```

### JavaScript Events

```javascript
// Analytics initialized (charts ready to load)
document.addEventListener('lr:analyticsInit', function(e) {
    console.log('Date range:', e.detail.dateRange);
    console.log('Site ID:', e.detail.siteId);
});

// Tab changed
document.addEventListener('lr:tabChanged', function(e) {
    console.log('Active tab:', e.detail.tabId);
});
```

### Available Blocks

| Block | Purpose |
|-------|---------|
| `tabs` | Tab content containers (required) |
| `actionButton` | Export/action button area |
| `extraToolbar` | Additional toolbar items |
| `scripts` | Custom JavaScript for chart initialization |

### Components

Use these components within your tab partials:

**Stat Box:**
```twig
{% include 'lindemannrock-base/_components/stat-box' with {
    value: 12345,
    label: 'Total Views',
    color: '#10b981',  {# optional #}
    suffix: '%',       {# optional #}
    id: 'views-stat',  {# optional, for JS updates #}
} only %}
```

**Chart Container:**
```twig
<div class="lr-chart-container full-width">
    <h3>{{ 'Daily Trend'|t('app') }}</h3>
    <canvas id="daily-chart"></canvas>
</div>
```

## Example Templates

The base plugin includes example templates you can copy and adapt:

| Example | Location | Description |
|---------|----------|-------------|
| Badges Reference | `_examples/badges.twig` | Visual reference of all color sets and badge styles |
| Table Layout | `_examples/table-layout.twig` | Complete cp-table example with all features |
| Grouped Filters | `_examples/grouped-filters.twig` | Multi-param grouped filter dropdown |
| Analytics Layout | `_examples/analytics-layout.twig` | Complete cp-analytics example with tabs, charts, stats |

### Using Examples

Copy the example to your plugin and adapt:

```bash
cp plugins/base/src/templates/_examples/table-layout.twig plugins/my-plugin/src/templates/items/index.twig
```

Or reference directly in development:

```php
// In your plugin's getCpUrlRules()
'my-plugin/examples/badges' => ['template' => 'lindemannrock-base/_examples/badges'],
'my-plugin/examples/table' => ['template' => 'lindemannrock-base/_examples/table-layout'],
```

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-plugin-base](https://github.com/LindemannRock/craft-plugin-base)
- **Issues**: [https://github.com/LindemannRock/craft-plugin-base/issues](https://github.com/LindemannRock/craft-plugin-base/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
