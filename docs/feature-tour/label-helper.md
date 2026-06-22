# LabelHelper @since(5.22.0)

Shorten long form-field labels for compact UI contexts — dropdown options, table column headers, tabs — where a full label would overflow the available space.

## shorten()

Strips leading numbering, collapses whitespace, and truncates to a maximum length with an ellipsis. A trailing parenthetical suffix (e.g. ` (Geely Service)`) is preserved when present so entries remain distinguishable after truncation.

```php
use lindemannrock\base\helpers\LabelHelper;

LabelHelper::shorten('1. This is a very long field label that will be truncated');
// "This is a very long field label that will be truncated"  (under 60 chars — no ellipsis)

LabelHelper::shorten('1. What is your preferred contact method? (Optional Field)', 40);
// "What is your preferred... (Optional Field)"

LabelHelper::shorten('10) Short label');
// "Short label"

LabelHelper::shorten('');
// ""
```

### Signature

```php
public static function shorten(string $label, int $maxLength = 60): string
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$label` | `string` | — | The full label to shorten |
| `$maxLength` | `int` | `60` | Max length of the result (excluding the ellipsis character) |

### Behaviour details

- Leading numbering patterns like `1. `, `10) `, `2. ` are stripped first
- Whitespace is collapsed before length is checked
- If the label fits within `$maxLength` after stripping, it is returned as-is (no ellipsis)
- If a trailing parenthetical suffix exists (e.g. ` (Geely Service)`), it is preserved at the end of the truncated string, as long as the suffix leaves at least 10 characters for the body
- Returns an empty string for empty/whitespace-only input

## Twig filter

Use the `|lrShortLabel` filter to call this directly in templates:

```twig
{{ field.label|lrShortLabel }}        {# default 60 chars #}
{{ field.label|lrShortLabel(40) }}    {# custom max length #}
```

See [Twig Filters & Functions](../template-guides/twig-filters-functions.md) for the full filter reference.

## Next Steps

- [Twig Filters & Functions](../template-guides/twig-filters-functions.md) — all available Twig filters
- [API Reference](../developers/api-reference.md) — full PHP API reference
