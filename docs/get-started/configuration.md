# Configuration

LindemannRock Base is configured through a single config file at `config/lindemannrock-base.php`. These settings control date/time formatting, export formats, and default date ranges across all LindemannRock plugins.

## Config File

Create `config/lindemannrock-base.php` in your project:

```php
<?php

return [
    'timeFormat' => '24',
    'monthFormat' => 'short',
    'dateOrder' => 'dmy',
    'dateSeparator' => '/',
    'showSeconds' => false,

    'exports' => [
        'excel' => true,
        'csv' => true,
        'json' => false,
    ],

    'defaultDateRange' => 'last30days',
];
```

> [!NOTE]
> The example above shows the sample config template (`src/config.php`). The "Default" column below shows what the code uses when no config file is present — these may differ from the sample values.

## Settings Reference

### Date/Time Formatting

These settings control how dates and times appear in CP pages, AJAX responses, and exports for all LindemannRock plugins.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timeFormat` | `string` | `'24'` | Time format: `'12'` (AM/PM) or `'24'` (24-hour) |
| `monthFormat` | `string` | `'numeric'` | Month display: `'numeric'` (01), `'short'` (Jan), `'long'` (January) |
| `dateOrder` | `string` | `'ymd'` | Date component order: `'dmy'`, `'mdy'`, or `'ymd'` |
| `dateSeparator` | `string` | `'/'` | Separator between date components: `'/'`, `'-'`, or `'.'` |
| `showSeconds` | `bool` | `false` | Show seconds in time display by default |

The `dateSeparator` is only used when `monthFormat` is `'numeric'`. With `'short'` or `'long'` month formats, dates use spaces as separators (e.g., "24 Jan 2026" instead of "24/01/2026").

**Examples with different settings:**

| Settings | Output |
|----------|--------|
| `dmy` + `numeric` + `/` | 24/01/2026 3:45 PM |
| `dmy` + `short` | 24 Jan 2026 3:45 PM |
| `mdy` + `long` | January 24, 2026 3:45 PM |
| `ymd` + `numeric` + `-` | 2026-01-24 15:45 |

### Export Formats

Control which export formats are available across all LindemannRock plugins. Plugins use `ExportHelper` to check format availability and render export menus.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `exports.excel` | `bool` | `true` | Enable Excel (.xlsx) export |
| `exports.csv` | `bool` | `true` | Enable CSV export |
| `exports.json` | `bool` | `false` | Enable JSON export (developer format) |

JSON export is disabled by default because it is primarily a developer format. Enable it if your workflow includes data processing or API integrations that consume JSON.

### Default Date Range

The default date range used for analytics dashboards, log viewers, and any date-filtered CP pages.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `defaultDateRange` | `string` | `'last30days'` | Default date range for date-filtered pages |

**Available values:** `'today'`, `'yesterday'`, `'last7days'`, `'last30days'`, `'last90days'`, `'thisMonth'`, `'lastMonth'`, `'thisYear'`, `'lastYear'`, `'all'`

## Full Config Example

```php
<?php
// config/lindemannrock-base.php

return [
    // Date/Time — European format with short month names
    'timeFormat' => '12',
    'monthFormat' => 'short',
    'dateOrder' => 'dmy',
    'dateSeparator' => '/',
    'showSeconds' => false,

    // Export — all formats enabled
    'exports' => [
        'excel' => true,
        'csv' => true,
        'json' => true,
    ],

    // Analytics/logs default to last 7 days
    'defaultDateRange' => 'last7days',
];
```

## Next Steps

- [DateFormatHelper](../feature-tour/date-format-helper.md) — how date/time formatting works in PHP
- [ExportHelper](../feature-tour/export-helper.md) — CSV, JSON, and Excel exports
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — using date formatting in templates

## Translations

LindemannRock Base includes translations for 12 languages. See [Translations](../resources/translations.md) for the full list and override instructions.
