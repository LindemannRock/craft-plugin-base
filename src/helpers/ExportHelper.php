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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
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
     * Per-handle resolved-config cache. Keyed by plugin handle, or
     * `__base__` when no handle is passed. Tests reset via
     * {@see clearConfigCache()}.
     *
     * @var array<string, array<string, bool>>
     */
    private static array $configCache = [];

    /**
     * Reset the resolved-config cache.
     *
     * Production callers don't need this — the cache lives for one request.
     * Tests use it to re-read the on-disk config between cases.
     *
     * @since 5.25.0
     */
    public static function clearConfigCache(): void
    {
        self::$configCache = [];
    }

    /**
     * Get export configuration
     *
     * Resolves the per-format enable map by layering (low → high priority).
     * Matches the cascade order used by `DateFormatHelper::getConfig()` —
     * plugin config file is the highest authority because it's env-controlled:
     *
     *   1. DEFAULT_FORMATS (hardcoded fallback)
     *   2. Base config:    'exports' hash in config/lindemannrock-base.php
     *   3. Plugin Settings model: $settings->exportsCsv / exportsJson /
     *      exportsExcel — when the plugin uses
     *      `lindemannrock\base\traits\ExportFormatSettingsTrait` and the
     *      property is non-null. Skipped silently when the trait isn't in use.
     *   4. Plugin config:  'exports' hash in config/{handle}.php — when this
     *      file sets a key, the CP form for that property shows the
     *      "overridden by config" warning via `isOverriddenByConfig('exports.X')`
     *      and the field is disabled.
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return array
     */
    public static function getConfig(?string $pluginHandle = null): array
    {
        $cacheKey = $pluginHandle ?? '__base__';
        if (isset(self::$configCache[$cacheKey])) {
            return self::$configCache[$cacheKey];
        }

        $baseConfigValues = [];
        $settingsValues = [];
        $pluginConfigValues = [];

        $baseConfig = Craft::$app->config->getConfigFromFile('lindemannrock-base') ?: [];
        if (isset($baseConfig['exports']) && is_array($baseConfig['exports'])) {
            $baseConfigValues = $baseConfig['exports'];
        }

        if ($pluginHandle) {
            $plugin = Craft::$app->plugins->getPlugin($pluginHandle);
            if ($plugin !== null) {
                $settings = $plugin->getSettings();
                if ($settings !== null) {
                    if (property_exists($settings, 'exportsCsv') && $settings->exportsCsv !== null) {
                        $settingsValues['csv'] = (bool) $settings->exportsCsv;
                    }
                    if (property_exists($settings, 'exportsJson') && $settings->exportsJson !== null) {
                        $settingsValues['json'] = (bool) $settings->exportsJson;
                    }
                    if (property_exists($settings, 'exportsExcel') && $settings->exportsExcel !== null) {
                        $settingsValues['excel'] = (bool) $settings->exportsExcel;
                    }
                }
            }

            $pluginConfig = Craft::$app->config->getConfigFromFile($pluginHandle) ?: [];
            if (isset($pluginConfig['exports']) && is_array($pluginConfig['exports'])) {
                $pluginConfigValues = $pluginConfig['exports'];
            }
        }

        $result = self::mergeFormatConfig(self::DEFAULT_FORMATS, $baseConfigValues, $settingsValues, $pluginConfigValues);

        self::$configCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Merge export format configuration layers.
     *
     * Priority, low → high:
     * 1. hardcoded defaults
     * 2. base config
     * 3. plugin Settings model values
     * 4. plugin config
     *
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $baseConfig
     * @param array<string, mixed> $settingsValues
     * @param array<string, mixed> $pluginConfig
     * @return array<string, bool>
     * @since 5.26.0
     */
    public static function mergeFormatConfig(
        array $defaults,
        array $baseConfig = [],
        array $settingsValues = [],
        array $pluginConfig = [],
    ): array {
        $result = self::DEFAULT_FORMATS;

        foreach ([$defaults, $baseConfig, $settingsValues, $pluginConfig] as $layer) {
            foreach (self::DEFAULT_FORMATS as $format => $default) {
                if (array_key_exists($format, $layer) && $layer[$format] !== null) {
                    $result[$format] = (bool) $layer[$format];
                }
            }
        }

        return $result;
    }

    /**
     * Format aliases mapping URL params to config keys
     */
    private const FORMAT_ALIASES = [
        'xlsx' => 'excel',
        'xls' => 'excel',
    ];

    /**
     * Normalize export format aliases to the canonical format key.
     *
     * @param string $format Export format from request/config
     * @return string Canonical format key (`csv`, `json`, or `excel` when supported)
     * @since 5.26.0
     */
    public static function normalizeFormat(string $format): string
    {
        $format = strtolower(trim($format));

        return self::FORMAT_ALIASES[$format] ?? $format;
    }

    /**
     * Get the filename extension for an export format.
     *
     * @param string $format Export format from request/config
     * @return string Filename extension
     * @throws BadRequestHttpException If the format is unsupported
     * @since 5.26.0
     */
    public static function extensionForFormat(string $format): string
    {
        return match (self::normalizeFormat($format)) {
            'csv' => 'csv',
            'json' => 'json',
            'excel' => 'xlsx',
            default => throw new BadRequestHttpException("Unknown export format: {$format}"),
        };
    }

    /**
     * Check if an export format is enabled
     *
     * Accepts both config keys ('excel', 'csv', 'json') and common aliases ('xlsx', 'xls').
     *
     * @param string $format 'csv', 'json', 'excel', 'xlsx', or 'xls'
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return bool
     */
    public static function isFormatEnabled(string $format, ?string $pluginHandle = null): bool
    {
        $configKey = self::normalizeFormat($format);
        $config = self::getConfig($pluginHandle);

        return $config[$configKey] ?? self::DEFAULT_FORMATS[$configKey] ?? false;
    }

    /**
     * Assert that a format is enabled for an optional plugin context.
     *
     * @param string $format Export format from request/config
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @throws BadRequestHttpException If the format is disabled
     * @since 5.26.0
     */
    public static function assertFormatEnabled(string $format, ?string $pluginHandle = null): void
    {
        if (!self::isFormatEnabled($format, $pluginHandle)) {
            throw new BadRequestHttpException("Export format '{$format}' is not enabled.");
        }
    }

    /**
     * Get list of enabled export formats
     *
     * @param string|null $pluginHandle Optional plugin handle to check for override
     * @return array ['csv', 'json', 'excel']
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
     */
    public static function assertNotEmpty(array $rows, ?string $message = null): void
    {
        if (empty($rows)) {
            throw new BadRequestHttpException($message ?? Craft::t('lindemannrock-base', 'Nothing to export.'));
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
     */
    public static function toJson(
        array $data,
        string $filename,
        array $dateColumns = [],
        bool $pretty = true,
    ): Response {
        $json = self::jsonContent($data, $dateColumns, $pretty);

        return self::createResponse($json, $filename, 'application/json');
    }

    /**
     * Build JSON content.
     *
     * @param array $data Data to export
     * @param array $dateColumns Column keys to format as ISO 8601
     * @param bool $pretty Pretty print JSON
     * @return string JSON bytes
     * @throws BadRequestHttpException If JSON encoding fails
     * @since 5.26.0
     */
    public static function jsonContent(
        array $data,
        array $dateColumns = [],
        bool $pretty = true,
    ): string {
        // Format date columns if specified (use API format for JSON)
        if (!empty($dateColumns)) {
            $data = self::formatDateColumnsForApi($data, $dateColumns);
        }

        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : JSON_UNESCAPED_UNICODE;
        $json = json_encode($data, $flags | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            throw new BadRequestHttpException('Failed to encode data as JSON: ' . json_last_error_msg());
        }

        return $json;
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
     */
    public static function toExcel(
        array $rows,
        array $headers,
        string $filename,
        array $dateColumns = [],
        array $options = [],
    ): Response {
        $content = self::excelContent($rows, $headers, $dateColumns, $options);

        return self::createResponse(
            $content,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Build Excel (.xlsx) content from rows and headers
     *
     * Use this when you need the raw spreadsheet bytes (writing to file storage,
     * bundling into a ZIP, attaching to an email). For an HTTP download, use {@see toExcel()}.
     *
     * @param array $rows Data rows to export
     * @param array $headers Column headers
     * @param array $dateColumns Column keys to format as database datetime
     * @param array $options Sheet options (sheetTitle, freezeHeader, autoFilter, columnWidths)
     * @return string Excel (.xlsx) file bytes
     * @since 5.25.0
     */
    public static function excelContent(
        array $rows,
        array $headers,
        array $dateColumns = [],
        array $options = [],
    ): string {
        if (!empty($dateColumns)) {
            $rows = self::formatDateColumns($rows, $dateColumns);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetTitle = $options['sheetTitle'] ?? 'Export';
        $sheet->setTitle(self::sanitizeSheetTitle($sheetTitle));
        self::writeSheet($sheet, $rows, $headers, $options);

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($content === false) {
            throw new \yii\web\BadRequestHttpException('Failed to read generated Excel file.');
        }

        return $content;
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

        if ($content === false) {
            $spreadsheet->disconnectWorksheets();
            throw new \yii\web\BadRequestHttpException('Failed to read generated Excel file.');
        }

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
     * @param string $delimiter CSV field delimiter @since 5.25.0
     * @param string $enclosure CSV field enclosure character @since 5.25.0
     * @return string CSV string
     * @since 5.13.1
     */
    public static function csvContent(
        array $rows,
        array $headers,
        array $dateColumns = [],
        string $delimiter = ',',
        string $enclosure = '"',
    ): string {
        // Format date columns if specified
        if (!empty($dateColumns)) {
            $rows = self::formatDateColumns($rows, $dateColumns);
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers, $delimiter, $enclosure);

        foreach ($rows as $row) {
            fputcsv($output, self::sanitizeRow(array_values($row)), $delimiter, $enclosure);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Normalize section definitions for multi-sheet Excel export.
     *
     * @param array $sections Section definitions
     * @return array Excel sheet definitions
     */
    private static function normalizeSectionsForExcel(array $sections): array
    {
        $sheets = [];

        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = (string)($section['title'] ?? $section['key'] ?? ('Section ' . ($index + 1)));
            $options = $section['options'] ?? [];
            if (!is_array($options)) {
                $options = [];
            }

            $sheets[] = [
                'title' => $title,
                'headers' => self::normalizeStringList($section['headers'] ?? []),
                'rows' => self::normalizeRows($section['rows'] ?? []),
                'dateColumns' => self::normalizeStringList($section['dateColumns'] ?? []),
                'options' => $options,
            ];
        }

        return $sheets;
    }

    /**
     * Build CSV files for section ZIP export.
     *
     * @param array $sections Section definitions
     * @return array<string, string>
     */
    private static function buildSectionCsvFiles(array $sections): array
    {
        $files = [];

        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = (string)($section['title'] ?? $section['key'] ?? ('section-' . ($index + 1)));
            $filename = (string)($section['filename'] ?? self::sectionFilename($title, $index));
            if (!str_ends_with($filename, '.csv')) {
                $filename .= '.csv';
            }

            $files[$filename] = self::csvContent(
                self::normalizeRows($section['rows'] ?? []),
                self::normalizeStringList($section['headers'] ?? []),
                self::normalizeStringList($section['dateColumns'] ?? []),
            );
        }

        return $files;
    }

    /**
     * Build default JSON payload for section exports.
     *
     * @param array $sections Section definitions
     * @return array<string, array{title: string, headers: array<int, string>, rows: array}>
     */
    private static function buildSectionJsonPayload(array $sections): array
    {
        $payload = [];

        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = (string)($section['title'] ?? $section['key'] ?? ('Section ' . ($index + 1)));
            $key = (string)($section['key'] ?? self::sectionKey($title, $index));

            $payload[$key] = [
                'title' => $title,
                'headers' => self::normalizeStringList($section['headers'] ?? []),
                'rows' => self::normalizeRows($section['rows'] ?? []),
            ];
        }

        return $payload;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_map(static fn(mixed $item): string => (string)$item, $value));
    }

    /**
     * @param mixed $value
     * @return array
     */
    private static function normalizeRows(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private static function sectionFilename(string $title, int $index): string
    {
        $filename = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($title));
        $filename = trim((string)$filename, '-_');

        return ($filename !== '' ? $filename : 'section-' . ($index + 1)) . '.csv';
    }

    private static function sectionKey(string $title, int $index): string
    {
        $key = preg_replace('/[^a-z0-9]+/i', ' ', $title);
        $key = str_replace(' ', '', ucwords(strtolower(trim((string)$key))));

        if ($key === '') {
            return 'section' . ($index + 1);
        }

        return lcfirst($key);
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

        $content = self::zipContent($files);

        return self::createResponse($content, $filename, 'application/zip');
    }

    /**
     * Build ZIP archive content from named files.
     *
     * Each file can be provided as:
     * - ['name' => 'file.csv', 'content' => '...']
     * - 'file.csv' => '...'
     *
     * @param array $files Files to include in the ZIP
     * @return string ZIP file bytes
     * @throws BadRequestHttpException If ZIP creation fails
     * @since 5.26.0
     */
    public static function zipContent(array $files): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new BadRequestHttpException('The PHP Zip extension is required to create ZIP exports.');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'zip_export_');
        if ($tempFile === false) {
            throw new BadRequestHttpException('Failed to create temporary ZIP file.');
        }

        $zip = new \ZipArchive();
        $opened = $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($opened !== true) {
            @unlink($tempFile);
            throw new BadRequestHttpException('Failed to open temporary ZIP file.');
        }

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

        if ($content === false) {
            throw new \yii\web\BadRequestHttpException('Failed to read generated ZIP file.');
        }

        return $content;
    }

    /**
     * Dispatch a single-table export response.
     *
     * @param array $rows Data rows
     * @param array $headers Column headers
     * @param string $format Export format
     * @param string $filename Output filename
     * @param array $dateColumns Date column keys
     * @param array $excelOptions Excel sheet options
     * @param array|null $jsonData Optional JSON-specific payload
     * @return Response
     * @throws BadRequestHttpException If the format is unsupported
     * @since 5.26.0
     */
    public static function dispatchTable(
        array $rows,
        array $headers,
        string $format,
        string $filename,
        array $dateColumns = [],
        array $excelOptions = [],
        ?array $jsonData = null,
    ): Response {
        return match (self::normalizeFormat($format)) {
            'csv' => self::toCsv($rows, $headers, $filename, $dateColumns),
            'json' => self::toJson($jsonData ?? $rows, $filename, $dateColumns),
            'excel' => self::toExcel($rows, $headers, $filename, $dateColumns, $excelOptions),
            default => throw new BadRequestHttpException("Unknown export format: {$format}"),
        };
    }

    /**
     * Dispatch a multi-section export response.
     *
     * Section shape:
     * - title: sheet/section title
     * - headers: column headers
     * - rows: data rows
     * - dateColumns: optional date column keys
     * - options: optional Excel options
     * - filename: optional CSV filename inside ZIP
     * - key: optional JSON section key
     *
     * @param array $sections Section definitions
     * @param string $format Export format
     * @param string $filename Output filename
     * @param array|null $jsonPayload Optional JSON-specific payload
     * @return Response
     * @throws BadRequestHttpException If the format is unsupported
     * @since 5.26.0
     */
    public static function dispatchSections(
        array $sections,
        string $format,
        string $filename,
        ?array $jsonPayload = null,
    ): Response {
        return match (self::normalizeFormat($format)) {
            'excel' => self::toExcelMulti(self::normalizeSectionsForExcel($sections), $filename),
            'csv' => self::toZip(self::buildSectionCsvFiles($sections), $filename),
            'json' => self::toJson($jsonPayload ?? self::buildSectionJsonPayload($sections), $filename),
            default => throw new BadRequestHttpException("Unknown export format: {$format}"),
        };
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

        // Write data rows — use explicit string type for dangerous values
        // (prevents formula injection without the visible ' prefix that sanitizeCellValue adds for CSV)
        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach (array_values($row) as $value) {
                $cellRef = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                if (self::isDangerousValue($value)) {
                    $sheet->setCellValueExplicit($cellRef, $value, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($cellRef, $value);
                }
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
            return $pluginOrPrefix . '-' . DateFormatHelper::toFilenameString() . '.' . $partsOrExtension;
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

            return $baseName . '-' . DateFormatHelper::toFilenameString() . '.' . $extension;
        }

        // Fallback
        return 'export-' . DateFormatHelper::toFilenameString() . '.csv';
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
     */
    public static function formatDateColumns(array $rows, array $dateColumns): array
    {
        foreach ($rows as &$row) {
            foreach ($dateColumns as $column) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $localDate = DateFormatHelper::toCraftTimezone($row[$column]);
                    $row[$column] = $localDate !== null
                        ? DateFormatHelper::toDateTimeString($localDate)
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
     */
    public static function formatDateColumnsForApi(array $rows, array $dateColumns): array
    {
        foreach ($rows as &$row) {
            foreach ($dateColumns as $column) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $localDate = DateFormatHelper::toCraftTimezone($row[$column]);
                    $row[$column] = $localDate !== null
                        ? DateFormatHelper::toApiString($localDate)
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
     */
    private static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitizeCellValue'], $row);
    }

    /**
     * Check if a value would trigger formula injection in a spreadsheet
     *
     * Same logic as sanitizeCellValue() but returns a boolean instead of modifying the value.
     * Used by Excel export to decide whether to use setCellValueExplicit() with TYPE_STRING.
     *
     * @param mixed $value The cell value to check
     * @return bool True if the value is potentially dangerous
     * @since 5.25.0 (made public)
     */
    public static function isDangerousValue(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $alwaysDangerous = ['=', '@', "\t", "\r", "\n"];

        if (in_array($value[0], $alwaysDangerous, true)) {
            return true;
        }

        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], $alwaysDangerous, true)) {
            return true;
        }

        // Allow +/- only when followed by numeric pattern
        if (preg_match('/^[+-]?\d+([.,]\d+)?$/', $trimmed)) {
            return false;
        }

        // Block +/- when NOT numeric
        if ($trimmed !== '' && in_array($trimmed[0], ['+', '-'], true)) {
            return true;
        }

        return false;
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
