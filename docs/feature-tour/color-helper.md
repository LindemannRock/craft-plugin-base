# ColorHelper @since(5.8.0)

Centralized color definitions for badges, filters, and status indicators. Provides a unified palette of 18 colors and 15 built-in color sets that plugins share for consistent styling.

## Palette Colors

The palette contains 18 colors, each with four properties:

| Property | Description | Example (`teal`) |
|----------|-------------|------------------|
| `class` | CSS class name | `'teal'` |
| `color` | Hex color for dots/indicators | `'#14b8a6'` |
| `rgb` | RGB values for semi-transparent backgrounds | `'20, 184, 166'` |
| `text` | Dark text color for readability | `'#115e59'` |

**Available colors:** `teal`, `cyan`, `gray`, `orange`, `red`, `blue`, `pink`, `purple`, `green`, `yellow`, `amber`, `emerald`, `indigo`, `violet`, `fuchsia`, `rose`, `lime`, `sky`

### Getting a Palette Color

```php
use lindemannrock\base\helpers\ColorHelper;

$teal = ColorHelper::getPaletteColor('teal');
// Returns: ['class' => 'teal', 'color' => '#14b8a6', 'rgb' => '20, 184, 166', 'text' => '#115e59']

// Unknown color names return the default color
$unknown = ColorHelper::getPaletteColor('magenta');
// Returns: ['color' => '#9aa5b1', 'rgb' => '154, 165, 177', 'text' => '#374151']
```

```php
// Get all palette color names
$names = ColorHelper::getPaletteColorNames();
// Returns: ['teal', 'cyan', 'gray', 'orange', 'red', 'blue', ...]
```

In Twig:

```twig
{% set color = lrPaletteColor('teal') %}
<span style="color: {{ color.color }}">Colored text</span>

{% set allNames = lrPaletteColorNames() %}
```

## Color Sets

Color sets map semantic values to palette colors. Use them with the badge component and filter components instead of hardcoding colors.

### Getting Colors from a Set

```php
// Get a specific color from a set
$enabledColor = ColorHelper::getSetColor('status', 'enabled');
// Returns teal palette color + dot: ['class' => 'teal', 'color' => '#14b8a6', ..., 'dot' => 'enabled']

// Get an entire color set
$statusColors = ColorHelper::getColorSet('status');
// Returns: ['enabled' => [...], 'disabled' => [...], 'pending' => [...], ...]

// Check if a color set exists
if (ColorHelper::hasColorSet('myCustomSet')) { ... }

// List all available color set names
$setNames = ColorHelper::getAvailableColorSets();
```

In Twig:

```twig
{% set color = lrSetColor('status', 'enabled') %}
{% set allColors = lrColorSet('status') %}
{% if lrHasColorSet('mySet') %}...{% endif %}
{% set names = lrAvailableColorSets() %}
```

### Built-In Color Sets

| Color Set | Values | Use Case |
|-----------|--------|----------|
| `status` | enabled, disabled, pending, expired, live, on, off | Craft element status |
| `yesNo` | yes, no, true, false | Boolean indicators |
| `handled` | yes, no, true, false | Handled state |
| `configSource` | config, database | Configuration source |
| `environmentType` | development, staging, production | Environment type |
| `priority` | low, normal, high, critical | Priority levels |
| `httpStatus` | success, redirect, client_error, server_error | HTTP response types |
| `logLevel` | debug, info, warning, error | Log severity |
| `logSource` | web, queue, console, php-errors, plugin | Log origin |
| `pluginStatus` | active, disabled, notInstalled | Plugin state |
| `exportStatus` | pending, processing, completed, failed | Export/job status |
| `triggerType` | manual, scheduled, api | Trigger types |
| `exportFormat` | xlsx, csv, json, zip | Export format types |
| `messageStatus` | pending, sent, delivered, failed | Message delivery |
| `healthStatus` | ok, low, high | Health checks |
| `backupReason` | import, restore, manual, scheduled, clean, clear, maintenance, other | Backup reasons |

> **Note:** Some color sets include a `dot` key (e.g., `status`, `pluginStatus`, `messageStatus`, `healthStatus`). The `dot` key maps to Craft's built-in status dot CSS classes (`enabled`, `disabled`, `pending`, `off`, `on`). Palette colors returned by `getPaletteColor()` do not include `dot`.

## Registering Custom Color Sets

Plugins register their own color sets during bootstrap:

```php
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\helpers\ColorHelper;

PluginHelper::bootstrap($this, 'myHelper', [...], [...], [
    'colorSets' => [
        'linkStatus' => [
            'active' => ColorHelper::getPaletteColor('teal'),
            'broken' => ColorHelper::getPaletteColor('red'),
            'redirect' => ColorHelper::getPaletteColor('orange'),
        ],
    ],
]);
```

Or register directly:

```php
ColorHelper::registerColorSet('linkStatus', [
    'active' => ColorHelper::getPaletteColor('teal'),
    'broken' => ColorHelper::getPaletteColor('red'),
    'redirect' => ColorHelper::getPaletteColor('orange'),
]);
```

## Filter Colors

The `getFilterColor()` method returns a color for filter display — the actual color when a filter value is selected, or the neutral gray when it is not.

```php
$color = ColorHelper::getFilterColor('status', 'enabled', $currentFilter);
// Returns '#14b8a6' if $currentFilter === 'enabled', otherwise '#aab6c1'
```

In Twig:

```twig
{{ lrFilterColor('status', 'enabled', currentFilter) }}
```

## Special Colors

```php
// Neutral gray for unselected filter items
$neutral = ColorHelper::getNeutralColor();  // '#aab6c1'

// Default fallback for unknown values
$default = ColorHelper::getDefaultColor();
// Returns: ['color' => '#9aa5b1', 'rgb' => '154, 165, 177', 'text' => '#374151']
```

In Twig:

```twig
{{ lrNeutralColor() }}     {# '#aab6c1' #}
{{ lrDefaultColor() }}     {# default color array #}
```

## Next Steps

- [Components](../template-guides/components.md) — using colors with badge, filter-status, and filter-dropdown components
- [Bootstrapping](../developers/bootstrapping.md) — registering custom color sets during plugin bootstrap
