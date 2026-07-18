# EditionTrait @since(5.5.0)

Standardized edition support for plugins with Standard/Pro tiers. Provides helper methods for checking editions, gating features, and building comparison UIs.

> [!NOTE]
> Single-edition plugins — free or paid — don't need this trait at all. Craft gives every plugin a default `standard` edition, and the price (including $0) is set per edition in the Plugin Store, never in code. Use this trait only when a plugin offers multiple editions.

## Edition Tiers

Two constants, ordered from lowest to highest:

| Constant | Value | Use Case |
|----------|-------|----------|
| `EDITION_STANDARD` | `'standard'` | Base edition — the lower tier, free or paid |
| `EDITION_PRO` | `'pro'` | Full-featured top tier |

The standard lineup is `[STANDARD, PRO]`, with Standard either free or paid. Additional tiers (a mid tier, or one above Pro) get a purpose-named constant added to this trait when the product decision exists — never invented per-plugin.

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
            self::EDITION_STANDARD,
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
$plugin->isPro();       // true if Pro

// Minimum edition check
$plugin->isAtLeast(MyPlugin::EDITION_PRO);   // true only for Pro

// Below edition check (for upgrade prompts)
$plugin->isBelow(MyPlugin::EDITION_PRO);     // true for Standard

// Edition metadata
$plugin->getEditionHandle();     // 'standard', 'pro'
$plugin->getEditionName();       // 'Standard', 'Pro' (current edition)
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
