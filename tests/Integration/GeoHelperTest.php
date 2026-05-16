<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\GeoHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see GeoHelper}.
 *
 * @since 5.25.0
 */
final class GeoHelperTest extends IntegrationTestCase
{
    public function testGetDialCodeReturnsBareDigitsForKnownCodesAndNullForUnknown(): void
    {
        // Documented contract: bare digits with NO leading '+'. Callers are
        // responsible for any display formatting.
        self::assertSame('1', GeoHelper::getDialCode('US'));
        self::assertSame('966', GeoHelper::getDialCode('SA'));
        self::assertSame('44', GeoHelper::getDialCode('GB'));

        // Case-insensitive + trims input.
        self::assertSame('1', GeoHelper::getDialCode('us'));
        self::assertSame('966', GeoHelper::getDialCode(' sa '));

        // Unknown / empty input returns null (NOT empty string, NOT the input).
        self::assertNull(GeoHelper::getDialCode('XX'));
        self::assertNull(GeoHelper::getDialCode(''));
    }

    public function testGetCountryNameReturnsOriginalCodeForUnknown(): void
    {
        // Happy path.
        self::assertSame('United States', GeoHelper::getCountryName('US'));
        self::assertSame('Saudi Arabia', GeoHelper::getCountryName('SA'));

        // Empty input returns empty string.
        self::assertSame('', GeoHelper::getCountryName(''));

        // Documented fallback: unknown codes return the (uppercased, trimmed)
        // input itself, NOT null. This differs from getDialCode's null
        // fallback — keep both contracts pinned so a "let's make these
        // consistent" refactor surfaces here.
        self::assertSame('XX', GeoHelper::getCountryName('XX'));
        self::assertSame('XX', GeoHelper::getCountryName('xx'));
        self::assertSame('XX', GeoHelper::getCountryName(' xx '));

        // isValidCountryCode is the boolean partner of getCountryName for
        // callers that want a yes/no answer.
        self::assertTrue(GeoHelper::isValidCountryCode('US'));
        self::assertFalse(GeoHelper::isValidCountryCode('XX'));
        self::assertFalse(GeoHelper::isValidCountryCode(''));
    }
}
