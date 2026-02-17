# CpNavHelper

Builds CP subnav arrays and determines default routes based on permissions, settings flags, and custom conditions. Centralizes the logic that decides which sidebar items a user can see.

## Building a Subnav

Define sections as an array and let `buildSubnav()` filter based on the current user's permissions and plugin settings:

```php
use lindemannrock\base\helpers\CpNavHelper;

$sections = [
    [
        'key' => 'dashboard',
        'label' => Craft::t('my-plugin', 'Dashboard'),
        'url' => 'my-plugin',
        'permissionsAny' => ['myPlugin:manageDashboard'],
    ],
    [
        'key' => 'logs',
        'label' => Craft::t('my-plugin', 'Logs'),
        'url' => 'my-plugin/logs',
        'permissionsAll' => ['myPlugin:manageLogs'],
    ],
    [
        'key' => 'analytics',
        'label' => Craft::t('my-plugin', 'Analytics'),
        'url' => 'my-plugin/analytics',
        'settingsFlag' => 'enableAnalytics',
        'permissionsAny' => ['myPlugin:manageAnalytics'],
    ],
    [
        'key' => 'settings',
        'label' => Craft::t('app', 'Settings'),
        'url' => 'my-plugin/settings',
        'permissionsAll' => ['myPlugin:manageSettings'],
    ],
];

$user = Craft::$app->getUser();
$settings = MyPlugin::$plugin->getSettings();

$subnav = CpNavHelper::buildSubnav($user, $settings, $sections);
```

## Section Options

Each section supports these properties:

| Property | Type | Description |
|----------|------|-------------|
| `key` | `string` | Unique identifier for the subnav item |
| `label` | `string` | Display label |
| `url` | `string` | URL path (e.g., `'my-plugin/logs'`) |
| `permissionsAll` | `string\|array` | User must have ALL listed permissions |
| `permissionsAny` | `string\|array` | User must have at least ONE permission |
| `settingsFlag` | `string` | Property on Settings model that must be truthy |
| `when` | `bool\|callable` | Custom condition — callable receives `($settings, $user)` |
| `enabled` | `bool` | Set to `false` to always hide |

### Permission Checks

```php
// User must have ALL permissions
['permissionsAll' => ['myPlugin:manageLogs', 'myPlugin:manageSettings']]

// User must have at least ONE permission
['permissionsAny' => ['myPlugin:manageLogs', 'myPlugin:manageAnalytics']]

// Single permission (string shorthand)
['permissionsAll' => 'myPlugin:manageLogs']
```

### Settings Flags

Show a section only when a plugin setting is enabled:

```php
['settingsFlag' => 'enableAnalytics']
// Section appears only if $settings->enableAnalytics is truthy
```

### Custom Conditions

Use a callable for complex logic:

```php
[
    'key' => 'providers',
    'label' => 'Providers',
    'url' => 'my-plugin/providers',
    'when' => fn($settings, $user) => PluginHelper::isPluginEnabled('formie'),
]
```

## Finding the Default Route

Get the first accessible route for the current user — useful for the plugin's `getCpUrl()`:

```php
$defaultRoute = CpNavHelper::firstAccessibleRoute($user, $settings, $sections);
// Returns 'my-plugin/logs' if that's the first section the user can see
// Returns null if no sections are accessible
```

## Typical Plugin Usage

```php
// In your Plugin class
public function getCpNavItems(): array
{
    $navItems = parent::getCpNavItems();
    $navItems['subnav'] = CpNavHelper::buildSubnav(
        Craft::$app->getUser(),
        $this->getSettings(),
        $this->getSections()
    );
    return $navItems;
}

public function getCpUrl(): ?string
{
    return CpNavHelper::firstAccessibleRoute(
        Craft::$app->getUser(),
        $this->getSettings(),
        $this->getSections()
    );
}

private function getSections(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'my-plugin', ...],
        ['key' => 'logs', 'label' => 'Logs', 'url' => 'my-plugin/logs', ...],
    ];
}
```

## Next Steps

- [PluginHelper](plugin-helper.md) — bootstrap and plugin detection
- [Bootstrapping](../developers/bootstrapping.md) — full plugin initialization guide
