# DateFormatHelper @since(5.8.0)

Centralized date/time formatting for all LindemannRock plugins. Converts dates to Craft's configured timezone, formats them for display, database storage, API responses, and filenames — all driven by a single config file.

> [!NOTE]
> Named `DateFormatHelper` (not `DateTimeHelper`) to avoid collision with Craft's built-in `craft\helpers\DateTimeHelper`.

## How It Works

All formatting respects two things:

1. **Craft's timezone** — dates are automatically converted from UTC to the site's configured timezone before formatting
2. **Config settings** — `config/lindemannrock-base.php` controls time format (12/24h), date order, month format, separator, and whether to show seconds

See [Configuration](../get-started/configuration.md) for all available settings.

### Cascade order @since(5.10.0)

When `DateFormatHelper::getConfig()` resolves a setting, it walks four layers (high → low priority):

```
1. Plugin config file       config/{handle}.php                  ← env overrides
2. Plugin DB settings       (via DateFormatSettingsTrait)        ← user-set in plugin CP
3. Base config file         config/lindemannrock-base.php        ← global default
4. Hardcoded fallback       (in the getter methods)              ← '24' / 'ymd' / etc.
```

The "current plugin" is auto-detected from `Craft::$app->controller->module` when its controller belongs to a plugin — so Twig filters like `|lrTime` automatically respect per-plugin overrides without callers needing to thread a plugin handle through every call site.

When plugin code is rendered by a Craft-owned controller, pass the plugin handle explicitly. Common examples are element-index attributes, element cards, slideouts, widgets, and other CP surfaces where Craft owns the request but the plugin owns the display code:

```php
DateFormatHelper::formatDate($date, pluginHandle: 'my-plugin');
DateFormatHelper::formatDatetime($date, pluginHandle: 'my-plugin');
```

To surface these settings in a plugin's CP, see [`DateFormatSettingsTrait`](../../src/traits/DateFormatSettingsTrait.php) and the shared partial `lindemannrock-base/_partials/cascade-date-format-settings.twig`.

## Display Formatting

### formatDatetime()

Formats a date and time for display. Accepts `DateTime` objects, date strings, or `null`.

```php
use lindemannrock\base\helpers\DateFormatHelper;

// With config: timeFormat='12', dateOrder='dmy', monthFormat='long'
DateFormatHelper::formatDatetime($date);                    // "24 January 2026 3:45 PM"
DateFormatHelper::formatDatetime($date, 'short');           // "24/01/2026 3:45 PM"
DateFormatHelper::formatDatetime($date, 'medium');          // "24 Jan 2026 3:45 PM"
DateFormatHelper::formatDatetime($date, 'long');            // "24 January 2026 at 3:45 PM"
DateFormatHelper::formatDatetime($date, 'cascade', true);   // "24 January 2026 3:45:32 PM" (with seconds)
```

| Style | Contract |
|-------|----------|
| `cascade` | Default. Respects the active plugin/base cascade: `monthFormat`, `dateOrder`, `dateSeparator`, `timeFormat`, and `showSeconds`. |
| `short` | Fixed compact numeric date. Respects `dateOrder`, `dateSeparator`, `timeFormat`, and `showSeconds`, but forces numeric month display. |
| `medium` | Fixed abbreviated month date. Respects `dateOrder`, `timeFormat`, and `showSeconds`, but forces short month names. |
| `long` | Fixed full month date with ` at ` between date and time. Respects `dateOrder`, `timeFormat`, and `showSeconds`, but forces full month names. |

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$date` | `DateTime\|string\|null` | required | The date to format |
| `$style` | `string` | `'cascade'` | `'cascade'`, `'short'`, `'medium'`, or `'long'` |
| `$showSeconds` | `bool\|null` | `null` | Override config's `showSeconds` |
| `$includeYear` | `bool` | `true` | Include year in output |
| `$isUtc` | `bool` | `true` | Whether string input is UTC |
| `$pluginHandle` | `string\|null` | `null` | Explicit plugin handle for cascade settings when auto-detection is unavailable |

### formatDate()

Formats the date portion only.

```php
DateFormatHelper::formatDate($date);                  // "24 January 2026" when cascade monthFormat='long'
DateFormatHelper::formatDate($date, 'short');          // "24/01/2026"
DateFormatHelper::formatDate($date, 'medium', false);  // "24 Jan" (no year)
DateFormatHelper::formatDate($date, 'long');           // "24 January 2026"
DateFormatHelper::formatDate($date, pluginHandle: 'my-plugin');
```

### formatTime()

Formats the time portion only.

```php
DateFormatHelper::formatTime($date);                     // "3:45 PM" (12h) or "15:45" (24h)
DateFormatHelper::formatTime($date, 'cascade', true);    // "3:45:32 PM" (with seconds)
DateFormatHelper::formatTime($date, pluginHandle: 'my-plugin');
```

When `$showSeconds` is `null` (default), the value flows from the [cascade](#cascade-order-since5100). Seconds appear if base config or the active plugin has `showSeconds=true`, regardless of `$style`. Pass explicit `true` or `false` to override seconds for one call site.

### formatCompactDatetime()

Short datetime without year — useful for dashboards and recent activity lists.

```php
DateFormatHelper::formatCompactDatetime($date);  // "24 Jan 3:45 PM"
DateFormatHelper::formatCompactDatetime($date, pluginHandle: 'my-plugin');
```

### formatCompactDatetimeFromSettings() @since(5.26.0)

Short datetime without year from an already-resolved plugin settings object. Use this for serialized strings such as queue descriptions, where the plugin settings object is available and the output should not depend on request-order config cache state.

```php
DateFormatHelper::formatCompactDatetimeFromSettings(
    $nextRun,
    MyPlugin::$plugin->getSettings(),
    false,
    false,
); // "May 25 18:57"

DateFormatHelper::formatCompactDatetimeFromSettings(
    $nextRun,
    MyPlugin::$plugin->getSettings(),
    false,
    false,
    true,
); // "2027 May 25 18:57" when the queue label needs the year
```

### formatRelative()

Relative time using Craft's formatter.

```php
DateFormatHelper::formatRelative($date);  // "2 hours ago"
```

## Machine Formatting

Fixed formats for database storage and exports — not affected by config settings.

```php
DateFormatHelper::toDateTimeString($date);  // "2026-01-24 15:45:32"
DateFormatHelper::toDateString($date);      // "2026-01-24"
DateFormatHelper::toDayStartString($date);  // "2026-01-24 00:00:00"
DateFormatHelper::toDayEndString($date);    // "2026-01-24 23:59:59"
```

## API Formatting

ISO 8601 format for API responses.

```php
DateFormatHelper::toApiString($date);  // "2026-01-24T15:45:32+00:00"
```

## Filename Formatting

Safe characters for use in filenames. Format: `Y-m-d-His`.

```php
DateFormatHelper::toFilenameString();              // "2026-01-24-154532" (current time)
DateFormatHelper::toFilenameString($date);         // "2026-01-24-154532"
DateFormatHelper::toFilenameString($date, false);  // "2026-01-24" (date only)
```

## Timezone Conversion

Convert a date to Craft's configured timezone.

```php
$local = DateFormatHelper::toCraftTimezone($date);
// DateTime object in Craft's timezone

// String input (assumes UTC by default)
$local = DateFormatHelper::toCraftTimezone('2026-01-24 15:45:32');

// String input already in local time
$local = DateFormatHelper::toCraftTimezone('2026-01-24 15:45:32', isUtc: false);
```

## Configuration Access

Read the resolved config values programmatically:

```php
DateFormatHelper::getConfig();         // Full config array
DateFormatHelper::getTimeFormat();     // '12' or '24'
DateFormatHelper::getDateOrder();      // 'dmy', 'mdy', or 'ymd'
DateFormatHelper::getDateSeparator();  // '/', '-', or '.'
DateFormatHelper::getShowSeconds();    // bool
DateFormatHelper::getMonthFormat();    // 'numeric', 'short', or 'long'
DateFormatHelper::clearConfigCache();             // Clear all cached config
DateFormatHelper::clearConfigCache('my-plugin');  // Clear one plugin's cached config
```

## SQL Expressions @since(5.15.0)

DB-agnostic timezone-aware SQL expressions for grouping queries by date or hour. These generate the correct SQL for both MySQL and PostgreSQL, using bound parameters to prevent SQL injection.

### localDateExpression() @since(5.15.0)

Groups by calendar date in the site's timezone.

```php
$localDate = DateFormatHelper::localDateExpression('dateCreated');
$query->select(['date' => $localDate, 'COUNT(*) as count'])
      ->groupBy($localDate);
```

Generates:
- **MySQL:** `DATE(CONVERT_TZ(dateCreated, '+00:00', '+03:00'))`
- **PostgreSQL:** `DATE(dateCreated AT TIME ZONE 'UTC' AT TIME ZONE '+03:00')`

### localHourExpression() @since(5.15.0)

Groups by hour-of-day (0–23) in the site's timezone.

```php
$localHour = DateFormatHelper::localHourExpression('dateCreated');
$query->select(['hour' => $localHour, 'COUNT(*) as count'])
      ->groupBy($localHour);
```

### getCraftTimezoneOffset() @since(5.15.0)

Returns the site's timezone offset in `±HH:MM` format.

```php
$offset = DateFormatHelper::getCraftTimezoneOffset();  // "+03:00" or "-05:00"
```

## Utility Methods

```php
$now = DateFormatHelper::now();              // Current DateTime in Craft timezone
$isToday = DateFormatHelper::isToday($date); // true if same calendar day
$isPast = DateFormatHelper::isPast($date);   // true if before now
$isFuture = DateFormatHelper::isFuture($date); // true if after now
```

## Twig Usage

All display methods are available as Twig filters and functions. See [Twig Filters & Functions](../template-guides/twig-filters-functions.md).

```twig
{{ entry.dateCreated|lrDatetime }}
{{ entry.dateCreated|lrDate('long') }}
{{ entry.dateCreated|lrTime('cascade', true) }}
{{ entry.dateCreated|lrRelative }}
{% if lrIsToday(entry.dateCreated) %}Today{% endif %}
```

## Next Steps

- [Configuration](../get-started/configuration.md) — date/time format settings
- [DateRangeHelper](date-range-helper.md) — standard date ranges for analytics
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — date formatting in templates
