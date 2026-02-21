# CP Analytics Layout

A reusable layout for analytics and dashboard pages with tabs, charts, stat boxes, date range filtering, site filtering, and export support.

## Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-analytics' %}

{% set analyticsConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Analytics'|t('my-plugin'),
        subnav: 'analytics',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
        ],
    },
    tabs: {
        overview: { label: 'Overview'|t('my-plugin') },
        details: { label: 'Details'|t('my-plugin') },
    },
    filters: {
        dateRange: { default: 'last7days', current: dateRange },
        sites: { enabled: true, current: siteId, sites: sites },
    },
    export: {
        permission: 'myPlugin:exportAnalytics',
        action: 'my-plugin/analytics/export',
    },
    charts: {
        prefix: 'myPlugin',
        dataEndpoint: 'my-plugin/analytics/get-data',
    },
} %}

{% block tabs %}
    <div id="overview" class="lr-tab-content">
        <div class="lr-analytics-stats">
            {% include 'lindemannrock-base/_components/stat-box' with {
                value: totalViews,
                label: 'Total Views'|t('my-plugin'),
                color: '#10b981',
            } only %}
        </div>

        <div class="lr-analytics-charts">
            <div class="lr-chart-container full-width">
                <h3>{{ 'Daily Trend'|t('my-plugin') }}</h3>
                <canvas id="daily-chart"></canvas>
            </div>
        </div>
    </div>

    <div id="details" class="lr-tab-content hidden">
        <div class="lr-table-scroll">
            <table class="data fullwidth">
                {# Detail table content #}
            </table>
        </div>
    </div>
{% endblock %}
```

## Configuration

### tabs

Define the tab structure. The first tab is active by default.

```twig
tabs: {
    overview: { label: 'Overview' },
    devices: { label: 'Devices' },
    geography: { label: 'Geography' },
},
```

### filters

#### Date Range Filter

```twig
filters: {
    dateRange: { default: 'last7days', current: dateRange },
},
```

#### Site Filter

```twig
filters: {
    sites: { enabled: true, current: siteId, sites: sites },
},
```

#### Custom Filters

```twig
filters: {
    custom: [
        {
            param: 'provider',
            current: providerId,
            label: 'All Providers'|t('my-plugin'),
            options: providerOptions,
        },
    ],
},
```

### export

Adds an export button to the toolbar.

```twig
export: {
    permission: 'myPlugin:exportAnalytics',
    action: 'my-plugin/analytics/export',
},
```

### charts

```twig
charts: {
    library: 'chartjs',
    prefix: 'myPlugin',
    dataEndpoint: 'my-plugin/analytics/get-data',
},
```

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `library` | `string` | `'chartjs'` | Chart library to use (currently only `'chartjs'`) |
| `prefix` | `string` | | Window variable prefix for chart instances |
| `dataEndpoint` | `string` | | Controller action for AJAX chart data |

## CSS Classes

| Class | Description |
|-------|-------------|
| `.lr-tab-content` | Tab wrapper (add `hidden` for non-active tabs) |
| `.lr-analytics-stats` | Stat boxes grid |
| `.lr-stat-box` | Individual stat box |
| `.lr-analytics-charts` | Charts grid |
| `.lr-analytics-charts.two-columns` | Two-column chart layout |
| `.lr-chart-container` | Chart wrapper |
| `.lr-chart-container.full-width` | Full-width chart |
| `.lr-table-scroll` | Scrollable table wrapper |

## Stat Box Component

```twig
{% include 'lindemannrock-base/_components/stat-box' with {
    value: 12345,
    label: 'Total Views',
    color: '#10b981',
    suffix: '%',
} only %}
```

## Overridable Blocks

| Block | Description |
|-------|-------------|
| `tabs` | Tab content (each tab in its own div) |
| `extraToolbar` | Additional toolbar items |
| `actionButton` | Action button area (export, etc.) |
| `scripts` | Additional JavaScript |

## JavaScript API

The analytics layout exposes globals for chart management: `lrLoadChartData` (AJAX data loading), `lrCreateChart` (Chart.js wrapper), `lrChartColors` (color palette), `lrDestroyCharts`, and `lrGetChart`. It fires `lr:analyticsInit` and `lr:tabChanged` events.

See [JavaScript API](../developers/javascript-api.md#analytics-api) for the full reference.

## Next Steps

- [CP Table Layout](cp-table-layout.md) — table pages with filters and pagination
- [Components](components.md) — stat-box, badge, and other reusable components
- [JavaScript API](../developers/javascript-api.md) — global functions and events
- [Front-End CSS](../developers/front-end-css.md) — CSS classes for charts, cards, and grids
- [DateRangeHelper](../feature-tour/date-range-helper.md) — date range parsing for analytics queries
