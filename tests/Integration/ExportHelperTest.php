<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\ExportHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use ReflectionClass;

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
    protected function setUp(): void
    {
        parent::setUp();
        // Each test starts from an empty cache so on-disk config (if any)
        // re-resolves cleanly per case.
        ExportHelper::clearConfigCache();
    }

    protected function tearDown(): void
    {
        ExportHelper::clearConfigCache();
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

    public function testGetEnabledFormatsReturnsOnlyTrueKeys(): void
    {
        self::writeConfigCache(['__base__' => ['csv' => true, 'json' => false, 'excel' => true]]);

        // Order is insertion-order from the underlying config hash; assert
        // the set, not the sequence, so a future reorder doesn't break this.
        self::assertEqualsCanonicalizing(['csv', 'excel'], ExportHelper::getEnabledFormats());
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
}
