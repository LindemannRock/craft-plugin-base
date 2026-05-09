# BooleanHelper @since(5.24.0)

Normalize boolean-like values from config files, environment variables, POST data, and HTML attributes.

Use this helper whenever a value may arrive as a real boolean, numeric flag, string boolean, or bare HTML attribute value.

```php
use lindemannrock\base\helpers\BooleanHelper;

BooleanHelper::normalize(true);       // true
BooleanHelper::normalize(false);      // false
BooleanHelper::normalize('1');        // true
BooleanHelper::normalize('0');        // false
BooleanHelper::normalize('true');     // true
BooleanHelper::normalize('false');    // false
BooleanHelper::normalize('on');       // true
BooleanHelper::normalize('off');      // false
BooleanHelper::normalize('yes');      // true
BooleanHelper::normalize('no');       // false
BooleanHelper::normalize('', false);  // true
BooleanHelper::normalize(null, true); // true
```

## Accepted Values

| Input | Result |
|-------|--------|
| `true`, `1`, `'1'`, `'true'`, `'on'`, `'yes'` | `true` |
| `false`, `0`, `'0'`, `'false'`, `'off'`, `'no'` | `false` |
| `''` | `true` |
| `null` | The provided default |
| Unknown strings | The provided default |

String values are trimmed and compared case-insensitively.

## Validation

Use `isBooleanLike()` before accepting arbitrary config or POST values:

```php
if (!BooleanHelper::isBooleanLike($value)) {
    $model->addError('enabled', 'Enabled must be a boolean-like value.');
}
```

This returns `true` for `null`, real booleans, `0`/`1`, and the recognized string variants.

## Style Values

Style config often stores booleans as `'1'` or `'0'` strings. Use `toStyleValue()` to normalize safely:

```php
$styles['highlightEnabled'] = BooleanHelper::toStyleValue($request->getBodyParam('highlightEnabled'));
```

## Common Use Cases

- Config values from `config/plugin-handle.php`
- Environment-derived settings via `App::env()`
- Lightswitch and checkbox POST values
- HTML boolean attributes
- Style config values that need stable `'1'`/`'0'` storage

## Related

- [Settings Config](settings-config.md) — detecting config-file overrides
- [Settings Persistence](settings-persistence.md) — saving normalized settings to database tables
