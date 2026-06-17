# Components

Reusable Twig components for consistent UI across all plugins. Include them with `{% include %}` and pass parameters using `with { ... } only`.

## Badge

Colored label for status values, categories, and tags. Supports three color modes.

**Path:** `lindemannrock-base/_components/badge`

### With Color Set

Look up colors from a registered [color set](../feature-tour/color-helper.md):

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Enabled',
    value: 'enabled',
    colorSet: 'status',
} only %}
```

### With Craft Status Class

Use Craft's built-in status classes (`teal`, `gray`, `orange`, `red`, `blue`, `pink`, `disabled`):

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Active',
    status: 'teal',
} only %}
```

### With Custom Color

Pass hex colors directly:

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Custom',
    color: '#6366f1',
    rgb: '99, 102, 241',
    textColor: '#312e81',
} only %}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `label` | `string` | Badge text |
| `value` | `string` | Value to look up in `colorSet` |
| `colorSet` | `string` | Color set name from ColorHelper |
| `status` | `string` | Craft status class (`green`, `red`, `disabled`, etc.) |
| `color` | `string` | Custom hex background color |
| `rgb` | `string` | RGB values for background opacity (e.g., `'99, 102, 241'`) |
| `textColor` | `string` | Custom hex text color |
| `url` | `string` | Wrap badge in a link |
| `title` | `string` | Tooltip text |

---

## Info Box

Alert and notification banners for warnings, errors, tips, and success messages.

**Path:** `lindemannrock-base/_components/info-box`

```twig
{% include 'lindemannrock-base/_components/info-box' with {
    message: 'Analytics tracking requires an IP hash salt.',
    type: 'warning',
} %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `message` | `string` | | Message text |
| `type` | `string` | `'info'` | `info`, `success`, `warning`, `error` |
| `variant` | `string` | `'subtle'` | `subtle` (light bg) or `colored` (full bg) |
| `margin` | `string` | `'top'` | `top`, `bottom`, `both`, `none` |
| `bg` | `string` | `'gray'` | Background: `gray` or `white` |
| `stretch` | `bool` | `false` | Full-width banner |
| `boxId` | `string` | | HTML `id` for the container |
| `messageId` | `string` | | HTML `id` for the message text |
| `hidden` | `bool` | `false` | Render hidden (for JS toggling) |
| `allowHtml` | `bool` | `true` | Render message as raw HTML |

```twig
{% include 'lindemannrock-base/_components/info-box' with {
    message: 'Import completed. <a href="' ~ url('my-plugin/items') ~ '">View items</a>.',
    type: 'success',
    allowHtml: true,
    margin: 'bottom',
} %}
```

---

## Stat Box

Single metric display for analytics dashboards. Formats numeric values with `number_format`.

**Path:** `lindemannrock-base/_components/stat-box`

```twig
{% include 'lindemannrock-base/_components/stat-box' with {
    value: 12345,
    label: 'Total Views',
} only %}
```

### With Palette Color

Pass a palette color name for a fully colored box (tinted background and border):

```twig
{% include 'lindemannrock-base/_components/stat-box' with {
    value: 50,
    label: 'New Items',
    palette: 'green',
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `value` | `mixed` | | Numeric or text value |
| `label` | `string` | | Description label |
| `id` | `string` | | HTML `id` for dynamic updates via JS |
| `color` | `string` | `'#0d78f2'` | Value text color (ignored when `palette` is set) |
| `palette` | `string` | | Palette color name (e.g., `'green'`, `'red'`, `'amber'`) — creates a tinted box |
| `suffix` | `string` | | Suffix (e.g., `'%'`, `'ms'`) |
| `prefix` | `string` | | Prefix (e.g., `'$'`) |
| `size` | `string` | | `'small'` for compact version |
| `class` | `string` | | Additional CSS classes |
| `valueIsFormatted` | `bool` | `false` | Render `value` as provided instead of applying `number_format` |

### Grid Layout

Wrap stat boxes in `.lr-analytics-stats` for a responsive grid. Add `.compact` for a five-column layout:

```twig
<div class="lr-analytics-stats">
    {% include 'lindemannrock-base/_components/stat-box' with {
        value: 100, label: 'Total',
    } only %}
    {% include 'lindemannrock-base/_components/stat-box' with {
        value: 50, label: 'New', palette: 'green',
    } only %}
</div>
```

---

## Dashboard Widget Components

Compact components for Craft dashboard widgets. Use these for widget body templates so repeated stat grids, ranked metric lists, empty states, and footer links share the same spacing and styles.

### Dashboard Widget Stats

**Path:** `lindemannrock-base/_components/dashboard-widget-stats`

```twig
{% include 'lindemannrock-base/_components/dashboard-widget-stats' with {
    stats: [
        {value: 7, label: 'Total Interactions'},
        {value: 3, label: 'Unique Visitors'},
        {value: 2, label: 'Active Links'},
        {value: 66, suffix: '%', label: 'Engagement Rate'},
    ],
    topItem: {
        label: 'Top Performer',
        title: 'Summer campaign',
        url: cpUrl('my-plugin/items/1'),
        meta: '7 interactions',
    },
    footer: {
        url: cpUrl('my-plugin/analytics'),
        label: 'View full analytics',
    },
} only %}
```

This component composes `stat-box` for each metric. Pass `valueIsFormatted: true` on a stat when the value already contains localized or custom formatting.

### Dashboard Widget List

**Path:** `lindemannrock-base/_components/dashboard-widget-list`

```twig
{% include 'lindemannrock-base/_components/dashboard-widget-list' with {
    primaryHeader: 'Link',
    valueHeader: 'Interactions',
    rows: [
        {
            primary: 'Summer campaign',
            primaryUrl: cpUrl('my-plugin/items/1'),
            meta: '/go/summer',
            value: 7|number,
        },
    ],
    empty: {
        title: 'No links yet',
        message: 'Create your first link to see it here.',
    },
    footer: {
        url: cpUrl('my-plugin/items'),
        label: 'View all links',
    },
} only %}
```

Row variants use shared badge/link styling:

- `primaryVariant`: `default`, `danger`, `success`, `warning`, `neutral`
- `badgeVariant`: `default`, `danger`, `success`, `warning`, `neutral`
- `primaryHtml`, `metaHtml`, and `valueHtml` are available for server-rendered markup when plain text is not enough.

### Empty State and Footer

Use these directly when a widget does not need the full stats/list wrappers:

```twig
{% include 'lindemannrock-base/_components/dashboard-widget-empty' with {
    title: 'No data yet',
    message: 'Activity will appear here after the first event.',
    type: 'empty',
} only %}

{% include 'lindemannrock-base/_components/dashboard-widget-footer' with {
    url: cpUrl('my-plugin/analytics'),
    label: 'View full analytics',
} only %}
```

Empty-state `type` supports `empty`, `success`, `warning`, and `error`.

---

## Unified Card

Flexible card component for dashboards, utilities, and analytics. Supports primary values, secondary values, badges, sub-metric boxes, palette coloring, and clickable cards.

**Path:** `lindemannrock-base/_components/unified-card`

### Basic Usage

```twig
{% include 'lindemannrock-base/_components/unified-card' with {
    title: 'Total Messages',
    color: '#059669',
    value: 1234,
    description: 'messages sent',
} only %}
```

### With Secondary Value and Badge

```twig
{% include 'lindemannrock-base/_components/unified-card' with {
    title: 'Messages Sent',
    color: '#0ea5e9',
    value: 150,
    secondary: 200,
    description: 'of total capacity',
} only %}

{% include 'lindemannrock-base/_components/unified-card' with {
    title: 'Success Rate',
    color: '#10b981',
    value: '89%',
    badge: '+15%',
    badgeType: 'positive',
} only %}
```

### With Sub-Metric Boxes

```twig
{% include 'lindemannrock-base/_components/unified-card' with {
    title: 'Analytics',
    color: '#8b5cf6',
    value: 1523,
    subBoxes: [
        {value: 1250, label: 'Sent', color: '#10b981'},
        {value: 23, label: 'Failed', color: '#ef4444'},
        {value: 250, label: 'Pending', color: '#f59e0b'},
    ],
} only %}
```

### Clickable Card

```twig
{% include 'lindemannrock-base/_components/unified-card' with {
    title: 'View Logs',
    color: '#0ea5e9',
    value: 150,
    description: 'messages today',
    url: url('my-plugin/logs'),
} only %}
```

### Palette-Colored Card

```twig
{% include 'lindemannrock-base/_components/unified-card' with {
    value: 1234,
    description: 'Total Messages',
    palette: 'green',
    align: 'center',
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | `string` | | HTML `id` attribute on the outer element (for JS targeting) |
| `title` | `string` | | Card title (shows colored dot) |
| `color` | `string` | `'#0d78f2'` | Accent color for dot and value |
| `url` | `string` | | Make card clickable with hover effect |
| `value` | `mixed` | | Primary value (auto-formatted if numeric) |
| `valueColor` | `string` | same as `color` | Custom value text color |
| `valueIsFormatted` | `bool` | `false` | Skip auto number formatting |
| `secondary` | `mixed` | | Secondary value (shown as `/ 200`) |
| `description` | `string` | | Description text below value |
| `badge` | `string` | | Badge text beside value |
| `badgeType` | `string` | `'default'` | `'default'`, `'positive'`, `'negative'` |
| `badgeColor` | `string` | | Custom badge hex color (overrides `badgeType`) |
| `subBoxes` | `array` | `[]` | Sub-metrics: `{value, label, color?}` |
| `palette` | `string` | | Palette color name for tinted background |
| `align` | `string` | `'start'` | Content alignment: `'start'`, `'center'`, `'end'` |

### Grid Layout

Wrap cards in `.lr-unified-cards` for a responsive grid:

```twig
<div class="lr-unified-cards">
    {% include 'lindemannrock-base/_components/unified-card' with {...} only %}
    {% include 'lindemannrock-base/_components/unified-card' with {...} only %}
</div>
```

---

## Status Dot

A standalone status indicator dot without a label. Uses the same color system as the badge.

**Path:** `lindemannrock-base/_components/status-dot`

```twig
{# Craft status class #}
{% include 'lindemannrock-base/_components/status-dot' with {
    status: 'enabled',
} only %}

{# Color set lookup #}
{% include 'lindemannrock-base/_components/status-dot' with {
    value: 'active',
    colorSet: 'pluginStatus',
} only %}

{# Custom hex color #}
{% include 'lindemannrock-base/_components/status-dot' with {
    color: '#059669',
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | `string` | | Craft status class (`enabled`, `disabled`, `live`, etc.) |
| `value` | `string` | | Value to look up in `colorSet` |
| `colorSet` | `string` | | Color set name from ColorHelper |
| `color` | `string` | | Custom hex color |
| `size` | `int` | | Dot size in pixels |
| `noMargin` | `bool` | `false` | Remove default margin |
| `title` | `string` | | Tooltip text |

---

## Chart Container

Wrapper for Chart.js canvas elements with consistent styling. Used inside [CP Analytics Layout](cp-analytics-layout.md) pages and dashboard widgets that need reusable chart rendering.

**Path:** `lindemannrock-base/_components/chart-container`

```twig
{% include 'lindemannrock-base/_components/chart-container' with {
    id: 'daily-trend-chart',
    title: 'Daily Trend',
    fullWidth: true,
} only %}
```

When `data` is provided, the component registers the base analytics assets and initializes the chart from the shared components JavaScript. This avoids inline widget scripts and supports Craft dashboard widget refreshes after settings are saved.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | `string` | | Canvas element ID (required for Chart.js) |
| `title` | `string` | | Chart heading |
| `subtitle` | `string` | | Subtitle below heading |
| `fullWidth` | `bool` | `false` | Span full grid width |
| `height` | `string|int` | | Height utility: `200`, `250`, or `300` |
| `center` | `bool` | `false` | Center the chart canvas in the container |
| `type` | `string` | `line` | Chart.js type when `data` is provided |
| `data` | `array` | | Chart.js data object |
| `options` | `array` | `[]` | Chart.js options object |
| `percentageTooltip` | `bool` | `false` | Add value and percentage tooltip labels |
| `class` | `string` | | Additional CSS classes |

---

## Export Menu

Dropdown button that shows enabled export formats (Excel, CSV, JSON). Format availability is controlled by `config/lindemannrock-base.php`.

**Path:** `lindemannrock-base/_components/export-menu`

```twig
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'my-plugin/logs/export',
    permission: 'myPlugin:downloadLogs',
} only %}
```

### With Extra Parameters

Pass current filter state to the export URL:

```twig
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'my-plugin/logs/export',
    permission: 'myPlugin:downloadLogs',
    pluginHandle: 'my-plugin',
    extraParams: {status: statusFilter, provider: providerFilter},
} only %}
```

### Selection-Aware Export

Shows "Export (X)" when table rows are selected and includes their IDs:

```twig
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'my-plugin/export',
    permission: 'myPlugin:export',
    selectionAware: true,
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `action` | `string` | **(required)** | Controller action URL |
| `permission` | `string` | | Permission check (hides button if user lacks it) |
| `pluginHandle` | `string` | | Plugin handle for config overrides |
| `dateRangeParam` | `string` | `'dateRange'` | URL param name for current date range |
| `extraParams` | `object` | `{}` | Additional URL parameters |
| `selectionAware` | `bool` | `false` | Show selection count and pass IDs |
| `idsParam` | `string` | `'ids'` | Parameter name for selected IDs |
| `id` | `string` | auto | Unique DOM ID for this menu instance |

---

## Row Actions

Per-row action buttons or dropdown menus with permission handling and conditional visibility.

**Path:** `lindemannrock-base/_components/row-actions`

### Simple Button

```twig
{% include 'lindemannrock-base/_components/row-actions' with {
    item: item,
    actions: {
        type: 'button',
        icon: 'delete',
        permission: 'myPlugin:deleteItems',
        class: 'delete',
        jsAction: 'delete',
    },
} only %}
```

### Menu with Multiple Actions

```twig
{% include 'lindemannrock-base/_components/row-actions' with {
    item: item,
    actions: {
        type: 'menu',
        icon: 'settings',
        permission: 'myPlugin:manageItems',
        items: [
            {
                label: 'Edit'|t('app'),
                url: url('my-plugin/items/' ~ item.id),
                permission: 'myPlugin:editItems',
            },
            {
                label: 'Create Redirect'|t('my-plugin'),
                url: url('my-plugin/new', {source: item.url}),
                hideIf: item.handled,
            },
            {type: 'divider'},
            {
                label: 'Delete'|t('app'),
                class: 'error',
                permission: 'myPlugin:deleteItems',
                jsAction: 'delete',
                confirm: 'Are you sure?'|t('app'),
            },
        ],
    },
} only %}
```

### Parameters

**Top-level `actions` object:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | `string` | `'button'` or `'menu'` |
| `icon` | `string` | Icon name (`delete`, `settings`, etc.) |
| `permission` | `string` | Column-level permission (hides entire column) |
| `label` | `string` | Button text (for `button` type without icon) |
| `title` | `string` | Button tooltip |
| `confirm` | `string` | Confirmation prompt before action (for `button` type) |
| `items` | `array` | Menu items (for `menu` type) |

**Menu item properties:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `label` | `string` | Display text |
| `url` | `string` | Link URL |
| `permission` | `string` | Per-action permission |
| `showIf` | `bool` | Show only if `true` |
| `hideIf` | `bool` | Hide if `true` |
| `disabled` | `bool` | Render as a non-interactive `<span class="menu-item-disabled" aria-disabled="true">` instead of an `<a>`. No `href`, no `data-action` / `data-id` / `data-confirm` attributes — the consuming template's delegated click handler never matches. Pair with `title:` to explain why (e.g. `title: disabled ? 'Reason it’s disabled' : null`). |
| `title` | `string` | Tooltip text. Rendered on hover for both enabled and disabled items, so the same key can host “what this does” or “why it’s currently disabled”. |
| `class` | `string` | CSS class (`'error'` for destructive actions) |
| `jsAction` | `string` | JS action name (fires `lr:rowAction` event) |
| `confirm` | `string` | Confirmation message before action |
| `data` | `object` | Additional `data-*` attributes |
| `type` | `string` | `'divider'` for a separator line |

---

## Filter Components

Three filter components for toolbar integration. The [CP Table Layout](cp-table-layout.md) renders these automatically based on your `filters` config — you rarely need to include them directly.

### Status Filter

Dropdown with colored status indicators. Supports flat options or grouped sections.

**Path:** `lindemannrock-base/_components/filter-status`

```twig
{% include 'lindemannrock-base/_components/filter-status' with {
    filter: {
        param: 'status',
        current: statusFilter,
        label: 'All Status'|t('my-plugin'),
        colorSet: 'status',
        options: [
            {value: 'all', label: 'All'|t('my-plugin'), status: 'all'},
            {value: 'enabled', label: 'Enabled'|t('my-plugin'), colorKey: 'enabled'},
            {value: 'disabled', label: 'Disabled'|t('my-plugin'), colorKey: 'disabled'},
        ],
    },
    urlParams: urlParams,
} only %}
```

**Color resolution priority:**

1. `statusColor` — direct hex color
2. `colorKey` + `colorSet` — ColorHelper lookup
3. `status` — Craft's built-in classes (`green`, `red`, `orange`, `disabled`, `all`)

Selected items show their actual color; unselected items show neutral gray.

**Grouped options** — combine multiple filter groups in one dropdown:

```twig
{% include 'lindemannrock-base/_components/filter-status' with {
    filter: {
        param: 'status',
        current: statusFilter,
        label: 'All'|t('my-plugin'),
        groups: [
            {
                param: 'status',
                current: statusFilter,
                options: [
                    {value: 'all', label: 'All'|t('my-plugin'), status: 'all'},
                    {value: 'enabled', label: 'Enabled'|t('my-plugin'), status: 'green'},
                ],
            },
            {
                header: 'Source'|t('my-plugin'),
                param: 'source',
                current: sourceFilter,
                colorSet: 'configSource',
                options: [
                    {value: 'all', label: 'All Sources'|t('my-plugin'), status: 'all'},
                    {value: 'config', label: 'Config'|t('my-plugin'), colorKey: 'config'},
                ],
            },
        ],
    },
    urlParams: urlParams,
} only %}
```

Options can include a `count` property to show totals: `{value: 'sent', label: 'Sent', colorKey: 'sent', count: 42}`.

Grouped options can include `extraParams` to merge additional URL parameters for that specific option: `{value: 'config', label: 'Config', colorKey: 'config', extraParams: {type: 'system'}}`.

### Dropdown Filter

Plain dropdown without status indicators.

**Path:** `lindemannrock-base/_components/filter-dropdown`

```twig
{% include 'lindemannrock-base/_components/filter-dropdown' with {
    filter: {
        param: 'language',
        current: languageFilter,
        label: 'All Languages'|t('my-plugin'),
        icon: 'language',
        options: [
            {value: 'all', label: 'All Languages'|t('my-plugin')},
            {value: 'en', label: 'English'},
            {value: 'de', label: 'German'},
        ],
    },
    urlParams: urlParams,
} only %}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter.param` | `string` | URL parameter name |
| `filter.current` | `string` | Currently selected value |
| `filter.label` | `string` | Default button label |
| `filter.icon` | `string` | Optional button icon |
| `filter.menuStyle` | `string` | Inline CSS style for the dropdown menu element |
| `filter.options` | `array` | `{value, label}` pairs (options may include `extra` for secondary text) |

### Date Range Filter

Date range dropdown using options from [DateRangeHelper](../feature-tour/date-range-helper.md).

**Path:** `lindemannrock-base/_components/filter-daterange`

```twig
{% include 'lindemannrock-base/_components/filter-daterange' with {
    filter: {
        param: 'dateRange',
        current: dateRange,
        label: 'Date Range'|t('my-plugin'),
    },
    urlParams: urlParams,
} only %}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter.param` | `string` | URL parameter name |
| `filter.current` | `string` | Currently selected value |
| `filter.label` | `string` | Default button label |
| `filter.icon` | `string` | Optional button icon |
| `filter.options` | `array` | Custom `{value, label}` pairs (defaults to `lrDateRangeOptions()`) |

---

## Search Input

Search field with clear button that preserves filter and sort state.

**Path:** `lindemannrock-base/_components/search-input`

```twig
{% include 'lindemannrock-base/_components/search-input' with {
    placeholder: 'Search items...'|t('my-plugin'),
    value: search,
    filters: filters,
    currentSort: sort,
    currentDir: dir,
} only %}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `placeholder` | `string` | Placeholder text |
| `value` | `string` | Current search value |
| `filters` | `array` | Active filters (rendered as hidden inputs) |
| `currentSort` | `string` | Current sort field |
| `currentDir` | `string` | Current sort direction |

---

## Phone Input

Country code selector with phone number input. Handles international number pasting, NANP area code detection, and input sanitization.

**Path:** `lindemannrock-base/_components/phone-input`

```twig
{% include 'lindemannrock-base/_components/phone-input' with {
    id: 'recipient',
    label: 'Phone Number'|t('my-plugin'),
    instructions: 'Enter phone number. Paste with country code to auto-detect.'|t('my-plugin'),
    defaultCountry: 'US',
    allowedCountries: ['US', 'CA', 'GB'],
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | `string` | **(required)** | Input ID |
| `name` | `string` | same as `id` | Form field name |
| `value` | `string` | | Initial phone number |
| `countryId` | `string` | `id ~ 'Country'` | Country select ID |
| `countryName` | `string` | same as `countryId` | Country select form name |
| `defaultCountry` | `string` | | Default country code (e.g., `'US'`) |
| `allowedCountries` | `array` | `['*']` | Allowed country codes, or `['*']` for all |
| `placeholder` | `string` | | Input placeholder |
| `instructions` | `string` | | Field instructions |
| `label` | `string` | | Field label |
| `required` | `bool` | `false` | Required field |
| `class` | `string` | | Additional CSS classes |

### JavaScript API

```javascript
// Get full number with dial code (digits only, no +)
window.lrPhoneInput.getFullNumber('recipient')  // '12025551234'

// Get local number without dial code
window.lrPhoneInput.getLocalNumber('recipient')  // '2025551234'

// Get/set country
window.lrPhoneInput.getCountry('recipient')      // 'US'
window.lrPhoneInput.setCountry('recipient', 'GB')

// Set number programmatically
window.lrPhoneInput.setNumber('recipient', '2025551234')

// Update allowed countries dynamically
window.lrPhoneInput.updateAllowedCountries('recipient', newDialCodes, 'US')

// Sanitize a phone number string
window.lrPhoneInput.sanitize('+1 (202) 555-1234')  // '12025551234'
```

### Events

| Event | Detail | Description |
|-------|--------|-------------|
| `lr:phoneCountryChanged` | `{country, dialCode, inputId}` | Country selection changed |
| `lr:phoneNumberChanged` | `{localNumber, fullNumber, inputId}` | Number changed (input or paste) |
| `lr:phoneCountryNotAllowed` | `{detectedCountry, detectedDialCode, localNumber, inputId}` | Pasted number has a country not in `allowedCountries` |

---

## IP Salt Error

Error banner for settings pages when IP hash salt is missing but analytics is enabled. Shows copy-to-clipboard commands for generating the salt.

**Path:** `lindemannrock-base/_partials/ip-salt-error`

```twig
{% include 'lindemannrock-base/_partials/ip-salt-error' with {
    translationCategory: 'redirect-manager',
} %}
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `translationCategory` | `string` | Plugin translation category (also used as plugin ID lookup) |
| `envVarName` | `string` | Optional env var name override (defaults to `<CATEGORY>_IP_SALT`) |

The banner only renders when `enableAnalytics` is `true` and the salt is missing or still set to the raw variable reference.

---

## Plugin Credit

LindemannRock branded footer for plugin settings pages. Shows the logo with an animated hover effect.

**Path:** `lindemannrock-base/_components/plugin-credit`

```twig
{% include 'lindemannrock-base/_components/plugin-credit' %}
```

No parameters. Include at the bottom of settings pages.

---

## Secret Reveal @since(5.25.0)

One-time secret reveal banner — for moments where a plugin generates a credential the operator must capture immediately because it is never re-displayed (API keys, OAuth tokens, signing keys, webhook secrets, etc.).

Renders a read-only monospace input with the secret + a Copy button. Click the input to select all text; click Copy to put it in the clipboard. Internally wraps the [Info Box](#info-box) component so visual treatment stays consistent with other banners.

**Path:** `lindemannrock-base/_components/secret-reveal`

```twig
{% include 'lindemannrock-base/_components/secret-reveal' with {
    secret: plaintextValue,
    title: 'Copy this key now — it will never be shown again.'|t('your-plugin'),
    helpText: 'We only store a hash. If you lose this value you will need to create a new credential.'|t('your-plugin'),
    type: 'success',
    margin: 'bottom',
} only %}
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `secret` | `string` | *(required)* | The value to reveal + copy |
| `title` | `string` | *(required)* | Bold heading shown above the input |
| `helpText` | `string` | *(none)* | Neutral paragraph shown below the input |
| `type` | `string` | `'success'` | `info-box` type — `'success'`, `'info'`, `'warning'` |
| `margin` | `string` | `'bottom'` | `info-box` margin — `'top'`, `'bottom'`, `'both'`, `'none'` |
| `copyLabel` | `string` | `'Copy'` | Copy button label (defaults to `lindemannrock-base` translation) |
| `copiedLabel` | `string` | `'Copied!'` | Post-click feedback label |
| `boxId` | `string` | *(auto)* | Unique id for JS targeting — auto-generated when absent |

### Behaviour

- **Click input** → text selects (keyboard users can `Cmd/Ctrl+C` without using the button).
- **Click Copy** → `navigator.clipboard.writeText`; falls back to `document.execCommand('copy')` when the Clipboard API is unavailable (older browsers, insecure contexts).
- **Visual feedback** → button text briefly becomes `Copied!` and gains Craft's `submit` class for 2 seconds, then reverts.
- **Scope** → each instance gets a unique `boxId` and an IIFE-wrapped event handler. Multiple secret-reveals on the same page coexist with no shared state.
- **No persistence** → the component does not retain the secret. The caller must show it exactly once (typically via session flash on a post-create redirect) and never render the value again.

### Caller responsibilities

The component renders whatever `secret` you pass in. The caller is responsible for:

- **Storing the plaintext exactly once.** Typical pattern: write the plaintext to a session flash in the controller's save action, redirect to the edit page, consume the flash with `Craft.$app.session.getFlash(...)` in the controller, pass it to the template under a one-shot variable like `newPlaintext`. Render the component conditioned on that variable being present.
- **Never re-rendering the value.** Edit of an existing record must not re-emit the secret — only the hash is stored, and there is nothing to re-emit.
- **Translating the `title` and `helpText`.** Component chrome (`Copy`, `Copied!`, error toast) uses `lindemannrock-base` translations; plugin-specific message text uses the caller's translation category.

### Reference usage

- Search Manager — API Keys edit page (post-creation reveal banner)

---

## Next Steps

- [CP Table Layout](cp-table-layout.md) — uses filters, badges, row-actions, search, and export-menu
- [CP Analytics Layout](cp-analytics-layout.md) — uses stat-box and chart-container
- [ColorHelper](../feature-tour/color-helper.md) — palette colors and color sets for badges and filters
- [Twig Filters & Functions](twig-filters-functions.md) — date, color, export, and geo template functions
