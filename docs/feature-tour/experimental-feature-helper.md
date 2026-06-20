# ExperimentalFeatureHelper @since(5.28.0)

`ExperimentalFeatureHelper` is the suite-wide gate for features that exist in code but are not public yet. Use it for internal experiments, launch-deferred features, and private testing surfaces that must stay invisible unless an explicit environment flag enables them.

This is separate from editions and permissions. Editions answer which customers can buy or use a public feature. Permissions answer which users can use a public feature. Experimental feature gates answer whether the feature exists publicly at all.

```php
use lindemannrock\base\helpers\ExperimentalFeatureHelper;

final class Plugin extends \craft\base\Plugin
{
    public const FEATURE_AI_SUGGESTIONS = 'TRANSLATION_MANAGER_ENABLE_AI';
}

if (ExperimentalFeatureHelper::isEnabled(Plugin::FEATURE_AI_SUGGESTIONS)) {
    // Register or render the internal-only surface.
}
```

## Strict Env Flag

Only the literal env value `true` enables a feature:

```dotenv
TRANSLATION_MANAGER_ENABLE_AI=true
```

Missing flags, `false`, `0`, `1`, `yes`, `on`, and empty strings are all treated as disabled. This is intentionally stricter than `BooleanHelper` because accidental exposure is worse than a missed internal opt-in.

## Optional Dev Mode Requirement

Some experiments should require both the env flag and Craft dev mode:

```php
ExperimentalFeatureHelper::isEnabled(
    Plugin::FEATURE_AI_SUGGESTIONS,
    requireDevMode: true,
);
```

Dev mode is additive. It never enables a feature by itself, because client staging sites may have dev mode enabled while the feature still must stay hidden.

## Controller Guard

Hidden UI is not enough. Put the server-side guard at every direct entry point:

```php
public function actionSuggest(): Response
{
    ExperimentalFeatureHelper::requireEnabled(Plugin::FEATURE_AI_SUGGESTIONS);

    // Perform the internal feature action.
}
```

`requireEnabled()` throws a 404 when the feature is off. That keeps direct URLs and AJAX endpoints from advertising an internal feature.

## Wiring Checklist

Use the same plugin constant at every surface:

- CP nav items, buttons, tabs, and settings fields
- Controller actions and service methods that perform the work
- Queue job creation and scheduled/background entry points
- Permission registration or display, where the plugin can conditionally hide it
- Public docs/README omission, plus a Deferred Coverage row

When a feature is behind this helper, record it in the plugin docs tracker's **Intentionally Undocumented / Deferred Coverage** table. Public docs should not describe the feature until the gate is removed or the feature is approved for release.

## Related

- [BooleanHelper](boolean-helper.md) — permissive boolean normalization for normal config, POST, and style values
- [Edition Support](edition-support.md) — public feature tiers
