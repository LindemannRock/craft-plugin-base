# SettingsDisplayNameTrait

Provides standardized plugin name helper methods for Settings models. Strips "Manager" suffixes, singularizes, and pluralizes names for consistent use throughout the plugin UI.

## Setup

```php
use craft\base\Model;
use lindemannrock\base\traits\SettingsDisplayNameTrait;

class Settings extends Model
{
    use SettingsDisplayNameTrait;

    public string $pluginName = 'Redirect Manager';
}
```

The trait requires a public `$pluginName` property on the Settings model.

## Available Methods

| Method | "Redirect Manager" | "Short Links" | "SMS Manager" |
|--------|---------------------|---------------|---------------|
| `getDisplayName()` | Redirect | Short Link | SMS |
| `getFullName()` | Redirect Manager | Short Links | SMS Manager |
| `getPluralDisplayName()` | Redirects | Short Links | SMS |
| `getLowerDisplayName()` | redirect | short link | sms |
| `getPluralLowerDisplayName()` | redirects | short links | sms |

### getDisplayName()

Singular name without "Manager". Strips "Manager", singularizes trailing "s" (unless it's an acronym or ends in "ss").

```php
$settings->getDisplayName();  // "Redirect"
```

### getFullName()

The plugin name exactly as configured.

```php
$settings->getFullName();  // "Redirect Manager"
```

### getPluralDisplayName()

Plural name without "Manager". Adds "s" if not already present.

```php
$settings->getPluralDisplayName();  // "Redirects"
```

### getLowerDisplayName() / getPluralLowerDisplayName()

Lowercase versions of `getDisplayName()` and `getPluralDisplayName()`.

```php
$settings->getLowerDisplayName();         // "redirect"
$settings->getPluralLowerDisplayName();   // "redirects"
```

## Twig Usage

After bootstrap, these methods are available via the Twig helper variable:

```twig
{{ myHelper.displayName }}         {# "Redirect" #}
{{ myHelper.fullName }}            {# "Redirect Manager" #}
{{ myHelper.pluralDisplayName }}   {# "Redirects" #}
```

Common usage patterns:

```twig
<h1>{{ myHelper.fullName }}</h1>
<p>No {{ myHelper.pluralDisplayName|lower }} found.</p>
<button>New {{ myHelper.displayName }}</button>
```

## Next Steps

- [SettingsPersistenceTrait](settings-persistence.md) — database storage for settings
- [PluginHelper](plugin-helper.md) — `applyPluginNameFromConfig()` for config-driven names
