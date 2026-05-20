# Partials

Embeddable template chunks with significant logic. Unlike [Components](components.md) (small, self-contained UI elements), partials are larger blocks that render full form sections, panels, or data displays.

All partials live in `lindemannrock-base/_partials/` and are included via `{% include %}` or `{% embed %}`.

---

## analytics-panel

An embeddable analytics panel for tabs and sub-pages. Unlike the full [CP Analytics Layout](cp-analytics-layout.md), this doesn't extend any layout — embed it inside an existing page.

Provides a date range filter, export button, loading overlay, and a `content` block you fill with cards, charts, and tables.

### Usage

```twig
{% embed 'lindemannrock-base/_partials/analytics-panel' with {
    config: {
        dateRange: {
            enabled: true,
            current: dateRange,
            param: 'range',
            hash: 'analytics',
        },
        export: {
            action: 'my-plugin/analytics/export',
            permission: 'myPlugin:export',
            extraParams: { campaignId: campaign.id },
        },
    }
} %}
    {% block content %}
        <div class="lr-unified-cards cols-3">
            {% include 'lindemannrock-base/_components/stat-box' with {
                value: stats.total,
                label: 'Total',
            } only %}
        </div>
        <div class="lr-analytics-charts two-columns">
            <div class="lr-chart-container">
                <h3>Daily Trend</h3>
                <canvas id="my-chart"></canvas>
            </div>
        </div>
    {% endblock %}
{% endembed %}
```

### Config

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `config.id` | `string` | `'lr-analytics-panel'` | Panel element ID |
| `config.dateRange.enabled` | `bool` | `false` | Show date range filter |
| `config.dateRange.current` | `string` | `'last30days'` | Selected date range |
| `config.dateRange.param` | `string` | `'dateRange'` | URL parameter name |
| `config.dateRange.hash` | `string` | `''` | URL hash for AJAX navigation |
| `config.export.action` | `string` | `''` | Export controller action |
| `config.export.permission` | `string` | `null` | Permission required for export |
| `config.export.extraParams` | `object` | `{}` | Additional export parameters |
| `config.export.pluginHandle` | `string` | `null` | Plugin handle for format config |
| `config.charts.enabled` | `bool` | `true` | Load Chart.js asset |

### Blocks

| Block | Description |
|-------|-------------|
| `content` | Main panel content (cards, charts, tables) |
| `filters` | Additional filter buttons next to the date range |

### Events

| Event | When |
|-------|------|
| `lr:panelChartsReady` | Chart.js is loaded and ready to use |

Listen for this event to initialize charts:

```javascript
document.addEventListener('lr:panelChartsReady', function() {
    window.lrCreateChart('my-chart', 'line', chartData);
});
```

### CSS Classes

Use the same CSS classes as the [CP Analytics Layout](cp-analytics-layout.md): `.lr-unified-cards`, `.lr-analytics-charts`, `.lr-chart-container`, `.lr-table-scroll`, `.lr-section-heading`, `.lr-analytics-empty`. See [Front-End CSS](../developers/front-end-css.md) for the full reference.

---

## import-csv

A complete CSV import form with file upload, delimiter selection, optional backup toggle, and an optional mode switch between CSV and alternate import methods.

### Usage

```twig
{% include 'lindemannrock-base/_partials/import-csv' with {
    action: actionUrl('my-plugin/import/upload'),
    title: 'Import Items'|t('my-plugin'),
    description: 'Upload a CSV file with one item per row.',
    fileLabel: 'CSV File'|t('my-plugin'),
    fileInstructions: 'Max 10 MB. UTF-8 encoding recommended.',
    submitLabel: 'Upload & Map Columns'|t('my-plugin'),
} %}
```

### With Backup Toggle

```twig
{% include 'lindemannrock-base/_partials/import-csv' with {
    action: actionUrl('my-plugin/import/upload'),
    title: 'Import Items'|t('my-plugin'),
    showBackupToggle: true,
    backupLabel: 'Create Backup Before Import'|t('my-plugin'),
    backupInstructions: 'Recommended when importing large files.',
    backupEnabled: settings.enableBackups,
    backupOnImport: settings.backupOnImport,
    backupWarning: 'Backups are disabled in plugin settings.',
} %}
```

### With Mode Switch

Switch between CSV import and an alternate method (e.g., paste URLs):

```twig
{% include 'lindemannrock-base/_partials/import-csv' with {
    action: actionUrl('my-plugin/import/upload'),
    title: 'Import'|t('my-plugin'),
    showModeSwitch: true,
    primaryLabel: 'CSV Import'|t('my-plugin'),
    secondaryLabel: 'Paste URLs'|t('my-plugin'),
    secondaryHtml: '<textarea name="urls" rows="10" style="width:100%"></textarea>
        <div class="buttons" style="margin-top:12px">
            <button type="submit" class="btn submit">Import URLs</button>
        </div>',
    renderTitleInSection: true,
} %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `action` | `string` | `''` | Form action URL |
| `formId` | `string` | `''` | Form element ID |
| `wrapForm` | `bool` | `true` | Wrap in `<form>` tag |
| `includeCsrf` | `bool` | same as `wrapForm` | Include CSRF token hidden input |
| `title` | `string` | `'Import from CSV'` | Section title |
| `description` | `string` | `''` | Intro text |
| `csvDescription` | `string` | `null` | Description inside CSV section (when using mode switch with `renderTitleInSection`) |
| `secondaryDescription` | `string` | `null` | Description inside alternate section |
| `csvFormatTip` | `string` | `null` | HTML for info popover next to title |
| `fileLabel` | `string` | `'CSV File'` | File input label |
| `fileInstructions` | `string` | `''` | File input instructions |
| `fileId` | `string` | `'csvFile'` | File input element ID |
| `fileName` | `string` | `'csvFile'` | File input name attribute |
| `delimiterLabel` | `string` | `'CSV Delimiter'` | Delimiter select label |
| `delimiterInstructions` | `string` | `''` | Delimiter select instructions |
| `delimiterValue` | `string` | `'auto'` | Selected delimiter |
| `delimiterOptions` | `object` | Auto, comma, semicolon, tab, pipe | Delimiter options |
| `submitLabel` | `string` | `'Upload & Map Columns'` | Submit button text |
| `showBackupToggle` | `bool` | `false` | Show backup lightswitch |
| `backupEnabled` | `bool` | `true` | Whether backups are enabled |
| `backupOnImport` | `bool` | `true` | Default backup toggle state |
| `backupWarning` | `string` | `null` | Warning when backups disabled |
| `showModeSwitch` | `bool` | `false` | Show CSV/alternate mode buttons |
| `toggleId` | `string` | `'csv-import-mode-switch'` | Element ID for the mode switch button group (auto-prefixed with `formId` when set) |
| `primaryLabel` | `string` | `'CSV Import'` | CSV mode button label |
| `secondaryLabel` | `string` | `'Alternate Import'` | Alternate mode button label |
| `secondaryHtml` | `string` | `null` | HTML for alternate import mode |
| `modeDefault` | `string` | `'csv'` | Default mode (`'csv'` or `'secondary'`) |
| `renderTitleInSection` | `bool` | `false` | Move title inside CSV section |
| `infoBoxMessage` | `string` | `null` | Info box message above submit button |

---

## cascade-geo-settings

Geo detection provider settings fields. Include this in plugin settings pages that support IP geolocation.

Renders a provider select, API key input, HTTP warning for ip-api.com free tier, and dynamic provider info. Provider-specific UI updates happen via inline JavaScript.

Pairs with [`GeoSettingsTrait`](../feature-tour/base-settings-traits.md#geosettingstrait--shared-geo-provider--api-key-fields) — adopt the trait in the plugin's Settings model to get the matching validation rules + attribute labels for the `geoProvider`/`geoApiKey` properties this partial binds to.

### Usage

```twig
{% include 'lindemannrock-base/_partials/cascade-geo-settings' with {
    settings: settings,
    translationCategory: 'search-manager',
} %}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `settings` | `Model` | Plugin settings model with `geoProvider` and `geoApiKey` properties |
| `pluginHandle` | `string` | Plugin handle (used for config override warnings) |

### Providers

| Provider | Protocol | Free Tier |
|----------|----------|-----------|
| `ip-api.com` | HTTP (HTTPS with API key) | 45 req/min |
| `ipapi.co` | HTTPS | 1,000 req/day |
| `ipinfo.io` | HTTPS | 50,000 req/month |

The partial automatically shows an HTTP warning when ip-api.com is selected without an API key, and respects `isOverriddenByConfig()` to disable fields that are set in the config file.

---

## backup-list

Lightweight containers for async backup history loading. Provides loading spinner, empty state, and error message containers.

### Usage

```twig
{% include 'lindemannrock-base/_partials/backup-list' with {
    idPrefix: 'settings-backup',
    loadingMessage: 'Loading backups...'|t('my-plugin'),
    emptyMessage: 'No backups yet.'|t('my-plugin'),
} %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `idPrefix` | `string` | `'backup'` | Prefix for element IDs |
| `loadingMessage` | `string` | `'Loading backup history...'` | Loading state text |
| `emptyMessage` | `string` | `'No backups found.'` | Empty state text |

### Element IDs

The partial creates these elements for your JavaScript to target:

| Element ID | Description |
|------------|-------------|
| `{idPrefix}-list-loading` | Loading spinner container (visible by default) |
| `{idPrefix}-list-content` | Main content wrapper (hidden by default) |
| `{idPrefix}-table-container` | Where your JS injects the backup table |
| `{idPrefix}-empty-message` | Empty state message (hidden) |
| `{idPrefix}-error-message` | Error message container (hidden) |

---

## Next Steps

- [Components](components.md) — small UI elements (badge, stat-box, filter, export-menu)
- [CP Analytics Layout](cp-analytics-layout.md) — full-page analytics layout
- [CP Table Layout](cp-table-layout.md) — table page layout
- [CsvImportHelper](../feature-tour/csv-import-helper.md) — PHP-side CSV parsing
- [GeoLookupTrait](../feature-tour/geo-lookup.md) — IP geolocation in services
