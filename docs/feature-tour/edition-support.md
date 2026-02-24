# EditionTrait @since(5.0.0)

Standardized edition support for plugins with Lite/Standard/Pro tiers. Provides helper methods for checking editions, gating features, and building comparison UIs.

## Edition Tiers

Three constants, ordered from lowest to highest:

| Constant | Value | Use Case |
|----------|-------|----------|
| `EDITION_STANDARD` | `'standard'` | Free tier or single-edition plugins |
| `EDITION_LITE` | `'lite'` | Entry-level paid tier |
| `EDITION_PRO` | `'pro'` | Full-featured paid tier |

Not every plugin uses all three. Common configurations:

- **Free-only:** `[STANDARD]`
- **Free + paid:** `[STANDARD, PRO]`
- **Two paid tiers:** `[LITE, PRO]`
- **Three tiers:** `[STANDARD, LITE, PRO]`

## Setup

```php
use craft\base\Plugin;
use lindemannrock\base\traits\EditionTrait;

class MyPlugin extends Plugin
{
    use EditionTrait;

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }
}
```

## Checking Editions

```php
$plugin = MyPlugin::getInstance();

// Exact edition check
$plugin->isStandard();  // true if Standard
$plugin->isLite();      // true if Lite
$plugin->isPro();       // true if Pro

// Minimum edition check
$plugin->isAtLeast(MyPlugin::EDITION_LITE);  // true for Lite and Pro
$plugin->isAtLeast(MyPlugin::EDITION_PRO);   // true only for Pro

// Below edition check (for upgrade prompts)
$plugin->isBelow(MyPlugin::EDITION_PRO);     // true for Standard and Lite

// Edition metadata
$plugin->getEditionHandle();     // 'lite', 'pro', etc.
$plugin->getEditionName();       // 'Lite', 'Pro', etc. (current edition)
$plugin->getEditionName('pro');  // 'Pro' (any edition by handle)
$plugin->hasMultipleEditions();  // true if more than one edition
```

## Gating Features

### In Controllers

```php
public function actionAdvancedExport(): Response
{
    MyPlugin::getInstance()->requireEdition(MyPlugin::EDITION_PRO);
    // Throws ForbiddenHttpException if not Pro

    // ... pro-only code
}

// With custom feature name in error message
MyPlugin::getInstance()->requireEdition(MyPlugin::EDITION_PRO, 'Advanced Export');
// "Advanced Export requires the Pro edition."
```

### In Services

```php
public function getAnalytics(): array
{
    if (!MyPlugin::getInstance()->isPro()) {
        return ['message' => 'Analytics requires Pro edition.'];
    }

    // ... analytics code
}
```

### In Templates

```twig
{% if plugin.isPro() %}
    {# Pro-only features #}
    {% include 'my-plugin/_partials/analytics' %}
{% else %}
    {# Upgrade prompt #}
    <div class="lr-upgrade-prompt">
        <p>Upgrade to Pro to unlock analytics.</p>
    </div>
{% endif %}
```

## Feature Comparison

Override `getEditionFeatures()` to provide edition comparison data. Use `editionIsAtLeast()` to compare the given edition against the hierarchy (unlike `isAtLeast()`, which checks the *active* edition):

```php
public function getEditionFeatures(string $edition): array
{
    $features = [
        'Basic management' => true,
        'CSV export' => true,
    ];

    if (static::editionIsAtLeast($edition, self::EDITION_PRO)) {
        $features['Analytics dashboard'] = true;
        $features['API access'] = true;
        $features['CLI commands'] = true;
    }

    return $features;
}
```

Check feature availability:

```php
$plugin->hasFeature('Analytics dashboard');  // true if Pro
```

## Next Steps

- [SettingsPersistenceTrait](settings-persistence.md) — database storage for settings
- [Bootstrapping](../developers/bootstrapping.md) — plugin initialization guide
