# PluginThemeStyleHelper @since(5.34.0)

Use `PluginThemeStyleHelper` when a plugin needs branded CSS custom properties derived from its icon. It keeps icon color extraction and readable theme generation in Base, so setup pages, docs pages, and future branded plugin surfaces do not each invent their own gradient and contrast logic.

The helper reads the same icon roles as [ColorHelper](color-helper.md): the **accent** is the most saturated color in the SVG, and the **ink** is the least-saturated non-accent color, usually the glyph. Those roles are then expanded into CSS variables for the surface you are rendering.

## Setup Hero Variables

For setup pages that use [CP Plugin Setup Layout](../template-guides/cp-plugin-setup-layout.md), generate hero variables in the controller and pass the resulting string as `setupConfig.plugin.heroStyle`:

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

`heroCssVarsFromSvg()` returns the `--plugin-hero-*` variables used by Base setup surfaces:

- `--plugin-hero-accent`
- `--plugin-hero-ink`
- `--plugin-hero-ink-muted`
- `--plugin-hero-credit`
- `--plugin-hero-from`
- `--plugin-hero-to`
- `--plugin-hero-shadow`
- `--plugin-hero-button-text`
- `--plugin-hero-soft-surface`
- `--plugin-hero-badge-bg`
- `--plugin-hero-badge-border`
- `--plugin-hero-badge-text`

The optional `$style` argument accepts `lighter`, `primary`, `deeper`, or `diagonal`. Unknown values fall back to `lighter`.

```php
$style = PluginThemeStyleHelper::heroCssVarsFromSvg($iconSvg, 'deeper');
```

The same method is available in Twig:

```twig
{% set heroStyle = lrPluginHeroCssVars(plugin.iconSvg ?? null, 'deeper') %}
```

## Docs Shell Variables

Docs pages need more than a hero gradient. They also need shell colors for page backgrounds, links, buttons, code blocks, changelog markers, callouts, and copy buttons. Use `docsShellCssVarsFromSvg()` for only the docs shell variables:

```php
$shellStyle = PluginThemeStyleHelper::docsShellCssVarsFromSvg($iconSvg);
```

Use `docsCssVarsFromSvg()` when a docs surface needs both the hero variables and the docs shell variables in one string:

```php
$docsStyle = PluginThemeStyleHelper::docsCssVarsFromSvg($iconSvg);
```

The same helpers are available in Twig:

```twig
{% set shellStyle = lrPluginDocsShellCssVars(plugin.iconSvg ?? null) %}
{% set docsStyle = lrPluginDocsCssVars(plugin.iconSvg ?? null, 'lighter', '#0F766E') %}
```

The docs shell output includes the `--plugin-shell-*` family, such as:

- `--plugin-shell-bg`
- `--plugin-shell-bg-secondary`
- `--plugin-shell-text`
- `--plugin-shell-accent`
- `--plugin-shell-code`
- `--plugin-shell-pre-bg`
- `--plugin-shell-btn-bg`
- `--plugin-shell-changelog-link`
- `--plugin-shell-callout-warning`
- `--plugin-shell-copy-btn-bg`

These variables use CSS `light-dark()` values so the same generated string supports light and dark docs themes.

## Fallback Accent

If an icon is missing or contains no usable hex colors, the helper falls back to Base's default purple accent. Pass `$fallbackAccent` when a consumer needs a different fallback:

```php
$style = PluginThemeStyleHelper::heroCssVarsFromSvg(
    $iconSvg,
    'lighter',
    '#0F766E',
);
```

## When Not to Use It

Do not use `PluginThemeStyleHelper` for badge colors, filter colors, status dots, or ad hoc color math. Those remain [ColorHelper](color-helper.md) responsibilities.

Use this helper when you need a complete CSS-variable theme string for a plugin-branded surface.

## Next Steps

- [CP Plugin Setup Layout](../template-guides/cp-plugin-setup-layout.md) — passing `heroStyle` into setup pages
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md#plugin-theme-style-functions) — Twig-callable CSS variable helpers
- [ColorHelper](color-helper.md) — palette colors, color sets, and icon role extraction
- [PluginHelper](plugin-helper.md) — reading `src/icon.svg` from a plugin instance
