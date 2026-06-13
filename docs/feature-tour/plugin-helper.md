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

## Plugin Icon @since(5.27.0)

Read the plugin's `src/icon.svg` as a string:

```php
$svg = PluginHelper::getIconSvg($this);
```

Returns the trimmed SVG markup, or `null` when the plugin has no `src/icon.svg` or it cannot be read. The file is located by reflection on the plugin class, so no path needs to be passed.

When you don't have a plugin instance — a Yii module, or a package that isn't installed/enabled — read the icon straight from a source directory instead:

```php
$svg = PluginHelper::readIconSvg($srcDir);   // $srcDir = the plugin's src/ folder
```

`getIconSvg()` is a thin wrapper over `readIconSvg()`: it resolves the plugin's `src/` by reflection and delegates. Same return contract (trimmed markup, or `null`).

Typical uses:

- install experience branding
- inline icon rendering in the control panel
- deriving brand colours with [`ColorHelper::primaryHexFromSvg()`](color-helper.md) or [`ColorHelper::iconColorRoles()`](color-helper.md)

## LindemannRock Logo @since(5.27.0)

Base is the single owner of the LindemannRock logo — both its geometry and its location. Consumers reference it through these accessors rather than bundling their own copy or reaching into base's directory layout:

```php
$path = PluginHelper::lrLogoFile();   // absolute path to the canonical lr-logo.svg
$paths = PluginHelper::lrLogoPaths();  // just the two <path> elements
```

`lrLogoPaths()` returns the logo's `<path>` elements with the outer `<svg>`/`<g>` stripped, so you can embed them in your own `<svg>` with a specific `viewBox` and `fill`:

```twig
<svg viewBox="0 -0.186 1350.04 682.02" xmlns="http://www.w3.org/2000/svg">
    <g fill="#E52521">
        {{ pathsFromController|raw }}
    </g>
</svg>
```

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

## Redis Cache Safeguard @since(5.23.0)

When a plugin is configured to use Redis cache storage but Craft's underlying cache
component isn't actually Redis (admin selected "Redis" in plugin settings but
`config/app.php` has no Redis component, deploy mishap, env-var drift, etc.), any
direct Redis commands silently no-op — leading to stale stats forever with no log.

Use `getRedisCacheOrLog()` whenever your plugin's code needs to call low-level Redis
commands directly via `$cache->redis->executeCommand(...)` (e.g. `SADD`, `SMEMBERS`,
`SCARD`, `DEL`, `SREM`):

```php
if ($settings->cacheStorageMethod === 'redis') {
    $redis = PluginHelper::getRedisCacheOrLog('my-plugin');
    if ($redis === null) {
        return true; // misconfig already logged, treat as graceful no-op
    }

    $redis->redis->executeCommand('SADD', [$keySet, $cacheKey]);
}
```

When a misconfiguration is detected, the helper logs **once per request per plugin
context** to the `lindemannrock-base` log category, e.g.:

```
my-plugin: cacheStorageMethod=redis configured, but Craft's cache component is
yii\caching\FileCache (not yii\redis\Cache). Redis cache operations silently
no-op. Either configure Redis in config/app.php, or change the plugin setting
to file storage.
```

Subsequent calls in the same request stay silent to avoid log spam. Behaviour for
correctly-configured installs is unchanged — zero extra logs, zero overhead beyond
a single `instanceof` check.

When you don't need this: `$cache->set()`, `$cache->get()`, `$cache->delete()` work
correctly through any cache backend. Only direct `->redis->executeCommand(...)` calls
require the safeguard.

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
