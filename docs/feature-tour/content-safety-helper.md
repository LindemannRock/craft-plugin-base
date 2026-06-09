# ContentSafetyHelper @since(5.27.0)

`ContentSafetyHelper` detects dangerous HTML/script markup in free-text values that will be rendered into a page — titles, descriptions, translation strings, and similar.

It is the content counterpart to [UrlSafetyHelper](url-safety-helper.md). Where that helper asks *"is this **URL** a safe place to send a browser?"* (a scheme, anchored at the start of the value), this one asks *"does this **text** contain markup that executes when injected into HTML?"* — matched **anywhere** in the value, because an embedded `<script>` or `href="javascript:"` is dangerous wherever it sits.

## Reject Malicious Markup on Save

Use it as a validation guard on a plain-text field, in both the import path and the element/model `rules()` so the CP form and CSV import enforce the same rule:

```php
use lindemannrock\base\helpers\ContentSafetyHelper;

if (ContentSafetyHelper::containsMaliciousMarkup($title)) {
    // reject the row / add a model validation error — do not store it
}
```

A safe value returns `false`; anything containing executable markup returns `true`:

```php
ContentSafetyHelper::containsMaliciousMarkup('Summer Sale 2026');               // false
ContentSafetyHelper::containsMaliciousMarkup('price < $5 today');               // false
ContentSafetyHelper::containsMaliciousMarkup('Hello <b>bold</b>');              // false (safe tags)
ContentSafetyHelper::containsMaliciousMarkup('<script>alert(1)</script>');      // true
ContentSafetyHelper::containsMaliciousMarkup('<img src=x onerror="alert(1)">'); // true
ContentSafetyHelper::containsMaliciousMarkup('&#106;avascript:alert(1)');       // true (encoded)
```

## Log Which Threats Were Found

Pass a variable by reference to capture the labels of every pattern that matched — handy for an audit log:

```php
if (ContentSafetyHelper::containsMaliciousMarkup($description, $threats)) {
    $this->logWarning('Blocked malicious markup in description', ['threats' => $threats]);
    // $threats e.g. ['Script tag', 'Event handler']
}
```

Encoded hits are labelled with an ` (encoded)` suffix (e.g. `Script tag (encoded)`), because the helper re-scans the HTML-entity-decoded form so a payload like `&lt;script&gt;` cannot slip past the raw match.

## Detect-and-Reject, Not Strip

This is a precise denylist, not a sanitizer. It flags only known-dangerous patterns (`<script>`, `<iframe>`, `<object>`, `<embed>`, `<form>`, `<svg>`, `<meta http-equiv>`, `<base href>`, `javascript:`, `vbscript:`, `data:text/html`, `on*=` handlers), so legitimate text containing a lone `<` — like `price < $5` — is **not** a match.

Reject the value when it matches; do not try to clean it. `strip_tags()` eats everything from a lone `<` onward, silently mangling `price < $5` to `price `, and encoding the stored value double-encodes against the template layer's own escaping.

## Scope

- A detection guard for **free text** rendered as HTML. It complements — it does not replace — output-time escaping (Twig's default auto-escaping is still your primary XSS defense). This is defense-in-depth: it keeps dangerous markup out of storage so a future non-escaped surface can't resurrect it.
- It does **not** flag CSV formula injection (`=`, `@`, `+`, `-`, `|` prefixes). That is a spreadsheet concern handled on import by [CsvImportHelper](csv-import-helper.md) and on export by [ExportHelper](export-helper.md).

## Not For

- Validating a **URL** field's scheme — use [UrlSafetyHelper](url-safety-helper.md)
- Cleaning a value for display by stripping tags — reject instead; this helper does not sanitize
- CSV formula-injection escaping — handled by [CsvImportHelper](csv-import-helper.md) / [ExportHelper](export-helper.md)

## Next Steps

- [UrlSafetyHelper](url-safety-helper.md) — the URL-scheme counterpart
- [API Reference](../developers/api-reference.md) — full PHP API reference
