# LindemannRock Plugin Base

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-plugin-base.svg)](https://packagist.org/packages/lindemannrock/craft-plugin-base)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-plugin-base.svg)](LICENSE.md)

Common utilities and building blocks for LindemannRock Craft CMS plugins.

## Overview

This package provides shared functionality for all LindemannRock plugins:

- **Traits** for Settings models (displayName, database persistence, config overrides)
- **Twig Extensions** for plugin name helpers in templates
- **Helpers** for common plugin initialization tasks
- **Templates** for shared components (plugin-credit, info-box, ip-salt-error)

## Requirements

- Craft CMS 5.0+
- PHP 8.2+

## Installation

### Via Composer

```bash
cd /path/to/project
composer require lindemannrock/craft-plugin-base
```

### Using DDEV

```bash
cd /path/to/project
ddev composer require lindemannrock/craft-plugin-base
```

## Usage

### In Settings Model

```php
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;

class Settings extends Model
{
    use SettingsDisplayNameTrait;
    use SettingsPersistenceTrait;
    use SettingsConfigTrait;

    public string $pluginName = 'My Plugin';

    protected static function tableName(): string
    {
        return 'myplugin_settings';
    }

    protected static function pluginHandle(): string
    {
        return 'my-plugin';
    }

    // Optional: specify field types for database persistence
    protected static function booleanFields(): array
    {
        return ['enableFeature', 'debugMode'];
    }

    protected static function integerFields(): array
    {
        return ['cacheTimeout', 'maxItems'];
    }

    protected static function jsonFields(): array
    {
        return ['excludePatterns', 'customSettings'];
    }
}
```

### In Main Plugin Class

```php
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();

    // Bootstrap base module (registers Twig extension + logging)
    PluginHelper::bootstrap($this, 'myPluginHelper', ['myPlugin:viewLogs']);

    // Apply plugin name from config file
    PluginHelper::applyPluginNameFromConfig($this);
}
```

### In Templates

```twig
{# Plugin name helpers (via Twig extension) #}
{{ myPluginHelper.displayName }}       {# "My Plugin" #}
{{ myPluginHelper.fullName }}          {# "My Plugin Manager" #}
{{ myPluginHelper.pluralDisplayName }} {# "My Plugins" #}
{{ myPluginHelper.lowerDisplayName }}  {# "my plugin" #}

{# Shared components #}
{% include 'lindemannrock-base/_components/plugin-credit' %}

{% include 'lindemannrock-base/_components/info-box' with {
    message: 'This is an informational message',
    type: 'info'  {# 'info', 'success', 'warning' #}
} %}

{% include 'lindemannrock-base/_components/ip-salt-error' with {
    pluginHandle: 'my-plugin',
    envVarName: 'MY_PLUGIN_IP_SALT'
} %}
```

## Components

### Traits

| Trait | Methods Provided |
|-------|------------------|
| `SettingsDisplayNameTrait` | `getDisplayName()`, `getFullName()`, `getPluralDisplayName()`, `getLowerDisplayName()`, `getPluralLowerDisplayName()` |
| `SettingsPersistenceTrait` | `loadFromDatabase()`, `saveToDatabase()` |
| `SettingsConfigTrait` | `isOverriddenByConfig()` |

### Templates

| Template | Purpose |
|----------|---------|
| `plugin-credit` | Footer credit with plugin name and developer link |
| `info-box` | Styled info/success/warning message box |
| `ip-salt-error` | Error banner for missing IP hash salt configuration |

### Helpers

| Helper | Purpose |
|--------|---------|
| `PluginHelper::bootstrap()` | Registers base module, Twig extension, and logging |
| `PluginHelper::applyPluginNameFromConfig()` | Applies custom plugin name from config file |

## Documentation

- [docs/MIGRATION.md](docs/MIGRATION.md) - How to migrate existing plugins
- [docs/TRAITS.md](docs/TRAITS.md) - Detailed trait documentation

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-plugin-base](https://github.com/LindemannRock/craft-plugin-base)
- **Issues**: [https://github.com/LindemannRock/craft-plugin-base/issues](https://github.com/LindemannRock/craft-plugin-base/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
