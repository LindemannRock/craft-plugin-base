# Base Settings Traits @since(5.25.0)

A set of six traits and their companion CP partials that centralize the duplicated Settings-model boilerplate found across every LindemannRock plugin — pluginName, logLevel, itemsPerPage, dateFormat, dateRange, and export-format toggles. Each trait pairs with a Twig partial that renders the corresponding form field with shared labels, validation, and override-warning behaviour. The translations live once in `lindemannrock-base` instead of being duplicated per plugin × 12 languages.

## Two patterns

| Pattern | Cascade overrides | Shared standalone fields |
|---------|-------------------|--------------------------|
| **Traits** | `DateFormatSettingsTrait`, `DateRangeSettingsTrait`, `ExportFormatSettingsTrait` | `ItemsPerPageSettingsTrait`, `PluginNameSettingsTrait`, `LogLevelSettingsTrait` |
| **Properties** | Nullable. `null` = "inherit from base config / hardcoded default". | Concrete typed values. Plugin owns its default. |
| **Cascade engine** | `DateFormatHelper`, `DateRangeHelper`, `ExportHelper` — each resolves a 4-layer cascade (defaults → base config → plugin Settings → plugin config) | None. Each plugin owns its own value. |
| **Form rendering** | Routes through the `cascade-base-overrides` umbrella partial, which adds the shared "Base Plugin Overrides" heading + cascade info-box + dispatches to per-section sub-partials. | Individual `field-*` partials, included directly by the plugin's settings template. |
| **CP UX** | Each select has a `"Use global default"` (empty) option. The field is disabled with an override-warning when plugin config sets the same key. | Standard inputs (textField, number input, selectField). Override-warning behaves the same way. |

## Adopting the patterns

The full per-plugin walkthrough lives in [`../../../_docs/guides/base-settings-rollout-prompt.md`](../../../_docs/guides/base-settings-rollout-prompt.md). The cross-plugin progress tracker is at [`../../../_docs/rollouts/base-settings.md`](../../../_docs/rollouts/base-settings.md). Two working reference plugins:

- **`search-manager`** — full coverage of all 6 patterns end-to-end. Canonical example for any pattern.
- **`logging-library`** — narrower (3 of 6). Demonstrates the `excludeFromSave()` subset-of-trait-fields trick and the pre-launch-with-migrations exception.

## `DateFormatSettingsTrait` — date/time format overrides

Adds five nullable date/time properties to the Settings model. Values cascade through `DateFormatHelper::getConfig()` so the same Twig filters (`|lrTime`, `|lrDate`, `|lrDatetime`) honor per-plugin overrides automatically — no caller threading required.

```php
use lindemannrock\base\traits\DateFormatSettingsTrait;
use lindemannrock\base\traits\SettingsConfigTrait;

class Settings extends Model
{
    use DateFormatSettingsTrait;
    use SettingsConfigTrait;

    // ... plugin-specific properties ...

    protected static function booleanFields(): array
    {
        return ['showSeconds'];   // include the bool trait property so persistence casts correctly
    }

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->dateFormatSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->dateFormatSettingsLabels());
    }
}
```

**Properties** (all nullable; `null` = inherit): `timeFormat` (`'12'`/`'24'`), `monthFormat` (`'numeric'`/`'short'`/`'long'`), `dateOrder` (`'dmy'`/`'mdy'`/`'ymd'`), `dateSeparator` (`'/'`/`'-'`/`'.'`), `showSeconds` (`bool`).

**Schema** — five nullable columns in the plugin's Settings table:

```php
'timeFormat'    => $this->string(2)->null(),
'monthFormat'   => $this->string(20)->null(),
'dateOrder'     => $this->string(3)->null(),
'dateSeparator' => $this->string(1)->null(),
'showSeconds'   => $this->boolean()->null(),
```

**Partial** — included via the umbrella's `sections.dateFormat`:

```twig
{% include 'lindemannrock-base/_partials/cascade-base-overrides' with {
    settings: settings,
    pluginHandle: 'my-plugin',
    sections: { dateFormat: {} },
} only %}
```

Pass `sections.dateFormat.fields: ['timeFormat', 'showSeconds']` to surface only a subset. Plugins that surface only a subset also need `excludeFromSave(): array` returning the unsurfaced property names so the persistence trait doesn't try to write columns that don't exist.

See: [DateFormatHelper](date-format-helper.md) for the cascade engine + Twig filters.

## `DateRangeSettingsTrait` — default analytics date range

Adds a single nullable `?string $defaultDateRange` whose value cascades through `DateRangeHelper::getDefaultDateRange()` (one of 10 standard ranges: `today`, `yesterday`, `last7days`, `last30days`, `last90days`, `thisMonth`, `lastMonth`, `thisYear`, `lastYear`, `all`).

```php
use lindemannrock\base\traits\DateRangeSettingsTrait;

class Settings extends Model
{
    use DateRangeSettingsTrait;

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->dateRangeSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->dateRangeSettingsLabel());
    }
}
```

**Schema** — one nullable column:

```php
'defaultDateRange' => $this->string(15)->null(),
```

**Partial** — `sections.dateRange` in the umbrella. No options needed — the partial renders all 10 ranges plus a `"Use global default"` entry.

See: [DateRangeHelper](date-range-helper.md) for the cascade engine.

## `ExportFormatSettingsTrait` — per-plugin export format toggles

Adds three nullable booleans (`exportsCsv`, `exportsJson`, `exportsExcel`) that override the base export configuration for this plugin. The cascade is implemented as a fourth layer on `ExportHelper::getConfig()` — when a flat property on the Settings model is non-null, it overrides the same key in `config/lindemannrock-base.php`'s `exports` hash. Plugin config (nested `exports.X` hash) still wins above all.

```php
use lindemannrock\base\traits\ExportFormatSettingsTrait;

class Settings extends Model
{
    use ExportFormatSettingsTrait;

    protected static function booleanFields(): array
    {
        return ['exportsCsv', 'exportsJson', 'exportsExcel'];
    }

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->exportFormatSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->exportFormatSettingsLabels());
    }
}
```

**Schema** — three nullable boolean columns:

```php
'exportsCsv'   => $this->boolean()->null(),
'exportsJson'  => $this->boolean()->null(),
'exportsExcel' => $this->boolean()->null(),
```

**Partial** — `sections.exports` in the umbrella. Each format renders as a 3-state selectField (`""` = Use global default, `"0"` = Disabled, `"1"` = Enabled), mirroring `showSeconds`. Pass `sections.exports.fields: ['exportsCsv']` to surface only a subset.

The existing `_components/export-menu` component reads through `ExportHelper::isFormatEnabled()` — no template changes needed elsewhere for the per-plugin override to take effect.

See: [ExportHelper](export-helper.md) for the cascade engine + `_components/export-menu`.

## `ItemsPerPageSettingsTrait` — shared paging field

Centralizes the `int $itemsPerPage` property that ~14 plugins carry today. The trait declares the property with `100` default + min `10` / max `500` validation + label. Plugin keeps its own DB column + the `integerFields()` entry (for `SettingsPersistenceTrait`'s cast).

```php
use lindemannrock\base\traits\ItemsPerPageSettingsTrait;

class Settings extends Model
{
    use ItemsPerPageSettingsTrait;

    protected static function integerFields(): array
    {
        return ['itemsPerPage', /* ... other int fields ... */];
    }

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->itemsPerPageSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->itemsPerPageSettingsLabel());
    }
}
```

**Schema** — one non-null integer column with `100` default:

```php
'itemsPerPage' => $this->integer()->notNull()->defaultValue(100),
```

**Partial** — standalone, **not** part of the umbrella (no cascade semantics):

```twig
{% include 'lindemannrock-base/_partials/field-items-per-page' with {
    settings: settings,
    pluginHandle: 'my-plugin',
    instructions: 'Number of log entries to display per page'|t('my-plugin'),
} only %}
```

The `instructions` text is plugin-specific (the wording for "log entries" / "campaigns" / "shortlinks" differs per plugin) and is passed in by the caller.

## `PluginNameSettingsTrait` — shared plugin-name field

Centralizes the `pluginName` validation rule (`required` + `string max:255`) and label. The trait **does not** declare the property — every plugin keeps its own `public string $pluginName = '...'` with a plugin-specific default ("Search Manager", "Logging Library", etc.).

```php
use lindemannrock\base\traits\PluginNameSettingsTrait;

class Settings extends Model
{
    use PluginNameSettingsTrait;

    // Plugin keeps its own property + default:
    public string $pluginName = 'My Plugin';

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->pluginNameSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->pluginNameSettingsLabel());
    }
}
```

**Schema** — no change. The plugin's existing `pluginName` column stays.

**Partial** — standalone:

```twig
{% include 'lindemannrock-base/_partials/field-plugin-name' with {
    settings: settings,
    pluginHandle: 'my-plugin',
} only %}
```

The default instructions ("The name of the plugin as it appears in the Control Panel menu.") come from base translations. Pass `instructions:` to override per-plugin if needed.

Pairs with [`SettingsDisplayNameTrait`](settings-display-name.md), which provides `getDisplayName()` / `getFullName()` / `getPluralDisplayName()` helpers built on top of `$pluginName`. The two traits are independent — adopt one, both, or neither.

## `LogLevelSettingsTrait` — shared log-level field

Centralizes `public string $logLevel = 'error'` + the in-range validation rule (allowing `debug`/`info`/`warning`/`error`) + the delegate to `SettingsConfigTrait::validateLogLevel`, which handles the devMode-gated `debug → info` fallback. Plugins that don't have logging — or that manage log levels for OTHER plugins like `logging-library` — don't adopt this trait.

```php
use lindemannrock\base\traits\LogLevelSettingsTrait;
use lindemannrock\base\traits\SettingsConfigTrait;

class Settings extends Model
{
    use LogLevelSettingsTrait;
    use SettingsConfigTrait;   // required — provides validateLogLevel

    public function rules(): array
    {
        return array_merge([
            // ... plugin-specific rules ...
        ], $this->logLevelSettingsRules());
    }

    public function attributeLabels(): array
    {
        return array_merge([
            // ... plugin-specific labels ...
        ], $this->logLevelSettingsLabel());
    }
}
```

**Schema** — one non-null short string with `'error'` default:

```php
'logLevel' => $this->string(10)->notNull()->defaultValue('error'),
// or, if your DB supports it:
'logLevel' => $this->enum('logLevel', ['debug', 'info', 'warning', 'error'])->notNull()->defaultValue('error'),
```

**Partial** — standalone. Owns the option list, the `debug` devMode gating, the label, and the override warning:

```twig
{% include 'lindemannrock-base/_partials/field-log-level' with {
    settings: settings,
    pluginHandle: 'my-plugin',
} only %}
```

## SettingsController `''` → null coercion

The three cascade traits (`DateFormatSettingsTrait`, `DateRangeSettingsTrait`, `ExportFormatSettingsTrait`) declare nullable properties whose CP form fields submit `''` for the "Use global default" option. PHP coerces `''` to the property's typed default on direct assignment (`false` for `?bool`, `''` for `?string`) which breaks the cascade because the value is no longer `null`.

Add this generic block to your plugin's `SettingsController::actionSave()` BEFORE `$settings->setAttributes($postedSettings)` (or the equivalent assignment loop):

```php
// Multi-state selects (e.g. "Use global default" = '') need '' → null
// so nullable properties hold null, not a coerced false / 0 / ''.
foreach ($postedSettings as $key => $value) {
    if ($value !== '' || !property_exists($settings, $key)) {
        continue;
    }
    $type = (new \ReflectionProperty($settings, $key))->getType();
    if ($type instanceof \ReflectionNamedType && $type->allowsNull()) {
        $postedSettings[$key] = null;
    }
}
```

It's reflection-driven so it covers every nullable property generically — not just the trait-managed ones. Search-manager's `SettingsController::actionSave()` is the working reference.

## Translations

All shared strings (field labels, instructions, option labels, the generic `{setting}/{handle}` override-warning template) live in `base/translations/{lang}/lindemannrock-base.php` × 12 languages. Plugins adopting these traits **remove** the duplicated keys from their own translation files.

The only plugin-specific translation surface that survives is plugin-specific instructions text (e.g., `field-items-per-page`'s `instructions:` prop), which is passed in by the caller using the plugin's own translation category.

## Related

- [DateFormatHelper](date-format-helper.md), [DateRangeHelper](date-range-helper.md), [ExportHelper](export-helper.md) — the cascade engines
- [SettingsConfigTrait](settings-config.md) — provides `isOverriddenByConfig()` + `validateLogLevel`; required by every cascade trait + the log-level trait
- [SettingsPersistenceTrait](settings-persistence.md) — DB-backed Settings models; required by every plugin that adopts these traits
- [SettingsDisplayNameTrait](settings-display-name.md) — pairs with `PluginNameSettingsTrait`
- [Rollout prompt](../../../_docs/guides/base-settings-rollout-prompt.md) — per-plugin adoption walkthrough
- [Rollout tracker](../../../_docs/rollouts/base-settings.md) — cross-plugin progress
