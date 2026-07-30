# YiiRedisConnectionHelper @since(5.37.0)

Use `YiiRedisConnectionHelper` when a service needs its own
`yii\redis\Connection` based on an existing Yii Redis configuration.

The returned connection is unopened and independently owned. It never shares a
persistent transport with the source connection, so the caller can select a
database, issue commands, and close its connection without changing or closing
Craft's Redis connection.

```php
use lindemannrock\base\helpers\YiiRedisConnectionHelper;

$connection = YiiRedisConnectionHelper::createIndependentConnection(
    $sourceConnection,
    database: 3,
);

try {
    $connection->open();
    // Run service-owned Redis operations.
} finally {
    $connection->close();
}
```

## Method

```php
public static function createIndependentConnection(
    Connection $source,
    ?int $database,
    ?int $retries = null,
): Connection
```

`$database` is always explicit:

- Pass a non-negative database number when the caller owns database selection.
- Pass `null` to disable Yii's automatic `SELECT` command on open. Redis starts
  a new connection on database 0 by default, but later commands can change the
  selected database.

`$retries` makes command-retry policy visible to the caller:

- Omit it or pass `null` to preserve the source connection's retry count.
- Pass an integer, including `0`, when the operation needs a specific retry
  policy.

The helper does not decide whether retries are safe for a transaction or
multi-command workflow. That policy remains with the consuming service.

## Copied Configuration

The helper copies this explicit allowlist:

- Hostname, scheme, port, and Unix socket
- Username and password
- TLS/SSL state and stream context options
- Connection and data timeouts
- Retry interval and Redis command configuration

It also preserves the source socket flags except that it removes
`STREAM_CLIENT_PERSISTENT` and adds `STREAM_CLIENT_CONNECT`.

## Deliberately Not Copied

The helper does not copy:

- Open sockets or Yii's internal connection pool
- Active connection state
- Events or behaviors attached to the source object
- `redirectConnectionString`, which Yii uses as transient Redis `MOVED`
  redirection state
- The source database or retry count when the caller supplies replacements

Creating the independent connection does not open, select, mutate, or close the
source connection. The caller owns the returned connection's complete
lifecycle.

## Scope

This helper provides safe connection construction only. It does not detect
Redis topology, enumerate logical databases, run diagnostics, normalize
configuration values, or define plugin-specific storage and retry policy.

## Related

- [Console Commands](../developers/console-commands.md) — Base's bounded,
  read-only Redis database diagnostics
- [CacheHelper](cache-helper.md) — bounded cleanup for plugin-owned Redis
  tracking sets
- [PluginHelper](plugin-helper.md) — Craft cache component resolution and
  plugin cache naming
- [API Reference](../developers/api-reference.md) — public method signature
