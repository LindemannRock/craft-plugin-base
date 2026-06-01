# SafeSegmentHelper @since(5.26.0)

`SafeSegmentHelper` normalizes safe non-DB string fragments. Use it for filename parts, ZIP/cache/file-storage fragments, and local token/config keys.

Use [SlugHandleHelper](slug-handle-helper.md) instead for persisted slugs/handles and DB uniqueness.

## filenamePart()

```php
use lindemannrock\base\helpers\SafeSegmentHelper;

$part = SafeSegmentHelper::filenamePart('My Report: June/2026');
// "my-report-june-2026"
```

Filename parts remove path separators, quotes, control characters, and unsafe punctuation. They are lowercased by default.

```php
SafeSegmentHelper::filenamePart('Report v2.csv', 'file', ['allowDots' => true]);
// "report.v2.csv"

SafeSegmentHelper::filenamePart('Report v2.csv');
// "report-v2-csv"
```

Options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `allowDots` | `bool` | `false` | Preserve dots inside the segment |
| `lowercase` | `bool` | `true` | Lowercase the result |
| `maxLength` | `int` | `120` | Truncate to this length; `0` disables truncation |

`filenamePart()` is for fragments. Final download responses should still go through `ExportHelper`, which performs response/header hardening.

## tokenKey()

```php
$key = SafeSegmentHelper::tokenKey('Primary Color!');
// "primary-color"
```

Token keys are lowercase dash-separated fragments for local JSON/config/CSS-ish keys, such as Canvas Studio theme token keys.

```php
SafeSegmentHelper::tokenKey('Primary Color', 'token', 7);
// "primary"
```

Parameters:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$value` | `string|null` | — | Raw key value |
| `$fallback` | `string` | `token` | Fallback when the key normalizes empty |
| `$maxLength` | `int` | `64` | Maximum length; `0` disables truncation |

## Not For

- DB-backed slug/handle uniqueness — use [SlugHandleHelper](slug-handle-helper.md)
- Full filesystem path validation — use `StoragePathHelper` / `StoragePathValidator`
- Export dispatch or final response creation — use `ExportHelper`

## Next Steps

- [SlugHandleHelper](slug-handle-helper.md) — persisted slugs/handles and DB uniqueness
- [ExportHelper](export-helper.md) — export files and response handling
- [Storage Path Validator](storage-path-validator.md) — validating administrator-provided storage paths
