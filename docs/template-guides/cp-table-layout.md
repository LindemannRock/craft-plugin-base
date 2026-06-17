# CP Table Layout

A reusable layout for Control Panel pages with tabular data. Provides a unified toolbar, filters, search, sortable/hideable columns, pagination, checkboxes, bulk actions, expandable rows, and optional AJAX auto-refresh.

## Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-table' %}

{% set tableConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'My Items'|t('my-plugin'),
        subnav: 'my-items',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
        ],
    },
    table: {
        columns: [
            {key: 'name', label: 'Name'|t('my-plugin'), sortable: true},
            {key: 'status', label: 'Status'|t('my-plugin'), hideable: true},
        ],
        items: items,
        emptyMessage: 'No items found.'|t('my-plugin'),
    },
    pagination: {
        page: page,
        limit: limit,
        totalCount: totalCount,
        itemLabel: {singular: 'item'|t('my-plugin'), plural: 'items'|t('my-plugin')},
    },
    sort: {
        field: sort,
        direction: dir,
    },
} %}

{% block tableRow %}
    <td>{{ item.name }}</td>
    <td>
        {% include 'lindemannrock-base/_components/badge' with {
            label: item.status|capitalize,
            value: item.status,
            colorSet: 'status',
        } only %}
    </td>
{% endblock %}
```

## Configuration

### plugin

| Key | Type | Description |
|-----|------|-------------|
| `handle` | `string` | Plugin handle (used for URL building and localStorage view settings key) |
| `name` | `string` | Plugin display name |

### page

| Key | Type | Description |
|-----|------|-------------|
| `title` | `string` | Page title |
| `subnav` | `string` | Active subnav item key |
| `crumbs` | `array` | Breadcrumb trail |
| `fullPageForm` | `bool` | Wrap page in a form (default: `false`) |

### table

| Key | Type | Description |
|-----|------|-------------|
| `columns` | `array` | Column definitions (see below) |
| `items` | `array` | Data rows to display |
| `emptyMessage` | `string` | Message when no items |
| `expandable` | `bool` | Enable click-to-expand rows (default: `false`) |
| `rowClassKey` | `string` | Item attribute to use as a CSS class on the `<tr>` (default: `null`) |
| `hasConfigItems` | `bool` | When `true`, items with `source == 'config'` get disabled checkboxes (default: `false`) |

### Column Definition

```twig
{key: 'name', label: 'Name', sortable: true}
{key: 'email', label: 'Email', sortable: true, hideable: true}
{key: 'status', label: 'Status', hideable: true}
```

| Key | Type | Description |
|-----|------|-------------|
| `key` | `string` | Column identifier (maps to sort param) |
| `label` | `string` | Column header text |
| `sortable` | `bool` | Appear in sort dropdown and clickable header |
| `hideable` | `bool` | Can be hidden via View button |
| `width` | `string` | CSS width for the column (e.g., `'120px'`, `'20%'`) |
| `nowrap` | `bool` | Add `nowrap` class to prevent text wrapping |
| `style` | `string` | Inline CSS style for data cells |
| `template` | `string` | Inline Twig template string for cell rendering (uses `template_from_string`) |

When any column has `hideable: true`, a "View" button appears in the toolbar. Users can sort, show/hide columns, and reset to defaults. Settings persist in localStorage.

The `template` option lets you render custom cell content without overriding the `tableRow` block. The `item` variable is available:

```twig
{key: 'status', label: 'Status', template: '{{ item.status|capitalize }}', hideable: true}
```

### pagination

| Key | Type | Description |
|-----|------|-------------|
| `page` | `int` | Current page number |
| `limit` | `int` | Items per page |
| `totalCount` | `int` | Total number of items |
| `itemLabel` | `object` | `{singular: 'item', plural: 'items'}` |

### sort

| Key | Type | Description |
|-----|------|-------------|
| `field` | `string` | Current sort field |
| `direction` | `string` | `'asc'` or `'desc'` |

### search

| Key | Type | Description |
|-----|------|-------------|
| `placeholder` | `string` | Search input placeholder |
| `value` | `string` | Current search value |

### checkboxes

Set to `true` (or a permission expression) to enable row selection checkboxes.

```twig
checkboxes: currentUser.can('myPlugin:deleteItems'),
```

### rowActions

Set to `false` to hide the Actions column (for read-only tables). Default: `true`.

### bulkActionsAlwaysVisible

Set to `true` to always show bulk action buttons, even when no items are selected. Default: `false`.

### footerActions

Array of action buttons rendered in the footer (next to pagination). Supports three types:

```twig
footerActions: [
    {type: 'button', label: 'Import'|t('my-plugin'), id: 'import-btn', icon: 'upload', class: 'secondary', permission: 'myPlugin:import'},
    {type: 'link', label: 'View All'|t('my-plugin'), url: url('my-plugin/items'), class: ''},
    {type: 'menu', label: 'More'|t('my-plugin'), icon: 'settings', align: 'right', items: [
        {label: 'Export'|t('my-plugin'), url: url('my-plugin/export'), id: 'export-link'},
        {label: 'Clear All'|t('my-plugin'), url: '#', class: 'error', id: 'clear-btn'},
    ]},
],
```

| Key | Type | Description |
|-----|------|-------------|
| `type` | `string` | `'button'`, `'link'`, or `'menu'` |
| `label` | `string` | Button/link text |
| `permission` | `string` | Required permission (action hidden if user lacks it) |
| `id` | `string` | HTML id attribute |
| `class` | `string` | CSS class (e.g., `'secondary'`, `'error'`) |
| `icon` | `string` | Craft icon name (for `button` and `menu` types) |
| `url` | `string` | Link URL (for `link` type) |
| `items` | `array` | Menu items (for `menu` type) — each with `label`, `url`, optional `id` and `class` |
| `align` | `string` | Menu alignment (for `menu` type) — e.g., `'right'` |

### newButton

| Key | Type | Description |
|-----|------|-------------|
| `url` | `string` | URL for the "New" button |
| `label` | `string` | Button label |
| `permission` | `string` | Required permission to show button. **Honored but redundant when the caller pre-gates** (`newButton: canCreate ? {…} : null`) — and pre-gating is the canonical shape, see [CP Table Index-Page Pattern](cp-table-index-pattern.md) → "`newButton` is pre-gated, not key-gated". Kept here for back-compat with layout-only callers from before pre-gating became canonical. |

### ajax

| Key | Type | Description |
|-----|------|-------------|
| `enabled` | `bool` | Enable AJAX auto-refresh |
| `interval` | `int` | Refresh interval in seconds |
| `endpoint` | `string` | Controller action URL |

When row checkboxes are enabled, auto-refresh pauses while one or more rows are selected so bulk-action state is not replaced underneath the user. Refresh resumes after the selection is cleared.

### sidebarMenu

Optional left sidebar navigation.

```twig
sidebarMenu: {
    label: 'Logs',
    items: {
        system: {label: 'System', url: 'my-plugin/logs/system'},
        activity: {label: 'Activity', url: 'my-plugin/logs/activity'},
    },
    selected: 'system',
},
```

## Filters

### Status Filter

```twig
filters: [
    {
        type: 'status',
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
],
```

### Grouped Status Filter

Multiple filter groups in one dropdown:

```twig
{
    type: 'status',
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
```

### Dropdown Filter

```twig
{
    type: 'dropdown',
    param: 'category',
    current: categoryFilter,
    label: 'All Categories'|t('my-plugin'),
    options: [
        {value: 'all', label: 'All Categories'|t('my-plugin')},
        {value: 'typeA', label: 'Type A'|t('my-plugin')},
    ],
},
```

### Date Range Filter

```twig
{
    type: 'dateRange',
    param: 'dateRange',
    current: dateRange,
    label: 'Date Range'|t('my-plugin'),
},
```

## Overridable Blocks

### tableRow

Custom row rendering. The `item` variable is available.

```twig
{% block tableRow %}
    <td>{{ item.name }}</td>
    <td>{{ item.email }}</td>
{% endblock %}
```

### rowActions

Per-row action menu.

```twig
{% block rowActions %}
    {% include 'lindemannrock-base/_components/row-actions' with {
        item: item,
        actions: {
            type: 'menu',
            icon: 'settings',
            items: [
                {label: 'Edit'|t('app'), url: url('my-plugin/items/' ~ item.id)},
                {label: 'Delete'|t('app'), class: 'error', jsAction: 'delete'},
            ],
        },
    } only %}
{% endblock %}
```

### expandableRow

Detail content shown when a row is clicked (requires `table.expandable: true`).

```twig
{% block expandableRow %}
    <div>{{ item.fullDescription }}</div>
{% endblock %}
```

### bulkActions

Buttons shown when items are selected via checkboxes.

```twig
{% block bulkActions %}
    {% include 'lindemannrock-base/_components/bulk-actions-menu' with {
        countId: 'bulk-actions-count',
        items: [
            {id: 'lr-bulk-delete-btn', label: 'Delete'|t('my-plugin'), class: 'error'},
        ],
    } only %}
{% endblock %}
```

### beforeTable

Content before the table (warnings, info boxes).

### toolbarActions

Toolbar buttons (export menus, etc.).

### sidebar

Left sidebar navigation. Auto-rendered from `sidebarMenu` config (shows `_includes/nav` when there are more than 1 menu items). Override to customize the sidebar entirely.

### sidebarContent

Right sidebar / details pane content.

### extraToolbar

Additional items inside the toolbar row.

### extraFooter

Additional footer content.

### scripts

Additional JavaScript.

## JavaScript API

The table layout exposes several globals for plugin scripts: `lrTableSelection` (selection management), `lrBuildUrl` (URL building), `lrTableConfig` (config), and `lrViewSettings` (column visibility). It also fires `lr:selectionChanged` and `lr:refresh` events. AJAX refresh pauses automatically while `lrTableSelection.getCount()` is greater than zero.

See [JavaScript API](../developers/javascript-api.md#table-selection-api) for the full reference.

## Next Steps

- [CP Table Index-Page Pattern](cp-table-index-pattern.md) — architectural rules for controller / template / row-action JS around this layout
- [Components](components.md) — badge, row-actions, filter, and export-menu components
- [CP Analytics Layout](cp-analytics-layout.md) — analytics dashboard layout
- [JavaScript API](../developers/javascript-api.md) — global functions and events
- [Front-End CSS](../developers/front-end-css.md) — CSS classes for tables, cards, and utilities
- [ColorHelper](../feature-tour/color-helper.md) — color sets for badges and filters
