# JavaScript API

The base module ships JavaScript assets that expose global functions and events for plugin developers. The main shared assets load automatically when a plugin uses the [CP Table Layout](../template-guides/cp-table-layout.md) or [CP Analytics Layout](../template-guides/cp-analytics-layout.md).

## Identifier API

Available when the base components asset is registered:

```twig
{% do view.registerAssetBundle('lindemannrock\\base\\web\\assets\\components\\ComponentsAsset') %}
```

Use this API for live Control Panel previews of slug-like handles. PHP save handlers must still normalize authoritatively with `SlugHandleHelper`.

### Globals

| Global | Type | Description |
|--------|------|-------------|
| `window.lrIdentifiers` | `object` | Identifier normalization and input binding helpers |

### `lrIdentifiers.normalizeSlug(value, fallback)`

Normalize a value using the same lowercase kebab-style rules as `SlugHandleHelper::normalizeSlug()`.

```javascript
window.lrIdentifiers.normalizeSlug('TEst this thing');
// -> 'test-this-thing'
```

### `lrIdentifiers.bindSlugHandle(sourceInput, targetInput, options)`

Bind a name/title input to a slug-like handle input.

```javascript
window.lrIdentifiers.bindSlugHandle('#name', '#handle', {
    isNew: true,
});
```

Options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `isNew` | `boolean` | `true` | Existing records do not auto-update by default |
| `updateExisting` | `boolean` | `false` | Allow auto-updating existing records until manual edit |
| `fallback` | `string\|function` | `''` | Fallback passed to `normalizeSlug()` |
| `manuallyEdited` | `boolean` | depends on `isNew` | Initial manual-edit state |
| `updateOnBind` | `boolean` | `false` | Generate immediately when binding |

The binding stops auto-updating after the target input is edited manually.

## Table Selection API

Available on pages using the CP Table Layout with checkboxes enabled.

### Globals

| Global | Type | Description |
|--------|------|-------------|
| `window.lrTableSelection` | `object` | Selection management |
| `window.lrBuildUrl(params)` | `function` | Build URL with query parameters |
| `window.lrTableConfig` | `object` | Current table configuration |
| `window.lrViewSettings` | `object` | Column visibility management |

### `lrTableSelection`

Manage row checkbox selections programmatically.

```javascript
// Get selected row IDs as an array
var ids = window.lrTableSelection.getSelectedIds();
// → [12, 34, 56]

// Get the count of selected rows
var count = window.lrTableSelection.getCount();
// → 3

// Clear all selections
window.lrTableSelection.clear();

// Re-bind checkbox listeners (after DOM changes)
window.lrTableSelection.rebindCheckboxes();
```

### `lrBuildUrl`

Build a URL with query parameters from the current page context.

```javascript
// Navigate to page 2 sorted by name
var url = window.lrBuildUrl({ page: 2, sort: 'name', dir: 'asc' });
// → "/admin/my-plugin/items?page=2&sort=name&dir=asc&status=all"
```

Parameters merge with the table's canonical `page.url`, current request context, and configured filter state (status, search, date range, etc.). Matching scalar keys are replaced rather than repeated, so Craft's `?site=en` context does not accumulate during pagination or sorting. The object passed to `lrBuildUrl()` applies last; use `null`, `undefined`, or an empty string to remove a parameter. Query encoding and canonical URL fragments are preserved.

### `lrViewSettings`

Manage column visibility. Settings persist in `localStorage` per plugin/subnav combination.

```javascript
// Get stored view settings
var settings = window.lrViewSettings.get();
// → { hiddenColumns: ['email', 'phone'] }

// Save view settings
window.lrViewSettings.save({ hiddenColumns: ['email'] });

// Toggle a single column (DOM only — does not persist to localStorage)
window.lrViewSettings.toggleColumn('email', true);  // show
window.lrViewSettings.toggleColumn('email', false); // hide

// Get hidden column keys
var hidden = window.lrViewSettings.getHiddenColumns();
// → ['email', 'phone']

// Reset to defaults and reload page
window.lrViewSettings.reset();
```

### `lrTableConfig`

Read-only object with the current table configuration.

```javascript
var config = window.lrTableConfig;
// {
//   ajaxEnabled: true,
//   ajaxInterval: 30,
//   ajaxEndpoint: '/actions/my-plugin/items/get-data',
//   page: 1,
//   urlParams: {status: 'all', sort: 'dateCreated', dir: 'desc'},
//   viewStorageKey: 'lr-table-view-my-plugin-items',
//   ...
// }
```

## Analytics API

Available on pages using the CP Analytics Layout. Requires Chart.js (bundled with the analytics asset).

### Globals

| Global | Type | Description |
|--------|------|-------------|
| `window.lrChartColors` | `string[]` | 12-color palette for chart datasets |
| `window.lrChartInstances` | `object` | Chart instance store (keyed by `prefix-canvasId`) |
| `window.lrAnalyticsConfig` | `object` | Current analytics configuration |
| `window.currentTab` | `string` | Currently active tab ID (set by CP Analytics Layout) |
| `window.lrLoadChartData(type, callback, extraParams)` | `function` | Fetch chart data via AJAX |
| `window.lrCreateChart(canvasId, type, data, options)` | `function` | Create a Chart.js chart |
| `window.lrDestroyCharts(prefix)` | `function` | Destroy all charts for a prefix |
| `window.lrGetChart(canvasId, prefix)` | `function` | Get a chart instance |
| `window.lrAnalyticsInit(config)` | `function` | Initialize analytics (called by the layout) |

### `lrChartColors`

A 12-color palette for consistent chart styling across all plugins.

```javascript
window.lrChartColors
// → ['#0d78f2', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#06b6d4',
//    '#ec4899', '#84cc16', '#f97316', '#6366f1', '#14b8a6', '#f43f5e']
```

Use these when building chart datasets:

```javascript
data.datasets.forEach(function(dataset, i) {
    dataset.borderColor = window.lrChartColors[i % window.lrChartColors.length];
});
```

### `lrLoadChartData`

Fetch chart data from the plugin's data endpoint via AJAX. Automatically includes the current date range, site ID, and CSRF token.

```javascript
window.lrLoadChartData('daily-trend', function(data) {
    window.lrCreateChart('daily-chart', 'line', data);
});

// With extra parameters
window.lrLoadChartData('by-country', function(data) {
    window.lrCreateChart('country-chart', 'doughnut', data);
}, { limit: 10 });
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | `string` | Chart data type (passed to the controller) |
| `callback` | `function` | Called with `response.data` on success |
| `extraParams` | `object` | Additional parameters to include in the request |

The function POSTs to `analyticsConfig.dataEndpoint` with `type`, `dateRange`, `siteId`, and any extra params.

### `lrCreateChart`

Create a Chart.js chart with sensible defaults.

```javascript
var chart = window.lrCreateChart('canvas-id', 'line', chartData);

// With custom options
var chart = window.lrCreateChart('canvas-id', 'bar', chartData, {
    plugins: { legend: { display: false } },
});
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `canvasId` | `string` | ID of the `<canvas>` element |
| `type` | `string` | Chart type: `line`, `bar`, `doughnut`, `pie` |
| `data` | `object` | Chart.js data object (labels + datasets) |
| `options` | `object` | Optional Chart.js options (merged with defaults) |

Defaults applied:
- `responsive: true`
- `maintainAspectRatio` — `true` for doughnut/pie, `false` for line/bar
- Legend position — `bottom` for doughnut/pie, `top` for line/bar
- Y-axis begins at zero with integer ticks (`stepSize: 1`, `precision: 0`) for line/bar charts
- Destroys any existing chart on the same canvas before creating

### `lrDestroyCharts`

Destroy all chart instances for a plugin prefix. Useful when switching tabs or refreshing data.

```javascript
window.lrDestroyCharts('myPlugin');
```

### `lrGetChart`

Retrieve a chart instance for programmatic updates. The `prefix` argument is optional — when omitted, falls back to `lrAnalyticsConfig.prefix`.

```javascript
var chart = window.lrGetChart('daily-chart');
if (chart) {
    chart.data.datasets[0].data = newData;
    chart.update();
}

// With explicit prefix
var chart = window.lrGetChart('daily-chart', 'myPlugin');
```

## Events

All events are dispatched on `document`.

### Table Events

| Event | `detail` | When |
|-------|----------|------|
| `lr:selectionChanged` | `{ selectedIds, count }` | Row checkbox selection changes |
| `lr:refresh` | AJAX response data | Auto-refresh completes |

CP table auto-refresh pauses while `lrTableSelection.getCount()` is greater than zero and resumes when selection is cleared.

When an AJAX response includes `pagination`, the layout updates the footer count and previous/next button state before dispatching `lr:refresh`. When an AJAX response includes `refresh.enabled: false`, the layout stops polling and hides the refresh notice.

AJAX row renderers that insert Craft menu buttons can call `window.lrInitMenuButtons(container)` after replacing markup. The helper requires a container and only initializes `.menubtn` elements inside that container; the table layout also calls it for `#lr-table-body` on the next animation frame after `lr:refresh`.

```javascript
document.addEventListener('lr:selectionChanged', function(e) {
    document.getElementById('bulk-count').textContent = e.detail.count;
});

document.addEventListener('lr:refresh', function(e) {
    // e.detail contains the fresh data from the AJAX endpoint
});
```

### Analytics Events

| Event | `detail` | When |
|-------|----------|------|
| `lr:analyticsInit` | `{ dateRange, siteId, customFilters, config }` | Analytics initialized (chart data ready to load) |
| `lr:tabChanged` | `{ tabId }` | User switches tab |
| `lr:panelChartsReady` | — | Chart.js loaded in an [analytics-panel](../template-guides/partials.md#analytics-panel) partial |

```javascript
document.addEventListener('lr:analyticsInit', function(e) {
    // Load your charts here
    window.lrLoadChartData('overview', function(data) {
        window.lrCreateChart('overview-chart', 'line', data);
    });
});

document.addEventListener('lr:tabChanged', function(e) {
    if (e.detail.tabId === 'details') {
        // Load charts for the details tab
    }
});
```

## Asset Loading

The JavaScript loads through Craft's asset bundle system — you don't need to include it manually.

| Asset Bundle | Loaded By | Provides |
|--------------|-----------|----------|
| `AnalyticsAsset` | CP Analytics Layout, Analytics Panel partial | Chart.js + `lrChartColors`, `lrLoadChartData`, `lrCreateChart`, `lrDestroyCharts`, `lrGetChart`, `lrAnalyticsInit` |
| `ComponentsAsset` | CP Table Layout, CP Analytics Layout, Analytics Panel partial, explicit template registration | Config tooltip behavior + `lrIdentifiers` |
| `InstallExperienceAsset` | Shared post-install CP modal | `window.LrInstallExperience` + preset-driven confetti animation |

The table globals (`lrTableSelection`, `lrViewSettings`, `lrBuildUrl`, `lrTableConfig`) are embedded directly in the CP Table Layout template, not in a separate asset bundle.

## Install Experience Asset

The install experience uses a dedicated asset bundle and is not part of the table or analytics layout stack.

Files:

- `src/web/assets/install/install-experience.src.js` — source file
- `src/web/assets/install/install-experience.js` — bundled dev build
- `src/web/assets/install/dist/js/install-experience.js` — bundled production build

The bundle is generated via:

```bash title="NPM"
npm run build:install
```

`InstallExperienceAsset` now always loads the canonical bundled file at `src/web/assets/install/dist/js/install-experience.js`.

## Next Steps

- [CP Table Layout](../template-guides/cp-table-layout.md) — full layout configuration reference
- [CP Analytics Layout](../template-guides/cp-analytics-layout.md) — analytics dashboard layout reference
- [Front-End CSS](front-end-css.md) — CSS classes for cards, charts, tables, and utilities
