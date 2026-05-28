# Storage Path Validator

`StoragePathValidator` validates local filesystem paths used by plugin settings, such as backup and export directories. Runtime/display code should use `StoragePathHelper::resolve()` for the same env-var + alias resolution contract.

Use it for settings fields where administrators can choose a storage root:

```php
use lindemannrock\base\validators\StoragePathValidator;

[
    ['backupPath'],
    StoragePathValidator::class,
    'translationCategory' => 'my-plugin',
    'allowedAliases' => ['@storage', '@root'],
    'requireAlias' => true,
    'preventWebroot' => true,
],
```

```php
use lindemannrock\base\helpers\StoragePathHelper;

$resolvedPath = StoragePathHelper::resolve($settings->backupPath);
```

## What It Blocks

- Parent directory traversal (`..`)
- `@web` and `@webroot`
- URL-resolved paths
- Paths inside the resolved `@webroot` directory when `preventWebroot` is enabled
- Non-allowed aliases when `allowedAliases` is set
- Non-alias paths when `requireAlias` is enabled, unless they resolve inside an allowed alias root

## Environment Variables

Environment variables are allowed by default with `allowEnvVars`.

When `requireAlias` is enabled, raw settings values should still usually be aliases or environment variables. Absolute paths are accepted only when they resolve inside one of the configured `allowedAliases` roots. This preserves config-file patterns such as `App::env('BACKUP_PATH')` while still blocking arbitrary filesystem locations.

Examples with `allowedAliases => ['@storage', '@root']`:

```php
// Valid
'@storage/my-plugin/backups'
'@root/backups/my-plugin'
'$BACKUP_PATH' // if it resolves under @storage or @root
'/resolved/storage/path/my-plugin' // if it is inside resolved @storage or @root

// Invalid
'@webroot/backups'
'https://example.com/backups'
'$BACKUP_PATH' // if it resolves outside @storage or @root
'/tmp/my-plugin'
```

Set `allowEnvVars => false` for settings that must be literal aliases only.

## Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `allowedAliases` | `array` | `['@storage', '@root']` | Alias prefixes allowed for alias values and env-resolved absolute paths |
| `preventWebroot` | `bool` | `true` | Reject paths that resolve inside `@webroot` |
| `requireAlias` | `bool` | `false` | Require a literal alias, env var, or absolute path that resolves inside an allowed alias root |
| `allowEnvVars` | `bool` | `true` | Allow `$VARIABLE` values and validate their resolved path |
| `translationCategory` | `string` | `'app'` | Translation category for validation errors |
