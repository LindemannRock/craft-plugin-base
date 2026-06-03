# AssetVolumeHelper @since(5.26.0)

`AssetVolumeHelper` validates, on the server, that a submitted asset ID is one the current user is actually allowed to select from an optionally-restricted volume.

An asset-select field's `sources` restriction is enforced **client-side only**. A crafted POST — or an imported CSV row — can carry any asset ID regardless of the configured volume. Any save, import, or preview path that accepts an asset ID must re-check it on the server. This helper centralizes that guard so every entry point enforces the same three rules: the asset exists, it belongs to the allowed volume, and the current user holds the matching `viewAssets:` permission.

## Validate a Submitted Asset ID

```php
use lindemannrock\base\helpers\AssetVolumeHelper;

// In a save controller — `qrLogoId` came straight from a POST body param.
$shortLink->qrLogoId = AssetVolumeHelper::validateAssetId(
    $this->request->getBodyParam('qrLogoId'),
    $settings->qrLogoVolumeUid,
);
```

The helper accepts the raw submitted value in any of the shapes an asset field produces — an `int`, a numeric string, an element-field array (`[id]`), or `null` — and returns either the validated `int` ID or `null`. Assign the result directly; a failed check yields `null`, so an unauthorized or bogus ID is simply dropped instead of persisted.

## Volume Restriction Is Optional

```php
// Restricted to one volume:
AssetVolumeHelper::validateAssetId($id, $settings->qrLogoVolumeUid);

// Any volume allowed (permission is still enforced):
AssetVolumeHelper::validateAssetId($id, null);
```

A `null` or empty `$allowedVolumeUid` means any volume is acceptable — but the user must still hold `viewAssets:` for the volume the asset actually lives in. This mirrors the behavior of a plugin setting where the volume is configurable and may be unset.

## Where to Call It

Call it at **every** write or render path that accepts an asset ID, not just the field UI:

| Path | Why |
|------|-----|
| Save controller action | The hidden field value is attacker-controllable on POST |
| CSV / data import | Imported rows carry arbitrary IDs |
| Public/preview endpoints | Query-param IDs bypass the field entirely |

The field's `sources` restriction stays as a UX nicety; this helper is the actual gate.

## Not For

- Generating safe filenames or path fragments — use [SafeSegmentHelper](safe-segment-helper.md)
- Deciding which volume a plugin should offer — that is a plugin setting, not a helper concern
- Validating non-asset element selections — this helper is asset/volume specific

## Next Steps

- [SafeSegmentHelper](safe-segment-helper.md) — safe non-DB string fragments
- [API Reference](../developers/api-reference.md) — full PHP API reference
