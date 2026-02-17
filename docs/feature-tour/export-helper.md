# ExportHelper

Centralized CSV, JSON, and Excel export for all LindemannRock plugins. Handles format availability, filename generation, date column formatting, and formula injection protection.

## Format Availability

Export formats are controlled by `config/lindemannrock-base.php`. Plugins can also override formats in their own config files.

```php
use lindemannrock\base\helpers\ExportHelper;

// Check if a format is enabled
ExportHelper::isFormatEnabled('excel');  // true (default)
ExportHelper::isFormatEnabled('csv');    // true (default)
ExportHelper::isFormatEnabled('json');   // false (default)

// Accepts aliases
ExportHelper::isFormatEnabled('xlsx');   // Same as 'excel'

// List all enabled formats
ExportHelper::getEnabledFormats();       // ['csv', 'excel']

// Options for select fields (order: Excel, CSV, JSON)
ExportHelper::getFormatOptions();
// [['value' => 'xlsx', 'label' => 'Excel (.xlsx)'], ['value' => 'csv', 'label' => 'CSV (.csv)']]
```

In Twig:

```twig
{% if lrExportEnabled('excel') %}...{% endif %}
{% set formats = lrExportFormats() %}
{% set options = lrExportFormatOptions() %}
```

## Generating Filenames

Three patterns for filename generation:

```php
// Pattern 1: Settings object + parts + extension (recommended)
$settings = MyPlugin::$plugin->getSettings();
ExportHelper::filename($settings, ['logs', 'last30days'], 'xlsx');
// → "my-plugin-logs-last30days-2026-01-24-153045.xlsx"

// Pattern 2: Simple prefix + extension
ExportHelper::filename('sms-logs', 'csv');
// → "sms-logs-2026-01-24-153045.csv"

// Pattern 3: Exact name (no modification)
ExportHelper::filename('exact-name.csv');
// → "exact-name.csv"
```

## Exporting Data

All export methods return a `craft\web\Response` — return them directly from a controller action.

### CSV Export

```php
$headers = ['Name', 'Email', 'Created'];
$rows = [
    ['name' => 'Alice', 'email' => 'alice@example.com', 'dateCreated' => $date],
    ['name' => 'Bob', 'email' => 'bob@example.com', 'dateCreated' => $date],
];

$filename = ExportHelper::filename('users', 'csv');
return ExportHelper::toCsv($rows, $headers, $filename, ['dateCreated']);
```

The `$dateColumns` parameter specifies which columns contain dates. CSV/Excel formats them as `2026-01-24 15:45:32` (database format).

### JSON Export

```php
$filename = ExportHelper::filename('users', 'json');
return ExportHelper::toJson($rows, $filename, ['dateCreated']);
```

JSON formats date columns as ISO 8601: `2026-01-24T15:45:32+00:00`.

### Excel Export

```php
$filename = ExportHelper::filename('users', 'xlsx');
return ExportHelper::toExcel($rows, $headers, $filename, ['dateCreated'], [
    'sheetTitle' => 'Users',
    'freezeHeader' => true,   // Default: true
    'autoFilter' => true,     // Default: true
    'columnWidths' => ['A' => 30, 'B' => 40],  // Optional custom widths
]);
```

Excel files include styled headers, auto-sized columns, frozen header row, auto-filter, alternating row colors, and thin borders.

### Multi-Sheet Excel

```php
return ExportHelper::toExcelMulti([
    [
        'title' => 'Users',
        'headers' => ['Name', 'Email'],
        'rows' => $userRows,
    ],
    [
        'title' => 'Orders',
        'headers' => ['ID', 'Amount', 'Date'],
        'rows' => $orderRows,
        'dateColumns' => ['dateCreated'],
    ],
], 'full-export.xlsx');
```

### ZIP Archive

Bundle multiple files into a ZIP download.

```php
$csvContent = ExportHelper::csvContent($rows, $headers, ['dateCreated']);

return ExportHelper::toZip([
    ['name' => 'users.csv', 'content' => $csvContent],
    ['name' => 'metadata.json', 'content' => json_encode($meta)],
], 'export-bundle.zip');
```

## Empty Data Handling

Check for empty data before exporting:

```php
// In CP controllers — redirect with flash message
if (empty($rows)) {
    Craft::$app->getSession()->setError(Craft::t('my-plugin', 'No data to export.'));
    return $this->redirect(Craft::$app->getRequest()->getReferrer());
}

// Or throw an exception (for API exports)
ExportHelper::assertNotEmpty($rows);  // Throws BadRequestHttpException
```

## Security

ExportHelper automatically sanitizes cell values to prevent CSV/Excel formula injection. Values starting with `=`, `@`, `\t`, `\r`, or `\n` are prefixed with a single quote. Values starting with `+` or `-` are allowed only when they are numeric (e.g., phone numbers like `+1234567890`).

## Typical Controller Pattern

```php
public function actionExport(): Response
{
    $this->requirePermission('myPlugin:downloadLogs');

    $format = Craft::$app->getRequest()->getRequiredBodyParam('format');
    $dateRange = DateRangeHelper::normalize(
        Craft::$app->getRequest()->getBodyParam('dateRange')
    );

    $rows = MyPlugin::$plugin->getService()->getExportData($dateRange);

    if (empty($rows)) {
        Craft::$app->getSession()->setError(Craft::t('my-plugin', 'No data to export.'));
        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }

    $headers = ['Date', 'Event', 'Status'];
    $settings = MyPlugin::$plugin->getSettings();
    $filename = ExportHelper::filename($settings, ['logs', $dateRange], $format);

    return match ($format) {
        'xlsx' => ExportHelper::toExcel($rows, $headers, $filename, ['dateCreated']),
        'json' => ExportHelper::toJson($rows, $filename, ['dateCreated']),
        default => ExportHelper::toCsv($rows, $headers, $filename, ['dateCreated']),
    };
}
```

## Next Steps

- [Configuration](../get-started/configuration.md) — enabling/disabling export formats
- [Components](../template-guides/components.md) — using the export-menu component in templates
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — export-related Twig functions
