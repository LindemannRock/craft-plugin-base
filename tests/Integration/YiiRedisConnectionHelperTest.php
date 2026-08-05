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
use lindemannrock\base\testing\IntegrationTestCase;
use Throwable;
use yii\redis\Connection;

/**
 * @since 5.37.0
 */
final class YiiRedisConnectionHelperTest extends IntegrationTestCase
{
    public function testCreatesIndependentConnectionFromSupportedConfiguration(): void
    {
        $source = new Connection([
            'hostname' => 'redis.internal',
            'scheme' => 'tls',
            'redirectConnectionString' => 'tcp://redirect.internal:6380',
            'port' => 6380,
            'unixSocket' => '/tmp/redis.sock',
            'username' => 'redis-user',
            'password' => 'redis-password',
            'database' => 2,
            'connectionTimeout' => 1.25,
            'dataTimeout' => 2.5,
            'useSSL' => true,
            'contextOptions' => ['ssl' => ['verify_peer' => true]],
            'socketClientFlags' => STREAM_CLIENT_PERSISTENT | STREAM_CLIENT_ASYNC_CONNECT,
            'retries' => 3,
            'retryInterval' => 25000,
            'redisCommands' => ['PING', 'SELECT', 'DBSIZE'],
        ]);

        $independent = YiiRedisConnectionHelper::createIndependentConnection($source, 7, 0);

        foreach ([
            'hostname',
            'scheme',
            'port',
            'unixSocket',
            'username',
            'password',
            'connectionTimeout',
            'dataTimeout',
            'useSSL',
            'contextOptions',
            'retryInterval',
            'redisCommands',
        ] as $property) {
            self::assertSame($source->{$property}, $independent->{$property}, $property);
        }

        self::assertNull($independent->redirectConnectionString);
        self::assertSame(
            STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT,
            $independent->socketClientFlags,
        );
        self::assertSame(0, $independent->socketClientFlags & STREAM_CLIENT_PERSISTENT);
        self::assertSame(0, $independent->retries);
        self::assertSame(7, $independent->database);
        self::assertFalse($independent->isActive);
        self::assertNotSame($source, $independent);

        self::assertSame('tcp://redirect.internal:6380', $source->redirectConnectionString);
        self::assertSame(STREAM_CLIENT_PERSISTENT | STREAM_CLIENT_ASYNC_CONNECT, $source->socketClientFlags);
        self::assertSame(3, $source->retries);
        self::assertSame(2, $source->database);
        self::assertFalse($source->isActive);
    }

    public function testNullDatabaseDisablesAutomaticSelectAndPreservesRetries(): void
    {
        $source = new Connection([
            'database' => 5,
            'retries' => 4,
        ]);

        $independent = YiiRedisConnectionHelper::createIndependentConnection($source, null);

        self::assertNull($independent->database);
        self::assertSame(4, $independent->retries);
        self::assertSame(5, $source->database);
        self::assertFalse($source->isActive);
        self::assertFalse($independent->isActive);
    }

    public function testRealRedisUsesDistinctTransportAndLeavesSourceUsable(): void
    {
        $source = new Connection(array_merge($this->localRedisConnectionConfig(), [
            'socketClientFlags' => STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
        ]));
        $independent = null;

        try {
            try {
                $source->open();
                $sourceClientId = (string)$source->executeCommand('CLIENT ID');
            } catch (Throwable) {
                $source->close();
                self::markTestSkipped('The configured test Redis endpoint is unavailable.');
            }

            $sourceDatabase = $source->database;
            $independent = YiiRedisConnectionHelper::createIndependentConnection(
                $source,
                $sourceDatabase,
            );
            $independent->open();
            $independentClientId = (string)$independent->executeCommand('CLIENT ID');

            self::assertNotSame($sourceClientId, $independentClientId);
            self::assertSame(0, $independent->socketClientFlags & STREAM_CLIENT_PERSISTENT);
            self::assertNotFalse($source->executeCommand('PING'));
            self::assertSame($sourceClientId, (string)$source->executeCommand('CLIENT ID'));
            self::assertSame($sourceDatabase, $source->database);

            $independent->close();

            self::assertNotFalse($source->executeCommand('PING'));
            self::assertSame($sourceClientId, (string)$source->executeCommand('CLIENT ID'));
        } finally {
            try {
                $independent?->close();
            } catch (Throwable) {
                // Best-effort connection cleanup after a failed assertion.
            }

            try {
                $source->close();
            } catch (Throwable) {
                // Best-effort connection cleanup after a failed assertion.
            }
        }
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
