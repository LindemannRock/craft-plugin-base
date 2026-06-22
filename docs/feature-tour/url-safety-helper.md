# UrlSafetyHelper @since(5.26.0)

`UrlSafetyHelper` constrains a value to a safe redirect target before it's handed to a redirect response.

Any URL that reaches `$this->redirect(...)` should be limited to a relative path or an `http`/`https` absolute URL — never an executable scheme like `javascript:`, `data:`, or `vbscript:`. This helper centralizes that rule so every redirect, fallback, and not-found path enforces it the same way, instead of each controller carrying its own copy.

## Sanitize a Redirect Target

```php
use lindemannrock\base\helpers\UrlSafetyHelper;

// In a controller, before redirecting to an admin-configured URL:
$redirectUrl = UrlSafetyHelper::sanitizeRedirectUrl($settings->notFoundRedirectUrl ?? '/');
return $this->redirect($redirectUrl);
```

A safe value (relative path starting with `/`, or an `http(s)` URL) is returned unchanged. Anything else collapses to the fallback (default `/`):

```php
UrlSafetyHelper::sanitizeRedirectUrl('/dashboard');            // '/dashboard'
UrlSafetyHelper::sanitizeRedirectUrl('https://example.com');   // 'https://example.com'
UrlSafetyHelper::sanitizeRedirectUrl('javascript:alert(1)');   // '/'
UrlSafetyHelper::sanitizeRedirectUrl('//evil.com');            // '/' (protocol-relative)
UrlSafetyHelper::sanitizeRedirectUrl('data:text/html,x', '/404'); // '/404'
```

## Branch or Log on a Blocked Value

When you want to log or react to an unsafe value rather than silently fall back, check it first:

```php
$url = $settings->notFoundRedirectUrl ?? '/';

if (!UrlSafetyHelper::isSafeRedirectUrl($url)) {
    $this->logWarning('Blocked unsafe redirect URL', ['url' => $url]);
}

return $this->redirect(UrlSafetyHelper::sanitizeRedirectUrl($url));
```

## Guard a Stored URL's Scheme @since(5.27.0)

`sanitizeRedirectUrl()` / `isSafeRedirectUrl()` are deliberately strict — they only accept `http(s)` or a relative path, which is right for a *redirect target* but too narrow for a field that legitimately stores other schemes (e.g. a deep link `myapp://`, or `mailto:`/`tel:`). When you need to keep those working but still block executable schemes, use `hasDangerousScheme()` as a denylist guard on top of your own validation:

```php
use lindemannrock\base\helpers\UrlSafetyHelper;

// Reject only the executable schemes; let app deep links through.
if (UrlSafetyHelper::hasDangerousScheme($url)) {
    // javascript:, vbscript:, data:, file: — block it
}
```

It blocks `javascript:`, `vbscript:`, `data:`, and `file:`, including whitespace- or entity-obfuscated variants (`java\tscript:`, `&#106;avascript:`, `javascript://%0a…`), while leaving `https://`, `myapp://`, `fb://`, `mailto:` and the like untouched:

```php
UrlSafetyHelper::hasDangerousScheme('javascript:alert(1)');     // true
UrlSafetyHelper::hasDangerousScheme('javascript://%0aalert(1)'); // true (obfuscated)
UrlSafetyHelper::hasDangerousScheme('file:///etc/passwd');      // true
UrlSafetyHelper::hasDangerousScheme('myapp://open/profile');    // false (app deep link)
UrlSafetyHelper::hasDangerousScheme('https://example.com');     // false
```

Unlike `isSafeRedirectUrl()`, this is anchored at the **scheme**: a URL such as `https://x.com/path?u=javascript:y` is *not* flagged, because `javascript:` isn't the scheme.

## Allow Extra Schemes @since(5.27.0)

When a field legitimately stores non-`http(s)` targets — action links like `mailto:`/`tel:`, or app deep links — pass an `$extraSchemes` allowlist to opt those schemes in while keeping everything else strict. Both `sanitizeRedirectUrl()` and `isSafeRedirectUrl()` accept it. Scheme names are lowercase and without the trailing colon:

```php
// Allow mailto: and tel: alongside relative paths and http(s):
UrlSafetyHelper::isSafeRedirectUrl('mailto:hi@example.com', ['mailto', 'tel']); // true
UrlSafetyHelper::isSafeRedirectUrl('mailto:hi@example.com');                    // false (strict default)

UrlSafetyHelper::sanitizeRedirectUrl('tel:+15551234', '/', ['mailto', 'tel']);  // 'tel:+15551234'
```

The default is an empty list, so existing callers keep the strict relative-or-`http(s)` contract. Listing a scheme here is an explicit allow — it does **not** run the dangerous-scheme check, so pair it with `hasDangerousScheme()` whenever the scheme list itself comes from untrusted input.

## Scope

- `sanitizeRedirectUrl()` accepts a single-leading-slash relative path (`/path`) but rejects scheme-relative `//host` URLs — the browser resolves `//host` to an external origin, so it collapses to the fallback like any other off-site target without an explicit `http(s)://` scheme.
- `isHttpUrlWithHost()` @since(5.27.0) is the underlying predicate for the `http(s)`-with-host test: it returns `true` only for an absolute `http`/`https` URL that includes a host. A bare scheme (`https://`) or a host-less form (`https:///path`) returns `false`. Exposed for callers that need just that check without the relative-path branch.
- This is a redirect-target guard only. It does not validate or normalize a URL for storage, display, or as a destination link — use the appropriate validator for those.

## Not For

- Generating safe filenames or path fragments — use [SafeSegmentHelper](safe-segment-helper.md)
- Normalizing slugs/handles — use [SlugHandleHelper](slug-handle-helper.md)
- Validating a settings field on save — use a Yii validator on the Settings model

## Next Steps

- [SafeSegmentHelper](safe-segment-helper.md) — safe non-DB string fragments
- [API Reference](../developers/api-reference.md) — full PHP API reference
