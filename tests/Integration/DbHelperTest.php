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
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see DbHelper}.
 *
 * MySQL dialect only — the workspace runs DDEV with MySQL. The PostgreSQL
 * branches in DbHelper are intentionally untested here; spinning up a
 * Postgres test install just to verify expression strings would dwarf the
 * value. If we ever ship Postgres-targeted CI, mirror these tests under
 * a separate group and skip the MySQL branch.
 *
 * @since 5.25.0
 */
final class DbHelperTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!Craft::$app->getDb()->getIsMysql()) {
            self::markTestSkipped('DbHelper SQL pinning is MySQL-only in this suite.');
        }
    }

    public function testJsonExtractMysqlDialect(): void
    {
        // Single key.
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.key'))",
            DbHelper::jsonExtract('metadata', 'key'),
        );

        // Nested path — array form, NOT dot-split.
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.outer.inner'))",
            DbHelper::jsonExtract('metadata', ['outer', 'inner']),
        );

        // Validator rejects unsafe path segments — stops a future caller from
        // sneaking SQL through `$path`.
        $this->expectException(\InvalidArgumentException::class);
        DbHelper::jsonExtract('metadata', "key'); DROP TABLE users;--");
    }

    public function testGroupConcatMysqlDialect(): void
    {
        // Default separator.
        self::assertSame(
            "GROUP_CONCAT(handle SEPARATOR ',')",
            DbHelper::groupConcat('handle'),
        );

        // Custom separator round-trips into the SQL — quote-escaping pins the
        // single-quote-doubling fix that came out of the SQL-injection audit.
        self::assertSame(
            "GROUP_CONCAT(handle SEPARATOR ' | ')",
            DbHelper::groupConcat('handle', ' | '),
        );
        self::assertSame(
            "GROUP_CONCAT(handle SEPARATOR '''')",
            DbHelper::groupConcat('handle', "'"),
        );
    }
}
