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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Pins the contract for {@see GeoHelper}.
 *
 * @since 5.25.0
 */
final class GeoHelperTest extends IntegrationTestCase
{
    /**
     * @param array{country: string, timezone: string} $expected
     */
    #[DataProvider('defaultLocationProvider')]
    public function testGetDefaultLocationReturnsCanonicalMetadata(string $countryCode, string $city, array $expected): void
    {
        $location = GeoHelper::getDefaultLocation($countryCode, $city);

        self::assertNotNull($location);
        self::assertSame($countryCode, $location['countryCode']);
        self::assertSame($expected['country'], $location['country']);
        self::assertSame($city, $location['city']);
        self::assertNotSame('', $location['region']);
        self::assertSame($expected['timezone'], $location['timezone']);
        self::assertIsFloat($location['latitude']);
        self::assertIsFloat($location['longitude']);
    }

    public function testGetDefaultLocationUsesCanonicalParisRegionWithoutLegacyAlias(): void
    {
        $location = GeoHelper::getDefaultLocation('FR', 'Paris');

        self::assertNotNull($location);
        self::assertSame('Île-de-France', $location['region']);
        self::assertNotSame('Ile-de-France', $location['region']);
    }

    public function testGetDefaultLocationUsesCanonicalCoordinates(): void
    {
        $coordinates = [
            'US/New York' => [40.7128, -74.0060],
            'US/Los Angeles' => [34.0522, -118.2437],
            'US/Chicago' => [41.8781, -87.6298],
            'US/San Francisco' => [37.7749, -122.4194],
            'GB/London' => [51.5074, -0.1278],
            'GB/Manchester' => [53.4808, -2.2426],
            'AE/Dubai' => [25.2048, 55.2708],
            'AE/Abu Dhabi' => [24.4539, 54.3773],
            'SA/Riyadh' => [24.7136, 46.6753],
            'SA/Jeddah' => [21.5433, 39.1728],
            'DE/Berlin' => [52.5200, 13.4050],
            'DE/Munich' => [48.1351, 11.5820],
            'FR/Paris' => [48.8566, 2.3522],
            'NL/Amsterdam' => [52.3676, 4.9041],
            'SE/Stockholm' => [59.3293, 18.0686],
            'DK/Copenhagen' => [55.6761, 12.5683],
            'NO/Oslo' => [59.9139, 10.7522],
            'CA/Toronto' => [43.6532, -79.3832],
            'CA/Vancouver' => [49.2827, -123.1207],
            'AU/Sydney' => [-33.8688, 151.2093],
            'AU/Melbourne' => [-37.8136, 144.9631],
            'JP/Tokyo' => [35.6762, 139.6503],
            'SG/Singapore' => [1.3521, 103.8198],
            'IN/Mumbai' => [19.0760, 72.8777],
            'IN/Delhi' => [28.7041, 77.1025],
        ];

        foreach ($coordinates as $pair => [$latitude, $longitude]) {
            [$countryCode, $city] = explode('/', $pair, 2);
            $location = GeoHelper::getDefaultLocation($countryCode, $city);

            self::assertNotNull($location);
            self::assertSame($latitude, $location['latitude']);
            self::assertSame($longitude, $location['longitude']);
        }
    }

    public function testGetDefaultLocationRequiresAnExactSupportedPair(): void
    {
        self::assertNull(GeoHelper::getDefaultLocation('ZZ', 'Missing City'));
        self::assertNull(GeoHelper::getDefaultLocation('NL', 'Rotterdam'));
        self::assertNull(GeoHelper::getDefaultLocation('nl', 'Amsterdam'));
        self::assertNull(GeoHelper::getDefaultLocation('NL', 'amsterdam'));
    }

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

    /**
     * @return iterable<string, array{string, string, array{country: string, timezone: string}}>
     */
    public static function defaultLocationProvider(): iterable
    {
        yield 'US / New York' => ['US', 'New York', ['country' => 'United States', 'timezone' => 'America/New_York']];
        yield 'US / Los Angeles' => ['US', 'Los Angeles', ['country' => 'United States', 'timezone' => 'America/Los_Angeles']];
        yield 'US / Chicago' => ['US', 'Chicago', ['country' => 'United States', 'timezone' => 'America/Chicago']];
        yield 'US / San Francisco' => ['US', 'San Francisco', ['country' => 'United States', 'timezone' => 'America/Los_Angeles']];
        yield 'GB / London' => ['GB', 'London', ['country' => 'United Kingdom', 'timezone' => 'Europe/London']];
        yield 'GB / Manchester' => ['GB', 'Manchester', ['country' => 'United Kingdom', 'timezone' => 'Europe/London']];
        yield 'AE / Dubai' => ['AE', 'Dubai', ['country' => 'United Arab Emirates', 'timezone' => 'Asia/Dubai']];
        yield 'AE / Abu Dhabi' => ['AE', 'Abu Dhabi', ['country' => 'United Arab Emirates', 'timezone' => 'Asia/Dubai']];
        yield 'SA / Riyadh' => ['SA', 'Riyadh', ['country' => 'Saudi Arabia', 'timezone' => 'Asia/Riyadh']];
        yield 'SA / Jeddah' => ['SA', 'Jeddah', ['country' => 'Saudi Arabia', 'timezone' => 'Asia/Riyadh']];
        yield 'DE / Berlin' => ['DE', 'Berlin', ['country' => 'Germany', 'timezone' => 'Europe/Berlin']];
        yield 'DE / Munich' => ['DE', 'Munich', ['country' => 'Germany', 'timezone' => 'Europe/Berlin']];
        yield 'FR / Paris' => ['FR', 'Paris', ['country' => 'France', 'timezone' => 'Europe/Paris']];
        yield 'NL / Amsterdam' => ['NL', 'Amsterdam', ['country' => 'Netherlands', 'timezone' => 'Europe/Amsterdam']];
        yield 'SE / Stockholm' => ['SE', 'Stockholm', ['country' => 'Sweden', 'timezone' => 'Europe/Stockholm']];
        yield 'DK / Copenhagen' => ['DK', 'Copenhagen', ['country' => 'Denmark', 'timezone' => 'Europe/Copenhagen']];
        yield 'NO / Oslo' => ['NO', 'Oslo', ['country' => 'Norway', 'timezone' => 'Europe/Oslo']];
        yield 'CA / Toronto' => ['CA', 'Toronto', ['country' => 'Canada', 'timezone' => 'America/Toronto']];
        yield 'CA / Vancouver' => ['CA', 'Vancouver', ['country' => 'Canada', 'timezone' => 'America/Vancouver']];
        yield 'AU / Sydney' => ['AU', 'Sydney', ['country' => 'Australia', 'timezone' => 'Australia/Sydney']];
        yield 'AU / Melbourne' => ['AU', 'Melbourne', ['country' => 'Australia', 'timezone' => 'Australia/Melbourne']];
        yield 'JP / Tokyo' => ['JP', 'Tokyo', ['country' => 'Japan', 'timezone' => 'Asia/Tokyo']];
        yield 'SG / Singapore' => ['SG', 'Singapore', ['country' => 'Singapore', 'timezone' => 'Asia/Singapore']];
        yield 'IN / Mumbai' => ['IN', 'Mumbai', ['country' => 'India', 'timezone' => 'Asia/Kolkata']];
        yield 'IN / Delhi' => ['IN', 'Delhi', ['country' => 'India', 'timezone' => 'Asia/Kolkata']];
    }
}
