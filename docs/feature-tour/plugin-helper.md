# PluginHelper @since(5.0.0)

Central initialization for LindemannRock plugins. The `bootstrap()` method replaces several lines of boilerplate with a single call that registers the base module, sets up Twig extensions, configures logging, registers color sets, and can optionally trigger the shared post-install CP experience.

## bootstrap()

Called in your plugin's `init()` method:

```php
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\helpers\ColorHelper;

public function init(): void
{
    parent::init();
    self::$plugin = $this;

    PluginHelper::bootstrap(
        $this,
        'myHelper',                          // Twig global variable name
        ['myPlugin:viewSystemLogs'],         // Permissions for viewing logs
        ['myPlugin:downloadSystemLogs'],     // Permissions for downloading logs
        [
            'colorSets' => [
                'myStatus' => [
                    'active' => ColorHelper::getPaletteColor('teal'),
                    'inactive' => ColorHelper::getPaletteColor('red'),
                ],
            ],
        ]
    );

    PluginHelper::applyPluginNameFromConfig($this);
}
```

### What It Does

1. **Registers the base module** — calls `Base::register()` (idempotent, safe to call from multiple plugins)
2. **Registers a Twig global** — `myHelper` becomes available in all CP templates via `PluginNameHelper`
3. **Configures LoggingLibrary** — sets up log viewer with permissions, log level, and items per page
4. **Registers color sets** — makes plugin-specific colors available for badges and filters
5. **Registers translations** — auto-discovers `translations/` directory (enabled by default)
6. **Registers the install experience** — enables the shared one-time CP install modal unless disabled

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$plugin` | `PluginInterface` | The plugin instance |
| `$helperVariableName` | `string` | Twig global name (e.g., `'searchHelper'`) |
| `$viewSystemLogsPermissions` | `array` | Permissions to view logs |
| `$downloadSystemLogsPermissions` | `array` | Permissions to download logs |
| `$options` | `array` | Additional options (see below) |

### Options

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `colorSets` | `array` | `[]` | Color sets to register |
| `logMenu` | `array` | `null` | Customize log sidebar menu (`label`, `items`) |
| `registerTranslations` | `bool` | `true` | Auto-register translations |
| `translationCategory` | `string` | plugin id | Translation category override |
| `translationBasePath` | `string` | `{plugin}/translations` | Translation path override |
| `installExperience` | `bool|array` | `true` | Enable/configure the shared post-install CP modal |

See [Bootstrapping](../developers/bootstrapping.md) for a complete guide.

## Plugin Metadata @since(5.0.0)

Read plugin package metadata from the plugin's `composer.json`:

```php
$version = PluginHelper::getPluginVersion($this);
$metadata = PluginHelper::getPluginComposerMetadata($this);
```

`getPluginVersion()` uses the plugin package as the canonical source of truth, so version does not need to be duplicated in the plugin class.

Typical uses:

- install experience metadata
- about pages
- settings footers
- diagnostics and support output

## Plugin Name Override

Apply a custom plugin name from the config file:

```php
PluginHelper::applyPluginNameFromConfig($this);
```

This checks `config/{plugin-handle}.php` for a `pluginName` setting:

```php
<?php
// config/my-plugin.php
return [
    'pluginName' => 'Custom Name',
];
```

Supports environment-specific and wildcard configurations:

```php
return [
    '*' => ['pluginName' => 'My Plugin'],
    'production' => ['pluginName' => 'Production Plugin'],
];
```

## Settings Config Overrides @since(5.16.0)

For DB-backed settings models, also merge config file values into the Settings model itself:

```php
use craft\base\Model;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\myplugin\models\Settings;

protected function createSettingsModel(): ?Model
{
    return PluginHelper::applyConfigOverridesToSettings(
        Settings::loadFromDatabase(),
        'my-plugin'
    );
}
```

This keeps `$settings->getFullName()` and other settings-derived labels aligned with Craft's plugin name in `/admin/settings`.

Why this matters:

- `applyPluginNameFromConfig($this)` updates `$plugin->name`
- CP nav labels, permission headings, breadcrumbs, widgets, and templates commonly use `$settings->getFullName()`
- `applyConfigOverridesToSettings()` makes the Settings model config-aware, so both paths resolve to the same configured value

You can skip nested config keys that are handled elsewhere:

```php
PluginHelper::applyConfigOverridesToSettings($settings, 'search-manager', [
    'indices',
    'backends',
    'transformers',
]);
```

Use this with [SettingsConfigTrait](settings-config.md) and [SettingsPersistenceTrait](settings-persistence.md) when a plugin stores settings in its own database table.

## Plugin Detection @since(5.9.0)

Check if other plugins are installed before using their APIs:

```php
// Check if a plugin is installed AND enabled
if (PluginHelper::isPluginEnabled('formie')) {
    $formie = PluginHelper::getPlugin('formie');
    // Safe to use Formie's API
}

// Check if installed (may be disabled)
PluginHelper::isPluginInstalled('redirect-manager');

// Get a plugin's display name (respects custom pluginName)
$name = PluginHelper::getPluginName('search-manager');
$name = PluginHelper::getPluginName('missing-plugin', 'Fallback Name');
```

In Twig:

```twig
{% if lrPluginEnabled('formie') %}
    <p>Formie integration active</p>
{% endif %}

{{ lrPluginName('search-manager') }}
```

## Cache Paths @since(5.5.0)

Build consistent cache paths for plugins:

```php
// Base: storage/runtime/{plugin-handle}/cache/
$basePath = PluginHelper::getCacheBasePath($this);

// Specific: storage/runtime/{plugin-handle}/cache/{type}/
$devicePath = PluginHelper::getCachePath($this, 'device');
```

## Cache Key Helpers @since(5.14.0)

Build consistent cache keys and Redis key sets:

```php
// Cache key prefix: "redirectmanager:device:"
$prefix = PluginHelper::getCacheKeyPrefix('redirect-manager', 'device');

// Redis key set name: "redirectmanager-device-keys"
$keySet = PluginHelper::getCacheKeySet('redirect-manager', 'device');
```

## Translation Registration @since(5.14.0)

Translations are registered automatically during `bootstrap()`. To register manually:

```php
// From a plugin instance
PluginHelper::registerTranslations($plugin);

// From a handle + path (optional $category defaults to the handle)
PluginHelper::registerTranslations('my-plugin', '/path/to/translations');
PluginHelper::registerTranslations('my-plugin', '/path/to/translations', 'custom-category');
```

## Next Steps

- [Bootstrapping](../developers/bootstrapping.md) — complete bootstrap integration guide
- [ColorHelper](color-helper.md) — registering custom color sets
- [SettingsDisplayNameTrait](settings-display-name.md) — custom plugin display names
