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

## Scope

- `sanitizeRedirectUrl()` treats **any** `/`-prefixed value as a relative path (matching the long-standing controller behavior). If your context must also reject scheme-relative `//host` URLs, check for that explicitly before calling.
- This is a redirect-target guard only. It does not validate or normalize a URL for storage, display, or as a destination link — use the appropriate validator for those.

## Not For

- Generating safe filenames or path fragments — use [SafeSegmentHelper](safe-segment-helper.md)
- Normalizing slugs/handles — use [SlugHandleHelper](slug-handle-helper.md)
- Validating a settings field on save — use a Yii validator on the Settings model

## Next Steps

- [SafeSegmentHelper](safe-segment-helper.md) — safe non-DB string fragments
- [API Reference](../developers/api-reference.md) — full PHP API reference
