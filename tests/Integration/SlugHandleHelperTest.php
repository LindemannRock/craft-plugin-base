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
use lindemannrock\base\helpers\SlugHandleHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see SlugHandleHelper}.
 *
 * @since 5.26.0
 */
final class SlugHandleHelperTest extends IntegrationTestCase
{
    private const TABLE = '{{%base_slug_handle_helper_test}}';

    protected function setUp(): void
    {
        parent::setUp();
        $this->dropTable();
        Craft::$app->getDb()->createCommand()->createTable(self::TABLE, [
            'id' => 'pk',
            'handle' => 'varchar(255) NOT NULL',
            'sourceId' => 'integer NULL',
            'source' => 'varchar(50) NULL',
        ])->execute();
    }

    protected function tearDown(): void
    {
        $this->dropTable();
        parent::tearDown();
    }

    public function testNormalizeHandleUsesCraftHandleRulesAndFallback(): void
    {
        self::assertSame('myBackendName', SlugHandleHelper::normalizeHandle(' My Backend Name '));
        self::assertSame('item', SlugHandleHelper::normalizeHandle(' !!! '));
        self::assertSame('fallbackName', SlugHandleHelper::normalizeHandle(' !!! ', 'Fallback Name'));
    }

    public function testNormalizeSlugAllowsUnderscoreAndHyphen(): void
    {
        self::assertSame('summer-sale-2026', SlugHandleHelper::normalizeSlug(' Summer Sale 2026! '));
        self::assertSame('my_code-1', SlugHandleHelper::normalizeSlug('My_Code---1'));
        self::assertSame('fallback-slug', SlugHandleHelper::normalizeSlug(' !!! ', 'Fallback Slug'));
    }

    public function testNormalizePathSlugPreservesSlashSeparatedSegments(): void
    {
        self::assertSame(
            'get-started/requirements-install',
            SlugHandleHelper::normalizePathSlug(' /Get Started//Requirements & Install/ '),
        );
        self::assertSame('fallback-path', SlugHandleHelper::normalizePathSlug('///', 'Fallback Path'));
    }

    public function testExistsSupportsScopeConditionsAndExcludedId(): void
    {
        $id = $this->insertRow('alpha', 1, 'database');
        $this->insertRow('alpha', 2, 'database');
        $this->insertRow('alpha', 1, 'config');

        self::assertTrue(SlugHandleHelper::exists(self::TABLE, 'handle', 'alpha'));
        self::assertTrue(SlugHandleHelper::exists(self::TABLE, 'handle', 'alpha', [
            'scope' => ['sourceId' => 2],
        ]));
        self::assertFalse(SlugHandleHelper::exists(self::TABLE, 'handle', 'alpha', [
            'scope' => ['sourceId' => 3],
        ]));
        self::assertFalse(SlugHandleHelper::exists(self::TABLE, 'handle', 'alpha', [
            'excludeId' => $id,
            'scope' => ['sourceId' => 1],
            'conditions' => ['source' => 'database'],
        ]));
    }

    public function testMakeUniqueUsesHyphenNumberSuffixes(): void
    {
        $this->insertRow('alpha');
        $this->insertRow('alpha-1');

        self::assertSame('alpha-2', SlugHandleHelper::makeUnique(self::TABLE, 'handle', 'alpha'));
    }

    public function testMakeUniqueExcludesCurrentIdOnEdit(): void
    {
        $id = $this->insertRow('alpha');

        self::assertSame('alpha', SlugHandleHelper::makeUnique(self::TABLE, 'handle', 'alpha', [
            'excludeId' => $id,
        ]));
    }

    public function testMakeUniqueSupportsScopedUniqueness(): void
    {
        $this->insertRow('alpha', 1);

        self::assertSame('alpha-1', SlugHandleHelper::makeUnique(self::TABLE, 'handle', 'alpha', [
            'scope' => ['sourceId' => 1],
        ]));
        self::assertSame('alpha', SlugHandleHelper::makeUnique(self::TABLE, 'handle', 'alpha', [
            'scope' => ['sourceId' => 2],
        ]));
    }

    public function testMakeUniqueThrowsAfterMaxAttempts(): void
    {
        $this->insertRow('alpha');
        $this->insertRow('alpha-1');

        $this->expectException(\RuntimeException::class);
        SlugHandleHelper::makeUnique(self::TABLE, 'handle', 'alpha', [
            'maxAttempts' => 1,
        ]);
    }

    public function testUnsafeColumnIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SlugHandleHelper::exists(self::TABLE, 'handle; DROP TABLE users', 'alpha');
    }

    private function insertRow(string $handle, ?int $sourceId = null, ?string $source = null): int
    {
        Craft::$app->getDb()->createCommand()->insert(self::TABLE, [
            'handle' => $handle,
            'sourceId' => $sourceId,
            'source' => $source,
        ])->execute();

        return (int)Craft::$app->getDb()->getLastInsertID();
    }

    private function dropTable(): void
    {
        Craft::$app->getDb()->createCommand()->dropTableIfExists(self::TABLE)->execute();
    }
}
