# SettingsConfigTrait @since(5.0.0)

Detects when settings are overridden by a config file and shows lock icons in the CP. Also provides log level validation that prevents `debug` logging in production.

## How It Works

When a setting is defined in `config/{plugin-handle}.php`, it takes precedence over the database value. The trait lets you detect this and show a visual indicator in the CP settings UI.

## Setup

```php
use craft\base\Model;
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;

class Settings extends Model
{
    use SettingsPersistenceTrait;
    use SettingsConfigTrait;

    protected static function tableName(): string { return 'myplugin_settings'; }

    // Required: identifies which config file to check
    protected static function pluginHandle(): string { return 'my-plugin'; }

    public string $pluginName = 'My Plugin';
    public string $logLevel = 'error';
    public bool $enableAnalytics = true;
}
```

## Checking for Config Overrides

```php
$settings = MyPlugin::$plugin->getSettings();

// Simple field check
if ($settings->isOverriddenByConfig('pluginName')) {
    // This setting comes from config/my-plugin.php
}

// Dot notation for nested config
if ($settings->isOverriddenByConfig('backends.algolia.enabled')) {
    // Nested setting is defined in config
}
```

The method checks the resolved config array returned by Craft's `getConfigFromFile()`. Craft handles environment-specific (`production`, `dev`) and wildcard (`*`) merging internally — by the time `isOverriddenByConfig()` runs, the config is already flattened to a single array for the current environment. The check is a simple `array_key_exists()` on the resolved config (or dot-notation traversal for nested keys).

## Template Integration

Show a lock icon and "defined in config" message for config-overridden settings:

```twig
{% if settings.isOverriddenByConfig('pluginName') %}
    <span class="info">Defined in <code>config/my-plugin.php</code></span>
{% endif %}
```

Typical pattern for a settings field:

```twig
{{ forms.textField({
    label: 'Plugin Name'|t('my-plugin'),
    name: 'pluginName',
    value: settings.pluginName,
    disabled: settings.isOverriddenByConfig('pluginName'),
    warning: settings.isOverriddenByConfig('pluginName')
        ? 'This setting is defined in config/my-plugin.php'|t('my-plugin')
        : null,
}) }}
```

## Integration with SettingsPersistenceTrait

When both traits are used together, `saveToDatabase()` automatically skips fields that are overridden by the config file. This prevents database writes from overwriting config-defined values.

## Log Level Validation

The trait includes a validator that prevents `debug` logging when `devMode` is disabled:

```php
public function rules(): array
{
    return [
        [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
        [['logLevel'], 'validateLogLevel'],
    ];
}
```

When `devMode` is off and `logLevel` is `'debug'`, the validator corrects it to `'info'` **in memory only**. The user's `'debug'` preference is preserved in the database so it takes effect again when `devMode` is re-enabled.

Behavior by source:

- **Value from config file** — corrects in-memory, logs a session-deduplicated warning suggesting you update `config/{plugin-handle}.php`.
- **Value from database** — corrects in-memory only (does not write back to the database), logs a session-deduplicated warning.

## Next Steps

- [SettingsPersistenceTrait](settings-persistence.md) — database storage for settings
- [SettingsDisplayNameTrait](settings-display-name.md) — custom plugin display names
- [Configuration](../get-started/configuration.md) — base plugin config reference
