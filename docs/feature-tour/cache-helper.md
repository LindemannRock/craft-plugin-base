# CacheHelper @since(5.31.0)

Use `CacheHelper` when a plugin needs to clear local runtime cache files or a Redis tracking set without loading the whole cache list into memory.

It is intentionally low-level. The helper does not know about a plugin's settings model, cache toggles, controller actions, or whether a Redis key type has the same name as a local cache directory. Keep that mapping in the consuming plugin, then call the specific primitive you need.

```php
use lindemannrock\base\helpers\CacheHelper;
use lindemannrock\base\helpers\PluginHelper;
use myvendor\myplugin\MyPlugin;

// Redis tracked-set cleanup.
$deleted = CacheHelper::clearTrackedRedisKeys(
    MyPlugin::$plugin->id,
    'redirect',
);

// Local file cleanup.
$deleted = CacheHelper::clearCacheFiles(
    PluginHelper::getCachePath(MyPlugin::$plugin, 'redirects'),
);
```

## Redis Tracking Sets

`clearTrackedRedisKeys()` clears cache entries whose keys are stored in a plugin-owned Redis set:

```php
$deleted = CacheHelper::clearTrackedRedisKeys('my-plugin', 'device');
```

The method:

- Resolves the Redis cache component through `PluginHelper::getRedisCacheOrLog()`.
- Builds the tracking set name with `PluginHelper::getCacheKeySet()`.
- Scans the set with `SSCAN` in batches.
- Deletes each tracked cache entry through Craft's cache component.
- Deletes the tracking set after the scan finishes.
- Returns the number of cache entries deleted.

If Craft's cache component is not Redis, the helper logs through the existing Redis safeguard and returns `0`.

### Batch Size

The default scan batch size is `500`. Pass a different positive value only when a plugin has measured a need for it:

```php
CacheHelper::clearTrackedRedisKeys('my-plugin', 'qr', 1000);
```

## Local Cache Files

`clearCacheFiles()` streams a directory with `DirectoryIterator` and deletes only regular files with the matching suffix:

```php
$deleted = CacheHelper::clearCacheFiles(
    PluginHelper::getCachePath(MyPlugin::$plugin, 'qr'),
);
```

The default suffix is `.cache`. Missing directories return `0`, so callers can use the helper without a separate `is_dir()` guard.

Use `countCacheFiles()` for file-only dashboard counts:

```php
$count = CacheHelper::countCacheFiles(
    PluginHelper::getCachePath(MyPlugin::$plugin, 'device'),
);
```

This deliberately does not count Redis keys. Redis dashboards should stay cheap unless the consuming plugin has a clear product reason to show a count.

## Keep Plugin Mapping Local

Pass explicit inputs instead of assuming one cache type names every backend. A plugin may use one name for Redis keys and another for its local directory:

```php
private const REDIRECT_CACHE_KEY_TYPE = 'redirect';
private const REDIRECT_CACHE_DIRECTORY = 'redirects';

if ($settings->cacheStorageMethod === 'redis') {
    return CacheHelper::clearTrackedRedisKeys(
        MyPlugin::$plugin->id,
        self::REDIRECT_CACHE_KEY_TYPE,
    );
}

return CacheHelper::clearCacheFiles(
    PluginHelper::getCachePath(MyPlugin::$plugin, self::REDIRECT_CACHE_DIRECTORY),
);
```

That keeps base focused on safe mechanics while each plugin owns its own cache naming, settings, and UI behavior.

## Related

- [PluginHelper](plugin-helper.md) - cache paths, Redis key prefixes, tracking set names, and Redis component safeguards
- [Testing](testing.md) - use `cleanupExternalState()` when integration tests create Redis keys or local cache files
