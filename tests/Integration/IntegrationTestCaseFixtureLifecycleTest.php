<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.26.0
 */
#[CoversClass(IntegrationTestCase::class)]
final class IntegrationTestCaseFixtureLifecycleTest extends IntegrationTestCase
{
    public function testNextTestMarkerUsesPrefixKindCounterAndRandomSuffix(): void
    {
        $first = $this->nextTestMarker('__base_test_', 'row');
        $second = $this->nextTestMarker('__base_test_', 'row');

        self::assertMatchesRegularExpression('/^__base_test_row_1_[a-f0-9]{8}$/', $first);
        self::assertMatchesRegularExpression('/^__base_test_row_2_[a-f0-9]{8}$/', $second);
        self::assertNotSame($first, $second);
    }

    public function testTrackedTempDirectoryIsRemovedDuringTearDown(): void
    {
        $case = new class ('fixture-cleanup') extends IntegrationTestCase {
            public string $dir;

            public function seedTempDir(): string
            {
                $this->dir = $this->createTrackedTempDirectory('__base_lifecycle_');
                file_put_contents($this->dir . DIRECTORY_SEPARATOR . 'fixture.txt', 'fixture');

                return $this->dir;
            }

            public function runBaseTearDown(): void
            {
                $this->tearDown();
            }
        };

        $dir = $case->seedTempDir();
        self::assertDirectoryExists($dir);
        self::assertFileExists($dir . DIRECTORY_SEPARATOR . 'fixture.txt');

        $case->runBaseTearDown();

        self::assertDirectoryDoesNotExist($dir);
    }
}
