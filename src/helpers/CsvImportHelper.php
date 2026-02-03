<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\web\UploadedFile;

/**
 * CSV Import Helper
 *
 * Provides utilities for parsing and validating CSV file uploads.
 *
 * @author    LindemannRock
 * @package   LindemannRockBase
 * @since     5.14.0
 */
class CsvImportHelper
{
    /**
     * Default maximum rows allowed for CSV imports
     *
     * @since 5.14.0
     */
    public const DEFAULT_MAX_ROWS = 4000;

    /**
     * Default maximum file size in bytes (5 MB)
     *
     * @since 5.14.0
     */
    public const DEFAULT_MAX_BYTES = 5242880;

    /**
     * Parse a CSV upload into headers + rows stored in memory.
     *
     * @param UploadedFile $file The uploaded CSV file
     * @param array $options Configuration options:
     *   - maxRows: Maximum number of rows to parse (default: 4000)
     *   - maxBytes: Maximum file size in bytes (default: 5MB)
     *   - allowedExtensions: Allowed file extensions (default: ['csv', 'txt'])
     *   - allowedMimeTypes: Allowed MIME types
     *   - delimiter: Force specific delimiter (default: auto-detect)
     *   - detectDelimiter: Whether to auto-detect delimiter (default: true)
     * @return array{headers: array, allRows: array, rowCount: int, delimiter: string}
     * @throws \RuntimeException If validation fails or file cannot be parsed
     * @since 5.14.0
     */
    public static function parseUpload(UploadedFile $file, array $options = []): array
    {
        $options = array_merge([
            'maxRows' => self::DEFAULT_MAX_ROWS,
            'maxBytes' => self::DEFAULT_MAX_BYTES,
            'allowedExtensions' => ['csv', 'txt'],
            'allowedMimeTypes' => [
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt',
            ],
            'delimiter' => null,
            'detectDelimiter' => true,
        ], $options);

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, $options['allowedExtensions'], true)) {
            throw new \RuntimeException('Invalid file type. Please upload a CSV file.');
        }

        if (!empty($options['maxBytes']) && $file->size > (int)$options['maxBytes']) {
            $maxMb = number_format(((int)$options['maxBytes']) / 1048576, 1);
            throw new \RuntimeException('File size exceeds the allowed limit of ' . $maxMb . 'MB.');
        }

        $mimeType = method_exists($file, 'getMimeType') ? $file->getMimeType() : $file->type;
        if (!empty($options['allowedMimeTypes']) && $mimeType && !in_array($mimeType, $options['allowedMimeTypes'], true)) {
            throw new \RuntimeException('Invalid file type. Please upload a CSV file.');
        }

        $tempPath = Craft::$app->getPath()->getTempPath() . '/csv-import-' . uniqid() . '.csv';
        if (!$file->saveAs($tempPath)) {
            throw new \RuntimeException('Failed to save uploaded file. Please try again.');
        }

        try {
            $delimiter = $options['delimiter'];
            if (!$delimiter && !empty($options['detectDelimiter'])) {
                $delimiter = self::detectDelimiter($tempPath);
            }

            $handle = fopen($tempPath, 'r');
            if ($handle === false) {
                throw new \RuntimeException('Could not open uploaded file for reading.');
            }

            $headers = fgetcsv($handle, 0, $delimiter);
            if (!$headers) {
                fclose($handle);
                throw new \RuntimeException('Could not read CSV headers.');
            }

            // Strip UTF-8 BOM from first header if present
            if (!empty($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            }

            if (count($headers) === 1) {
                fclose($handle);
                throw new \RuntimeException('Could not detect CSV delimiter. The file may have only one column or use an unsupported delimiter.');
            }

            $allRows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (!empty($options['maxRows']) && count($allRows) >= (int)$options['maxRows']) {
                    fclose($handle);
                    throw new \RuntimeException('CSV file is too large. Maximum ' . (int)$options['maxRows'] . ' rows allowed. Please split your file into smaller batches.');
                }
                $allRows[] = $row;
            }

            fclose($handle);
            @unlink($tempPath);

            if (count($allRows) === 0) {
                throw new \RuntimeException('CSV file is empty or contains only headers.');
            }

            return [
                'headers' => $headers,
                'allRows' => $allRows,
                'rowCount' => count($allRows),
                'delimiter' => $delimiter,
            ];
        } catch (\Throwable $e) {
            @unlink($tempPath);
            throw $e;
        }
    }

    /**
     * Detect the most likely CSV delimiter from the first line.
     *
     * @param string $filePath Path to the temporary CSV file
     * @return string Detected delimiter
     * @since 5.14.0
     */
    private static function detectDelimiter(string $filePath): string
    {
        $delimiters = [
            ',' => 0,
            ';' => 0,
            "\t" => 0,
            '|' => 0,
        ];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ',';
        }

        $line = fgets($handle);
        fclose($handle);

        if ($line === false) {
            return ',';
        }

        foreach ($delimiters as $delimiter => $count) {
            $delimiters[$delimiter] = substr_count($line, $delimiter);
        }

        $maxCount = 0;
        $detected = ',';

        foreach ($delimiters as $delimiter => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $detected = $delimiter;
            }
        }

        return $detected;
    }
}
