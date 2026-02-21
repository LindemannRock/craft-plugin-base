# CsvImportHelper

Parses CSV file uploads with automatic delimiter detection, file validation, and row limits. Handles UTF-8 BOM stripping and common MIME type variations.

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `DEFAULT_MAX_ROWS` | `4000` | Default row limit for imports |
| `DEFAULT_MAX_BYTES` | `5242880` | Default file size limit (5 MB) |

## Basic Usage

```php
use lindemannrock\base\helpers\CsvImportHelper;
use craft\web\UploadedFile;

$file = UploadedFile::getInstanceByName('csvFile');

try {
    $result = CsvImportHelper::parseUpload($file);

    $headers = $result['headers'];     // ['Name', 'Email', 'Phone']
    $rows = $result['allRows'];        // [[...], [...], ...]
    $count = $result['rowCount'];      // 150
    $delimiter = $result['delimiter']; // ','
} catch (\RuntimeException $e) {
    Craft::$app->getSession()->setError($e->getMessage());
    return $this->redirect(Craft::$app->getRequest()->getReferrer());
}
```

## Options

Customize validation and parsing behavior:

```php
$result = CsvImportHelper::parseUpload($file, [
    'maxRows' => 4000,                      // Default: 4000
    'maxBytes' => 5242880,                   // Default: 5 MB
    'allowedExtensions' => ['csv', 'txt'],   // Default: ['csv', 'txt']
    'delimiter' => ';',                      // Force delimiter (default: auto-detect)
    'detectDelimiter' => true,               // Auto-detect delimiter (default: true)
]);
```

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `maxRows` | `int` | `4000` | Maximum rows to parse |
| `maxBytes` | `int` | `5242880` | Maximum file size in bytes (5 MB) |
| `allowedExtensions` | `array` | `['csv', 'txt']` | Allowed file extensions |
| `allowedMimeTypes` | `array` | (many CSV types) | Allowed MIME types |
| `delimiter` | `string\|null` | `null` | Force a specific delimiter |
| `detectDelimiter` | `bool` | `true` | Auto-detect delimiter from first line |

## Delimiter Detection

When `detectDelimiter` is enabled (default), the helper reads the first line and counts occurrences of `,`, `;`, `\t`, and `|`. The character with the most occurrences wins.

If you know the delimiter in advance, pass it directly:

```php
$result = CsvImportHelper::parseUpload($file, ['delimiter' => ';']);
```

## Error Handling

`parseUpload()` throws `\RuntimeException` with user-friendly messages:

| Error | Message |
|-------|---------|
| Wrong extension | "Invalid file type. Please upload a CSV file." |
| Too large | "File size exceeds the allowed limit of 5.0MB." |
| Wrong MIME type | "Invalid file type. Please upload a CSV file." |
| Too many rows | "CSV file is too large. Maximum 4000 rows allowed. Please split your file into smaller batches." |
| Empty file | "CSV file is empty or contains only headers." |

Single-column CSVs are valid. A file with only one column (e.g., a list of email addresses or phone numbers) parses without error.

## Typical Controller Pattern

```php
public function actionImport(): Response
{
    $this->requirePostRequest();
    $this->requirePermission('myPlugin:importData');

    $file = UploadedFile::getInstanceByName('csvFile');
    if (!$file) {
        Craft::$app->getSession()->setError('Please select a CSV file.');
        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }

    try {
        $result = CsvImportHelper::parseUpload($file, [
            'maxRows' => 2000,
        ]);
    } catch (\RuntimeException $e) {
        Craft::$app->getSession()->setError($e->getMessage());
        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }

    // Process the parsed data
    $imported = MyPlugin::$plugin->getImportService()->processRows(
        $result['headers'],
        $result['allRows']
    );

    Craft::$app->getSession()->setNotice("Imported {$imported} records.");
    return $this->redirectToPostedUrl();
}
```

## Stripping Formula-Escape Prefixes @since(5.16.0)

When data is exported via `ExportHelper`, cell values that start with formula characters (`=`, `+`, `-`, `@`) are prefixed with a leading apostrophe (`'`) to prevent spreadsheet formula injection. If the same data is later re-imported, those apostrophes must be removed to restore the original values.

Use `stripFormulaEscapePrefix()` to reverse this escaping on a per-value basis:

```php
use lindemannrock\base\helpers\CsvImportHelper;

// After parsing the upload, clean values that may have been exported by ExportHelper
foreach ($result['allRows'] as &$row) {
    foreach ($row as &$value) {
        $value = CsvImportHelper::stripFormulaEscapePrefix($value);
    }
}
```

The method only strips the apostrophe when it is followed (with optional whitespace) by a formula character. Ordinary apostrophes in data (e.g., `O'Brien`) are left untouched:

| Input | Output | Reason |
|-------|--------|--------|
| `'=SUM(A1)` | `=SUM(A1)` | Formula escape stripped |
| `'+1234` | `+1234` | Formula escape stripped |
| `'  =1` | `  =1` | Whitespace preserved, escape stripped |
| `'test` | `'test` | `t` is not a formula char — no change |
| `O'Brien` | `O'Brien` | Apostrophe is mid-string — no change |

This pairs naturally with `ExportHelper::toCsv()` to create a safe round-trip for data that contains formulas or values starting with special characters.

## Next Steps

- [ExportHelper](export-helper.md) — exporting data as CSV, JSON, and Excel
