<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\AssetVolumeHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see AssetVolumeHelper}.
 *
 * Covers the input-normalization and rejection branches, including a real
 * "asset not found" query. The accept branch (valid asset in the allowed
 * volume + a user holding `viewAssets:`) requires a volume/asset/user fixture,
 * which is out of pattern for this pure-logic suite; the security-relevant
 * direction — bad or unauthorized input is rejected — is what is asserted here.
 *
 * @since 5.26.0
 */
final class AssetVolumeHelperTest extends IntegrationTestCase
{
    public function testNullAndEmptyValuesReturnNull(): void
    {
        self::assertNull(AssetVolumeHelper::validateAssetId(null));
        self::assertNull(AssetVolumeHelper::validateAssetId(''));
        self::assertNull(AssetVolumeHelper::validateAssetId('0'));
        self::assertNull(AssetVolumeHelper::validateAssetId(0));
        self::assertNull(AssetVolumeHelper::validateAssetId(-5));
    }

    public function testEmptyArrayShapesReturnNull(): void
    {
        self::assertNull(AssetVolumeHelper::validateAssetId([]));
        self::assertNull(AssetVolumeHelper::validateAssetId(['']));
        self::assertNull(AssetVolumeHelper::validateAssetId([null]));
        self::assertNull(AssetVolumeHelper::validateAssetId(['0']));
    }

    public function testNonExistentAssetIdReturnsNull(): void
    {
        // A very high ID that will not exist in the test database. The helper
        // must reject it rather than trusting the submitted value.
        self::assertNull(AssetVolumeHelper::validateAssetId(2147483000));
        self::assertNull(AssetVolumeHelper::validateAssetId('2147483000'));
        self::assertNull(AssetVolumeHelper::validateAssetId([2147483000]));
    }

    public function testNonExistentAssetIdReturnsNullEvenWithVolumeRestriction(): void
    {
        self::assertNull(AssetVolumeHelper::validateAssetId(2147483000, 'some-volume-uid'));
    }
}
