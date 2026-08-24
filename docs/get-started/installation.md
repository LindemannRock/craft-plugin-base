# Installation and setup

LindemannRock Base is included automatically when you install any LindemannRock plugin. You do not need to install it separately.

## How it gets installed

When you require a LindemannRock plugin, Composer pulls in the base module as a dependency:

```bash title="Composer"
composer require lindemannrock/craft-search-manager
```

```bash title="DDEV"
ddev composer require lindemannrock/craft-search-manager
```

This also installs `lindemannrock/craft-plugin-base` because each plugin declares it in its `composer.json`.

## How it gets registered

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

## Post-install setup

Base has no separate plugin-install or setup action. Confirm that your consuming plugin calls `PluginHelper::bootstrap()` during `init()`; that call registers the module, template root, translations, and Twig extensions.

See [Configuration](configuration.md) if the project needs global date/time, export-format, or date-range overrides.

## For plugin developers

If you are building a new LindemannRock plugin that depends on the base module, add it to your plugin's `composer.json`:

```json
{
    "require": {
        "lindemannrock/craft-plugin-base": "^5.0"
    }
}
```

Then call `PluginHelper::bootstrap()` in your plugin's `init()` method. See [Bootstrapping](../developers/bootstrapping.md) for a complete guide.

## Quick start

See [Quickstart](quickstart.md) for the shortest path from dependency installation to a rendered Base component.
