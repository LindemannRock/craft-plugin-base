<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\CacheHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * @since 5.31.0
 */
final class CacheHelperTest extends IntegrationTestCase
{
    public function testCountCacheFilesCountsOnlyMatchingSuffix(): void
    {
        $directory = $this->createTrackedTempDirectory('cache-helper');
        $this->writeFile($directory . '/one.cache');
        $this->writeFile($directory . '/two.cache');
        $this->writeFile($directory . '/notes.txt');
        mkdir($directory . '/nested');
        $this->writeFile($directory . '/nested/three.cache');

        self::assertSame(2, CacheHelper::countCacheFiles($directory));
    }

    public function testClearCacheFilesDeletesOnlyMatchingSuffix(): void
    {
        $directory = $this->createTrackedTempDirectory('cache-helper');
        $cacheFile = $directory . '/one.cache';
        $otherFile = $directory . '/notes.txt';
        $this->writeFile($cacheFile);
        $this->writeFile($otherFile);

        self::assertSame(1, CacheHelper::clearCacheFiles($directory));
        self::assertFileDoesNotExist($cacheFile);
        self::assertFileExists($otherFile);
    }

    public function testMissingDirectoryReturnsZero(): void
    {
        $directory = $this->createTrackedTempDirectory('cache-helper') . '/missing';

        self::assertSame(0, CacheHelper::countCacheFiles($directory));
        self::assertSame(0, CacheHelper::clearCacheFiles($directory));
    }

    public function testCustomSuffixIsSupported(): void
    {
        $directory = $this->createTrackedTempDirectory('cache-helper');
        $jsonFile = $directory . '/one.json';
        $cacheFile = $directory . '/two.cache';
        $this->writeFile($jsonFile);
        $this->writeFile($cacheFile);

        self::assertSame(1, CacheHelper::countCacheFiles($directory, '.json'));
        self::assertSame(1, CacheHelper::clearCacheFiles($directory, '.json'));
        self::assertFileDoesNotExist($jsonFile);
        self::assertFileExists($cacheFile);
    }

    public function testImplementationUsesBoundedCachePrimitives(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/helpers/CacheHelper.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('glob(', $source);
        self::assertStringNotContainsString('SMEMBERS', $source);
        self::assertStringContainsString('SSCAN', $source);
        self::assertStringContainsString('DirectoryIterator', $source);
    }

    private function writeFile(string $path): void
    {
        file_put_contents($path, 'cache-helper-test');
    }
}
