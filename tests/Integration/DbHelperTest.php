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
use lindemannrock\base\testing\SqlDialectLinter;

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
        // Single key — bare column comes back [[...]]-bracketed so PostgreSQL
        // keeps identifier case (unquoted identifiers fold to lowercase there).
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT([[metadata]], '$.key'))",
            DbHelper::jsonExtract('metadata', 'key'),
        );

        // Nested path — array form, NOT dot-split.
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT([[metadata]], '$.outer.inner'))",
            DbHelper::jsonExtract('metadata', ['outer', 'inner']),
        );

        // Alias-qualified column brackets as one token (Yii quotes both parts).
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT([[a.metadata]], '$.clickType'))",
            DbHelper::jsonExtract('a.metadata', 'clickType'),
        );

        // Craft table-prefix syntax: the {{%...}} token is preserved for Yii to
        // resolve/quote and only the column part is bracketed — never nested
        // inside [[...]].
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT({{%formie_submissions}}.[[content]], '$.fieldUid'))",
            DbHelper::jsonExtract('{{%formie_submissions}}.content', 'fieldUid'),
        );

        // Already-bracketed references pass through unchanged.
        self::assertSame(
            "JSON_UNQUOTE(JSON_EXTRACT([[s.content]], '$.fieldUid'))",
            DbHelper::jsonExtract('[[s.content]]', 'fieldUid'),
        );

        // Validator rejects unsafe path segments — stops a future caller from
        // sneaking SQL through `$path`.
        $this->expectException(\InvalidArgumentException::class);
        DbHelper::jsonExtract('metadata', "key'); DROP TABLE users;--");
    }

    public function testGroupConcatMysqlDialect(): void
    {
        // Default separator; bare column is [[...]]-bracketed.
        self::assertSame(
            "GROUP_CONCAT([[handle]] SEPARATOR ',')",
            DbHelper::groupConcat('handle'),
        );

        // Custom separator round-trips into the SQL — quote-escaping pins the
        // single-quote-doubling fix that came out of the SQL-injection audit.
        self::assertSame(
            "GROUP_CONCAT([[handle]] SEPARATOR ' | ')",
            DbHelper::groupConcat('handle', ' | '),
        );
        self::assertSame(
            "GROUP_CONCAT([[handle]] SEPARATOR '''')",
            DbHelper::groupConcat('handle', "'"),
        );
    }

    public function testCastToTextMysqlDialect(): void
    {
        // Bare columns are bracketed — this is what keeps a camelCase column
        // like sessionId resolvable on PostgreSQL.
        self::assertSame('CAST([[id]] AS CHAR)', DbHelper::castToText('id'));
        self::assertSame('CAST([[sessionId]] AS CHAR)', DbHelper::castToText('sessionId'));

        // Composed expressions pass through unchanged.
        self::assertSame(
            'CAST(COALESCE(a, b) AS CHAR)',
            DbHelper::castToText('COALESCE(a, b)'),
        );
    }

    public function testExistingColumnBuildsQualifiedUpsertReference(): void
    {
        self::assertSame(
            '{{%searchmanager_search_terms}}.[[frequency]]',
            DbHelper::existingColumn('searchmanager_search_terms', 'frequency'),
        );
    }

    public function testExistingColumnRejectsNonIdentifierInputs(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DbHelper::existingColumn('{{%searchmanager_search_terms}}', 'frequency');
    }

    public function testSqlDialectLinterFlagsUnsafeLiteralsAndPassesBracketedOnes(): void
    {
        $fixture = sys_get_temp_dir() . '/lr-sql-dialect-linter-fixture.php';
        file_put_contents($fixture, <<<'PHP'
<?php
// Comment mentioning SUM(resultsCount) must NOT be flagged.
$unsafeAggregate = 'SUM(resultsCount) AS actionResults';
$unsafeAlias = 'COUNT(*) as searchCount';
$unsafeCase = "CASE WHEN trafficType = 'bot' THEN 1 ELSE 0 END";
$safe = 'SUM([[resultsCount]]) AS [[actionResults]]';
$safeLowercase = 'COUNT(DISTINCT query) as total';
PHP);

        try {
            $violations = SqlDialectLinter::scanFile($fixture);
        } finally {
            unlink($fixture);
        }

        self::assertCount(3, $violations);
        self::assertStringContainsString('SUM(resultsCount)', $violations[0]);
        self::assertStringContainsString('unbracketed camelCase alias', $violations[1]);
        self::assertStringContainsString('CASE WHEN trafficType', $violations[2]);
    }
}
