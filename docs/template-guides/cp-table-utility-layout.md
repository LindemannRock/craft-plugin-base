# CP Table Utility Layout

A variant of the [CP Table Layout](cp-table-layout.md) designed for use inside Craft's utility pages. Unlike the standard CP Table Layout (which extends `_layouts/cp`), this layout does not extend any parent layout — it renders table content directly, making it embeddable inside utility page templates.

## When to Use

Use this layout when you need table functionality (filters, search, pagination, sorting, column visibility) inside a **Craft utility page** rather than a standalone CP page. The outer CP chrome is provided by Craft's utility system.

## Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-table-utility' %}

{% set tableConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Utility Logs',
        subnav: 'logs',
    },
    table: {
        columns: [
            {key: 'date', label: 'Date'|t('my-plugin'), sortable: true},
            {key: 'message', label: 'Message'|t('my-plugin')},
            {key: 'level', label: 'Level'|t('my-plugin'), hideable: true},
        ],
        items: logs,
        emptyMessage: 'No logs found.'|t('my-plugin'),
    },
    pagination: {
        page: page,
        limit: limit,
        totalCount: totalCount,
        itemLabel: {singular: 'log'|t('my-plugin'), plural: 'logs'|t('my-plugin')},
    },
    sort: {
        field: sort,
        direction: dir,
    },
} %}

{% block tableRow %}
    <td>{{ item.date|lrDatetime }}</td>
    <td>{{ item.message }}</td>
    <td>
        {% include 'lindemannrock-base/_components/badge' with {
            label: item.level|capitalize,
            value: item.level,
            colorSet: 'logLevel',
        } only %}
    </td>
{% endblock %}
```

## Configuration

The configuration is identical to the [CP Table Layout](cp-table-layout.md) — all the same `tableConfig` keys, filter types, and overridable blocks are supported.

Like the standalone layout, utility tables can build from a `page.url` that already includes query parameters. Filter links and JavaScript navigation merge the canonical query, current request context, `preserveParams`, filters, search, sorting, pagination, and explicit navigation changes. Matching scalar keys such as `site` are emitted once, while encoded values and URL fragments remain intact.

AJAX auto-refresh also follows the standalone layout's interaction guard: polling pauses while rows are selected or an expandable row is open, then resumes once the user clears the selection or collapses the detail row.

Refresh requests use the same merge and precedence rules, including query parameters already present on the configured endpoint.

### Key Differences from CP Table Layout

| Feature | CP Table Layout | CP Table Utility Layout |
|---------|----------------|------------------------|
| Extends `_layouts/cp` | Yes | No |
| Has sidebar navigation | Yes (`sidebarMenu`) | No |
| Has right details pane | Yes (`sidebarContent`) | No |
| `hasConfigItems` | Yes | No |
| `bulkActionsAlwaysVisible` | Yes | No |
| `rowClassKey` | Yes | No |
| Form action URL | Built from `pluginHandle/subnav` | Uses current request path |
| Use case | Standalone CP pages | Inside utility pages |

## Overridable Blocks

All blocks from the CP Table Layout are available except `sidebar` and `sidebarContent`:

- `beforeTable` — Content before the table
- `tableRow` — Custom row rendering
- `rowActions` — Per-row action buttons
- `expandableRow` — Expandable detail rows
- `bulkActions` — Bulk action buttons
- `toolbarActions` — Toolbar buttons
- `extraToolbar` — Additional toolbar items
- `extraFooter` — Additional footer content
- `scripts` — Additional JavaScript

## Next Steps

- [CP Table Layout](cp-table-layout.md) — the full-featured standalone table layout
- [CP Utilities Layout](cp-utilities-layout.md) — overview/dashboard utility pages
