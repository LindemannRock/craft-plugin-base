# SettingsPostHelper @since(5.26.0)

Applies raw Control Panel settings POST values to typed settings models without throwing PHP type errors before Yii validation can render field errors.

Use this helper in settings controllers between reading `settings[...]` from the request and calling `validate()` / `saveToDatabase()`.

## Contract

`SettingsPostHelper::apply()` only auto-handles these destination property types:

- `int` / `?int`
- `float` / `?float`
- `bool` / `?bool`
- `string` / `?string`
- `array`

Unknown fields, off-section fields, config-overridden fields, untyped properties, and unsupported typed properties are ignored before assignment. Unsupported fields require an adapter.

## Example

```php
use lindemannrock\base\helpers\SettingsPostHelper;

$settings = Settings::loadFromDatabase();
$settingsData = Craft::$app->getRequest()->getBodyParam('settings', []);
$sectionAttributes = $this->_validationAttributesForSection($section);

$result = SettingsPostHelper::apply(
    model: $settings,
    postedValues: $settingsData,
    allowedAttributes: $sectionAttributes,
    isOverridden: fn(string $attribute): bool => $settings->isOverriddenByConfig($attribute),
    adapters: [
        'enabledIntegrations' => static fn(mixed $value): array => is_string($value)
            ? (json_decode($value, true) ?: [])
            : (is_array($value) ? $value : []),
        'redirectManagerEvents' => static fn(mixed $value): array => is_array($value) ? $value : [],
    ],
);

if ($result->hasErrors || !$settings->validate($result->attributesToValidate)) {
    return $this->renderTemplate("plugin-handle/settings/{$section}", [
        'settings' => $settings,
    ]);
}

$settings->saveToDatabase($result->attributesToValidate);
```

When the settings model uses `SettingsPersistenceTrait`, passing `$result->attributesToValidate` to `saveToDatabase()` validates and persists only the active section's non-overridden attributes. Passing `null` keeps the full-settings save behavior.

## Empty Strings

Empty strings become `null` only when the destination property allows null.

For non-nullable numeric and boolean properties, an empty string is invalid and the helper adds a model error.

For non-nullable string properties, an empty string remains an empty string.

## Booleans

Boolean properties accept common Craft/HTML form values:

| Input | Result |
|-------|--------|
| `true`, `1`, `'1'`, `'true'`, `'on'`, `'yes'` | `true` |
| `false`, `0`, `'0'`, `'false'`, `'off'`, `'no'` | `false` |

Missing checkbox fields are not inferred as `false`. Controllers and templates should keep posting hidden fallbacks where the UI needs that behavior.

## Arrays

Array properties preserve posted arrays. Associative arrays are allowed.

JSON strings, asset ID arrays, nested payloads, and checkbox groups are plugin-specific and should use adapters.

## Helper Errors

The helper can add these base-owned translated model errors:

- `Value must be a whole number.`
- `Value must be a number.`
- `Value must be either true or false.`
- `Value must be an array.`

Range checks, required fields, enum checks, URL/path validation, and cross-field validation remain the settings model's responsibility.

## Result

`apply()` returns `SettingsPostResult`:

| Property | Type | Description |
|----------|------|-------------|
| `attributesToValidate` | `string[]` | Active-section attributes after config overrides are removed |
| `assignedAttributes` | `string[]` | Posted attributes assigned to the model |
| `ignoredAttributes` | `string[]` | Posted attributes ignored before assignment |
| `hasErrors` | `bool` | Whether the helper added model errors |

## Related

- [SettingsPersistenceTrait](settings-persistence.md) — database load/save type conversion
- [SettingsConfigTrait](settings-config.md) — config-file override detection
