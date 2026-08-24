# Settings Validators @since(5.18.0)

These Yii validators enforce the formatting rules that recur across plugin settings — route prefixes, redirect targets, and template paths. Add them to a Settings model's `rules()` so an administrator gets a clear, translated error instead of an invalid value silently breaking routing or template resolution.

All three extend `yii\validators\Validator`, run only on non-empty values (empty passes — pair with `required` if a value is mandatory), and expose a `$translationCategory` (default `'app'`, so messages reuse Craft's core strings) you can point at your own plugin category.

Validating a filesystem **storage path** or an **asset volume** instead? See [Storage Path Validator](storage-path-validator.md).

## RoutePrefixValidator

Validates a CP/route prefix: no leading or trailing slash, and no empty (`//`) segments.

```php
use lindemannrock\base\validators\RoutePrefixValidator;

public function rules(): array
{
    return [
        ['routePrefix', RoutePrefixValidator::class],
    ];
}
```

| Input | Result |
|-------|--------|
| `go` | valid |
| `go/links` | valid |
| `/go` or `go/` | error — "Do not start or end the prefix with "/"." |
| `go//links` | error — "Prefix cannot contain empty path segments…" |

## UrlOrPathValidator

Validates a value that may be **either** an absolute `http(s)` URL **or** a site-relative path beginning with `/`. Useful for "base URL or path" settings.

```php
use lindemannrock\base\validators\UrlOrPathValidator;

['baseUrl', UrlOrPathValidator::class],
```

| Input | Result |
|-------|--------|
| `https://example.com/path` | valid (must pass `FILTER_VALIDATE_URL`) |
| `/section/page` | valid |
| `$BASE_URL` or `$BASE_URL/path` | valid — environment variable references are allowed through |
| `/bad\path` | error — "Path must use forward slashes only." |
| `/a/../b` | error — parent directory traversal |
| `/a//b` | error — empty segment |
| `example.com` | error — must start with `/`, `http://`, or `https://` |

## TemplatePathValidator

Validates a Craft template path: relative to the templates folder, forward slashes only, no traversal, no absolute/URL/drive-letter forms.

```php
use lindemannrock\base\validators\TemplatePathValidator;

['template', TemplatePathValidator::class],

// Optionally also assert the template actually exists:
['template', TemplatePathValidator::class, 'checkTemplateExists' => true],
```

| Input | Result |
|-------|--------|
| `_emails/notification` | valid |
| `$TEMPLATES_DIR/page` or `${TEMPLATES_DIR}/page` | valid — env prefix is stripped before checking |
| `/var/www/templates/x` | error — must be relative |
| `https://…` or `C:/…` | error — not a URL or absolute path |
| `a/../b`, `a//b` | error — traversal / empty segment |
| `page!.twig` | error — invalid characters |

`$checkTemplateExists` (default `false`): when `true`, the validator also resolves the value with `App::parseEnv()` and confirms the template exists — but it skips that check if environment variables are still unresolved, so it is safe in multi-environment configs.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `$translationCategory` | `string` | `'app'` | Translation category for error messages |
| `$checkTemplateExists` | `bool` | `false` | Also verify the template exists (TemplatePathValidator only) |

## Next Steps

- [Storage Path Validator](storage-path-validator.md) — validate filesystem storage paths and asset volumes
- [URL Safety Helper](url-safety-helper.md) — runtime safety checks for redirect target URLs
