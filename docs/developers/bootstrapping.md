# Bootstrapping

How `PluginHelper::bootstrap()` initializes the base module for your plugin. This single call replaces multiple manual setup steps.

## What Bootstrap Does

When you call `PluginHelper::bootstrap()`, it performs six actions in order:

1. **Registers the base module** — calls `Base::register()` (idempotent, safe to call from multiple plugins)
2. **Registers a Twig global** — adds a `PluginNameHelper` instance as a Twig variable (e.g., `redirectHelper`)
3. **Configures logging** — sets up [LoggingLibrary](https://github.com/lindemannrock/craft-logging-library) integration (when permissions are provided and the library is installed)
4. **Registers color sets** — adds plugin-specific color sets to [ColorHelper](../feature-tour/color-helper.md) for badges and filters
5. **Registers translations** — sets up `PhpMessageSource` for the plugin's `translations/` directory (enabled by default)
6. **Registers the install experience** — enables the shared one-time CP install modal for CP installs unless disabled

## Basic Usage

In your plugin's `init()` method:

```php
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();
    self::$plugin = $this;

    PluginHelper::bootstrap(
        $this,
        'redirectHelper',                     // Twig variable name
        ['redirectManager:viewSystemLogs'],    // Log viewing permissions
        ['redirectManager:downloadSystemLogs'] // Log download permissions
    );

    // Optional: override plugin name from config file
    PluginHelper::applyPluginNameFromConfig($this);

    // ... rest of plugin-specific init
}
```

## Method Signature

```php
public static function bootstrap(
    PluginInterface $plugin,
    string $helperVariableName,
    array $viewSystemLogsPermissions = [],
    array $downloadSystemLogsPermissions = [],
    array $options = [],
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$plugin` | `PluginInterface` | Your plugin instance (`$this`) |
| `$helperVariableName` | `string` | Twig global variable name (e.g., `'redirectHelper'`) |
| `$viewSystemLogsPermissions` | `array` | Permissions for viewing logs (e.g., `['myPlugin:viewLogs']`) |
| `$downloadSystemLogsPermissions` | `array` | Permissions for downloading logs |
| `$options` | `array` | Additional configuration (see below) |

## Options

### colorSets

Register plugin-specific color sets for use in badges and filters:

```php
PluginHelper::bootstrap(
    $this,
    'smsHelper',
    ['smsManager:viewLogs'],
    ['smsManager:downloadLogs'],
    [
        'colorSets' => [
            'smsStatus' => [
                'sent' => ColorHelper::getPaletteColor('teal'),
                'failed' => ColorHelper::getPaletteColor('red'),
                'pending' => ColorHelper::getPaletteColor('orange'),
            ],
        ],
    ]
);
```

These sets are then available in templates:

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: message.status|capitalize,
    value: message.status,
    colorSet: 'smsStatus',
} only %}
```

### logMenu

Customize the log sidebar menu:

```php
'logMenu' => [
    'label' => 'Logs',
    'items' => [
        'system' => ['label' => 'System', 'url' => 'my-plugin/logs/system'],
        'activity' => ['label' => 'Activity', 'url' => 'my-plugin/logs/activity'],
    ],
],
```

### registerTranslations

Controls automatic translation registration. Enabled by default. Set to `false` if your plugin handles translations manually:

```php
'registerTranslations' => false,
```

### translationCategory / translationBasePath

Override the translation category or base path:

```php
'translationCategory' => 'my-custom-category',
'translationBasePath' => '/path/to/translations',
```

By default, the category is the plugin's `id` and the base path is `{pluginBasePath}/translations`.

### installExperience

Enable or customize the shared post-install CP modal:

```php
'installExperience' => [
    'headline' => 'Canvas Studio',
    'body' => 'Start shaping documents, fonts, and themes from one place.',
    'redirectUri' => 'canvas-studio',
    'ctaLabel' => 'Open Canvas Studio',
    'ctaUrl' => 'canvas-studio',
    'accent' => '#c2410c',
    'sidebarColor' => '#820EFF',
    'uiColor' => '#820EFF',
    'confettiPreset' => 'surprise',
],
```

Set to `false` to disable it entirely:

```php
'installExperience' => false,
```

Supported keys:

| Key | Type | Description |
|-----|------|-------------|
| `headline` | `string` | Main modal headline |
| `body` | `string` | Supporting body copy |
| `redirectUri` | `string` | CP URL to redirect to after install |
| `ctaLabel` | `string` | Primary button label |
| `ctaUrl` | `string` | Primary button URL |
| `accent` | `string` | Accent color fallback |
| `sidebarColor` | `string` | Left rail/background color |
| `uiColor` | `string` | CTA/eyebrow/confetti color override |
| `confettiPreset` | `string` | `surprise`, `spray`, `shower`, `fireworks`, `rain`, or `fountains` |

### Dev Preview

With `devMode` enabled, preview the install experience without reinstalling the plugin:

```text
/admin/tailwind-manager?lrInstallPreview=tailwind-manager
```

The query string value must match the plugin handle.

## The Twig Global

Bootstrap registers a `PluginNameHelper` instance as a Twig global. This helper proxies calls to your Settings model's display name methods (from [SettingsDisplayNameTrait](../feature-tour/settings-display-name.md)):

```twig
{{ redirectHelper.displayName }}          {# "Redirect" #}
{{ redirectHelper.fullName }}             {# "Redirect Manager" or custom name #}
{{ redirectHelper.pluralDisplayName }}    {# "Redirects" #}
{{ redirectHelper.lowerDisplayName }}     {# "redirect" #}
{{ redirectHelper.pluralLowerDisplayName }} {# "redirects" #}
```

It also provides cache path display helpers:

```twig
{{ redirectHelper.cacheBasePath }}        {# "storage/runtime/redirect-manager/cache/" #}
{{ redirectHelper.getCachePath('device') }} {# "storage/runtime/redirect-manager/cache/device/" #}
```

If the Settings model doesn't use `SettingsDisplayNameTrait`, the helper falls back to the plugin's default `name` property.

## Plugin Name Override

After bootstrapping, optionally apply a custom plugin name from a config file:

```php
PluginHelper::applyPluginNameFromConfig($this);
```

This checks `config/{plugin-handle}.php` for a `pluginName` key:

```php
// config/redirect-manager.php
return [
    'pluginName' => 'URL Redirects',
];
```

Supports environment-specific and wildcard configurations:

```php
return [
    '*' => ['pluginName' => 'Redirects'],
    'production' => ['pluginName' => 'URL Redirects (Prod)'],
];
```

Resolution order: config root level → environment-specific → wildcard (`*`) → database `pluginName` setting.

If your plugin uses DB-backed settings via `SettingsPersistenceTrait`, also apply config overrides when loading the Settings model:

```php
protected function createSettingsModel(): ?Model
{
    return PluginHelper::applyConfigOverridesToSettings(
        Settings::loadFromDatabase(),
        'redirect-manager'
    );
}
```

This keeps `$plugin->name` and `$settings->getFullName()` aligned. Without this, `/admin/settings` can show the configured plugin name while CP nav labels or permission headings still show the database/default name.

## Without Logging

If your plugin doesn't need logging integration, omit the permission arrays:

```php
PluginHelper::bootstrap($this, 'myHelper');
```

Logging is only configured when `$viewSystemLogsPermissions` is non-empty and `LoggingLibrary` is available.

## Next Steps

- [Plugin Helper](../feature-tour/plugin-helper.md) — cache paths, plugin detection, and other utilities
- [Settings Display Name](../feature-tour/settings-display-name.md) — display name methods used by the Twig global
- [Color Helper](../feature-tour/color-helper.md) — registering and using color sets
