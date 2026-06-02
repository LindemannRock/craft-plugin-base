# ExportHelper @since(5.8.0)

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
ExportHelper::normalizeFormat('xlsx');   // 'excel'
ExportHelper::extensionForFormat('excel'); // 'xlsx'

// List all enabled formats
ExportHelper::getEnabledFormats();       // ['csv', 'excel']

// Full resolved config (merges plugin + base configs)
ExportHelper::getConfig('my-plugin');   // ['excel' => true, 'csv' => true, 'json' => false]

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

Generated timestamp filenames normalize the plugin/prefix and each filename part to safe lowercase filename segments. Exact names remain unchanged for callers that already provide a complete filename.

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

### Multi-Sheet Excel @since(5.13.1)

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

### ZIP Archive @since(5.13.1)

Bundle multiple files into a ZIP download. Use the content-only helpers (`csvContent` / `excelContent`) to build the pieces, then `toZip` to package them.

```php
$csvContent = ExportHelper::csvContent($rows, $headers, ['dateCreated']);
$xlsxContent = ExportHelper::excelContent($rows, $headers, ['dateCreated']);

return ExportHelper::toZip([
    ['name' => 'users.csv', 'content' => $csvContent],
    ['name' => 'users.xlsx', 'content' => $xlsxContent],
    ['name' => 'metadata.json', 'content' => json_encode($meta)],
], 'export-bundle.zip');
```

## Content-Only Methods

Use `csvContent()` / `excelContent()` (@since 5.25.0) and `jsonContent()` / `zipContent()` (@since 5.26.0) when you need raw file bytes instead of an HTTP `Response`. Typical use cases: queued background exports that write to disk, bundling into a ZIP, attaching to an email, storing in an asset volume.

```php
// Raw CSV bytes
$csv = ExportHelper::csvContent($rows, $headers, ['dateCreated']);

// Raw JSON bytes
$json = ExportHelper::jsonContent($rows, ['dateCreated']);

// Custom delimiter / enclosure (e.g. semicolon-separated for European locales)
$csv = ExportHelper::csvContent($rows, $headers, ['dateCreated'], ';', '"');

// Raw XLSX bytes
$xlsx = ExportHelper::excelContent($rows, $headers, ['dateCreated'], [
    'sheetTitle' => 'Users',
    'freezeHeader' => true,
    'autoFilter' => true,
]);

// Write to file storage
file_put_contents('/path/to/export.xlsx', $xlsx);

// Or attach to an email
$message->attachContent($xlsx, ['fileName' => 'users.xlsx', 'contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

// Raw ZIP bytes
$zip = ExportHelper::zipContent([
    'users.csv' => $csv,
    'users.xlsx' => $xlsx,
]);
```

`csvContent()` and `excelContent()` apply the same formula-injection sanitization, header styling, and date formatting as the `Response`-returning methods — `toCsv()` and `toExcel()` are thin wrappers around them. `zipContent()` normalizes ZIP member names while preserving safe subfolders. `toJson()` and `toZip()` likewise delegate to the content-only helpers.

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

## Date Column Formatting

Format date columns manually when you need the formatted data without sending a download response:

```php
// For CSV/Excel: formats to local time Y-m-d H:i:s
$rows = ExportHelper::formatDateColumns($rows, ['dateCreated', 'dateUpdated']);

// For JSON/API: formats to ISO 8601 with timezone offset
$rows = ExportHelper::formatDateColumnsForApi($rows, ['dateCreated']);
```

The `toCsv`, `toJson`, and `toExcel` methods call these internally when you pass `$dateColumns`, so you only need these for custom export logic.

## Security

ExportHelper automatically sanitizes cell values to prevent CSV/Excel formula injection. Values starting with `=`, `@`, `\t`, `\r`, or `\n` are prefixed with a single quote. Values starting with `+` or `-` are allowed only when they are numeric (e.g., phone numbers like `+1234567890`).

### Custom Writers @since(5.25.0)

If you build your own spreadsheet writer (multi-sheet workbook, custom styling, etc.) and can't route through `toExcel()` / `excelContent()`, use `isDangerousValue()` to apply the same guard inline:

```php
foreach ($row as $value) {
    if (ExportHelper::isDangerousValue($value)) {
        $sheet->setCellValueExplicit($cellRef, $value, DataType::TYPE_STRING);
    } else {
        $sheet->setCellValue($cellRef, $value);
    }
}
```

`TYPE_STRING` tells PhpSpreadsheet to treat the value as a literal string regardless of leading characters — no visible quote prefix, fully formula-safe.

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
    $extension = ExportHelper::extensionForFormat($format);
    $filename = ExportHelper::filename($settings, ['logs', $dateRange], $extension);

    return ExportHelper::dispatchTable(
        rows: $rows,
        headers: $headers,
        format: $format,
        filename: $filename,
        dateColumns: ['dateCreated'],
        excelOptions: ['sheetTitle' => 'Logs'],
    );
}
```

## Multi-Section Controller Pattern @since(5.26.0)

Use `dispatchSections()` when one export contains multiple logical tables. It returns a multi-sheet workbook for Excel, a ZIP of CSV files for CSV, and a structured JSON payload for JSON.

```php
public function actionExportAnalytics(): Response
{
    $format = Craft::$app->getRequest()->getRequiredBodyParam('format');
    ExportHelper::assertFormatEnabled($format, 'my-plugin');

    $sections = [
        [
            'key' => 'summary',
            'title' => 'Summary',
            'filename' => 'summary.csv',
            'headers' => ['Metric', 'Value'],
            'rows' => $summaryRows,
        ],
        [
            'key' => 'rawRows',
            'title' => 'Raw Rows',
            'filename' => 'raw-rows.csv',
            'headers' => ['Date', 'Event'],
            'rows' => $rawRows,
            'dateColumns' => ['dateCreated'],
        ],
    ];

    $settings = MyPlugin::$plugin->getSettings();
    $extension = $format === 'csv' ? 'zip' : ExportHelper::extensionForFormat($format);
    $filename = ExportHelper::filename($settings, ['analytics'], $extension);

    return ExportHelper::dispatchSections($sections, $format, $filename);
}
```

## Next Steps

- [Configuration](../get-started/configuration.md) — enabling/disabling export formats
- [Components](../template-guides/components.md) — using the export-menu component in templates
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — export-related Twig functions
