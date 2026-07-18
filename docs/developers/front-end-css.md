# Front-End CSS

The base module ships a single CSS file (`components.css`) loaded by the `ComponentsAsset` bundle. It provides CSS classes for cards, stat boxes, chart containers, table enhancements, and utility helpers used across all LindemannRock plugins.

The CSS loads automatically when a plugin uses the [CP Table Layout](../template-guides/cp-table-layout.md), [CP Analytics Layout](../template-guides/cp-analytics-layout.md), or a reusable template that emits `ComponentsAsset`-owned classes. **Styled Twig component self-registration** @since(5.36.0) makes those components independent of their surrounding layout; manual bundle registration is only needed when a template writes the raw CSS classes directly instead of including the corresponding base component or partial. Layout-level registrations remain in place and are safely deduplicated by Craft.

## Card Grids

Three card grid systems share the same API. Wrap cards in a container with an optional column count class.

### Containers

| Class | Default Columns | Description |
|-------|----------------|-------------|
| `.lr-dashboard-cards` | Auto-fit (min 250px) | Dashboard navigation cards |
| `.lr-overview-cards` | Auto-fit (min 250px) | Overview summary cards |
| `.lr-unified-cards` | Auto-fit (min 250px) | General-purpose card grid |
| `.lr-analytics-stats` | Auto-fit (min 250px) | Stat box grid |

### Column Modifiers

Add a column class to fix the grid layout:

| Class | Columns |
|-------|---------|
| `.cols-2` | 2 columns |
| `.cols-3` | 3 columns |
| `.cols-4` | 4 columns |
| `.cols-5` | 5 columns (unified cards and analytics stats only) |

All column layouts collapse to a single column on screens below 768px.

```twig
<div class="lr-unified-cards cols-4">
    {# Cards here #}
</div>
```

## Card Types

### Stat Card (`.lr-stat-card`)

Simple metric display with a centered value and label.

```html
<div class="lr-stat-card">
    <div class="lr-stat-value">1,234</div>
    <div class="lr-stat-label">Total Views</div>
</div>
```

| Modifier | Effect |
|----------|--------|
| `.lr-stat-card-colored` | Transparent background with colored border |
| `.lr-stat-card-small` | Reduced padding (16px) and 24px value font |

### Dashboard Card (`.lr-dashboard-card`)

Clickable navigation card with dot indicator, values, and change badge.

```html
<a href="/admin/my-plugin/links" class="lr-dashboard-card">
    <div class="lr-dashboard-card-header">
        <span class="lr-dashboard-card-dot" style="background: #10b981"></span>
        <h3 class="lr-dashboard-card-title">Active Links</h3>
    </div>
    <div class="lr-dashboard-card-values">
        <span class="lr-dashboard-card-primary">456</span>
        <span class="lr-dashboard-card-secondary">links</span>
    </div>
    <span class="lr-dashboard-card-change positive">+12%</span>
    <div class="lr-dashboard-card-description">Since last month</div>
</a>
```

| Class | Description |
|-------|-------------|
| `.lr-dashboard-card-change.positive` | Green badge (increase) |
| `.lr-dashboard-card-change.negative` | Red badge (decrease) |

### Overview Card (`.lr-overview-card`)

Non-clickable display card with optional sub-boxes.

| Class | Description |
|-------|-------------|
| `.lr-overview-card-header` | Dot + title row |
| `.lr-overview-card-dot` | Color dot inside header |
| `.lr-overview-card-title` | Title text inside header |
| `.lr-overview-card-content` | Flex column wrapper for card body |
| `.lr-overview-card-value-row` | Row containing primary value + badge |
| `.lr-overview-card-primary` | Large value (28px) |
| `.lr-overview-card-badge` | Colored badge next to value |
| `.lr-overview-card-description` | Description text |
| `.lr-overview-card-subboxes` | Grid of smaller metric boxes |
| `.lr-overview-card-subbox` | Individual sub-box |
| `.lr-overview-card-subbox-value` | Sub-box value |
| `.lr-overview-card-subbox-label` | Sub-box label |

### Unified Card (`.lr-unified-card`)

Flexible card with alignment variants, badges, and sub-boxes. Combines features from dashboard and overview cards.

| Modifier | Effect |
|----------|--------|
| `a.lr-unified-card-link` | Clickable card with hover animation |
| `.align-center` | Center-aligned content |
| `.align-end` | Right-aligned content |

| Class | Description |
|-------|-------------|
| `.lr-unified-card-header` | Dot + title + badge row |
| `.lr-unified-card-title` | Title text inside header |
| `.lr-unified-card-value-row` | Row containing value + badge |
| `.lr-unified-card-badge` | Default gray badge |
| `.lr-unified-card-badge.positive` | Green badge |
| `.lr-unified-card-badge.negative` | Red badge |
| `.lr-unified-card-badge.custom` | Custom-colored badge (set `background` inline) |
| `.lr-unified-card-value` | Large value (32px) |
| `.lr-unified-card-secondary` | Secondary metric |
| `.lr-unified-card-description` | Description text |
| `.lr-unified-card-subboxes` | Sub-box grid |
| `.lr-unified-card-subbox` | Individual sub-box |
| `.lr-unified-card-subbox-value` | Sub-box value |
| `.lr-unified-card-subbox-label` | Sub-box label |

## Analytics Layout Classes

### Panel & Stats

| Class | Description |
|-------|-------------|
| `.lr-analytics-panel` | Main analytics panel wrapper (`position: relative`) |
| `.lr-analytics-stats.compact` | Compact stat grid (min 140px, smaller gap) |
| `.lr-section-heading` | Section heading (18px, 600 weight, `margin: 30px 0 20px`) |
| `.lr-analytics-empty` | Empty state wrapper for analytics panels |

### Charts

| Class | Description |
|-------|-------------|
| `.lr-analytics-charts` | Chart grid (auto-fit, min 280px) |
| `.lr-analytics-charts.two-columns` | Fixed two-column chart grid |
| `.lr-chart-container` | White bordered chart wrapper (`display: flex; flex-direction: column`). When the last child is a height-utility (`.lr-chart-height-200/250/300`) or `.lr-chart-center`, it receives `flex: 1` automatically — the chart canvas expands to fill remaining vertical space when the card is grid-stretched. |
| `.lr-chart-container.full-width` | Spans full grid width |
| `.lr-chart-canvas` | Full-width canvas (`width: 100% !important; height: 100% !important`) |
| `.lr-chart-loading` | Loading state (centered, 200px min height) |
| `.lr-chart-empty` | Empty state message |

```twig
<div class="lr-analytics-charts two-columns">
    <div class="lr-chart-container full-width">
        <h3>Daily Trend</h3>
        <canvas id="daily-chart"></canvas>
    </div>
    <div class="lr-chart-container">
        <h3>By Country</h3>
        <canvas id="country-chart"></canvas>
    </div>
</div>
```

### Chart Height Helpers

| Class | Height |
|-------|--------|
| `.lr-chart-height-200` | 200px |
| `.lr-chart-height-250` | 250px |
| `.lr-chart-height-300` | 300px |
| `.lr-chart-center` | Flex centered, max 300px width and height on canvas |

### Tab Content

```twig
<div id="overview" class="lr-tab-content">
    {# Active tab content #}
</div>
<div id="details" class="lr-tab-content hidden">
    {# Hidden tab content #}
</div>
```

### Analytics Header

```html
<div class="lr-analytics-header">
    <div class="lr-analytics-header-filters">
        {# Date range, site selector, etc. #}
    </div>
    {# Export button #}
</div>
```

### Loading Overlay

| Class | Description |
|-------|-------------|
| `.lr-analytics-loader` | Overlay container (hidden by default) |
| `.lr-analytics-loader.is-active` | Show overlay |
| `.lr-analytics-loader-inner` | White rounded box with spinner + text |
| `.lr-analytics-spinner` | Animated spinning circle |

## Table Layout Classes

### Row Highlighting

Apply tone classes to `<tr>` elements for contextual row backgrounds.

| Class | Background | Use Case |
|-------|------------|----------|
| `.lr-row--info` | Blue | Informational |
| `.lr-row--success` | Green | Success state |
| `.lr-row--warning` | Amber | Warning state |
| `.lr-row--danger` | Red | Error state |

Each tone sets `--hover-bg-color` and `--selected-bg-color` custom properties on the `<tr>` for Craft element index integration. Selected rows use `.sel` / `.selected` class selectors.

### Expandable Rows

| Class | Description |
|-------|-------------|
| `.lr-clickable-row` | Pointer cursor, hover highlight |
| `.lr-context-row` | Hidden detail row |
| `.lr-context-content` | Detail content with left blue border |

### Config Tooltip

Display config file contents on hover.

| Class | Description |
|-------|-------------|
| `.lr-config-info-icon` | Gray circle with "i" — triggers tooltip on hover |
| `.lr-config-tooltip` | Positioned tooltip with monospace text |
| `.lr-config-tooltip-header` | Muted header (e.g., config file path) |

```html
<span class="lr-config-info-icon"
      data-config="'apiKey' => 'sk-...'"
      data-config-source="config/my-plugin.php"></span>
```

### Column Visibility

| Class | Description |
|-------|-------------|
| `.lr-column-hidden` | `display: none !important` — applied to hidden columns |
| `.lr-view-menu` | View dropdown menu (min 280px) |

### AJAX Refresh Notice

| Class | Description |
|-------|-------------|
| `.lr-refresh-notice` | Inline notice with countdown |
| `.lr-refresh-notice.is-refreshing` | Animated spinner state |
| `.lr-refresh-notice.is-paused` | Refresh is paused because rows are selected |

## Utility Classes

### Typography

| Class | Description |
|-------|-------------|
| `.lr-text-center` | Center-aligned text |
| `.lr-text-end` | End-aligned text |
| `.lr-text-start` | Start-aligned text |
| `.lr-text-muted` | Muted gray text (`#6b7280`) |

### Color Helpers

| Class | Color |
|-------|-------|
| `.lr-text-purple` | `#9b59b6` |
| `.lr-text-green` | `#27ae60` |
| `.lr-text-red` | `#e74c3c` |
| `.lr-text-blue` | `#3498db` |
| `.lr-text-amber` | `#f39c12` |

### Spacing & Layout

| Class | Description |
|-------|-------------|
| `.lr-mt-24` | `margin-top: 24px` |
| `.lr-mt-30` | `margin-top: 30px` |
| `.lr-mb-16` | `margin-bottom: 16px` |
| `.lr-mb-24` | `margin-bottom: 24px` |
| `.lr-mb-30` | `margin-bottom: 30px` |
| `.lr-inline-flex` | Inline flex with centered items |
| `.lr-gap-4` | `gap: 4px` |
| `.lr-border-top-muted` | `border-top: 2px solid #e5e7eb` |

### Content

| Class | Description |
|-------|-------------|
| `.lr-heading-tight` | Heading with `margin: 0 0 10px` |
| `.lr-paragraph` | Paragraph with `margin: 0 0 20px` |
| `.lr-note` | Small note text (12px) |
| `.lr-note-tight` | Note with tighter top margin |
| `.lr-footnote` | Footnote with top margin |
| `.lr-muted-center` | Centered muted text |
| `.lr-empty-icon` | Large faded icon for empty states |

### Misc

| Class | Description |
|-------|-------------|
| `.lr-menu-header` | Styled section header inside dropdown menus |
| `.lr-info-box-table-wrapper` | Padding wrapper for info-box/table layout |

### Table Scroll

```twig
<div class="lr-table-scroll">
    <table class="data fullwidth">
        {# Table content #}
    </table>
</div>
```

Provides horizontal scrolling on narrow screens.

## Word Cloud

For tag/keyword visualization in analytics.

| Class | Description |
|-------|-------------|
| `.lr-word-cloud` | Container (300px height) |
| `.lr-word-cloud-item` | Individual word (blue `#0d78f2`, hover: orange `#d35400` + scale) |
| `.lr-peak-label` | Centered label below chart |

## Responsive Behavior

- All card grids collapse to single column below 768px
- Two-column chart grids collapse below 1024px (900px inside `.lr-analytics-panel`)
- Table scroll containers add horizontal scrolling below 768px
- Dashboard and overview card primary values shrink from 32px to 24px on mobile (unified cards do not shrink)

## Next Steps

- [JavaScript API](javascript-api.md) — global functions and events
- [CP Table Layout](../template-guides/cp-table-layout.md) — table layout configuration
- [CP Analytics Layout](../template-guides/cp-analytics-layout.md) — analytics dashboard layout
- [Components](../template-guides/components.md) — Twig component reference
