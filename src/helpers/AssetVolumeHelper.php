<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use Craft;
use craft\elements\Asset;

/**
 * Server-side validation that a submitted asset ID is one the current user is
 * actually allowed to select from an (optionally) restricted volume.
 *
 * An asset-select field's `sources` restriction is enforced CLIENT-SIDE only:
 * a crafted POST (or an imported row) can carry any asset ID regardless of the
 * configured volume. Save, import, and preview paths that accept an asset ID
 * must re-check, on the server, that the asset exists, belongs to the allowed
 * volume, and that the current user holds the matching `viewAssets:` permission.
 * This helper centralizes that guard so every entry point enforces it the same
 * way.
 *
 * @since 5.26.0
 */
class AssetVolumeHelper
{
    /**
     * Validate a submitted asset ID against an optional volume restriction and
     * the current user's view permission.
     *
     * A null/empty `$allowedVolumeUid` means any volume is acceptable, but the
     * `viewAssets:` permission is still enforced.
     *
     * @param mixed $assetId Submitted ID — an int, numeric string, element-field array (`[id]`), or null.
     * @param string|null $allowedVolumeUid Volume UID the asset must belong to, or null/empty for any volume.
     * @return int|null The validated asset ID, or null if it fails any check.
     * @since 5.26.0
     */
    public static function validateAssetId(mixed $assetId, ?string $allowedVolumeUid = null): ?int
    {
        // Normalize the element-field array shape and empty/invalid values.
        if (is_array($assetId)) {
            $assetId = $assetId[0] ?? null;
        }

        if ($assetId === null || $assetId === '' || (int)$assetId <= 0) {
            return null;
        }

        $assetId = (int)$assetId;

        $asset = Asset::find()->id($assetId)->one();
        if (!$asset instanceof Asset) {
            return null;
        }

        $volumeUid = $asset->getVolume()->uid;

        // If a volume is configured, the asset must live in it.
        if ($allowedVolumeUid !== null && $allowedVolumeUid !== '' && $volumeUid !== $allowedVolumeUid) {
            return null;
        }

        // The user must be able to view assets in the resolved volume.
        if (!Craft::$app->getUser()->checkPermission('viewAssets:' . $volumeUid)) {
            return null;
        }

        return $assetId;
    }
}
