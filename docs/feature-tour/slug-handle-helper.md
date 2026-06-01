# SlugHandleHelper @since(5.26.0)

`SlugHandleHelper` normalizes persisted slugs/handles and resolves database collisions with the shared `base`, `base-1`, `base-2` suffix convention.

It owns mechanics only. Plugins still decide the product policy: append a suffix, reject duplicates, reuse an existing row, perform deterministic sync updates, or allow config-file shadowing.

## Normalize Handles

```php
use lindemannrock\base\helpers\SlugHandleHelper;

$handle = SlugHandleHelper::normalizeHandle($postedHandle ?: $name);
// "My Backend Name" -> "myBackendName"
```

Use for Craft-style handles. Empty or invalid values fall back to the provided fallback, then `item`.

## Normalize Slugs

```php
$slug = SlugHandleHelper::normalizeSlug($postedSlug ?: $title);
// "Summer Sale 2026!" -> "summer-sale-2026"
```

Use for URL-style slugs/codes. The helper keeps lowercase letters, numbers, underscores, and hyphens; other runs become `-`.

## Normalize Path Slugs

```php
$slug = SlugHandleHelper::normalizePathSlug($docPath);
// "Get Started/Requirements" -> "get-started/requirements"
```

Use for slash-preserving docs/page slugs. Empty path segments collapse.

## Existence Checks

```php
$taken = SlugHandleHelper::exists('{{%myplugin_items}}', 'handle', $handle, [
    'excludeId' => $item->id,
    'scope' => ['sourceId' => $sourceId],
]);
```

Supported options:

| Option | Type | Description |
|--------|------|-------------|
| `excludeId` | `int|string|null` | Current row ID to ignore during edit flows |
| `idColumn` | `string` | ID column used with `excludeId`; defaults to `id` |
| `scope` | `array` | Equality conditions, e.g. `['sourceId' => 1]` |
| `conditions` | `array` | Additional Yii query conditions |

## Unique Candidates

```php
$handle = SlugHandleHelper::makeUnique('{{%myplugin_items}}', 'handle', $handle, [
    'excludeId' => $item->id,
]);
```

Candidate order is fixed:

```text
base
base-1
base-2
base-3
```

Supported options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `excludeId` | `int|string|null` | `null` | Current row ID to ignore during edit flows |
| `idColumn` | `string` | `id` | ID column used with `excludeId` |
| `scope` | `array` | `[]` | Equality conditions for scoped uniqueness |
| `conditions` | `array` | `[]` | Additional Yii query conditions |
| `start` | `int` | `1` | First suffix number |
| `maxAttempts` | `int` | `100` | Maximum suffixed candidates to try |

If no unique candidate can be generated, `makeUnique()` throws `RuntimeException`.

## Patterns

Append-mode CP handle:

```php
$handle = SlugHandleHelper::normalizeHandle($request->getBodyParam('handle') ?: $model->name);
$model->handle = SlugHandleHelper::makeUnique('{{%myplugin_items}}', 'handle', $handle, [
    'excludeId' => $model->id,
]);
```

Reject-mode slug:

```php
$slug = SlugHandleHelper::normalizeSlug($postedSlug);
if (SlugHandleHelper::exists('{{%myplugin_pages}}', 'slug', $slug, [
    'excludeId' => $page->id,
    'scope' => ['sourceId' => $page->sourceId],
])) {
    $page->addError('slug', Craft::t('my-plugin', 'Slug is already in use.'));
}
```

Deterministic sync/reuse:

```php
$slug = SlugHandleHelper::normalizePathSlug($relativePath);
$existing = (new Query())
    ->from('{{%myplugin_docs}}')
    ->where(['sourceId' => $sourceId, 'slug' => $slug])
    ->one();
```

## Not For

- Filename or ZIP member safety — use [SafeSegmentHelper](safe-segment-helper.md)
- Header/download filename hardening — keep using `ExportHelper`
- Product policy decisions such as config-file precedence or whether duplicate slugs should append, reject, or reuse

## Next Steps

- [SafeSegmentHelper](safe-segment-helper.md) — safe non-DB string fragments
- [API Reference](../developers/api-reference.md) — full PHP API reference
