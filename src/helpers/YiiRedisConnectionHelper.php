<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use yii\redis\Connection;

/**
 * Creates independently owned Yii Redis connections from supported source
 * configuration.
 *
 * @since 5.37.0
 */
final class YiiRedisConnectionHelper
{
    /**
     * Connection configuration that is safe and meaningful to copy.
     *
     * Yii's redirectConnectionString is intentionally absent because it is
     * transient MOVED redirection state rather than source configuration.
     *
     * @var list<string>
     */
    private const CONNECTION_PROPERTIES = [
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
    ];

    /**
     * Build an unopened Yii Redis connection without sharing source transport
     * state.
     *
     * The caller owns the returned connection and must close it. Passing null
     * for $database disables Yii's automatic SELECT on open. Passing null for
     * $retries preserves the source retry count; any integer is an explicit
     * caller-owned retry policy.
     */
    public static function createIndependentConnection(
        Connection $source,
        ?int $database,
        ?int $retries = null,
    ): Connection {
        $config = [];
        foreach (self::CONNECTION_PROPERTIES as $property) {
            $config[$property] = $source->{$property};
        }

        $config['database'] = $database;
        $config['retries'] = $retries ?? $source->retries;
        $config['socketClientFlags'] = (
            (int)$source->socketClientFlags & ~STREAM_CLIENT_PERSISTENT
        ) | STREAM_CLIENT_CONNECT;

        return new Connection($config);
    }
}
