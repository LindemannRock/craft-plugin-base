# Quickstart

Bootstrap Base in a Craft plugin and render a shared component. By the end of this guide, the module will be registered and a Base badge will appear in your plugin's Control Panel template.

## Before you start

Complete [Installation and setup](installation.md#post-install-setup) first. Your plugin should already:

- Require `lindemannrock/craft-plugin-base` through Composer.
- Have a working plugin class and CP template.

## 1. Bootstrap Base

Call `PluginHelper::bootstrap()` from the plugin's `init()` method:

```php
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();

    PluginHelper::bootstrap($this, 'myPluginHelper');
}
```

This registers Base once per request, even when several plugins depend on it.

## 2. Render a shared component

Include the badge component from a CP template:

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Ready'|t('my-plugin'),
    status: 'teal',
} only %}
```

## 3. Verify it works

Open the plugin's CP page. You should see a teal **Ready** badge and no unknown-template or missing-module error.

## What's next

- [Bootstrapping](../developers/bootstrapping.md) — configure logging, translations, color sets, and the install experience
- [Components](../template-guides/components.md) — choose from the complete shared component set
- [CP Table Layout](../template-guides/cp-table-layout.md) — build a searchable plugin index page
- [Configuration](configuration.md) — set project-wide date, export, and date-range defaults
