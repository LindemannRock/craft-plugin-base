# SettingsPersistenceTrait @since(5.0.0)

Saves and loads plugin settings from a dedicated database table instead of Craft's project config. This avoids project config merge conflicts and gives full control over settings storage.

## Why Not Project Config?

LindemannRock plugins use database tables for settings because:

- Settings persist across environments without project config sync issues
- Complex settings structures (arrays, JSON) are easier to manage
- No merge conflicts when multiple developers work on different environments
- Better control over migrations and type conversion

## Setup

### 1. Add the Trait to Your Settings Model

```php
use craft\base\Model;
use lindemannrock\base\traits\SettingsPersistenceTrait;

class Settings extends Model
{
    use SettingsPersistenceTrait;

    // Required: table name without prefix
    protected static function tableName(): string
    {
        return 'myplugin_settings';
    }

    // Type conversion methods (optional)
    protected static function booleanFields(): array
    {
        return ['enableAnalytics', 'enableCache'];
    }

    protected static function integerFields(): array
    {
        return ['itemsPerPage', 'cacheDuration'];
    }

    protected static function floatFields(): array
    {
        return ['scoreThreshold'];
    }

    protected static function jsonFields(): array
    {
        return ['patterns', 'allowedDomains'];
    }

    // Settings properties
    public bool $enableAnalytics = true;
    public bool $enableCache = true;
    public int $itemsPerPage = 100;
    public int $cacheDuration = 3600;
    public float $scoreThreshold = 0.5;
    public array $patterns = [];
    public array $allowedDomains = [];
}
```

### 2. Create the Database Table

In your plugin's `Install.php` migration, create the settings table with an `id=1` row:

```php
$this->createTable('{{%myplugin_settings}}', [
    'id' => $this->primaryKey(),
    'enableAnalytics' => $this->boolean()->defaultValue(true),
    'enableCache' => $this->boolean()->defaultValue(true),
    'itemsPerPage' => $this->integer()->defaultValue(100),
    'cacheDuration' => $this->integer()->defaultValue(3600),
    'scoreThreshold' => $this->float()->defaultValue(0.5),
    'patterns' => $this->text(),
    'allowedDomains' => $this->text(),
    'dateCreated' => $this->dateTime()->notNull(),
    'dateUpdated' => $this->dateTime()->notNull(),
    'uid' => $this->uid(),
]);

// Insert default row (always id=1)
$this->insert('{{%myplugin_settings}}', ['id' => 1]);
```

## Loading Settings

```php
$settings = Settings::loadFromDatabase();

// Or populate an existing instance
$settings = new Settings();
$settings = Settings::loadFromDatabase($settings);
```

`loadFromDatabase()` handles:
- Missing tables during installation (returns defaults)
- Type conversion for boolean, integer, float, and JSON fields
- Removing system columns (`id`, `dateCreated`, `dateUpdated`, `uid`)

## Saving Settings

```php
$settings->enableAnalytics = false;
$settings->patterns = ['*.pdf', '*.doc'];

$success = $settings->saveToDatabase();
// Returns true on success, false on failure
```

`saveToDatabase()` handles:
- Model validation before saving
- JSON encoding for array fields
- Excluding fields overridden by config file (if using `SettingsConfigTrait`)
- Excluding fields listed in `excludeFromSave()`
- Updating the `dateUpdated` timestamp

## Failure behavior

Database load failures keep the existing Settings instance or its defaults, and database save failures return `false`. Failure logs retain safe operational details—the load or save operation, Settings resource, exception class, and validated database codes when available—without including persisted values, credentials, SQL, or query parameters.

## Type Conversion Methods

| Method | DB Type | PHP Type | Example |
|--------|---------|----------|---------|
| `booleanFields()` | `0`/`1` | `true`/`false` | Feature toggles |
| `integerFields()` | String digit | `int` | Limits, durations |
| `floatFields()` | String decimal | `float` | Thresholds, scores |
| `jsonFields()` | JSON string | `array` | Patterns, lists |

## Excluding Fields from Save

Fields that come from `.env` or config files only:

```php
protected static function excludeFromSave(): array
{
    return ['apiKey', 'secretToken'];
}
```

These fields are never written to the database — they must be provided via config file or environment variables.

## Using with SettingsConfigTrait

When combined with [SettingsConfigTrait](settings-config.md), fields overridden by the config file are automatically excluded from database saves:

```php
class Settings extends Model
{
    use SettingsPersistenceTrait;
    use SettingsConfigTrait;

    // ...
}
```

If `enableAnalytics` is set in `config/my-plugin.php`, it will not be saved to the database, and the config file value takes precedence.

## Next Steps

- [SettingsConfigTrait](settings-config.md) — config file overrides with lock icons
- [SettingsDisplayNameTrait](settings-display-name.md) — custom plugin display names
- [Bootstrapping](../developers/bootstrapping.md) — loading settings during plugin init
