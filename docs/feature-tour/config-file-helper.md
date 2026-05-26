# Config File Helper

`ConfigFileHelper` reads handle-keyed sections from plugin config files and merges config-defined records with database-defined records.

Use it for features where users can define reusable resources in both the Control Panel and `config/{plugin-handle}.php`, such as backends, providers, sender IDs, widgets, or external log handlers.

## Config Sections

```php
use lindemannrock\base\helpers\ConfigFileHelper;

$handlers = ConfigFileHelper::getConfigSection('logging-library', 'externalHandlers');
$handles = ConfigFileHelper::getHandles('logging-library', 'externalHandlers');
$handler = ConfigFileHelper::getConfigByHandle('logging-library', 'externalHandlers', 'sentry-production');
```

Config is cached per plugin handle for the current request.

## Merge Pattern

```php
$merged = ConfigFileHelper::mergeConfigAndDatabase($configItems, $databaseItems);
```

Config items take precedence over database items with the same handle. The returned array is keyed by handle.

Database items may be arrays with a `handle` key or objects with a `handle` property.

## Clearing Cache

```php
ConfigFileHelper::clearCache('logging-library');
ConfigFileHelper::clearCache(); // all plugin config cache
```
