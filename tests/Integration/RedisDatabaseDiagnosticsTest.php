<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use craft\helpers\App;
use lindemannrock\base\helpers\YiiRedisConnectionHelper;
use lindemannrock\base\services\RedisDatabaseDiagnostics;
use lindemannrock\base\services\RedisDatabaseDiagnosticsResult;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Throwable;
use yii\db\Exception as YiiDbException;
use yii\redis\Connection;

/**
 * @since 5.37.0
 */
final class RedisDatabaseDiagnosticsTest extends IntegrationTestCase
{
    private const CLUSTER_DISABLED_EXCEPTION = "Redis error: ERR This instance has cluster support disabled\nRedis command was: CLUSTER INFO";

    public function testStandaloneProbeRunsOnlyBoundedReadOnlyDiagnostics(): void
    {
        $source = new Connection([
            'database' => 4,
            'socketClientFlags' => STREAM_CLIENT_PERSISTENT,
        ]);
        $selectedDatabase = 0;
        $diagnostic = new RecordingRedisConnection(
            static function(string $command, array $params) use (&$selectedDatabase): mixed {
                return match ($command) {
                    'PING' => true,
                    'CLUSTER' => throw new YiiDbException(self::CLUSTER_DISABLED_EXCEPTION),
                    'SELECT' => $selectedDatabase = (int)$params[0],
                    'DBSIZE' => (string)($selectedDatabase + 10),
                    default => throw new RuntimeException("Unexpected command: {$command}"),
                };
            },
        );
        $runner = $this->runner($diagnostic);

        $result = $runner->inspect($source, 2, 4);

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE, $result->topology);
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_OK, $result->ping);
        self::assertTrue($result->enumerationAvailable);
        self::assertTrue($result->enumerationComplete);
        self::assertSame([
            ['database' => 2, 'keyCount' => 12],
            ['database' => 3, 'keyCount' => 13],
            ['database' => 4, 'keyCount' => 14],
        ], $result->databases);
        self::assertSame([
            ['PING', []],
            ['CLUSTER', ['INFO']],
            ['SELECT', [2]],
            ['DBSIZE', []],
            ['SELECT', [3]],
            ['DBSIZE', []],
            ['SELECT', [4]],
            ['DBSIZE', []],
        ], $diagnostic->commands);
        self::assertTrue($diagnostic->closed);
        self::assertSame(4, $source->database);
        self::assertSame(STREAM_CLIENT_PERSISTENT, $source->socketClientFlags);
        self::assertFalse($source->isActive);

        foreach ($diagnostic->commands as [$command]) {
            self::assertNotContains($command, [
                'SET',
                'DEL',
                'FLUSHDB',
                'FLUSHALL',
                'KEYS',
                'SCAN',
            ]);
        }
    }

    public function testRecognizedClusterInfoDetectsClusterWithoutEnumeration(): void
    {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => true,
                'CLUSTER' => "cluster_state:ok\r\ncluster_slots_assigned:16384\r\n",
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection());

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER, $result->topology);
        self::assertFalse($result->enumerationAvailable);
        self::assertFalse($result->enumerationComplete);
        self::assertSame([
            ['PING', []],
            ['CLUSTER', ['INFO']],
        ], $diagnostic->commands);
        self::assertSame([], $result->databases);
    }

    /**
     * @param callable(): mixed $clusterResponse
     */
    #[DataProvider('unknownTopologyProvider')]
    public function testUnknownTopologyNeverSelectsOrCountsDatabases(
        callable $clusterResponse,
    ): void {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => true,
                'CLUSTER' => $clusterResponse(),
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection());

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::TOPOLOGY_UNKNOWN, $result->topology);
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_OK, $result->ping);
        self::assertFalse($result->enumerationAvailable);
        self::assertFalse($result->enumerationComplete);
        self::assertSame([
            ['PING', []],
            ['CLUSTER', ['INFO']],
        ], $diagnostic->commands);
        self::assertSame([], $result->databases);
    }

    /**
     * @return iterable<string, array{callable(): mixed}>
     */
    public static function unknownTopologyProvider(): iterable
    {
        yield 'ACL denial' => [
            static fn(): mixed => throw new YiiDbException(
                "Redis error: NOPERM this user has no permissions to run the 'cluster|info' command\nRedis command was: CLUSTER INFO",
            ),
        ];
        yield 'transport ambiguity' => [
            static fn(): mixed => throw new RuntimeException('connection reset'),
        ];
        yield 'proxy response' => [
            static fn(): string => 'OK',
        ];
        yield 'unrecognized result type' => [
            static fn(): bool => true,
        ];
    }

    public function testOnlyExactDisabledResponseClassifiesStandalone(): void
    {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => true,
                'CLUSTER' => throw new YiiDbException(
                    "Redis error: ERR This instance has cluster support disabled.\nRedis command was: CLUSTER INFO",
                ),
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection());

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED, $result->outcome);
        self::assertSame([
            ['PING', []],
            ['CLUSTER', ['INFO']],
        ], $diagnostic->commands);
    }

    public function testPartialSelectFailurePreservesAvailabilityAndCompletedRows(): void
    {
        $selectedDatabase = null;
        $diagnostic = new RecordingRedisConnection(
            static function(string $command, array $params) use (&$selectedDatabase): mixed {
                return match ($command) {
                    'PING' => true,
                    'CLUSTER' => throw new YiiDbException(self::CLUSTER_DISABLED_EXCEPTION),
                    'SELECT' => (int)$params[0] === 1
                        ? throw new RuntimeException('SELECT is disabled')
                        : $selectedDatabase = (int)$params[0],
                    'DBSIZE' => $selectedDatabase === 0
                        ? '8'
                        : throw new RuntimeException("Unexpected selected database"),
                    default => throw new RuntimeException("Unexpected command: {$command}"),
                };
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection(), 0, 2);

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_SELECT_FAILED, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE, $result->topology);
        self::assertTrue($result->enumerationAvailable);
        self::assertFalse($result->enumerationComplete);
        self::assertSame([
            ['database' => 0, 'keyCount' => 8],
        ], $result->databases);
        self::assertSame([
            ['PING', []],
            ['CLUSTER', ['INFO']],
            ['SELECT', [0]],
            ['DBSIZE', []],
            ['SELECT', [1]],
        ], $diagnostic->commands);
    }

    public function testDbsizeFailureReturnsOnlyCompletedCounts(): void
    {
        $selectedDatabase = 0;
        $diagnostic = new RecordingRedisConnection(
            static function(string $command, array $params) use (&$selectedDatabase): mixed {
                if ($command === 'PING') {
                    return true;
                }
                if ($command === 'CLUSTER') {
                    throw new YiiDbException(self::CLUSTER_DISABLED_EXCEPTION);
                }
                if ($command === 'SELECT') {
                    $selectedDatabase = (int)$params[0];

                    return true;
                }
                if ($command === 'DBSIZE' && $selectedDatabase === 0) {
                    return '8';
                }
                if ($command === 'DBSIZE') {
                    throw new RuntimeException('DBSIZE failed');
                }

                throw new RuntimeException("Unexpected command: {$command}");
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection(), 0, 2);

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_DBSIZE_FAILED, $result->outcome);
        self::assertTrue($result->enumerationAvailable);
        self::assertFalse($result->enumerationComplete);
        self::assertSame([
            ['database' => 0, 'keyCount' => 8],
        ], $result->databases);
    }

    #[DataProvider('sourceDatabaseProvider')]
    public function testSourceDatabaseNormalization(
        mixed $value,
        bool $supported,
        ?int $normalized,
    ): void {
        $source = new Connection();
        $source->database = $value;
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => true,
                'CLUSTER' => "cluster_state:ok\r\n",
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect($source);

        self::assertSame(
            $supported
                ? RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED
                : RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE,
            $result->outcome,
        );
        self::assertSame($supported, $result->hasSourceConfiguredDatabase);
        self::assertSame($normalized, $result->sourceConfiguredDatabase);
        self::assertSame(
            $supported
                ? [['PING', []], ['CLUSTER', ['INFO']]]
                : [],
            $diagnostic->commands,
        );
    }

    /**
     * @return iterable<string, array{mixed, bool, ?int}>
     */
    public static function sourceDatabaseProvider(): iterable
    {
        yield 'integer zero' => [0, true, 0];
        yield 'positive integer' => [12, true, 12];
        yield 'digits string' => ['12', true, 12];
        yield 'leading-zero digits string' => ['00012', true, 12];
        yield 'null disables automatic select' => [null, true, null];
        yield 'boolean true' => [true, false, null];
        yield 'boolean false' => [false, false, null];
        yield 'float' => [1.0, false, null];
        yield 'negative integer' => [-1, false, null];
        yield 'negative string' => ['-1', false, null];
        yield 'positive sign' => ['+1', false, null];
        yield 'leading whitespace' => [' 1', false, null];
        yield 'trailing whitespace' => ['1 ', false, null];
        yield 'empty string' => ['', false, null];
        yield 'overflow string' => [(string)PHP_INT_MAX . '0', false, null];
    }

    public function testPublicNamespaceAndInspectSignatureAreExact(): void
    {
        $reflection = new \ReflectionClass(RedisDatabaseDiagnostics::class);
        $method = $reflection->getMethod('inspect');
        $parameters = $method->getParameters();
        $returnType = $method->getReturnType();
        $sourceType = $parameters[0]->getType();
        $fromType = $parameters[1]->getType();
        $toType = $parameters[2]->getType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertInstanceOf(\ReflectionNamedType::class, $sourceType);
        self::assertInstanceOf(\ReflectionNamedType::class, $fromType);
        self::assertInstanceOf(\ReflectionNamedType::class, $toType);

        self::assertSame(
            'lindemannrock\base\services\RedisDatabaseDiagnostics',
            $reflection->getName(),
        );
        self::assertSame(
            'lindemannrock\base\services\RedisDatabaseDiagnosticsResult',
            $returnType->getName(),
        );
        self::assertSame(
            ['source', 'fromDatabase', 'toDatabase'],
            array_map(
                static fn(\ReflectionParameter $parameter): string => $parameter->getName(),
                $parameters,
            ),
        );
        self::assertSame('yii\redis\Connection', $sourceType->getName());
        self::assertSame('int', $fromType->getName());
        self::assertSame(0, $parameters[1]->getDefaultValue());
        self::assertSame('int', $toType->getName());
        self::assertSame(15, $parameters[2]->getDefaultValue());
        self::assertSame(
            ['inspect'],
            array_values(array_map(
                static fn(\ReflectionMethod $publicMethod): string => $publicMethod->getName(),
                $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            )),
        );
    }

    public function testRequestedRangeContainsCompleteFrozenMetadata(): void
    {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => true,
                'CLUSTER' => "cluster_state:ok\r\n",
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection(), 4, 67);

        self::assertSame([
            'from' => 4,
            'to' => 67,
            'databaseCount' => 64,
            'maximumDatabaseCount' => 64,
        ], $result->requestedRange);
    }

    public function testInvalidRangeReturnsNullRequestedRangeBeforeConnectionConstruction(): void
    {
        $factoryCalled = false;
        $runner = new TestRedisDatabaseDiagnostics(
            static function(Connection $source) use (&$factoryCalled): Connection {
                $factoryCalled = true;

                return new Connection();
            },
        );

        $result = $runner->inspect(new Connection(), 0, 64);

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST, $result->outcome);
        self::assertNull($result->requestedRange);
        self::assertFalse($factoryCalled);
    }

    public function testUnsupportedSourceDatabaseDoesNotExposeRawValueOrOpenConnection(): void
    {
        $factoryCalled = false;
        $source = new Connection([
            'hostname' => 'private.redis.internal',
            'username' => 'secret-user',
            'password' => 'secret-password',
        ]);
        $runner = new TestRedisDatabaseDiagnostics(
            static function(Connection $connection) use (&$factoryCalled): Connection {
                $factoryCalled = true;

                return new Connection();
            },
        );
        (new \ReflectionProperty(Connection::class, 'database'))->setValue($source, ' secret-value ');

        $result = $runner->inspect($source);
        $json = json_encode($result->toArray(), JSON_THROW_ON_ERROR);

        self::assertFalse($factoryCalled);
        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED, $result->topology);
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED, $result->ping);
        self::assertArrayNotHasKey('sourceConfiguredDatabase', $result->toArray());
        self::assertStringNotContainsString('secret-value', $json);
        self::assertStringNotContainsString('secret-user', $json);
        self::assertStringNotContainsString('secret-password', $json);
        self::assertStringNotContainsString('private.redis.internal', $json);
    }

    public function testPrePingConnectionConstructionFailureIsNotAttemptedAndCredentialSafe(): void
    {
        $source = new Connection([
            'hostname' => 'private.redis.internal',
            'username' => 'secret-user',
            'password' => 'secret-password',
            'database' => null,
        ]);
        $runner = new TestRedisDatabaseDiagnostics(
            static fn(Connection $connection): Connection => throw new RuntimeException(
                'Failed at redis://secret-user:secret-password@private.redis.internal',
            ),
        );

        $result = $runner->inspect($source);
        $json = json_encode($result->toArray(), JSON_THROW_ON_ERROR);

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED, $result->ping);
        self::assertNull($result->sourceConfiguredDatabase);
        self::assertSame(null, $result->toArray()['sourceConfiguredDatabase']);
        self::assertStringNotContainsString('secret-user', $json);
        self::assertStringNotContainsString('secret-password', $json);
        self::assertStringNotContainsString('private.redis.internal', $json);
    }

    public function testUnrecognizedPingResponseStopsBeforeTopologyProbe(): void
    {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => 'proxy-pong',
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection());

        self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE, $result->outcome);
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_FAILED, $result->ping);
        self::assertSame([
            ['PING', []],
        ], $diagnostic->commands);
    }

    public function testPingExceptionIsFailedAndClosesDiagnosticConnection(): void
    {
        $diagnostic = new RecordingRedisConnection(
            static fn(string $command): mixed => match ($command) {
                'PING' => throw new RuntimeException('open failed'),
                default => throw new RuntimeException("Unexpected command: {$command}"),
            },
        );

        $result = $this->runner($diagnostic)->inspect(new Connection());

        self::assertSame(
            RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE,
            $result->outcome,
        );
        self::assertSame(RedisDatabaseDiagnosticsResult::PING_FAILED, $result->ping);
        self::assertSame([['PING', []]], $diagnostic->commands);
        self::assertTrue($diagnostic->closed);
    }

    public function testRealRedisUsesDistinctClientAndLeavesSourceUsable(): void
    {
        $source = new Connection(array_merge($this->localRedisConnectionConfig(), [
            'socketClientFlags' => STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
        ]));
        $diagnosticClientId = null;

        try {
            try {
                $source->open();
                $sourceClientId = (string)$source->executeCommand('CLIENT ID');
            } catch (Throwable) {
                $source->close();
                self::markTestSkipped('The configured test Redis endpoint is unavailable.');
            }

            $sourceDatabase = $source->database;
            $runner = new TestRedisDatabaseDiagnostics(
                static function(Connection $connection) use (&$diagnosticClientId): Connection {
                    $diagnostic = YiiRedisConnectionHelper::createIndependentConnection(
                        $connection,
                        null,
                        0,
                    );
                    $diagnostic->on(
                        Connection::EVENT_AFTER_OPEN,
                        static function() use ($diagnostic, &$diagnosticClientId): void {
                            $diagnosticClientId = (string)$diagnostic->executeCommand('CLIENT ID');
                        },
                    );

                    return $diagnostic;
                },
            );

            $result = $runner->inspect($source, 0, 0);

            self::assertSame(RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE, $result->outcome);
            self::assertNotNull($diagnosticClientId);
            self::assertNotSame($sourceClientId, $diagnosticClientId);
            self::assertSame($sourceClientId, (string)$source->executeCommand('CLIENT ID'));
            self::assertNotFalse($source->executeCommand('PING'));
            self::assertSame($sourceDatabase, $source->database);
        } finally {
            try {
                $source->close();
            } catch (Throwable) {
                // Best-effort connection cleanup after a failed assertion.
            }
        }
    }

    private function runner(RecordingRedisConnection $diagnostic): RedisDatabaseDiagnostics
    {
        return new TestRedisDatabaseDiagnostics(
            static fn(Connection $connection): Connection => $diagnostic,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function localRedisConnectionConfig(): array
    {
        return [
            'hostname' => App::env('REDIS_HOST') ?: 'redis',
            'port' => App::env('REDIS_PORT') ?: 6379,
            'username' => App::env('REDIS_USERNAME') ?: null,
            'password' => App::env('REDIS_PASSWORD') ?: null,
            'database' => (int)(App::env('REDIS_DATABASE') ?: 0),
            'connectionTimeout' => 1,
            'dataTimeout' => 1,
        ];
    }
}

/**
 * Injects an independently owned diagnostic connection through the protected
 * service seam without expanding the public production API.
 */
final class TestRedisDatabaseDiagnostics extends RedisDatabaseDiagnostics
{
    /**
     * @var \Closure(Connection): Connection
     */
    private \Closure $factory;

    /**
     * @param callable(Connection): Connection $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = \Closure::fromCallable($factory);
    }

    protected function createDiagnosticConnection(Connection $source): Connection
    {
        return ($this->factory)($source);
    }
}

/**
 * Test double that records diagnostic commands without opening a socket.
 */
final class RecordingRedisConnection extends Connection
{
    /**
     * @var list<array{string, array<int, mixed>}>
     */
    public array $commands = [];

    public bool $closed = false;

    /**
     * @var \Closure(string, array<int, mixed>): mixed
     */
    private \Closure $handler;

    /**
     * @param callable(string, array<int, mixed>): mixed $handler
     */
    public function __construct(callable $handler)
    {
        parent::__construct();
        $this->handler = \Closure::fromCallable($handler);
    }

    /**
     * @param array<int, mixed> $params
     */
    public function executeCommand(string $name, array $params = []): mixed
    {
        $command = strtoupper($name);
        $this->commands[] = [$command, $params];

        return ($this->handler)($command, $params);
    }

    public function close(): void
    {
        $this->closed = true;
    }
}
