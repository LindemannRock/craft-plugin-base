# DateRangeHelper @since(5.2.0)

Standard date range parsing for analytics, logs, and any date-filtered CP pages. Converts range names like `'last30days'` into UTC date bounds and applies them to database queries.

## Available Ranges

| Value | Description |
|-------|-------------|
| `today` | Start of today (in site timezone) to now |
| `yesterday` | Start of yesterday to start of today |
| `thisWeek` | Configured Craft week start to now |
| `lastWeek` | Previous full configured Craft week |
| `last7days` | 7 days ago to now |
| `last14days` | 14 days ago to now |
| `last30days` | 30 days ago to now |
| `last90days` | 90 days ago to now |
| `thisMonth` | First day of current month to now |
| `lastMonth` | First day of last month to first day of current month |
| `thisQuarter` | First day of current calendar quarter to now |
| `lastQuarter` | Previous full calendar quarter |
| `thisYear` | January 1st of current year to now |
| `lastYear` | January 1st of last year to January 1st of current year |
| `last12months` | 12 months ago to now |
| `all` | No date filter (returns all records) |
| `custom` | Uses caller-provided start/end dates |

`custom` is opt-in for dropdowns and query helpers. Plugins decide whether to expose custom date fields in their own UI.

Week-based ranges use Craft's `defaultWeekStartDay` general config setting. The helper converts Craft's weekday numbering (`0` = Sunday) to PHP ISO weekdays (`7` = Sunday) via `DateRangeHelper::getWeekStartIsoDay()`.

## Getting the Default Range @since(5.3.0)

The default date range resolves through a priority chain:

1. Plugin config: `defaultDateRange` in `config/{plugin-handle}.php`
2. Plugin config: `analytics.defaultDateRange` (legacy key)
3. Base config: `defaultDateRange` in `config/lindemannrock-base.php`
4. Base config: `analytics.defaultDateRange` (legacy key)
5. Hardcoded fallback: `'last30days'`

```php
use lindemannrock\base\helpers\DateRangeHelper;

// Uses the resolution chain above
$default = DateRangeHelper::getDefaultDateRange();

// Check a specific plugin's config first
$default = DateRangeHelper::getDefaultDateRange('search-manager');
```

## Normalizing Range Values

Sanitizes user input and applies defaults.

```php
$range = DateRangeHelper::normalize($request->getQueryParam('dateRange'));
// Returns the input if valid, or the default range if null/empty

// With explicit fallback default
$range = DateRangeHelper::normalize($input, 'last7days');
// Uses 'last7days' instead of config default when input is null/invalid

// Also normalizes legacy 'alltime' to 'all'
$range = DateRangeHelper::normalize('alltime');  // Returns 'all'
```

## Getting Date Bounds

Returns UTC `DateTime` objects for the start and end of a range.

```php
$bounds = DateRangeHelper::getBounds('last7days');
// Returns: ['start' => DateTime, 'end' => null]
// 'end' is null when the range extends to the current moment

$bounds = DateRangeHelper::getBounds('lastMonth');
// Returns: ['start' => DateTime (first of last month), 'end' => DateTime (first of this month)]

$bounds = DateRangeHelper::getBounds('lastWeek');
// Returns the previous full week using Craft's configured week start day

$bounds = DateRangeHelper::getBounds('all');
// Returns: ['start' => null, 'end' => null]

$bounds = DateRangeHelper::getBounds('custom', null, '2026-01-01', '2026-01-31');
// Returns start of Jan 1 to start of Feb 1, converted to UTC
```

Bounds are calculated in the site's timezone and then converted to UTC for database queries. You can pass a custom timezone:

```php
$bounds = DateRangeHelper::getBounds('today', new \DateTimeZone('America/New_York'));
```

## Applying to Queries

Apply a date range filter to a Yii2 query in a single call.

```php
$query = (new Query())->from('{{%myPlugin_logs}}');
DateRangeHelper::applyToQuery($query, 'last30days');
// Adds: WHERE dateCreated >= '2025-12-11 ...' (UTC)

// With a custom column name
DateRangeHelper::applyToQuery($query, 'last7days', 'sentAt');

// With custom start/end dates
DateRangeHelper::applyToQuery($query, 'custom', 'sentAt', null, '2026-01-01', '2026-01-31');
```

## Counting Days in a Range

Useful for calculating averages (e.g., "average clicks per day").

```php
$days = DateRangeHelper::getDaysCount('last30days');  // 30
$days = DateRangeHelper::getDaysCount('lastWeek');    // 7
$days = DateRangeHelper::getDaysCount('thisMonth');   // Day of month (1–31)
$days = DateRangeHelper::getDaysCount('lastYear');    // 365 or 366
```

## Dropdown Options @since(5.3.0)

Get options for date range dropdowns in templates.

```php
// Array of {value, label} objects (for Craft select fields)
$options = DateRangeHelper::getOptions();
// [['value' => 'today', 'label' => 'Today'], ['value' => 'yesterday', ...], ...]

// Associative array (value => label)
$options = DateRangeHelper::getOptions('assoc');
// ['today' => 'Today', 'yesterday' => 'Yesterday', ...]

// Include an opt-in custom range option
$options = DateRangeHelper::getOptions('array', true);
// [..., ['value' => 'custom', 'label' => 'Custom Range']]
```

## Typical Controller Pattern

```php
public function actionIndex(): Response
{
    $dateRange = DateRangeHelper::normalize(
        Craft::$app->getRequest()->getQueryParam('dateRange')
    );

    $query = (new Query())->from('{{%myPlugin_logs}}');
    DateRangeHelper::applyToQuery($query, $dateRange);

    $logs = $query->all();

    return $this->renderTemplate('my-plugin/logs/index', [
        'logs' => $logs,
        'dateRange' => $dateRange,
    ]);
}
```

## Next Steps

- [DateFormatHelper](date-format-helper.md) — formatting dates for display
- [Configuration](../get-started/configuration.md) — setting the default date range
- [CP Analytics Layout](../template-guides/cp-analytics-layout.md) — using date ranges in analytics dashboards
