<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use Craft;
use craft\web\Response as WebResponse;
use DateTime;
use DateTimeZone;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\ExportHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ReflectionClass;
use ZipArchive;

/**
 * Pins the contract for {@see ExportHelper::getConfig()} — the 4-layer cascade
 * resolving the `exports` enable map for each plugin.
 *
 * Cascade order (low → high priority):
 *   1. DEFAULT_FORMATS (hardcoded)
 *   2. Base config:     'exports' in config/lindemannrock-base.php
 *   3. Plugin Settings: $settings->exportsCsv / exportsJson / exportsExcel
 *   4. Plugin config:   'exports' in config/{handle}.php
 *
 * The May 2026 rollout introduced two bugs in this cascade that the umbrella
 * partial's override-warning UX caught only after browser testing: an
 * incorrect priority (Settings model used to win over plugin config) and
 * dynamic property access PHPStan flagged. These tests pin the post-fix
 * behaviour. Driving real config-file inputs through the helper still
 * requires a stub for Craft's config service that this suite doesn't have —
 * tests below exercise the cache + shape contract directly.
 *
 * @since 5.25.0
 */
final class ExportHelperTest extends IntegrationTestCase
{
    private object $originalResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalResponse = Craft::$app->getResponse();
        Craft::$app->set('response', new WebResponse());

        // Each test starts from an empty cache so on-disk config (if any)
        // re-resolves cleanly per case.
        ExportHelper::clearConfigCache();
    }

    protected function tearDown(): void
    {
        ExportHelper::clearConfigCache();
        Craft::$app->set('response', $this->originalResponse);

        parent::tearDown();
    }

    public function testGetConfigReturnsHashWithCsvJsonExcelBooleanKeys(): void
    {
        $config = ExportHelper::getConfig();

        // The format key set is fixed at exactly these three. Adding a key
        // here would require updating ExportFormatSettingsTrait, the
        // partial, and the umbrella dispatch — pin so a drive-by addition
        // doesn't slip in.
        self::assertSame(['csv', 'json', 'excel'], array_keys($config));
        foreach ($config as $format => $enabled) {
            self::assertIsBool($enabled, "format '$format' must resolve to a bool");
        }
    }

    public function testGetConfigPopulatesCacheKeyedByPluginHandle(): void
    {
        ExportHelper::getConfig();
        ExportHelper::getConfig('some-handle');

        $cache = self::readConfigCache();

        // No handle uses the `__base__` sentinel so the no-plugin path is
        // cacheable too. Without this, every page render that calls
        // getConfig() reads config files fresh.
        self::assertArrayHasKey('__base__', $cache);
        self::assertArrayHasKey('some-handle', $cache);
    }

    public function testRepeatCallsReturnCachedResultWithoutReReadingConfig(): void
    {
        // Poison the cache directly so we know the helper isn't going back
        // to disk. If anyone refactors getConfig() to skip the cache check,
        // this returns the on-disk shape (default ['csv'=>true,…]) instead
        // of the sentinel and the assertion fails.
        self::writeConfigCache(['__base__' => ['csv' => false, 'json' => false, 'excel' => false]]);

        $config = ExportHelper::getConfig();

        self::assertSame(['csv' => false, 'json' => false, 'excel' => false], $config);
    }

    public function testClearConfigCacheEmptiesAllEntries(): void
    {
        self::writeConfigCache([
            '__base__' => ['csv' => true, 'json' => false, 'excel' => true],
            'some-handle' => ['csv' => false, 'json' => false, 'excel' => false],
        ]);

        ExportHelper::clearConfigCache();

        self::assertSame([], self::readConfigCache());
    }

    public function testGetConfigWithNonExistentPluginHandleDoesNotErrorAndReturnsExpectedShape(): void
    {
        // A handle that no plugin owns — `getPlugin()` returns null and the
        // helper must fall through silently to the base+defaults layers
        // rather than throwing. Pin this so a future refactor that assumes
        // `getPlugin()` is non-null doesn't break consumer plugins.
        $config = ExportHelper::getConfig('definitely-not-a-real-plugin-' . uniqid());

        self::assertSame(['csv', 'json', 'excel'], array_keys($config));
        foreach ($config as $enabled) {
            self::assertIsBool($enabled);
        }
    }

    public function testIsFormatEnabledAliasesXlsxToExcel(): void
    {
        // Poison the cache so we know what 'excel' resolves to without
        // depending on on-disk config state.
        self::writeConfigCache(['__base__' => ['csv' => true, 'json' => false, 'excel' => true]]);

        // The FORMAT_ALIASES map ('xlsx' / 'xls' → 'excel') is what lets
        // URL params and Excel-file extensions pass through `isFormatEnabled`
        // without callers having to canonicalize first.
        self::assertTrue(ExportHelper::isFormatEnabled('xlsx'));
        self::assertTrue(ExportHelper::isFormatEnabled('xls'));
        self::assertTrue(ExportHelper::isFormatEnabled('excel'));

        // And the negative case — alias still routes when the format is off.
        self::writeConfigCache(['__base__' => ['csv' => true, 'json' => false, 'excel' => false]]);
        self::assertFalse(ExportHelper::isFormatEnabled('xlsx'));
    }

    public function testNormalizeFormatAndExtensionForFormatHandleExcelAliases(): void
    {
        self::assertSame('excel', ExportHelper::normalizeFormat(' XLSX '));
        self::assertSame('excel', ExportHelper::normalizeFormat('xls'));
        self::assertSame('csv', ExportHelper::normalizeFormat('CSV'));
        self::assertSame('xlsx', ExportHelper::extensionForFormat('excel'));
        self::assertSame('xlsx', ExportHelper::extensionForFormat('xlsx'));
        self::assertSame('json', ExportHelper::extensionForFormat('json'));
    }

    public function testAssertFormatEnabledThrowsForDisabledFormat(): void
    {
        self::writeConfigCache(['__base__' => ['csv' => true, 'json' => false, 'excel' => true]]);

        ExportHelper::assertFormatEnabled('csv');

        $this->expectException(\yii\web\BadRequestHttpException::class);
        ExportHelper::assertFormatEnabled('json');
    }

    public function testMergeFormatConfigPinsCascadePriority(): void
    {
        $config = ExportHelper::mergeFormatConfig(
            ['csv' => true, 'json' => false, 'excel' => true],
            ['json' => true, 'excel' => false],
            ['json' => false, 'excel' => null],
            ['csv' => false],
        );

        self::assertSame(
            [
                'csv' => false,
                'json' => false,
                'excel' => false,
            ],
            $config,
        );
    }

    public function testGetEnabledFormatsReturnsOnlyTrueKeys(): void
    {
        self::writeConfigCache(['__base__' => ['csv' => true, 'json' => false, 'excel' => true]]);

        // Order is insertion-order from the underlying config hash; assert
        // the set, not the sequence, so a future reorder doesn't break this.
        self::assertEqualsCanonicalizing(['csv', 'excel'], ExportHelper::getEnabledFormats());
    }

    public function testCsvContentQuotesCommasQuotesAndNewlines(): void
    {
        $csv = ExportHelper::csvContent(
            [
                [
                    'name' => 'Alice',
                    'note' => "comma, quote \" and\nnewline",
                    'score' => 42,
                ],
            ],
            ['Name', 'Note', 'Score'],
        );

        self::assertSame(
            "Name,Note,Score\nAlice,\"comma, quote \"\" and\nnewline\",42\n",
            $csv,
        );
    }

    public function testCsvContentSanitizesFormulaInjectionValues(): void
    {
        $csv = ExportHelper::csvContent(
            [
                ['value' => '=cmd|calc'],
                ['value' => '+SUM(A1:A2)'],
                ['value' => '-SUM(A1:A2)'],
                ['value' => '@HYPERLINK("https://example.test")'],
                ['value' => "\t=tab-prefixed"],
                ['value' => "\r=carriage-return-prefixed"],
                ['value' => "\n=newline-prefixed"],
                ['value' => '+123'],
                ['value' => '-123.45'],
            ],
            ['Value'],
        );

        self::assertSame(
            [
                ['Value'],
                ["'=cmd|calc"],
                ["'+SUM(A1:A2)"],
                ["'-SUM(A1:A2)"],
                ['\'@HYPERLINK("https://example.test")'],
                ["'\t=tab-prefixed"],
                ["'\r=carriage-return-prefixed"],
                ["'\n=newline-prefixed"],
                ['+123'],
                ['-123.45'],
            ],
            self::parseCsv($csv),
        );
    }

    public function testIsDangerousValueAllowsSignedNumericValuesOnly(): void
    {
        self::assertFalse(ExportHelper::isDangerousValue('+123'));
        self::assertFalse(ExportHelper::isDangerousValue('-123'));
        self::assertFalse(ExportHelper::isDangerousValue('+12.5'));
        self::assertFalse(ExportHelper::isDangerousValue('-12,5'));

        self::assertTrue(ExportHelper::isDangerousValue('+foo'));
        self::assertTrue(ExportHelper::isDangerousValue('-foo'));
        self::assertTrue(ExportHelper::isDangerousValue('  +foo'));
        self::assertTrue(ExportHelper::isDangerousValue('  =SUM(A1:A2)'));
    }

    public function testDateColumnFormattingUsesDatabaseStringsForCsvExcelAndApiStringsForJson(): void
    {
        $rows = [
            [
                'label' => 'sent',
                'dateCreated' => '2026-05-22 10:15:30',
                'untouched' => null,
            ],
        ];

        $localDate = DateFormatHelper::toCraftTimezone($rows[0]['dateCreated']);

        self::assertInstanceOf(DateTime::class, $localDate);
        self::assertSame(
            [
                [
                    'label' => 'sent',
                    'dateCreated' => DateFormatHelper::toDateTimeString($localDate),
                    'untouched' => null,
                ],
            ],
            ExportHelper::formatDateColumns($rows, ['dateCreated', 'untouched']),
        );
        self::assertSame(
            [
                [
                    'label' => 'sent',
                    'dateCreated' => DateFormatHelper::toApiString($localDate),
                    'untouched' => null,
                ],
            ],
            ExportHelper::formatDateColumnsForApi($rows, ['dateCreated', 'untouched']),
        );
    }

    public function testToJsonFormatsDateColumnsAndSetsDownloadHeaders(): void
    {
        $response = ExportHelper::toJson(
            [
                [
                    'name' => 'München',
                    'dateCreated' => '2026-05-22 10:15:30',
                ],
            ],
            "../unsafe\nname.json",
            ['dateCreated'],
        );

        $decoded = json_decode((string)$response->content, true);
        $localDate = DateFormatHelper::toCraftTimezone('2026-05-22 10:15:30');

        self::assertIsArray($decoded);
        self::assertInstanceOf(DateTime::class, $localDate);
        self::assertSame('München', $decoded[0]['name']);
        self::assertSame(DateFormatHelper::toApiString($localDate), $decoded[0]['dateCreated']);
        self::assertSame('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        self::assertSame('attachment; filename="unsafename.json"', $response->headers->get('Content-Disposition'));
    }

    public function testJsonContentSupportsCompactOutputAndDateFormatting(): void
    {
        $json = ExportHelper::jsonContent(
            [
                [
                    'name' => 'Alice',
                    'dateCreated' => '2026-05-22 10:15:30',
                ],
            ],
            ['dateCreated'],
            false,
        );
        $decoded = json_decode($json, true);
        $localDate = DateFormatHelper::toCraftTimezone('2026-05-22 10:15:30');

        self::assertIsArray($decoded);
        self::assertInstanceOf(DateTime::class, $localDate);
        self::assertSame(DateFormatHelper::toApiString($localDate), $decoded[0]['dateCreated']);
        self::assertStringNotContainsString("\n", $json);
    }

    public function testToCsvSetsHeadersAndSanitizesFilename(): void
    {
        $response = ExportHelper::toCsv(
            [['name' => 'Alice']],
            ['Name'],
            "../unsafe\rname.csv",
        );

        self::assertSame("Name\nAlice\n", $response->content);
        self::assertSame('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        self::assertSame('attachment; filename="unsafename.csv"', $response->headers->get('Content-Disposition'));
    }

    public function testExcelContentFormatsDatesAndWritesDangerousValuesAsStrings(): void
    {
        $rows = [
            [
                'label' => '=SUM(A1:A2)',
                'dateCreated' => '2026-05-22 10:15:30',
            ],
        ];
        $content = ExportHelper::excelContent(
            $rows,
            ['Label', 'Date Created'],
            ['dateCreated'],
            ['sheetTitle' => 'Dangerous / Dates'],
        );
        $tempFile = self::writeTempFile($content, 'xlsx');

        try {
            $spreadsheet = IOFactory::load($tempFile);
            $sheet = $spreadsheet->getActiveSheet();
            $localDate = DateFormatHelper::toCraftTimezone($rows[0]['dateCreated']);

            self::assertInstanceOf(DateTime::class, $localDate);
            self::assertSame('Dangerous - Dates', $sheet->getTitle());
            self::assertSame('Label', $sheet->getCell('A1')->getValue());
            self::assertSame('=SUM(A1:A2)', $sheet->getCell('A2')->getValue());
            self::assertSame(DataType::TYPE_STRING, $sheet->getCell('A2')->getDataType());
            self::assertSame(DateFormatHelper::toDateTimeString($localDate), $sheet->getCell('B2')->getValue());

            $spreadsheet->disconnectWorksheets();
        } finally {
            @unlink($tempFile);
        }
    }

    public function testToZipIncludesNamedAndKeyedFilesAndAddsZipExtension(): void
    {
        $response = ExportHelper::toZip(
            [
                ['name' => 'summary.csv', 'content' => "Name\nAlice\n"],
                'raw/data.json' => '{"ok":true}',
            ],
            'exports',
        );
        $tempFile = self::writeTempFile((string)$response->content, 'zip');
        $zip = new ZipArchive();

        try {
            self::assertSame('application/zip; charset=utf-8', $response->headers->get('Content-Type'));
            self::assertSame('attachment; filename="exports.zip"', $response->headers->get('Content-Disposition'));
            self::assertTrue($zip->open($tempFile));
            self::assertSame("Name\nAlice\n", $zip->getFromName('summary.csv'));
            self::assertSame('{"ok":true}', $zip->getFromName('raw/data.json'));
        } finally {
            $zip->close();
            @unlink($tempFile);
        }
    }

    public function testZipContentReturnsArchiveBytes(): void
    {
        $content = ExportHelper::zipContent([
            'summary.csv' => "Name\nAlice\n",
        ]);
        $tempFile = self::writeTempFile($content, 'zip');
        $zip = new ZipArchive();

        try {
            self::assertTrue($zip->open($tempFile));
            self::assertSame("Name\nAlice\n", $zip->getFromName('summary.csv'));
        } finally {
            $zip->close();
            @unlink($tempFile);
        }
    }

    public function testZipContentSanitizesEntryNamesAndPreservesSafeSubfolders(): void
    {
        $content = ExportHelper::zipContent([
            '../Raw Data/Unsafe "Name".csv' => "Name\nAlice\n",
            ['name' => '..\\Raw Data\\Unsafe Name.csv', 'content' => "Name\nBob\n"],
            '../../../' => "Name\nCarol\n",
        ]);
        $tempFile = self::writeTempFile($content, 'zip');
        $zip = new ZipArchive();

        try {
            self::assertTrue($zip->open($tempFile));
            self::assertSame("Name\nAlice\n", $zip->getFromName('raw-data/unsafe-name.csv'));
            self::assertSame("Name\nBob\n", $zip->getFromName('raw-data/unsafe-name-2.csv'));
            self::assertSame("Name\nCarol\n", $zip->getFromName('export-file.txt'));
        } finally {
            $zip->close();
            @unlink($tempFile);
        }
    }

    public function testDispatchTableRoutesCsvJsonAndExcelAliases(): void
    {
        $rows = [
            [
                'name' => '=SUM(A1:A2)',
                'dateCreated' => '2026-05-22 10:15:30',
            ],
        ];
        $headers = ['Name', 'Date Created'];

        $csv = ExportHelper::dispatchTable($rows, $headers, 'csv', 'users.csv', ['dateCreated']);
        self::assertSame('text/csv; charset=utf-8', $csv->headers->get('Content-Type'));
        self::assertStringContainsString("'=SUM(A1:A2)", (string)$csv->content);

        Craft::$app->set('response', new WebResponse());
        $json = ExportHelper::dispatchTable(
            $rows,
            $headers,
            'json',
            'users.json',
            [],
            [],
            ['meta' => ['count' => 1]],
        );
        self::assertSame(['meta' => ['count' => 1]], json_decode((string)$json->content, true));

        Craft::$app->set('response', new WebResponse());
        $excel = ExportHelper::dispatchTable($rows, $headers, 'xlsx', 'users.xlsx', ['dateCreated']);

        self::assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            $excel->headers->get('Content-Type'),
        );
    }

    public function testDispatchSectionsRoutesExcelJsonAndCsvZip(): void
    {
        $sections = [
            [
                'key' => 'summary',
                'title' => 'Summary',
                'filename' => 'summary.csv',
                'headers' => ['Name', 'Created'],
                'rows' => [
                    ['name' => 'Alice', 'dateCreated' => '2026-05-22 10:15:30'],
                ],
                'dateColumns' => ['dateCreated'],
            ],
            [
                'key' => 'rawRows',
                'title' => 'Raw Rows',
                'headers' => ['Name'],
                'rows' => [
                    ['name' => 'Bob'],
                ],
            ],
        ];

        $excel = ExportHelper::dispatchSections($sections, 'excel', 'sections.xlsx');
        self::assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            $excel->headers->get('Content-Type'),
        );

        Craft::$app->set('response', new WebResponse());
        $json = ExportHelper::dispatchSections($sections, 'json', 'sections.json');
        self::assertSame(['summary', 'rawRows'], array_keys(json_decode((string)$json->content, true)));

        Craft::$app->set('response', new WebResponse());
        $zip = ExportHelper::dispatchSections($sections, 'csv', 'sections.zip');
        $tempFile = self::writeTempFile((string)$zip->content, 'zip');
        $archive = new ZipArchive();

        try {
            self::assertTrue($archive->open($tempFile));
            self::assertNotFalse($archive->getFromName('summary.csv'));
            self::assertNotFalse($archive->getFromName('raw-rows.csv'));
        } finally {
            $archive->close();
            @unlink($tempFile);
        }
    }

    public function testFilenameSupportsExactPrefixAndSettingsPatterns(): void
    {
        $before = time();
        $settings = new class {
            public function getLowerDisplayName(): string
            {
                return 'Search Manager';
            }
        };

        self::assertSame('exact-name.csv', ExportHelper::filename('exact-name.csv'));

        $simple = ExportHelper::filename('Logs & Reports', 'CSV');
        $withParts = ExportHelper::filename($settings, ['Analytics', null, 'Last 30 days', '../Unsafe "Part"'], 'XLSX');
        $after = time();

        self::assertMatchesRegularExpression('/^logs-reports-\d{4}-\d{2}-\d{2}-\d{6}\.csv$/', $simple);
        self::assertMatchesRegularExpression(
            '/^search-manager-analytics-last-30-days-unsafe-part-\d{4}-\d{2}-\d{2}-\d{6}\.xlsx$/',
            $withParts,
        );
        self::assertTimestampInRange($simple, 'logs-reports-', '.csv', $before, $after);
        self::assertTimestampInRange($withParts, 'search-manager-analytics-last-30-days-unsafe-part-', '.xlsx', $before, $after);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Read the private static config cache via reflection. Tests that want
     * to assert on cache state (vs. just the helper's return value) use this.
     *
     * @return array<string, array<string, bool>>
     */
    private static function readConfigCache(): array
    {
        $property = (new ReflectionClass(ExportHelper::class))->getProperty('configCache');
        /** @var array<string, array<string, bool>> $value */
        $value = $property->getValue();
        return $value;
    }

    /**
     * Write the private static config cache via reflection. Tests use this
     * to inject controlled values without going through real config files.
     *
     * @param array<string, array<string, bool>> $value
     */
    private static function writeConfigCache(array $value): void
    {
        $property = (new ReflectionClass(ExportHelper::class))->getProperty('configCache');
        $property->setValue(null, $value);
    }

    /**
     * @return list<list<string|null>>
     */
    private static function parseCsv(string $csv): array
    {
        $stream = fopen('php://temp', 'r+');
        self::assertIsResource($stream);
        fwrite($stream, $csv);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream)) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }

    private static function writeTempFile(string $content, string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'export_helper_test_');
        self::assertIsString($path);
        $target = $path . '.' . $extension;
        rename($path, $target);
        file_put_contents($target, $content);

        return $target;
    }

    private static function assertTimestampInRange(
        string $filename,
        string $prefix,
        string $suffix,
        int $before,
        int $after,
    ): void {
        $timestamp = substr($filename, strlen($prefix), -strlen($suffix));
        $date = DateTime::createFromFormat(
            'Y-m-d-His',
            $timestamp,
            new DateTimeZone(Craft::$app->getTimeZone()),
        );

        self::assertInstanceOf(DateTime::class, $date);
        self::assertGreaterThanOrEqual($before, $date->getTimestamp());
        self::assertLessThanOrEqual($after, $date->getTimestamp());
    }
}
