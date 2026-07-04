# CP Utilities Layout

A reusable layout for plugin utility/overview pages with system overview cards and quick action buttons. Unlike the [CP Table Layout](cp-table-layout.md) or [CP Analytics Layout](cp-analytics-layout.md), this layout does not extend Craft's `_layouts/cp` — it renders content inside the utility page context provided by Craft's utility system.

## Basic Usage

```twig
{% extends 'lindemannrock-base/_layouts/cp-utilities' %}

{% set utilitiesConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Overview'|t('my-plugin'),
        description: 'Monitor and manage your plugin.'|t('my-plugin'),
    },
} %}

{% block overview %}
    {% include 'lindemannrock-base/_components/unified-card' with {
        title: 'Total Items',
        color: '#059669',
        value: stats.total,
    } only %}
{% endblock %}

{% block notices %}
    {% include 'lindemannrock-base/_components/setup-incomplete' with {
        complete: setupStatus.complete,
        setupUrl: setupStatus.setupUrl,
        heading: 'Setup incomplete'|t('my-plugin'),
        message: 'Finish setup before using this utility.'|t('my-plugin'),
        actionLabel: 'Open setup'|t('my-plugin'),
    } only %}
{% endblock %}

{% block beforeQuickActions %}
    {% include 'lindemannrock-base/_components/info-box' with {
        message: '<strong>' ~ 'Site'|t('my-plugin') ~ ':</strong> ' ~ selectedSiteLabel,
        margin: 'both',
    } %}
{% endblock %}

{% block quickActions %}
    {% include 'lindemannrock-base/_layouts/cp-utilities/_action-section' with {
        title: 'Navigation'|t('my-plugin'),
        description: 'Access main plugin sections.'|t('my-plugin'),
        buttons: [
            {type: 'link', label: 'Manage Items'|t('my-plugin'), url: url('my-plugin/items')},
            {type: 'link', label: 'Settings'|t('my-plugin'), url: url('my-plugin/settings')},
        ],
    } only %}
{% endblock %}
```

## Configuration

### plugin

| Key | Type | Description |
|-----|------|-------------|
| `handle` | `string` | Plugin handle (used for translations) |
| `name` | `string` | Plugin display name |

### page

| Key | Type | Description |
|-----|------|-------------|
| `title` | `string` | Page title (default: `'Overview'`) |
| `description` | `string` | Description text below the title |
| `overviewTitle` | `string` | Custom heading for the overview section (default: `'System Overview'`) |

## Overridable Blocks

### headerActions

Actions in the header area (e.g., a site selector dropdown). Renders beside the title.

```twig
{% block headerActions %}
    <div class="select">
        <select name="site" onchange="window.location.href=this.value">
            {% for site in sites %}
                <option value="{{ url('my-plugin/utilities', {siteId: site.id}) }}">{{ site.name }}</option>
            {% endfor %}
        </select>
    </div>
{% endblock %}
```

### notices

Alert or info notices above the page header. Use this for setup-incomplete banners or high-priority warnings that should appear before the utility title and metrics.

```twig
{% block notices %}
    {% include 'lindemannrock-base/_components/setup-incomplete' with {
        complete: setupStatus.complete,
        setupUrl: setupStatus.setupUrl,
        heading: 'Setup incomplete'|t('my-plugin'),
        message: 'Finish setup before using this utility.'|t('my-plugin'),
        actionLabel: 'Open setup'|t('my-plugin'),
    } only %}
{% endblock %}
```

### overview

System overview cards. Rendered inside an `.lr-unified-cards` grid container.

```twig
{% block overview %}
    {% include 'lindemannrock-base/_components/unified-card' with {
        title: 'Total Items',
        color: '#059669',
        value: stats.total,
        description: 'items in database',
    } only %}
    {% include 'lindemannrock-base/_components/unified-card' with {
        title: 'Cache Status',
        color: '#0ea5e9',
        value: 'Active',
        subBoxes: [
            {value: '✓', label: 'Search'},
            {value: '✓', label: 'Data'},
        ],
    } only %}
{% endblock %}
```

### beforeQuickActions

Plugin-specific content between the overview grid and the Quick Actions pane. Use this for active filter summaries or contextual status messages tied to the overview. Use `notices` for high-priority alerts that should appear before the page header.

```twig
{% block beforeQuickActions %}
    {% include 'lindemannrock-base/_components/info-box' with {
        message: '<strong>' ~ 'Site'|t('my-plugin') ~ ':</strong> ' ~ selectedSiteLabel,
        margin: 'both',
    } %}
{% endblock %}
```

### quickActions

Quick action sections. Use the `_action-section` sub-template to create grouped action buttons.

```twig
{% block quickActions %}
    {% include 'lindemannrock-base/_layouts/cp-utilities/_action-section' with {
        title: 'Navigation'|t('my-plugin'),
        description: 'Access main plugin sections.'|t('my-plugin'),
        buttons: [
            {type: 'link', label: 'Items'|t('my-plugin'), url: url('my-plugin/items'), permission: 'myPlugin:viewItems'},
            {type: 'link', label: 'Settings'|t('my-plugin'), url: url('my-plugin/settings'), permission: 'myPlugin:manageSettings'},
        ],
    } only %}

    {% include 'lindemannrock-base/_layouts/cp-utilities/_action-section' with {
        title: 'Cache Management'|t('my-plugin'),
        description: 'Clear cached data.'|t('my-plugin'),
        showSeparator: true,
        buttons: [
            {type: 'button', label: 'Clear Cache'|t('my-plugin'), id: 'clear-cache', count: cacheCount},
            {type: 'button', label: 'Clear All'|t('my-plugin'), id: 'clear-all', class: 'btn secondary'},
        ],
    } only %}
{% endblock %}
```

### additionalContent

Plugin-specific sections after the Quick Actions pane. For filter summaries that should appear before Quick Actions, use `beforeQuickActions`; for high-priority banners above the page header, use `notices`.

### scripts

JavaScript handlers for action buttons.

### pluginCredit

Defaults to the standard LindemannRock credit footer. Override to customize.

## Action Section Sub-Template

The `_action-section` sub-template creates a titled group of buttons within the Quick Actions pane.

**Path:** `lindemannrock-base/_layouts/cp-utilities/_action-section`

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | `string` | | Section heading |
| `description` | `string` | | Description text |
| `showSeparator` | `bool` | `false` | Show `<hr>` above the section |
| `buttons` | `array` | `[]` | Button configurations (see below) |

**Button properties:**

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `type` | `string` | `'button'` | `'button'` or `'link'` |
| `label` | `string` | | Button text |
| `url` | `string` | | URL (for `link` type) |
| `id` | `string` | | HTML id (for `button` type JavaScript handlers) |
| `class` | `string` | `'btn'` | CSS class (use `'btn secondary'` for secondary style) |
| `permission` | `string` | | Permission check (button hidden if user lacks it) |
| `count` | `int` | | Number to display after label (e.g., `'Clear Cache (42)'`) |
| `showCount` | `bool` | auto | Whether to show count (defaults `true` when `count` is provided) |

## AJAX Button Handler

The `_ajax-button` sub-template generates JavaScript for buttons that make AJAX POST calls to controller actions.

**Path:** `lindemannrock-base/_layouts/cp-utilities/_ajax-button`

```twig
{% block scripts %}
<script>
$(function() {
    {% include 'lindemannrock-base/_layouts/cp-utilities/_ajax-button' with {
        id: 'clear-cache',
        action: actionUrl('my-plugin/settings/clear-cache'),
        errorMessage: 'Failed to clear cache'|t('my-plugin'),
    } only %}

    {% include 'lindemannrock-base/_layouts/cp-utilities/_ajax-button' with {
        id: 'clear-all-analytics',
        action: actionUrl('my-plugin/settings/clear-analytics'),
        confirm: 'Are you sure you want to delete all analytics?'|t('my-plugin'),
        confirmSecond: 'This cannot be undone. Are you absolutely sure?'|t('my-plugin'),
        errorMessage: 'Failed to clear analytics'|t('my-plugin'),
        reloadDelay: 2000,
    } only %}
});
</script>
{% endblock %}
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | `string` | **(required)** | Button element ID (without `#`) |
| `action` | `string` | **(required)** | Controller action URL |
| `confirm` | `string` | | Confirmation message (shows dialog before executing) |
| `confirmSecond` | `string` | | Second confirmation for destructive actions |
| `successMessage` | `string` | | Message on success (uses `response.message` if not set) |
| `errorMessage` | `string` | `'An error occurred'` | Message on error |
| `reloadDelay` | `int` | `1500` | Delay before page reload in ms (use `0` to skip reload) |

## Next Steps

- [Unified Card](components.md#unified-card) — card component used in the overview grid
- [CP Table Layout](cp-table-layout.md) — table pages for data listings
- [CP Analytics Layout](cp-analytics-layout.md) — analytics dashboard pages
