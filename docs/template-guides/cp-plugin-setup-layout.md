# CP Plugin Setup Layout

A reusable layout for plugin onboarding and readiness pages. Use this for persistent setup routes such as `my-plugin/setup`, where a plugin needs to show first-run tasks, missing configuration, commands, or follow-up actions before users start using the main workspace.

Unlike the one-time install experience, this layout is a normal Control Panel page. It should compute live status on every request, so it remains useful later from settings, help, or troubleshooting links.

## Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-plugin-setup' %}

{% set setupConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
        version: pluginVersion,
    },
    page: {
        badge: 'Setup'|t('my-plugin'),
        title: 'Set up My Plugin'|t('my-plugin'),
        description: 'Complete these steps before using public plugin features.'|t('my-plugin'),
    },
    actions: {
        primary: {
            label: 'Open My Plugin'|t('my-plugin'),
            url: cpUrl('my-plugin'),
        },
        secondary: {
            label: 'Open settings'|t('my-plugin'),
            url: cpUrl('settings/plugins/my-plugin'),
        },
    },
} %}

{% block tasks %}
    {% include 'lindemannrock-base/_components/setup-task' with {
        status: saltConfigured ? 'complete' : 'warning',
        statusLabel: saltConfigured ? 'Configured'|t('my-plugin') : 'Required'|t('my-plugin'),
        title: 'Configure IP hash salt'|t('my-plugin'),
        body: saltConfigured
            ? 'Analytics privacy salt is configured.'|t('my-plugin')
            : 'Analytics tracking requires a secure salt.'|t('my-plugin'),
        commands: saltConfigured ? [] : [
            {
                legend: 'Standard'|t('my-plugin'),
                value: 'php craft my-plugin/security/generate-salt',
                copyLabel: 'Copy'|t('my-plugin'),
                copiedMessage: 'Command copied to clipboard'|t('my-plugin'),
            },
        ],
    } only %}
{% endblock %}
```

For a branded hero, generate `heroStyle` in the controller with `PluginThemeStyleHelper::heroCssVarsFromSvg()` and pass the string through `setupConfig.plugin.heroStyle`:

```php
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\helpers\PluginThemeStyleHelper;

$iconSvg = PluginHelper::getIconSvg($plugin);

return $this->renderTemplate('my-plugin/setup', [
    'pluginIconSvg' => $iconSvg,
    'pluginHeroStyle' => PluginThemeStyleHelper::heroCssVarsFromSvg($iconSvg),
]);
```

```twig
{% set setupConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
        iconSvg: pluginIconSvg,
        heroStyle: pluginHeroStyle,
    },
} %}
```

## Configuration

### plugin

| Key | Type | Description |
|-----|------|-------------|
| `handle` | `string` | Plugin handle used for default route fallbacks |
| `name` | `string` | Plugin display name |
| `version` | `string` | Optional version text shown below the title |
| `iconSvg` | `string` | Optional plugin icon SVG markup rendered in the hero |
| `logoPaths` | `string` | Optional LindemannRock logo path markup for the hero background |
| `heroStyle` | `string` | Optional CSS custom properties, usually from `PluginThemeStyleHelper::heroCssVarsFromSvg()` |
| `heroMarkRotation` | `int` | Optional LR background mark rotation in degrees |
| `heroMarkTranslateX` | `string` | Optional LR background mark X translation |
| `sidebarColor` | `string` | Optional fallback color for the hero sidebar |

### page

| Key | Type | Description |
|-----|------|-------------|
| `badge` | `string` | Optional eyebrow/status text above the title |
| `title` | `string` | Page title |
| `description` | `string` | Short setup intro |

### actions

| Key | Type | Description |
|-----|------|-------------|
| `primary` | `array` | Optional primary link with `label` and `url` |
| `secondary` | `array` | Optional secondary link with `label` and `url` |

Pass translated labels from the consuming plugin. The layout does not emit setup-specific copy of its own.

## Blocks

### heroAside

Optional content rendered beside the hero copy. Use this for a compact status summary, documentation links, or a “setup complete” note.

```twig
{% block heroAside %}
    {% include 'lindemannrock-base/_components/info-box' with {
        message: allComplete
            ? 'Setup is complete.'|t('my-plugin')
            : 'Some setup tasks still need attention.'|t('my-plugin'),
        type: allComplete ? 'success' : 'warning',
        margin: 'none',
    } %}
{% endblock %}
```

### summary

Optional content between the hero and the task grid. Use it for contextual warnings or installation notes.

### tasks

Main checklist content. Compose this block with [setup-task](components.md#setup-task) components.

### sidebar

Optional side panel. Use it for links to docs, settings, or related plugin tools.

### additionalContent

Full-width content after the setup grid.

### scripts

Page-specific JavaScript handlers when needed.

## Setup Task Component

Use `lindemannrock-base/_components/setup-task` for individual checklist rows. It supports live status, body copy, copyable commands, links, and custom action HTML for plugin-owned POST forms.

See [Components](components.md#setup-task) for the complete parameter reference.

For plugin-wide reminders outside the setup page, use `lindemannrock-base/_components/setup-incomplete` and let the consuming plugin pass its status result and translated copy.

## Next Steps

- [PluginThemeStyleHelper](../feature-tour/plugin-theme-style-helper.md) — deriving branded hero CSS variables from plugin icons
- [Components](components.md) — setup-incomplete, setup-task, info-box, copy-input, and related UI components
- [Install Experience](../feature-tour/install-experience.md) — one-time install modal that can route users to a setup page
- [CP Utilities Layout](cp-utilities-layout.md) — utility pages for ongoing tools and overview actions
