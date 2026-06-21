# ConfigFileHelper

`ConfigFileHelper` reads handle-keyed sections from plugin config files and merges config-defined records with database-defined records.

Use it for features where users can define reusable resources in both the Control Panel and `config/{plugin-handle}.php`, such as backends, providers, sender IDs, widgets, or external log handlers.

Reach for it instead of calling `Craft::$app->getConfig()->getConfigFromFile()` directly when records are handle-keyed and may come from both places — it gives you per-section access, the list of defined handles, single-record lookup, and the config-wins merge in one place, with per-request caching so repeated reads don't re-parse the file.

## Config sections

```php
use lindemannrock\base\helpers\ConfigFileHelper;

// Whole config array for the plugin (all sections)
$config   = ConfigFileHelper::getConfig('logging-library');

// One section — array of records as defined in the config file
$handlers = ConfigFileHelper::getConfigSection('logging-library', 'externalHandlers');

// Just the handles defined in a section — string[]
$handles  = ConfigFileHelper::getHandles('logging-library', 'externalHandlers');

// One record by handle — ?array (null when not defined in the config file)
$handler  = ConfigFileHelper::getConfigByHandle('logging-library', 'externalHandlers', 'sentry-production');

// Does the config file define this handle? — bool
$exists   = ConfigFileHelper::handleExistsInConfig('logging-library', 'externalHandlers', 'sentry-production');
```

Config is cached per plugin handle for the current request, so calling these repeatedly parses the file only once.

## Merge pattern

The common shape is "config-file records + CP/database records, config wins on conflict" — which is exactly what `mergeConfigAndDatabase()` does:

```php
$merged = ConfigFileHelper::mergeConfigAndDatabase($configItems, $databaseItems);
```

Config items take precedence over database items with the same handle, and the returned array is keyed by handle. Database items may be arrays with a `handle` key or objects with a `handle` property. Use it when an admin can add a record in the CP but a config file should be able to lock or shadow it.

## Clearing cache

```php
ConfigFileHelper::clearCache('logging-library');
ConfigFileHelper::clearCache(); // all plugin config cache
```
