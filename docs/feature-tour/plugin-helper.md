# PluginHelper

Central initialization for LindemannRock plugins. The `bootstrap()` method replaces several lines of boilerplate with a single call that registers the base module, sets up Twig extensions, configures logging, and registers color sets.

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

See [Bootstrapping](../developers/bootstrapping.md) for a complete guide.

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

## Plugin Detection

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

## Cache Paths

Build consistent cache paths for plugins:

```php
// Base: storage/runtime/{plugin-handle}/cache/
$basePath = PluginHelper::getCacheBasePath($this);

// Specific: storage/runtime/{plugin-handle}/cache/{type}/
$devicePath = PluginHelper::getCachePath($this, 'device');
```

## Cache Key Helpers

Build consistent cache keys and Redis key sets:

```php
// Cache key prefix: "redirectmanager:device:"
$prefix = PluginHelper::getCacheKeyPrefix('redirect-manager', 'device');

// Redis key set name: "redirectmanager-device-keys"
$keySet = PluginHelper::getCacheKeySet('redirect-manager', 'device');
```

## Translation Registration

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
