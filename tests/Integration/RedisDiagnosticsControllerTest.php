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
use craft\console\controllers\HelpController as CraftHelpController;
use lindemannrock\base\Base;
use lindemannrock\base\console\controllers\HelpController;
use lindemannrock\base\console\controllers\RedisController;
use lindemannrock\base\console\RedisDiagnosticsRenderer;
use lindemannrock\base\services\RedisDatabaseDiagnosticsResult;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\caching\ArrayCache;
use yii\console\ExitCode;
use yii\redis\Cache;
use yii\redis\Connection;

/**
 * @since 5.37.0
 */
final class RedisDiagnosticsControllerTest extends IntegrationTestCase
{
    public function testBaseModuleResolvesApprovedRedisDatabasesRoute(): void
    {
        $module = new Base('lindemannrock-base');
        $route = $module->createController('redis/databases');

        self::assertNotFalse($route);
        self::assertInstanceOf(RedisController::class, $route[0]);
        self::assertSame('databases', $route[1]);
    }

    public function testBaseModuleResolvesHelpRoute(): void
    {
        $module = new Base('lindemannrock-base');
        $route = $module->createController('help');

        self::assertNotFalse($route);
        self::assertInstanceOf(HelpController::class, $route[0]);
        self::assertSame('', $route[1]);
    }

    public function testBaseHelpOverviewListsApprovedRoute(): void
    {
        $controller = new TestBaseHelpController(
            'help',
            new Base('lindemannrock-base'),
        );

        $exitCode = $controller->actionIndex();

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString('LindemannRock Base CLI', $controller->output);
        self::assertStringContainsString(
            'lindemannrock-base/redis/databases',
            $controller->output,
        );
        self::assertStringContainsString(
            'php craft lindemannrock-base/help <group/action>',
            $controller->output,
        );
    }

    public function testBaseFocusedHelpDescribesApprovedRouteAndOptions(): void
    {
        $controller = new TestBaseHelpController(
            'help',
            new Base('lindemannrock-base'),
        );

        $exitCode = $controller->actionIndex('redis/databases');

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString(
            'lindemannrock-base/redis/databases [--from=<database>] [--to=<database>] [--format=<human|json>]',
            $controller->output,
        );
        self::assertStringContainsString('--from', $controller->output);
        self::assertStringContainsString('--to', $controller->output);
        self::assertStringContainsString('--format', $controller->output);
        self::assertStringContainsString(
            'php craft help lindemannrock-base/redis/databases',
            $controller->output,
        );
    }

    public function testBaseHelpOutputUsesBaseTranslations(): void
    {
        $originalLanguage = Craft::$app->language;

        try {
            Craft::$app->language = 'de';
            $controller = new TestBaseHelpController(
                'help',
                new Base('lindemannrock-base'),
            );

            $exitCode = $controller->actionIndex('redis/databases');

            self::assertSame(ExitCode::OK, $exitCode);
            self::assertStringContainsString(
                'Prüfen Sie ausschließlich den konfigurierten Redis-Cache-Endpunkt von Craft',
                $controller->output,
            );
            self::assertStringContainsString(
                'Erste logische Datenbank im inklusiven Bereich. Standard: 0.',
                $controller->output,
            );
            self::assertStringNotContainsString(
                'Show bounded point-in-time Redis database key counts.',
                $controller->output,
            );
        } finally {
            Craft::$app->language = $originalLanguage;
        }
    }

    public function testNativeCraftHelpResolvesApprovedRoute(): void
    {
        Base::register();
        $route = Craft::$app->createController(
            'lindemannrock-base/redis/databases',
        );

        self::assertNotFalse($route);
        self::assertInstanceOf(RedisController::class, $route[0]);
        self::assertSame('databases', $route[1]);

        $controller = new TestCraftHelpController('help', Craft::$app);
        $exitCode = $controller->actionIndex(
            'lindemannrock-base/redis/databases',
        );

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString(
            'lindemannrock-base/redis/databases',
            $controller->output,
        );
        self::assertStringContainsString('--from', $controller->output);
        self::assertStringContainsString('--to', $controller->output);
        self::assertStringContainsString('--format', $controller->output);
    }

    public function testInvalidRangeReturnsFrozenJsonAndUsageExit(): void
    {
        $controller = $this->controller();
        $controller->format = 'json';
        $controller->from = 0;
        $controller->to = 64;

        $exitCode = $controller->actionDatabases();
        $payload = $this->decode($controller->output);

        self::assertSame(ExitCode::USAGE, $exitCode);
        self::assertSame(
            RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST,
            $payload['outcome'],
        );
        self::assertSame(null, $payload['requestedRange']);
        self::assertSame(
            RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
            $payload['ping'],
        );
        $this->assertFrozenJsonShape($payload, false);
    }

    public function testInvalidFormatReturnsUsageWithoutJson(): void
    {
        $controller = $this->controller();
        $controller->format = 'yaml';

        $exitCode = $controller->actionDatabases();

        self::assertSame(ExitCode::USAGE, $exitCode);
        self::assertSame(
            "Output format must be human or json.\n",
            $controller->output,
        );
    }

    public function testUnsupportedCacheReturnsConfigExit(): void
    {
        $controller = $this->controller();
        $controller->format = 'json';
        $controller->cache = new ArrayCache();

        $exitCode = $controller->actionDatabases();
        $payload = $this->decode($controller->output);

        self::assertSame(ExitCode::CONFIG, $exitCode);
        self::assertSame(
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CACHE,
            $payload['outcome'],
        );
        $this->assertFrozenJsonShape($payload, false);
    }

    public function testHumanUnsupportedCacheUsesNotCheckedTopology(): void
    {
        $controller = $this->controller();
        $controller->cache = new ArrayCache();

        $exitCode = $controller->actionDatabases();

        self::assertSame(ExitCode::CONFIG, $exitCode);
        self::assertStringContainsString(
            'Topology: Not checked',
            $controller->output,
        );
        self::assertStringContainsString(
            'Database enumeration: Unavailable',
            $controller->output,
        );
        self::assertStringContainsString(
            'Enumeration completion: Not started',
            $controller->output,
        );
    }

    public function testUnsupportedConnectionReturnsConfigExit(): void
    {
        $controller = $this->controller();
        $controller->format = 'json';
        $cache = new TestRedisCache();
        $cache->redis = 'unsupported-redis-component';
        $controller->cache = $cache;

        $exitCode = $controller->actionDatabases();
        $payload = $this->decode($controller->output);

        self::assertSame(ExitCode::CONFIG, $exitCode);
        self::assertSame(
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CONNECTION,
            $payload['outcome'],
        );
        $this->assertFrozenJsonShape($payload, false);
    }

    #[DataProvider('outcomeExitCodeProvider')]
    public function testEveryServiceOutcomeUsesFrozenJsonAndExitCode(
        RedisDatabaseDiagnosticsResult $result,
        int $expectedExitCode,
    ): void {
        $controller = $this->controller();
        $controller->format = 'json';
        $controller->result = $result;

        $exitCode = $controller->actionDatabases();
        $payload = $this->decode($controller->output);

        self::assertSame($expectedExitCode, $exitCode);
        self::assertSame($result->outcome, $payload['outcome']);
        self::assertSame($result->toArray(), $payload);
        $this->assertFrozenJsonShape(
            $payload,
            $result->hasSourceConfiguredDatabase,
        );
    }

    /**
     * @return iterable<string, array{RedisDatabaseDiagnosticsResult, int}>
     */
    public static function outcomeExitCodeProvider(): iterable
    {
        yield 'complete' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
                RedisDatabaseDiagnosticsResult::PING_OK,
                true,
                true,
                [['database' => 0, 'keyCount' => 4]],
            ),
            ExitCode::OK,
        ];
        yield 'unsupported source database' => [
            new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                [
                    'from' => 0,
                    'to' => 15,
                    'databaseCount' => 16,
                    'maximumDatabaseCount' => 64,
                ],
            ),
            ExitCode::CONFIG,
        ];
        yield 'diagnostic connection unavailable' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_FAILED,
                false,
                false,
            ),
            ExitCode::UNAVAILABLE,
        ];
        yield 'cluster detected' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER,
                RedisDatabaseDiagnosticsResult::PING_OK,
                true,
                false,
            ),
            ExitCode::PROTOCOL,
        ];
        yield 'topology undetermined' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_UNKNOWN,
                RedisDatabaseDiagnosticsResult::PING_OK,
                false,
                false,
            ),
            ExitCode::PROTOCOL,
        ];
        yield 'select failed' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_SELECT_FAILED,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
                RedisDatabaseDiagnosticsResult::PING_OK,
                false,
                false,
            ),
            ExitCode::PROTOCOL,
        ];
        yield 'dbsize failed' => [
            self::diagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_DBSIZE_FAILED,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
                RedisDatabaseDiagnosticsResult::PING_OK,
                true,
                false,
                [['database' => 0, 'keyCount' => 4]],
            ),
            ExitCode::PROTOCOL,
        ];
    }

    public function testJsonIsByteEquivalentAcrossAllSupportedLocales(): void
    {
        $result = self::diagnosticsResult(
            RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE,
            RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
            RedisDatabaseDiagnosticsResult::PING_OK,
            true,
            true,
            [['database' => 0, 'keyCount' => 0]],
            null,
        );
        $originalLanguage = Craft::$app->language;
        $outputs = [];

        try {
            foreach ([
                'en',
                'de',
                'fr',
                'nl',
                'es',
                'ar',
                'it',
                'pt',
                'ja',
                'sv',
                'da',
                'no',
            ] as $language) {
                Craft::$app->language = $language;
                $controller = $this->controller();
                $controller->format = 'json';
                $controller->result = $result;
                self::assertSame(
                    ExitCode::OK,
                    $controller->actionDatabases(),
                );
                $outputs[$language] = $controller->output;
            }
        } finally {
            Craft::$app->language = $originalLanguage;
        }

        self::assertCount(1, array_unique($outputs));
    }

    public function testFrozenEnumSetsAreExact(): void
    {
        $constants = (new \ReflectionClass(
            RedisDatabaseDiagnosticsResult::class,
        ))->getConstants();

        self::assertSame([
            'complete',
            'invalid-request',
            'unsupported-cache',
            'unsupported-connection',
            'unsupported-source-database',
            'diagnostic-connection-unavailable',
            'cluster-detected',
            'topology-undetermined',
            'select-failed',
            'dbsize-failed',
        ], array_values(array_filter(
            $constants,
            static fn(string $name): bool => str_starts_with($name, 'OUTCOME_'),
            ARRAY_FILTER_USE_KEY,
        )));
        self::assertSame([
            'not-checked',
            'standalone',
            'cluster',
            'unknown',
        ], array_values(array_filter(
            $constants,
            static fn(string $name): bool => str_starts_with($name, 'TOPOLOGY_'),
            ARRAY_FILTER_USE_KEY,
        )));
        self::assertSame([
            'not-attempted',
            'ok',
            'failed',
        ], array_values(array_filter(
            $constants,
            static fn(string $name): bool => str_starts_with($name, 'PING_'),
            ARRAY_FILTER_USE_KEY,
        )));
    }

    public function testHumanAndJsonOutputsRepresentZeroKeysWithoutUsageClaims(): void
    {
        $result = self::diagnosticsResult(
            RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE,
            RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
            RedisDatabaseDiagnosticsResult::PING_OK,
            true,
            true,
            [['database' => 0, 'keyCount' => 0]],
            null,
        );

        $human = RedisDiagnosticsRenderer::human($result);
        $json = $this->decode(RedisDiagnosticsRenderer::json($result));

        self::assertStringContainsString(
            'Source configured database: default (no automatic SELECT)',
            $human,
        );
        self::assertStringContainsString('DB 0: 0 keys', $human);
        self::assertStringNotContainsString('unused', strtolower($human));
        self::assertStringNotContainsString('unreserved', strtolower($human));
        self::assertSame(null, $json['sourceConfiguredDatabase']);
        self::assertSame([
            ['database' => 0, 'keyCount' => 0],
        ], $json['databases']);
    }

    public function testInclusive64DatabaseRangePassesControllerValidation(): void
    {
        $controller = $this->controller();
        $controller->format = 'json';
        $controller->from = 4;
        $controller->to = 67;
        $controller->result = new RedisDatabaseDiagnosticsResult(
            RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED,
            RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER,
            RedisDatabaseDiagnosticsResult::PING_OK,
            false,
            false,
            [
                'from' => 4,
                'to' => 67,
                'databaseCount' => 64,
                'maximumDatabaseCount' => 64,
            ],
            [],
            true,
            0,
        );

        self::assertSame(ExitCode::PROTOCOL, $controller->actionDatabases());
        self::assertSame([
            'from' => 4,
            'to' => 67,
            'databaseCount' => 64,
            'maximumDatabaseCount' => 64,
        ], $this->decode($controller->output)['requestedRange']);
    }

    /**
     * @param list<array{database: int, keyCount: int}> $databases
     */
    private static function diagnosticsResult(
        string $outcome,
        string $topology,
        string $ping,
        bool $enumerationAvailable,
        bool $enumerationComplete,
        array $databases = [],
        ?int $sourceConfiguredDatabase = 0,
    ): RedisDatabaseDiagnosticsResult {
        return new RedisDatabaseDiagnosticsResult(
            $outcome,
            $topology,
            $ping,
            $enumerationAvailable,
            $enumerationComplete,
            [
                'from' => 0,
                'to' => 15,
                'databaseCount' => 16,
                'maximumDatabaseCount' => 64,
            ],
            $databases,
            true,
            $sourceConfiguredDatabase,
        );
    }

    private function controller(): TestRedisController
    {
        $cache = new TestRedisCache();
        $cache->redis = new Connection();

        $controller = new TestRedisController(
            'redis',
            new Base('lindemannrock-base'),
        );
        $controller->cache = $cache;

        return $controller;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertFrozenJsonShape(
        array $payload,
        bool $hasSourceConfiguredDatabase,
    ): void {
        $expectedKeys = [
            'schemaVersion',
            'scope',
            'outcome',
            'topology',
        ];
        if ($hasSourceConfiguredDatabase) {
            $expectedKeys[] = 'sourceConfiguredDatabase';
        }
        array_push(
            $expectedKeys,
            'automaticDatabaseSelection',
            'selectIssuedOnOpen',
            'ping',
            'enumerationAvailable',
            'enumerationComplete',
            'requestedRange',
            'databases',
        );

        self::assertSame($expectedKeys, array_keys($payload));
        self::assertSame(1, $payload['schemaVersion']);
        self::assertSame(
            'craft-redis-cache-endpoint',
            $payload['scope'],
        );
        self::assertArrayNotHasKey('reason', $payload);
        self::assertArrayNotHasKey('evidence', $payload);
        self::assertArrayNotHasKey('notice', $payload);
        self::assertArrayNotHasKey('connection', $payload);
        self::assertArrayNotHasKey('result', $payload);
    }
}

/**
 * Captures Base help output without writing to PHPUnit process streams.
 */
final class TestBaseHelpController extends HelpController
{
    public string $output = '';

    public function stdout($string)
    {
        $this->output .= (string)$string;

        return strlen((string)$string);
    }
}

/**
 * Captures Craft's native help output.
 */
final class TestCraftHelpController extends CraftHelpController
{
    public string $output = '';

    public function stdout($string)
    {
        $this->output .= (string)$string;

        return strlen((string)$string);
    }
}

/**
 * Controls cache/result inputs while exercising the real controller action.
 */
final class TestRedisController extends RedisController
{
    public mixed $cache = null;

    public ?RedisDatabaseDiagnosticsResult $result = null;

    public string $output = '';

    protected function cacheComponent(): mixed
    {
        return $this->cache;
    }

    protected function inspect(
        Connection $source,
        int $from,
        int $to,
    ): RedisDatabaseDiagnosticsResult {
        return $this->result ?? parent::inspect(
            $source,
            $from,
            $to,
        );
    }

    public function stdout($string)
    {
        $this->output .= (string)$string;

        return strlen((string)$string);
    }

    public function stderr($string)
    {
        $this->output .= (string)$string;

        return strlen((string)$string);
    }
}

/**
 * Keeps the Redis property unresolved for controller boundary tests.
 */
final class TestRedisCache extends Cache
{
    public function init(): void
    {
        // The controller test supplies the exact raw Redis property shape.
    }
}
