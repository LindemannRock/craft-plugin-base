# SettingsConfigTrait

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

The method checks these locations in order:
1. Root level: `return ['setting' => 'value']`
2. Environment-specific: `return ['production' => ['setting' => 'value']]`
3. Wildcard: `return ['*' => ['setting' => 'value']]`

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

When `devMode` is off:
- `debug` is automatically changed to `info`
- If the value came from the database, the correction is saved back
- If the value came from a config file, a warning is logged

## Next Steps

- [SettingsPersistenceTrait](settings-persistence.md) — database storage for settings
- [SettingsDisplayNameTrait](settings-display-name.md) — custom plugin display names
- [Configuration](../get-started/configuration.md) — base plugin config reference
