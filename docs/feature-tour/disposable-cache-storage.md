# Disposable cache storage @since(5.38.0)

Keep recomputable plugin data available across requests without assuming that every host has a durable local filesystem or Redis. Base resolves the configured storage choice against Craft's effective application cache and provides one semantic presentation model for settings and utility screens.

Use this family for derived data that can be rebuilt. Durable records, ownership state, audit history, recovery metadata, and queue state do not belong in a disposable cache.

## Resolve the effective backend

Pass the consumer's saved token and diagnostic context to `DisposableCacheStorageResolver`:

```php
use lindemannrock\base\cache\DisposableCacheStorageResolver;

$resolver = new DisposableCacheStorageResolver();
$decision = $resolver->resolve(
    configuredStorageToken: $settings->cacheStorageMethod,
    diagnosticContext: 'my-plugin:statistics-cache',
);

if ($decision->usesApplicationCache()) {
    $cache = $decision->applicationCache;
} elseif ($decision->usesFileCache()) {
    // Use the consumer-owned durable file path.
} else {
    // Recompute instead of caching.
}
```

The default token policy treats `file` as file storage and `redis` or `craft` as application-cache choices. Consumers with established token names can pass their own `fileTokens` and `applicationTokens` lists.

| Configured choice | Durable host | Ephemeral host |
|---|---|---|
| File token | Consumer-owned file cache | Suitable Craft application cache, otherwise disabled |
| Application token | Suitable Craft application cache | Suitable Craft application cache |
| Unknown token | Disabled | Disabled |

The resolver calls `PluginHelper::getApplicationCacheOrLog()` only when application storage is needed. The accepted cache instance is carried on the decision, so consumers do not resolve it a second time.

## Backend capability model

`CacheBackendStatus::fromCache()` classifies Craft's exposed cache component without inspecting hidden or wrapped layers.

| Backend | Cross-request persistence | Suitable on an ephemeral host |
|---|---:|---:|
| Redis | confirmed | yes |
| Craft Cascade Cache | unknown | yes, best effort |
| Database | confirmed | yes |
| Filesystem | confirmed | no |
| Array/memory | no | no |
| Unknown component | unknown | unknown, best effort |
| Missing component | no | no |

`supportsCrossRequest($ephemeral)` returns the capability decision. A suitable unknown component remains best effort rather than being described as confirmed persistent storage.

## Present configured and effective state

`DisposableCacheStoragePresenter` maps a decision to translation keys and semantic severities. Render that model through the shared field and status templates instead of duplicating backend labels or ephemeral-host rules:

```php
use lindemannrock\base\cache\DisposableCacheStoragePresenter;

$presenter = new DisposableCacheStoragePresenter();
$fileDecision = $resolver->resolve('file', 'my-plugin:cache-settings');
$applicationToken = DisposableCacheStorageResolver::applicationOptionToken(
    $settings->cacheStorageMethod,
);
$applicationDecision = $resolver->resolve($applicationToken, 'my-plugin:cache-settings');

return $this->renderTemplate('my-plugin/settings/cache', [
    'filePresentation' => $presenter->present($fileDecision),
    'applicationPresentation' => $presenter->present($applicationDecision),
    'applicationOptionToken' => $applicationToken,
]);
```

The settings template can then include:

```twig
{% include 'lindemannrock-base/_partials/field-cache-storage' with {
    settings: settings,
    pluginHandle: 'my-plugin',
    configuredStorageToken: settings.cacheStorageMethod,
    filePresentation: filePresentation,
    applicationPresentation: applicationPresentation,
    applicationOptionToken: applicationOptionToken,
    filePath: cachePath,
} only %}
```

The field keeps the saved choice visible while the status panel explains the effective backend, automatic fallback, or disabled/recompute state. File paths render only when file storage is actually eligible.

## API reference

| Class / method | Purpose |
|---|---|
| `CacheBackendStatus::fromCache(?CacheInterface $cache)` | Classify the exposed cache component. |
| `CacheBackendStatus::supportsCrossRequest(bool $ephemeral)` | Decide whether the component can support the requested host lifecycle. |
| `DisposableCacheStorageResolver::resolve(...)` | Resolve a configured token to application, file, or disabled storage. |
| `DisposableCacheStorageResolver::applicationOptionToken(...)` | Preserve a compatible application token, otherwise return the preferred token. |
| `DisposableCacheStorageDecision::usesApplicationCache()` | Whether the effective choice is Craft's application cache. |
| `DisposableCacheStorageDecision::usesFileCache()` | Whether the effective choice is consumer-owned file storage. |
| `DisposableCacheStorageDecision::isDisabled()` | Whether the consumer must recompute instead of caching. |
| `DisposableCacheStoragePresenter::present(...)` | Build semantic Base translation keys and severities for UI rendering. |

Use [Scoped cache](scoped-cache.md) for backend-neutral value storage and generation-based invalidation after the resolver selects an application cache.

## Gotchas

- Do not treat a saved File choice as permission to access a runtime directory on an ephemeral host.
- Do not invent a local-file fallback when the application cache is unavailable or unsuitable. Disable caching and recompute.
- Keep configured choice and effective backend separate in the UI.
- Do not expose cache component class names, credentials, or internal reason data in user-facing presentation.

## Next steps

- [Scoped cache](scoped-cache.md) — store and invalidate disposable values without enumerating shared keys
- [Components](../template-guides/components.md#cache-storage-status-since5380) — render the semantic status model directly
- [Partials](../template-guides/partials.md#field-cache-storage-since5380) — render the complete settings field
- [PluginHelper](plugin-helper.md) — inspect application-cache resolution and diagnostic logging
