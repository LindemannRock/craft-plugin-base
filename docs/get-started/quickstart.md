# Quickstart

A condensed reference for building a new plugin with the base module. Every pattern below is used across all LindemannRock plugins — copy, adapt, and ship.

## 1. Plugin Class — init()

```php
use lindemannrock\base\helpers\ColorHelper;
use lindemannrock\base\helpers\PluginHelper;

public function init(): void
{
    parent::init();
    self::$plugin = $this;

    PluginHelper::bootstrap(
        $this,
        'myHelper',                            // Twig global name
        ['myPlugin:viewSystemLogs'],           // Log viewing permissions
        ['myPlugin:downloadSystemLogs'],       // Log download permissions
        [
            'colorSets' => [
                'itemStatus' => [
                    'active' => ColorHelper::getPaletteColor('teal'),
                    'inactive' => ColorHelper::getPaletteColor('red'),
                    'pending' => ColorHelper::getPaletteColor('amber'),
                ],
            ],
        ]
    );
}
```

`bootstrap()` handles: Base module registration, Twig extension loading, logging integration, color set registration.

## 2. Settings Model

```php
use craft\base\Model;
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;

class Settings extends Model
{
    use SettingsDisplayNameTrait;
    use SettingsPersistenceTrait;
    use SettingsConfigTrait;

    public ?string $pluginName = null;
    public bool $enableAnalytics = true;
    public int $analyticsLimit = 10000;
    public ?array $excludePatterns = null;

    protected static function tableName(): string
    {
        return 'myplugin_settings';
    }

    protected static function booleanFields(): array
    {
        return ['enableAnalytics'];
    }

    protected static function integerFields(): array
    {
        return ['analyticsLimit'];
    }

    protected static function jsonFields(): array
    {
        return ['excludePatterns'];
    }
}
```

Settings are stored in a dedicated DB table (row `id = 1`), not in project config.

## 3. CP Navigation

```php
use lindemannrock\base\helpers\CpNavHelper;

public function getCpNavItem(): ?array
{
    $item = parent::getCpNavItem();

    if ($item) {
        $settings = $this->getSettings();
        $user = Craft::$app->getUser();

        $item['label'] = $settings->getFullName();

        $sections = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'my-plugin',
            ],
            [
                'key' => 'items',
                'label' => 'Items',
                'url' => 'my-plugin/items',
                'permissionsAny' => ['myPlugin:manageItems'],
            ],
            [
                'key' => 'settings',
                'label' => 'Settings',
                'url' => 'my-plugin/settings',
                'permissionsAll' => ['myPlugin:manageSettings'],
            ],
        ];

        $item['subnav'] = CpNavHelper::buildSubnav($user, $settings, $sections);

        if (empty($item['subnav'])) {
            return null;
        }
    }

    return $item;
}
```

## 4. CP Table Page

The most-used layout. Extends `cp-table` and provides filters, search, sort, pagination, and row actions.

```twig
{% extends 'lindemannrock-base/_layouts/cp-table' %}

{% set tableConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Items'|t('my-plugin'),
        subnav: 'items',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
        ],
    },
    filters: [
        {
            type: 'status',
            param: 'status',
            current: statusFilter,
            label: statusFilter == 'all' ? 'All'|t('my-plugin') : statusFilter|capitalize,
            options: [
                {value: 'all', label: 'All'|t('my-plugin'), status: 'all'},
                {value: 'active', label: 'Active'|t('my-plugin'), status: 'green'},
                {value: 'inactive', label: 'Inactive'|t('my-plugin'), status: 'disabled'},
            ],
        },
    ],
    search: {value: search, placeholder: 'Search items…'|t('my-plugin')},
    sort: {current: sort, direction: dir},
    table: {
        columns: [
            {key: 'name', label: 'Name'|t('my-plugin'), sortable: true},
            {key: 'status', label: 'Status'|t('my-plugin'), sortable: true, hideable: true},
            {key: 'dateCreated', label: 'Created'|t('my-plugin'), sortable: true, hideable: true},
        ],
        items: items,
        emptyMessage: 'No items found.'|t('my-plugin'),
    },
    pagination: pagination,
    newButton: canCreate ? {label: 'New Item'|t('my-plugin'), url: url('my-plugin/items/new')} : null,
} %}

{% block tableRow %}
    <td>
        {% if canEdit %}
            <a href="{{ url('my-plugin/items/' ~ item.id) }}">{{ item.name }}</a>
        {% else %}
            {{ item.name }}
        {% endif %}
    </td>
    <td>
        {% include 'lindemannrock-base/_components/badge' with {
            label: item.status|capitalize,
            colorSet: 'itemStatus',
            colorKey: item.status,
        } only %}
    </td>
    <td>{{ item.dateCreated|lrDatetime }}</td>
{% endblock %}

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

{% block toolbarActions %}
    {% include 'lindemannrock-base/_components/export-menu' with {
        action: 'my-plugin/items/export',
        permission: 'myPlugin:manageItems',
    } only %}
{% endblock %}
```

## 5. CP Analytics Page

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
        dateRange: { default: 'last30days', current: dateRange },
        sites: { enabled: hasSites, current: siteId, sites: sites },
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
            {% include 'lindemannrock-base/_components/unified-card' with {
                title: 'Total Views'|t('my-plugin'),
                color: '#6366f1',
                value: stats.totalViews|number,
            } only %}
            {% include 'lindemannrock-base/_components/unified-card' with {
                title: 'Unique Visitors'|t('my-plugin'),
                color: '#0ea5e9',
                value: stats.uniqueVisitors|number,
            } only %}
        </div>
        <div class="lr-analytics-charts">
            <div class="lr-chart-container">
                <canvas id="myPlugin-overview-chart"></canvas>
            </div>
        </div>
    </div>
    <div id="details" class="lr-tab-content hidden">
        {# Details tab content #}
    </div>
{% endblock %}
```

## 6. CP Utilities Page

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
        title: 'Total Items'|t('my-plugin'),
        color: lrPaletteColor('emerald').color,
        value: stats.total,
        description: 'items in database',
    } only %}
{% endblock %}

{% block quickActions %}
    {% include 'lindemannrock-base/_layouts/cp-utilities/_action-section' with {
        title: 'Navigation'|t('my-plugin'),
        description: 'Access main plugin sections.'|t('my-plugin'),
        buttons: [
            {type: 'link', label: 'Manage Items'|t('my-plugin'), url: url('my-plugin/items'), permission: 'myPlugin:manageItems'},
            {type: 'link', label: 'Settings'|t('my-plugin'), url: url('my-plugin/settings'), permission: 'myPlugin:manageSettings'},
        ],
    } only %}

    {% include 'lindemannrock-base/_layouts/cp-utilities/_action-section' with {
        title: 'Maintenance'|t('my-plugin'),
        description: 'Clear cached data.'|t('my-plugin'),
        showSeparator: true,
        buttons: [
            {type: 'button', label: 'Clear Cache'|t('my-plugin'), id: 'clear-cache', count: cacheCount},
        ],
    } only %}
{% endblock %}

{% block scripts %}
<script>
$(function() {
    {% include 'lindemannrock-base/_layouts/cp-utilities/_ajax-button' with {
        id: 'clear-cache',
        action: actionUrl('my-plugin/settings/clear-cache'),
        errorMessage: 'Failed to clear cache'|t('my-plugin'),
    } only %}
});
</script>
{% endblock %}
```

## 7. Common Components

### Badge

```twig
{% include 'lindemannrock-base/_components/badge' with {
    label: 'Active',
    colorSet: 'itemStatus',
    colorKey: 'active',
} only %}
```

### Info Box

```twig
{% include 'lindemannrock-base/_components/info-box' with {
    type: 'tip',
    message: 'Analytics data is cached for 5 minutes.'|t('my-plugin'),
} only %}
```

### Export Menu

```twig
{% include 'lindemannrock-base/_components/export-menu' with {
    action: 'my-plugin/items/export',
    permission: 'myPlugin:manageItems',
} only %}
```

### Row Actions

```twig
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
```

## 8. Helper Quick Reference

| Task | Helper | Example |
|------|--------|---------|
| Format a date | `DateFormatHelper` | `DateFormatHelper::formatDatetime($date)` |
| Format in Twig | `lrDatetime` filter | `{{ item.dateCreated\|lrDatetime }}` |
| Timezone-safe SQL | `DateFormatHelper` | `DateFormatHelper::localDateExpression('dateCreated')` |
| Color from palette | `ColorHelper` | `ColorHelper::getPaletteColor('teal')` |
| Color set lookup | `lrSetColor` function | `{{ lrSetColor('itemStatus', 'active') }}` |
| Export data | `ExportHelper` | `ExportHelper::toCsv($rows, $headers, $filename, $dateColumns)` |
| JSON in SQL | `DbHelper` | `DbHelper::jsonExtract('metadata', 'provider')` |
| GROUP_CONCAT | `DbHelper` | `DbHelper::groupConcat('tag', ', ')` |
| Country name | `GeoHelper` | `GeoHelper::getCountryName('US')` |
| Dial code | `GeoHelper` | `GeoHelper::getDialCode('DE')` → `"49"` |
| Check plugin | `PluginHelper` | `PluginHelper::isPluginEnabled('other-plugin')` |
| CSV import | `CsvImportHelper` | `CsvImportHelper::parseUpload($file)` |
| Slug/handle uniqueness | `SlugHandleHelper` | `SlugHandleHelper::makeUnique('{{%my_items}}', 'handle', $handle)` |
| Safe filename segment | `SafeSegmentHelper` | `SafeSegmentHelper::filenamePart($label)` |

## Next Steps

- [Feature Tour](../feature-tour/overview.md) — detailed docs for each helper and trait
- [CP Table Layout](../template-guides/cp-table-layout.md) — full config reference for table pages
- [CP Analytics Layout](../template-guides/cp-analytics-layout.md) — full config reference for analytics pages
- [Components](../template-guides/components.md) — all available components
- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — complete Twig reference
