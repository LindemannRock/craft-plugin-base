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
        'json' => true,
        'excel' => true,
    ];

    /**
     * Get export configuration
     *
     * @return array
     * @since 5.8.0
     */
    public static function getConfig(): array
    {
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
     * @return bool
     * @since 5.8.0
     */
    public static function isFormatEnabled(string $format): bool
    {
        // Normalize format to config key
        $configKey = self::FORMAT_ALIASES[$format] ?? $format;
        $config = self::getConfig();

        return $config[$configKey] ?? self::DEFAULT_FORMATS[$configKey] ?? false;
    }

    /**
     * Get list of enabled export formats
     *
     * @return array ['csv', 'json', 'excel']
     * @since 5.8.0
     */
    public static function getEnabledFormats(): array
    {
        $config = self::getConfig();
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
        $sheet->setTitle(substr($sheetTitle, 0, 31)); // Excel limit is 31 chars

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
            return $pluginOrPrefix . '-' . DateTimeHelper::forFilename() . '.' . $partsOrExtension;
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

            return $baseName . '-' . DateTimeHelper::forFilename() . '.' . $extension;
        }

        // Fallback
        return 'export-' . DateTimeHelper::forFilename() . '.csv';
    }

    /**
     * Format date columns in rows for CSV/Excel (database format)
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
                    $row[$column] = DateTimeHelper::forDatabase($row[$column]);
                }
            }
        }

        return $rows;
    }

    /**
     * Format date columns in rows for JSON (ISO 8601 format)
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
                    $row[$column] = DateTimeHelper::forApi($row[$column]);
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
