# Console Commands

Base exposes a bounded, read-only Redis diagnostics command. It uses Craft's
effective Yii Redis cache configuration while keeping its socket and selected
database isolated from Craft.

The Base module must already be registered by a consuming plugin. The command
does not require a separate Base installation.

## Console Help @since(5.37.0)

List Base commands or focus the Redis database command:

```bash title="PHP"
php craft lindemannrock-base/help [command]
```

```bash title="DDEV"
ddev craft lindemannrock-base/help [command]
```

Omit `[command]` for Base's manifest-backed catalog, or use `redis/databases`
for the focused Base entry. Craft's native exact-command help is also available:

```bash title="PHP"
php craft help lindemannrock-base/redis/databases
```

```bash title="DDEV"
ddev craft help lindemannrock-base/redis/databases
```

## Redis Database Diagnostics @since(5.37.0)

Run the default database range, DB 0 through DB 15:

```bash title="PHP"
php craft lindemannrock-base/redis/databases
```

```bash title="DDEV"
ddev craft lindemannrock-base/redis/databases
```

The human-readable result is a point-in-time key count:

```text
Redis connection: Craft cache
Topology: Standalone
Source configured database: 0
Automatic database selection: none
SELECT issued on open: no
Database enumeration: Available
Enumeration completion: Complete
Diagnostic range: DB 0 to DB 2

Point-in-time Redis database key counts:
DB 0: 11,280 keys
DB 1: 1 key
DB 2: 0 keys

This is a point-in-time key-count diagnostic for Craft's configured Redis-cache endpoint.
Other plugin Redis endpoints are not inspected.
Database ownership must be confirmed with the hosting provider.
```

Zero means that `DBSIZE` returned no keys at that instant. It does not mean the
database is available, unused, unreserved, or safe to claim. Confirm database
ownership with the hosting provider.

### Choose a bounded range

Use `--from` and `--to` to select an inclusive range:

```bash title="PHP"
php craft lindemannrock-base/redis/databases --from=4 --to=7
```

```bash title="DDEV"
ddev craft lindemannrock-base/redis/databases --from=4 --to=7
```

Both values must be non-negative whole numbers, `from` must be less than or
equal to `to`, and the inclusive size must satisfy:

```text
to - from + 1 <= 64
```

The 0–15 default is deliberately bounded. The command never discovers or
expands the range automatically.

### JSON output

Use JSON for monitoring or automation:

```bash title="PHP"
php craft lindemannrock-base/redis/databases --from=0 --to=2 --format=json
```

```bash title="DDEV"
ddev craft lindemannrock-base/redis/databases --from=0 --to=2 --format=json
```

JSON keys and enum values are stable English tokens and are never translated:

```json
{
  "schemaVersion": 1,
  "scope": "craft-redis-cache-endpoint",
  "outcome": "complete",
  "topology": "standalone",
  "sourceConfiguredDatabase": 0,
  "automaticDatabaseSelection": null,
  "selectIssuedOnOpen": false,
  "ping": "ok",
  "enumerationAvailable": true,
  "enumerationComplete": true,
  "requestedRange": {
    "from": 0,
    "to": 2,
    "databaseCount": 3,
    "maximumDatabaseCount": 64
  },
  "databases": [
    {
      "database": 0,
      "keyCount": 11280
    },
    {
      "database": 1,
      "keyCount": 1
    },
    {
      "database": 2,
      "keyCount": 0
    }
  ]
}
```

JSON v1 contains only these fields:

| Field | Contract |
|---|---|
| `schemaVersion` | Integer `1` |
| `scope` | `craft-redis-cache-endpoint` |
| `outcome` | See the complete enum set below |
| `topology` | `not-checked`, `standalone`, `cluster`, `unknown` |
| `sourceConfiguredDatabase` | Validated non-negative integer or `null`; omitted until normalization succeeds |
| `automaticDatabaseSelection` | Always `null` |
| `selectIssuedOnOpen` | Always `false` |
| `ping` | `not-attempted`, `ok`, `failed` |
| `enumerationAvailable` | Boolean |
| `enumerationComplete` | Boolean |
| `requestedRange` | `{from, to, databaseCount, maximumDatabaseCount}` after validation; otherwise `null`. `maximumDatabaseCount` is always `64` |
| `databases` | Completed `{database, keyCount}` rows |

The complete `outcome` enum is:

- `complete`
- `invalid-request`
- `unsupported-cache`
- `unsupported-connection`
- `unsupported-source-database`
- `diagnostic-connection-unavailable`
- `cluster-detected`
- `topology-undetermined`
- `select-failed`
- `dbsize-failed`

JSON v1 does not add overlapping result, reason, evidence, notice, or
connection-classification fields.

`sourceConfiguredDatabase: null` means Yii sends no automatic `SELECT` when
opening the source connection; Redis starts a new connection on DB 0 by
default.

`automaticDatabaseSelection` and `selectIssuedOnOpen` describe the independent
diagnostic connection at open time. Later enumeration deliberately changes
that connection's selected database.

### Topology and command safety

After a successful `PING`, the diagnostic connection runs `CLUSTER INFO`.

- A recognized successful cluster-info response identifies Cluster.
- The exact Redis `ERR This instance has cluster support disabled` response
  identifies Standalone.
- ACL denial, transport ambiguity, proxy responses, and unrecognized results
  leave topology Unknown.
Cluster and Unknown never run `SELECT` or `DBSIZE`. The command does not infer
Standalone from a generic exception or message fragment.

On a confirmed standalone endpoint, each requested database uses:

1. `SELECT <database>`
2. `DBSIZE`

The command never uses `KEYS`, `SCAN`, `SET`, `DEL`, `FLUSHDB`, or `FLUSHALL`.
It creates no cleanup keys or other Redis state.

Once Standalone is confirmed, `enumerationAvailable` remains `true`. A later
`SELECT` or `DBSIZE` failure sets `enumerationComplete` to `false` and retains
every database row completed before the failure.

### Connection and credential safety

The command inspects only Craft's configured Redis-cache endpoint. Redis
endpoints configured directly by Logging Library, Search Manager, another
plugin, or project code are outside its visibility.

The independent connection:

- Preserves the supported host, socket, authentication, TLS, timeout, and
  command configuration from Craft's Yii Redis connection.
- Disables Yii's automatic database selection by setting its database to
  `null`.
- Uses zero command retries for this diagnostic workflow.
- Removes `STREAM_CLIENT_PERSISTENT` so Craft's transport cannot be reused.
- Never opens, selects on, closes, or changes Craft's source connection.

Passwords, usernames, endpoint URLs, rejected database values, and raw
transport exceptions are never written to human or JSON output. Yii may still
record normal connection debug/error events according to the application's
logging configuration.

If the independent connection cannot be constructed, `ping` is
`not-attempted`. If opening the connection or `PING` fails, or the PING response
is not recognized, `ping` is `failed`.

### Supported source database values

The source Yii connection's configured database accepts:

- A non-negative integer.
- A digits-only string representable as a PHP integer.
- `null`, meaning Yii issues no automatic `SELECT`.

Booleans, floats, negative values, empty strings, signed or whitespace-padded
strings, and integer overflows are rejected as unsupported configuration. The
raw rejected value is never printed.

### Exit codes

| Code | Yii name | Meaning |
|---:|---|---|
| `0` | `OK` | The requested range completed. |
| `64` | `USAGE` | The requested range or output format is invalid. |
| `69` | `UNAVAILABLE` | The diagnostic connection or `PING` failed. |
| `76` | `PROTOCOL` | Cluster, unknown topology, `SELECT`, or `DBSIZE` prevented completion. |
| `78` | `CONFIG` | Craft is not using a supported Yii Redis cache/connection, or the source database value is unsupported. |

Treat nonzero exit codes as incomplete diagnostics, even when the output still
contains topology or completed database rows.

## Related

- [YiiRedisConnectionHelper](../feature-tour/yii-redis-connection-helper.md) —
  the connection isolation contract used by the command
- [API Reference](api-reference.md) — public diagnostics service and result
- [Troubleshooting](../resources/troubleshooting.md) — general Base integration
  checks
