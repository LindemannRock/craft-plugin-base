# BooleanHelper @since(5.24.0)

Normalize boolean-like configuration, environment, and style values while preserving the presence semantics of HTML boolean attributes.

Use this helper when a value may arrive as a real boolean, numeric flag, recognized string boolean, or valueless HTML attribute. An empty string deliberately becomes `true`, matching attributes such as `disabled=""` where presence means enabled.

Do not pass raw Craft lightswitch POST values directly to this helper. Craft submits `''` when a lightswitch is off, so typed settings forms should use [SettingsPostHelper](settings-post-helper.md), which maps that value to `false`.

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

The `''` behavior is specific to valueless HTML attribute semantics. It is not a general form-post convention.

## Validation

Use `isBooleanLike()` before accepting arbitrary configuration or canonical form values:

```php
if (!BooleanHelper::isBooleanLike($value)) {
    $model->addError('enabled', 'Enabled must be a boolean-like value.');
}
```

This returns `true` for `null`, real booleans, `0`/`1`, and the recognized string variants.

## Style Values

Style config often stores booleans as `'1'` or `'0'` strings. Use `toStyleValue()` after the input has the intended boolean semantics:

```php
$styles['highlightEnabled'] = BooleanHelper::toStyleValue(
    $styleConfig['highlightEnabled'] ?? false,
);
```

## Common Use Cases

- Config values from `config/plugin-handle.php`
- Environment-derived settings via `App::env()`
- Canonical boolean-like values produced by an application-specific form boundary
- Valueless HTML boolean attributes where presence means true
- Style config values that need stable `'1'`/`'0'` storage

## Related

- [SettingsPostHelper](settings-post-helper.md) — applying raw Craft settings POST values, including lightswitch off values
- [Settings Config](settings-config.md) — detecting config-file overrides
- [Settings Persistence](settings-persistence.md) — saving normalized settings to database tables
