# Installation & Setup

LindemannRock Base is included automatically when you install any LindemannRock plugin. You do not need to install it separately.

## How It Gets Installed

When you require a LindemannRock plugin, Composer pulls in the base module as a dependency:

```bash
composer require lindemannrock/craft-search-manager
```

This also installs `lindemannrock/craft-plugin-base` because each plugin declares it in its `composer.json`.

## How It Gets Registered

Each LindemannRock plugin calls `PluginHelper::bootstrap()` in its `init()` method, which registers the base module with Craft:

```php
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();

    PluginHelper::bootstrap($this, 'myHelper', [...], [...]);
}
```

The base module registers itself idempotently — if multiple plugins call `bootstrap()`, the module only initializes once. This means all shared Twig extensions, template roots, and helpers are available regardless of which plugin triggered the registration first.

## Copy Config File (Optional)

To customize date/time formatting and export settings across all LindemannRock plugins, copy the config file:

```bash
cp vendor/lindemannrock/craft-plugin-base/src/config.php config/lindemannrock-base.php
```

Or with DDEV:

```bash
ddev exec cp vendor/lindemannrock/craft-plugin-base/src/config.php config/lindemannrock-base.php
```

See [Configuration](configuration.md) for all available settings.

## For Plugin Developers

If you are building a new LindemannRock plugin that depends on the base module, add it to your plugin's `composer.json`:

```json
{
    "require": {
        "lindemannrock/craft-plugin-base": "^5.0"
    }
}
```

Then call `PluginHelper::bootstrap()` in your plugin's `init()` method. See [Bootstrapping](../developers/bootstrapping.md) for a complete guide.
