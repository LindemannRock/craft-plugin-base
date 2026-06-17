# GqlHelper @since(5.27.0)

`GqlHelper` centralizes the small Craft GraphQL mechanics that plugin queries and resolvers repeat: schema permission checks, `site` / `siteId` argument resolution, virtual site fields, and null-normalization for array-backed GraphQL types.

It does not define plugin queries, types, fields, or resolver behavior. Each plugin still owns its public GraphQL schema.

## Check Schema Permissions

Use `canQuery()` when registering plugin-owned GraphQL queries. Pass the schema component without the action suffix.

```php
use lindemannrock\base\helpers\GqlHelper;

if (!GqlHelper::canQuery('redirectManager.all')) {
    return [];
}
```

For a Craft schema scope stored as `redirectManager.all:read`, the component is `redirectManager.all`.

```php
public static function canQuery(string $component, ?GqlSchema $schema = null): bool
```

## Resolve Site Arguments

Use `resolveSiteId()` in resolvers that accept Craft-style `site` and `siteId` arguments.

```php
$siteId = GqlHelper::resolveSiteId($arguments, Craft::$app->getSites()->getCurrentSite()->id);
```

The `site` handle wins when both arguments are present. Invalid explicit handles or IDs return `null` instead of silently falling back to another site.

```php
public static function resolveSiteId(array $arguments, ?int $fallbackSiteId = null): ?int
```

Supported arguments:

| Argument | Type | Description |
|----------|------|-------------|
| `site` | `string` | Craft site handle |
| `siteId` | `int|string` | Craft site ID |

## Virtual Site Fields

Use `siteHandle()` from array-backed GraphQL object types when the stored row only has a `siteId`.

```php
$site = GqlHelper::siteHandle($source['siteId'] ?? null);
```

```php
public static function siteHandle(?int $siteId): ?string
```

## Empty String Normalization

Use `nullIfEmptyString()` when GraphQL should return `null` for blank database strings while preserving meaningful falsey values like `0`, `'0'`, and `false`.

```php
$value = GqlHelper::nullIfEmptyString($source[$fieldName] ?? null);
```

```php
public static function nullIfEmptyString(mixed $value): mixed
```

## Pattern

A plugin resolver can combine the helpers while keeping its query behavior local:

```php
public static function resolve(mixed $source, array $arguments): ?array
{
    $siteId = GqlHelper::resolveSiteId($arguments);
    $uri = $arguments['uri'] ?? '/';

    // Plugin-owned matching, analytics, and response shaping stay here.
    return MyPlugin::$plugin->redirects->resolveForGraphql($uri, $siteId);
}
```

## Not For

- Registering GraphQL queries or types
- Defining plugin field lists
- Deciding resolver side effects such as analytics, hit counts, or mutations
- Replacing Craft's native `craft\helpers\Gql` APIs for core element permissions

## Next Steps

- [API Reference](../developers/api-reference.md) — full PHP API reference
- [PluginHelper](plugin-helper.md) — plugin bootstrap patterns
