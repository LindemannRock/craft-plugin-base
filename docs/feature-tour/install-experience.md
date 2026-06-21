# Install Experience @since(5.19.0)

The install experience is a one-time welcome modal shown in the control panel immediately after a plugin is installed. It greets the user by plugin name, shows the installed version, and gives them a single button to jump straight into the plugin (or its settings) instead of leaving them on the generic plugin list.

It is opt-in per plugin and shows **once** — the payload is stored in the session on install, rendered on the next CP page load, then cleared.

## Enabling it

The simplest path is the `installExperience` option on `PluginHelper::bootstrap()`:

```php
use lindemannrock\base\helpers\PluginHelper;

PluginHelper::bootstrap($this, 'myPluginHelper', [...], [...], [
    'installExperience' => true,
]);
```

Pass an options array instead of `true` to customize the copy and branding:

```php
'installExperience' => [
    'headline' => 'Search Manager is ready',
    'body'     => 'Connect a backend and run your first index.',
    'ctaLabel' => 'Open Search Manager',
],
```

You can also register it directly:

```php
use lindemannrock\base\helpers\InstallExperienceHelper;

InstallExperienceHelper::register($this, [
    'headline' => 'Search Manager is ready',
]);
```

## register()

```php
InstallExperienceHelper::register(PluginInterface $plugin, array $options = []): void
```

`register()` hooks `Plugins::EVENT_AFTER_INSTALL_PLUGIN`. When *this* plugin is installed during a (non-console) CP request, it stores a payload in the session and redirects to the resolved landing page. On the next CP page render the modal partial and its asset bundle are injected, then the session entry is removed so it never shows again.

### Options

Every option has a sensible default, so `[]` produces a complete modal.

| Option | Default | Description |
|--------|---------|-------------|
| `headline` | `"{Plugin Name} is installed"` | Main heading |
| `body` | "Everything is wired up…" | Supporting paragraph |
| `eyebrow` | "Installed successfully" | Small label above the headline |
| `ctaLabel` | "Open plugin" / "Open settings" / "Continue" | Primary button text (resolved from the plugin's CP surface) |
| `ctaUrl` | the resolved redirect URI | Primary button target |
| `secondaryLabel` | `"Close"` | Secondary (dismiss) button text |
| `redirectUri` | CP section → settings → plugin list | Post-install landing page (CP-relative) |
| `accent` | `#0f766e` | Accent color |
| `sidebarColor` | the plugin icon's primary color | Modal sidebar color |
| `uiColor` | `sidebarColor` (or `accent`) | Accent for interactive UI bits |
| `theme` | `"classic"` | Modal theme preset |
| `confettiPreset` | `"surprise"` | Confetti animation preset |

When `redirectUri` / `ctaLabel` are not supplied, they are resolved from the plugin: a plugin with a CP section opens the section, one with only settings opens its settings page, otherwise the generic plugin list.

### Branding

The modal pulls the plugin's own icon SVG (via `PluginHelper::getIconSvg()`) and derives `sidebarColor` from the icon's primary color (`ColorHelper::primaryHexFromSvg()`), so each plugin's welcome screen matches its identity without extra configuration.

## Previewing during development

Because the modal only fires once on install, there is a dev-only preview. With `devMode` enabled, append the preview query param (the value is the plugin handle) to any CP URL for that plugin:

```
/admin/search-manager?lrInstallPreview=search-manager
```

The preview is ignored entirely when `devMode` is off.

## Under the hood

- **Partial** — `lindemannrock-base/_partials/install-experience` renders the modal markup.
- **Asset** — `InstallExperienceAsset` registers the modal CSS/JS; the JS exposes `window.LrInstallExperience.mount(payload)`.
- The payload is serialized into the page with [`JsonHelper::htmlSafeJson()`](../developers/api-reference.md) for safe inline embedding.

## Next Steps

- [Bootstrapping](../developers/bootstrapping.md) — where `installExperience` sits in the bootstrap options
- [PluginHelper](plugin-helper.md) — icon/version metadata used to brand the modal
