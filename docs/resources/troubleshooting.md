# Troubleshooting

Common issues when using the base module and how to resolve them.

## Twig Functions Not Available

**Symptom:** Error like `Unknown "lrDatetime" filter` or `Unknown "lrPaletteColor" function`.

**Cause:** The base module hasn't been registered yet. Twig extensions are loaded when `Base::register()` runs, which happens inside `PluginHelper::bootstrap()`.

**Fix:** Ensure your plugin calls `PluginHelper::bootstrap()` in its `init()` method:

```php
public function init(): void
{
    parent::init();
    PluginHelper::bootstrap($this, 'myHelper');
}
```

If you're working on a module (not a plugin), call `Base::register()` directly in your module's `init()`.

---

## Helper Variable Returns Default Names

**Symptom:** `{{ myHelper.fullName }}` returns the default plugin name instead of a custom name.

**Cause:** The Settings model doesn't use `SettingsDisplayNameTrait`, or the `$pluginName` property is empty.

**Fix:** Add the trait and property to your Settings model:

```php
use lindemannrock\base\traits\SettingsDisplayNameTrait;

class Settings extends Model
{
    use SettingsDisplayNameTrait;

    public ?string $pluginName = null;
}
```

The helper falls back to the plugin's default `name` when the trait is missing or `$pluginName` is `null`.

---

## Color Set Not Found

**Symptom:** Badges or filters show no color, or `lrSetColor('mySet', 'active')` returns the default color.

**Cause:** The color set wasn't registered before templates rendered.

**Fix:** Register color sets in the `options` array of `bootstrap()`:

```php
PluginHelper::bootstrap($this, 'myHelper', [], [], [
    'colorSets' => [
        'mySet' => [
            'active' => ColorHelper::getPaletteColor('teal'),
            'inactive' => ColorHelper::getPaletteColor('red'),
        ],
    ],
]);
```

Or register them manually before any template rendering:

```php
ColorHelper::registerColorSet('mySet', [
    'active' => ColorHelper::getPaletteColor('teal'),
]);
```

---

## Date Formatting Shows Wrong Timezone

**Symptom:** Dates appear in UTC instead of the site's timezone.

**Cause:** The `$isUtc` parameter defaults to `true`, meaning the input is treated as UTC and converted to the Craft timezone. If your date is already in local time, pass `false`.

**Fix:**

```php
// Input is UTC (database value) — default behavior
DateFormatHelper::formatDatetime($utcDate);

// Input is already in local time
DateFormatHelper::formatDatetime($localDate, isUtc: false);
```

In Twig:

```twig
{{ localDate|lrDatetime('cascade', null, true, false) }}
```

---

## Saved Plugin Date Formats Are Ignored During Bootstrap

**Symptom:** A plugin uses the global date/time format during startup and continues using it later in the same request, even though the plugin has saved format overrides.

**Cause:** An older Base version can cache the plugin-specific cascade while Craft is still registering plugins. At that point the global Base and plugin-file layers are available, but the plugin's database settings are not.

**Fix:** Update to LindemannRock Base 5.37+. Keep passing the plugin's explicit handle from startup and Craft-owned display contexts; removing the handle only hides the missing cascade. Current Base returns the usable startup result without caching it, then resolves and caches database settings after Craft registers the plugin.

Manual cache clearing is still useful after changing settings within the same request, but it is not required to recover from normal plugin registration.

---

## Export Button Not Showing

**Symptom:** The export menu component renders nothing.

**Cause:** Either the user lacks the required permission, the `action` parameter is empty, or all export formats are disabled in config.

**Fix:** Check these in order:

1. Verify the permission (if set) is registered and granted to the user
2. Ensure `action` is not empty
3. Check `config/lindemannrock-base.php` — at least one format must be enabled:

```php
return [
    'exports' => [
        'excel' => true,  // At least one must be true
        'csv' => true,
        'json' => false,
    ],
];
```

---

## Settings Not Saving to Database

**Symptom:** Settings save without errors but revert to defaults on page reload.

**Cause:** The settings table doesn't exist, or the `tableName()` return value doesn't match the actual table.

**Fix:**

1. Verify the table exists in your database
2. Check that `tableName()` returns the correct name (without `{{%` prefix):

```php
protected static function tableName(): string
{
    return 'myplugin_settings'; // Must match actual table name
}
```

3. Ensure the table has an `id` column — `SettingsPersistenceTrait` always reads/writes row with `id = 1`

---

## JSON Fields Not Decoding

**Symptom:** A JSON setting returns a raw string instead of an array.

**Cause:** The field isn't listed in `jsonFields()`.

**Fix:** Add the field name to the `jsonFields()` method in your Settings model:

```php
protected static function jsonFields(): array
{
    return ['allowedDomains', 'excludePatterns'];
}
```

The trait JSON-encodes these fields on save and JSON-decodes them on load.

---

## SQL Expression Errors with PostgreSQL

**Symptom:** Timezone or JSON queries fail on PostgreSQL but work on MySQL.

**Cause:** Using MySQL-specific SQL instead of the DB-agnostic helpers.

**Fix:** Replace raw SQL with the helper methods:

```php
// Instead of MySQL-specific CONVERT_TZ
$dateExpr = DateFormatHelper::localDateExpression('dateCreated');

// Instead of MySQL-specific JSON_EXTRACT
$valueExpr = DbHelper::jsonExtract('metadata', 'provider');

// Instead of MySQL-specific GROUP_CONCAT
$concatExpr = DbHelper::groupConcat('tag', ', ');
```

These methods generate the correct SQL for the current database driver.

---

## Phone Input Not Detecting Country

**Symptom:** Pasting an international number doesn't auto-select the country.

**Cause:** The pasted number doesn't start with `+` or `00`, or the detected country isn't in `allowedCountries`.

**Fix:**

- The auto-detection only triggers for numbers starting with `+` or `00`
- Check that the country is included in `allowedCountries` (or use `['*']` for all countries)
- The component fires `lr:phoneCountryNotAllowed` when the detected country isn't in the allowed list — listen for this event to show a user-facing message

---

## Next Steps

- [Installation](../get-started/installation.md) — setup and bootstrap instructions
- [Configuration](../get-started/configuration.md) — config file reference
- [Bootstrapping](../developers/bootstrapping.md) — detailed bootstrap flow
