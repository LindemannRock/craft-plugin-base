# Scoped cache @since(5.38.0)

Store disposable plugin data in Craft's application cache without flushing, scanning, or maintaining a registry of shared keys. `ScopedCache` isolates values by Craft application, plugin, cache family, and optional owner scope, then invalidates them by advancing generation tokens.

## Create a cache family

Pass the exact `CacheInterface` accepted by your storage decision, plus stable plugin and family names:

```php
use lindemannrock\base\cache\ScopedCache;

$cache = new ScopedCache(
    cache: $decision->applicationCache,
    pluginHandle: 'my-plugin',
    family: 'statistics',
);
```

Plugin handles and family names must be non-empty namespace segments containing letters, numbers, dots, underscores, or hyphens. The Craft application ID is hashed into the namespace so two applications sharing a backend do not share values.

## Read and write values

Every write needs a finite positive TTL:

```php
$cache->set(
    itemIdentity: ['report' => 'monthly', 'siteId' => 2],
    value: $statistics,
    ttl: 3600,
    scopeIdentity: ['ownerId' => 42],
);

$result = $cache->get(
    itemIdentity: ['siteId' => 2, 'report' => 'monthly'],
    scopeIdentity: ['ownerId' => 42],
);

if ($result->isHit()) {
    $statistics = $result->value;
} elseif ($result->isFailure()) {
    // Recompute and keep the request working.
}
```

String and nested array identities are supported. Associative-array key order does not change the resolved key, and raw identity values are hashed rather than embedded in cache keys. Identity arrays may contain only arrays and scalar values.

`ScopedCacheResult` distinguishes three states:

| State | Meaning |
|---|---|
| `hit` | A valid wrapped value was found. The value itself may be `false` or `null`. |
| `miss` | No current-generation value exists. |
| `failure` | The backend failed or returned malformed Base-owned data. Recompute instead of treating this as an authoritative miss. |

Backend exceptions and false write/delete results fail softly through result states or boolean return values.

## Invalidate without enumeration

Advance a generation token instead of finding and deleting every family key:

```php
$cache->invalidateScope(['ownerId' => 42]);
$cache->invalidateFamily();
```

Scope invalidation hides only values for that scope. Family invalidation hides every value in the family while preserving other families and plugins on the shared backend. Old values remain unreachable until their finite TTL expires.

Use `delete($itemIdentity, $scopeIdentity)` only for one exact item. `ScopedCache` never calls `flush()`, Redis key enumeration, or raw Redis commands.

## API reference

| Method | Returns | Purpose |
|---|---|---|
| `get(string|array $itemIdentity, string|array|null $scopeIdentity = null)` | `ScopedCacheResult` | Read one current-generation value. |
| `set(string|array $itemIdentity, mixed $value, int $ttl, string|array|null $scopeIdentity = null)` | `bool` | Store one value with a finite TTL. |
| `delete(string|array $itemIdentity, string|array|null $scopeIdentity = null)` | `bool` | Delete one exact current-generation value. |
| `invalidateFamily()` | `bool` | Advance the family generation. |
| `invalidateScope(string|array $scopeIdentity)` | `bool` | Advance one scope generation. |
| `status()` | `CacheBackendStatus` | Classify the injected cache component. |

`ScopedCacheResult::hit()`, `miss()`, and `failure()` construct explicit result states; `isHit()`, `isMiss()`, and `isFailure()` inspect them.

## Gotchas

- Use stable family and scope identities. Renaming either creates a new namespace rather than migrating values.
- Never use a zero, negative, or unbounded TTL. Such writes return `false`.
- Cache only recomputable data. Generation invalidation deliberately does not prove that old backend keys were physically deleted.
- Preserve the distinction between `miss` and `failure` when operational behavior or diagnostics depend on backend health.

## Next steps

- [Disposable cache storage](disposable-cache-storage.md) — choose a suitable backend for the current host
- [CacheHelper](cache-helper.md) — clean up legacy tracked Redis/file cache implementations
- [Device detection](device-detection.md) — see a Base consumer of the scoped application-cache contract
