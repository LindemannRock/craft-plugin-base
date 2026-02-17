<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\web\Response;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\BadRequestHttpException;

/**
 * Export Helper
 *
 * Provides centralized CSV, JSON, and Excel export functionality for all LindemannRock plugins.
 * Handles date formatting, response headers, and consistent file naming.
 *
 * Configuration via config/lindemannrock-base.php:
 * ```php
 * return [
 *     'exports' => [
 *         'csv' => true,
 *         'json' => true,
 *         'excel' => true,
 *     ],
 * ];
 * ```
 *
 * Usage:
 * ```php
 * use lindemannrock\base\helpers\ExportHelper;
 *
 * // Check enabled formats
 * ExportHelper::isFormatEnabled('excel');  // true/false
 * ExportHelper::getEnabledFormats();       // ['csv', 'json', 'excel']
 *
 * // CSV export
 * return ExportHelper::toCsv($rows, $headers, 'my-export.csv', ['dateCreated']);
 *
 * // JSON export
 * return ExportHelper::toJson($rows, 'my-export.json', ['dateCreated']);
 *
 * // Excel export
 * return ExportHelper::toExcel($rows, $headers, 'my-export.xlsx', ['dateCreated']);
 *
 * // Generate filename with timestamp
 * $filename = ExportHelper::filename('sms-logs', 'xlsx'); // "sms-logs-2026-01-24-153045.xlsx"
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class ExportHelper
{
    /**
     * Default export formats configuration
     */
    private const DEFAULT_FORMATS = [
        'csv' => true,
        'json' => false,  // Developer format - disabled by default
        'excel' => true,
    ];

    /**
     * Get export configuration
     *
     * Checks plugin-specific config first, then falls back to lindemannrock-base.php.
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return array
     * @since 5.8.0
     */
    public static function getConfig(?string $pluginHandle = null): array
    {
        // Check plugin-specific config first
        if ($pluginHandle) {
            $pluginConfig = Craft::$app->config->getConfigFromFile($pluginHandle) ?: [];
            if (isset($pluginConfig['exports'])) {
                // Merge with defaults (plugin config overrides base)
                $baseConfig = Craft::$app->config->getConfigFromFile('lindemannrock-base') ?: [];
                $baseExports = $baseConfig['exports'] ?? self::DEFAULT_FORMATS;
                return array_merge($baseExports, $pluginConfig['exports']);
            }
        }

        // Fall back to base config
        $config = Craft::$app->config->getConfigFromFile('lindemannrock-base') ?: [];

        return $config['exports'] ?? self::DEFAULT_FORMATS;
    }

    /**
     * Format aliases mapping URL params to config keys
     */
    private const FORMAT_ALIASES = [
        'xlsx' => 'excel',
        'xls' => 'excel',
    ];

    /**
     * Check if an export format is enabled
     *
     * Accepts both config keys ('excel', 'csv', 'json') and common aliases ('xlsx', 'xls').
     *
     * @param string $format 'csv', 'json', 'excel', 'xlsx', or 'xls'
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return bool
     * @since 5.8.0
     */
    public static function isFormatEnabled(string $format, ?string $pluginHandle = null): bool
    {
        // Normalize format to config key
        $configKey = self::FORMAT_ALIASES[$format] ?? $format;
        $config = self::getConfig($pluginHandle);

        return $config[$configKey] ?? self::DEFAULT_FORMATS[$configKey] ?? false;
    }

    /**
     * Get list of enabled export formats
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return array ['csv', 'json', 'excel']
     * @since 5.8.0
     */
    public static function getEnabledFormats(?string $pluginHandle = null): array
    {
        $config = self::getConfig($pluginHandle);
        $enabled = [];

        foreach (self::DEFAULT_FORMATS as $format => $default) {
            if ($config[$format] ?? $default) {
                $enabled[] = $format;
            }
        }

        return $enabled;
    }

    /**
     * Get export format options for select fields
     *
     * Returns options in the format expected by Craft form select fields.
     * Only enabled formats are returned. Order: Excel → CSV → JSON.
     *
     * Usage:
     * ```php
     * {{ forms.selectField({
     *     label: 'Export Format',
     *     name: 'format',
     *     options: ExportHelper::getFormatOptions(),
     * }) }}
     * ```
     *
     * @return array Array of options with 'value' and 'label' keys
     * @since 5.8.0
     */
    public static function getFormatOptions(): array
    {
        $allFormats = [
            'xlsx' => 'Excel (.xlsx)',
            'csv' => 'CSV (.csv)',
            'json' => 'JSON (.json)',
        ];

        $formatMapping = [
            'xlsx' => 'excel',
            'csv' => 'csv',
            'json' => 'json',
        ];

        $options = [];

        foreach ($allFormats as $value => $label) {
            $configKey = $formatMapping[$value];
            if (self::isFormatEnabled($configKey)) {
                $options[] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }
        }

        return $options;
    }

    /**
     * Assert that data is not empty before exporting
     *
     * @param array $rows Data rows to check
     * @param string|null $message Custom error message
     * @throws BadRequestHttpException If rows are empty
     * @since 5.8.0
     */
    public static function assertNotEmpty(array $rows, ?string $message = null): void
    {
        if (empty($rows)) {
            throw new BadRequestHttpException($message ?? Craft::t('app', 'Nothing to export.'));
        }
    }

    /**
     * Export data as CSV
     *
     * @param array $rows Data rows to export
     * @param array $headers Column headers
     * @param string $filename Output filename
     * @param array $dateColumns Column keys to format as database datetime
     * @return Response
     * @since 5.8.0
     */
    public static function toCsv(
        array $rows,
        array $headers,
        string $filename,
        array $dateColumns = [],
    ): Response {
        $csv = self::csvContent($rows, $headers, $dateColumns);

        return self::createResponse($csv, $filename, 'text/csv');
    }

    /**
     * Export data as JSON
     *
     * @param array $data Data to export
     * @param string $filename Output filename
     * @param array $dateColumns Column keys to format as ISO 8601
     * @param bool $pretty Pretty print JSON
     * @return Response
     * @since 5.8.0
     */
    public static function toJson(
        array $data,
        string $filename,
        array $dateColumns = [],
        bool $pretty = true,
    ): Response {
        // Format date columns if specified (use API format for JSON)
        if (!empty($dateColumns)) {
            $data = self::formatDateColumnsForApi($data, $dateColumns);
        }

        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : JSON_UNESCAPED_UNICODE;
        $json = json_encode($data, $flags | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            throw new BadRequestHttpException('Failed to encode data as JSON: ' . json_last_error_msg());
        }

        return self::createResponse($json, $filename, 'application/json');
    }

    /**
     * Export data as Excel (.xlsx)
     *
     * @param array $rows Data rows to export
     * @param array $headers Column headers
     * @param string $filename Output filename
     * @param array $dateColumns Column keys to format as database datetime
     * @param array $options Additional options (sheetTitle, freezeHeader, autoFilter, columnWidths)
     * @return Response
     * @since 5.8.0
     */
    public static function toExcel(
        array $rows,
        array $headers,
        string $filename,
        array $dateColumns = [],
        array $options = [],
    ): Response {
        // Format date columns if specified
        if (!empty($dateColumns)) {
            $rows = self::formatDateColumns($rows, $dateColumns);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set sheet title
        $sheetTitle = $options['sheetTitle'] ?? 'Export';
        $sheet->setTitle(self::sanitizeSheetTitle($sheetTitle));
        self::writeSheet($sheet, $rows, $headers, $options);

        // Write to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return self::createResponse(
            $content,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Export multiple sheets as an Excel workbook
     *
     * Each sheet should include:
     * - title (string, optional)
     * - headers (array)
     * - rows (array)
     * - dateColumns (array, optional)
     * - options (array, optional)
     *
     * @param array $sheets Sheet definitions
     * @param string $filename Output filename
     * @return Response
     * @since 5.13.1
     */
    public static function toExcelMulti(array $sheets, string $filename): Response
    {
        if (empty($sheets)) {
            throw new BadRequestHttpException('No sheets to export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        foreach ($sheets as $sheetConfig) {
            if (!is_array($sheetConfig)) {
                continue;
            }

            $headers = $sheetConfig['headers'] ?? [];
            $rows = $sheetConfig['rows'] ?? [];
            $dateColumns = $sheetConfig['dateColumns'] ?? [];
            $options = $sheetConfig['options'] ?? [];
            $title = $sheetConfig['title'] ?? ($options['sheetTitle'] ?? ('Sheet ' . ($sheetIndex + 1)));

            if (!empty($dateColumns)) {
                $rows = self::formatDateColumns($rows, $dateColumns);
            }

            $sheet = $sheetIndex === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle(self::sanitizeSheetTitle((string)$title));
            self::writeSheet($sheet, $rows, $headers, $options);

            $sheetIndex++;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return self::createResponse(
            $content,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Build CSV content from rows and headers
     *
     * @param array $rows Data rows to export
     * @param array $headers Column headers
     * @param array $dateColumns Column keys to format as database datetime
     * @return string CSV string
     * @since 5.13.1
     */
    public static function csvContent(
        array $rows,
        array $headers,
        array $dateColumns = [],
    ): string {
        // Format date columns if specified
        if (!empty($dateColumns)) {
            $rows = self::formatDateColumns($rows, $dateColumns);
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, self::sanitizeRow(array_values($row)));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export multiple files as a ZIP archive
     *
     * Each file can be provided as:
     * - ['name' => 'file.csv', 'content' => '...']
     * - 'file.csv' => '...'
     *
     * @param array $files Files to include in the ZIP
     * @param string $filename Output filename (.zip)
     * @return Response
     * @since 5.13.1
     */
    public static function toZip(array $files, string $filename): Response
    {
        if (!str_ends_with($filename, '.zip')) {
            $filename .= '.zip';
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'zip_export_');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($files as $key => $file) {
            $name = null;
            $content = '';

            if (is_array($file)) {
                $name = $file['name'] ?? null;
                $content = (string)($file['content'] ?? '');
            } elseif (is_string($key)) {
                $name = $key;
                $content = (string)$file;
            }

            if ($name) {
                $zip->addFromString($name, $content);
            }
        }

        $zip->close();

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return self::createResponse($content, $filename, 'application/zip');
    }

    /**
     * Write headers and rows to a worksheet
     *
     * @param Worksheet $sheet Sheet instance
     * @param array $rows Data rows
     * @param array $headers Column headers
     * @param array $options Sheet options
     */
    private static function writeSheet(Worksheet $sheet, array $rows, array $headers, array $options = []): void
    {
        if (empty($headers)) {
            return;
        }

        // Write headers
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . '1', $header);
            $colIndex++;
        }

        // Style headers
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:' . $lastCol . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A5568'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Write data rows (sanitized to prevent formula injection)
        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach (self::sanitizeRow(array_values($row)) as $value) {
                $cellRef = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $sheet->setCellValue($cellRef, $value);
                $colIndex++;
            }
            $rowIndex++;
        }

        // Auto-size columns or use custom widths
        if (isset($options['columnWidths']) && is_array($options['columnWidths'])) {
            foreach ($options['columnWidths'] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
        } else {
            // Auto-size all columns
            for ($i = 1; $i <= count($headers); $i++) {
                $col = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Freeze header row
        if ($options['freezeHeader'] ?? true) {
            $sheet->freezePane('A2');
        }

        // Add auto filter
        if ($options['autoFilter'] ?? true) {
            $lastRow = $rowIndex - 1;
            $sheet->setAutoFilter('A1:' . $lastCol . $lastRow);
        }

        // Add borders to data area
        $dataRange = 'A1:' . $lastCol . ($rowIndex - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Alternate row colors for readability
        for ($r = 2; $r < $rowIndex; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle('A' . $r . ':' . $lastCol . $r)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F7FAFC'],
                    ],
                ]);
            }
        }
    }

    /**
     * Sanitize a sheet title for Excel
     */
    private static function sanitizeSheetTitle(string $title): string
    {
        $title = trim($title);
        $title = str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $title);
        $title = preg_replace('/\s+/', ' ', $title);

        $title = trim($title, " \t\n\r\0\x0B-'");

        if ($title === '') {
            $title = 'Sheet';
        }

        return substr($title, 0, 31);
    }

    /**
     * Generate an export filename
     *
     * Supports three usage patterns:
     *
     * 1. Standard pattern with settings (recommended):
     *    ```php
     *    $settings = MyPlugin::$plugin->getSettings();
     *    ExportHelper::filename($settings, ['logs', $dateRange], 'xlsx');
     *    // → "my-plugin-logs-last30days-2026-01-24-153045.xlsx"
     *    ```
     *
     * 2. Simple with timestamp:
     *    ```php
     *    ExportHelper::filename('my-export', 'csv');
     *    // → "my-export-2026-01-24-153045.csv"
     *    ```
     *
     * 3. Exact name (no modification):
     *    ```php
     *    ExportHelper::filename('exact-name.csv');
     *    // → "exact-name.csv"
     *    ```
     *
     * @param object|string $pluginOrPrefix Settings object, plugin name, or exact filename
     * @param array|string|null $partsOrExtension Array of parts or extension string
     * @param string|null $extension File extension (when using parts array)
     * @return string Generated filename
     * @since 5.8.0
     */
    public static function filename(
        object|string $pluginOrPrefix,
        array|string|null $partsOrExtension = null,
        ?string $extension = null,
    ): string {
        // Pattern 3: Exact name (single string with no extension param)
        if (is_string($pluginOrPrefix) && $partsOrExtension === null) {
            return $pluginOrPrefix;
        }

        // Pattern 2: Simple prefix + extension
        if (is_string($pluginOrPrefix) && is_string($partsOrExtension) && $extension === null) {
            return $pluginOrPrefix . '-' . DateFormatHelper::forFilename() . '.' . $partsOrExtension;
        }

        // Pattern 1: Settings/string + parts array + extension
        if (is_array($partsOrExtension) && $extension !== null) {
            // Get plugin name from settings or use string directly
            if (is_object($pluginOrPrefix) && method_exists($pluginOrPrefix, 'getLowerDisplayName')) {
                $pluginName = strtolower(str_replace(' ', '-', $pluginOrPrefix->getLowerDisplayName()));
            } elseif (is_string($pluginOrPrefix)) {
                $pluginName = $pluginOrPrefix;
            } else {
                $pluginName = 'export';
            }

            // Filter out empty/null parts and join
            $allParts = array_filter([$pluginName, ...$partsOrExtension], fn($p) => $p !== null && $p !== '');
            $baseName = implode('-', $allParts);

            return $baseName . '-' . DateFormatHelper::forFilename() . '.' . $extension;
        }

        // Fallback
        return 'export-' . DateFormatHelper::forFilename() . '.csv';
    }

    /**
     * Format date columns in rows for CSV/Excel export.
     *
     * Converts UTC database dates to the Craft site timezone so exported
     * data matches what users see in the Control Panel.
     *
     * @param array $rows Data rows
     * @param array $dateColumns Column keys containing dates
     * @return array Rows with formatted dates
     * @since 5.8.0
     */
    public static function formatDateColumns(array $rows, array $dateColumns): array
    {
        foreach ($rows as &$row) {
            foreach ($dateColumns as $column) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $localDate = DateFormatHelper::toCraftTimezone($row[$column]);
                    $row[$column] = $localDate !== null
                        ? DateFormatHelper::forDatabase($localDate)
                        : $row[$column];
                }
            }
        }

        return $rows;
    }

    /**
     * Format date columns in rows for JSON export (ISO 8601).
     *
     * Converts UTC database dates to the Craft site timezone so exported
     * data includes the correct local offset.
     *
     * @param array $rows Data rows
     * @param array $dateColumns Column keys containing dates
     * @return array Rows with formatted dates
     * @since 5.8.0
     */
    public static function formatDateColumnsForApi(array $rows, array $dateColumns): array
    {
        foreach ($rows as &$row) {
            foreach ($dateColumns as $column) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $localDate = DateFormatHelper::toCraftTimezone($row[$column]);
                    $row[$column] = $localDate !== null
                        ? DateFormatHelper::forApi($localDate)
                        : $row[$column];
                }
            }
        }

        return $rows;
    }

    /**
     * Sanitize a cell value to prevent formula injection
     *
     * Spreadsheet applications treat values starting with =, +, -, @, tab, or carriage return
     * as formulas, which can be exploited for data exfiltration or code execution.
     * This method prefixes such values with a single quote to prevent interpretation.
     *
     * @param mixed $value The cell value to sanitize
     * @return mixed The sanitized value
     * @since 5.9.0
     */
    private static function sanitizeCellValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        // Always dangerous - block regardless of what follows
        $alwaysDangerous = ['=', '@', "\t", "\r", "\n"];

        // Check first character for always-dangerous chars
        if (in_array($value[0], $alwaysDangerous, true)) {
            return "'" . $value;
        }

        // Check first non-whitespace for always-dangerous chars
        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], $alwaysDangerous, true)) {
            return "'" . $value;
        }

        // Allow +/- only when followed by numeric pattern (phone numbers, negative numbers)
        // Pattern: optional +/-, digits, optional decimal part with . or ,
        if (preg_match('/^[+-]?\d+([.,]\d+)?$/', $trimmed)) {
            return $value;
        }

        // Block +/- when NOT numeric (could be formula like +A1 or -A1)
        if ($trimmed !== '' && in_array($trimmed[0], ['+', '-'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitize all values in a row for spreadsheet export
     *
     * @param array $row The row data
     * @return array The sanitized row
     * @since 5.9.0
     */
    private static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitizeCellValue'], $row);
    }

    /**
     * Create a download response
     *
     * @param string $content File content
     * @param string $filename Output filename
     * @param string $contentType MIME type
     * @return Response
     */
    private static function createResponse(string $content, string $filename, string $contentType): Response
    {
        // Sanitize filename to prevent header injection
        // Remove path traversal, control characters, and problematic characters
        $filename = basename($filename);
        $filename = preg_replace('/[\x00-\x1f\x7f"\\\\]/', '', $filename);
        $filename = $filename ?: 'export';

        $response = Craft::$app->getResponse();
        $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->content = $content;

        return $response;
    }
}
