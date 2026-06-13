<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\ColorHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use ReflectionClass;

/**
 * Pins the contract for {@see ColorHelper}.
 *
 * @since 5.25.0
 */
final class ColorHelperTest extends IntegrationTestCase
{
    public function testGetPaletteColorShapeAndExpectedMembership(): void
    {
        $red = ColorHelper::getPaletteColor('red');

        // Documented shape: exactly four keys — class, color, rgb, text. NO
        // 'dot' key. Dots only appear on a handful of color sets via
        // array_merge() inside initialize(), never on the bare palette entry.
        self::assertSame(['class', 'color', 'rgb', 'text'], array_keys($red));
        self::assertArrayNotHasKey('dot', $red);

        $names = ColorHelper::getPaletteColorNames();

        // Palette is the documented 18-color set. If a future PR adds or
        // removes a color this test fires so the docs can be updated
        // alongside the change.
        self::assertCount(18, $names);
        self::assertContains('emerald', $names);
        self::assertContains('fuchsia', $names);
        self::assertContains('sky', $names);
        self::assertNotContains('white', $names);
        self::assertNotContains('black', $names);
        self::assertNotContains('brown', $names);

        // Unknown name falls back to DEFAULT_COLOR (the documented contract,
        // not throw-on-miss).
        self::assertSame(ColorHelper::DEFAULT_COLOR, ColorHelper::getPaletteColor('not-a-color'));
    }

    public function testPrimaryHexFromSvgPicksFirstNonMonochromeHex(): void
    {
        // Skips white/black (3- and 6-digit), returns the first real colour
        // upper-cased. Order matters: #FFFFFF and #000 are skipped, #1a73e8 wins.
        self::assertSame(
            '#1A73E8',
            ColorHelper::primaryHexFromSvg('<svg fill="#FFFFFF" stroke="#000"><path fill="#1a73e8"/><path fill="#820eff"/></svg>'),
        );

        // 3-digit shorthand is honoured.
        self::assertSame('#ABC', ColorHelper::primaryHexFromSvg('<svg><path fill="#fff"/><path fill="#abc"/></svg>'));

        // No usable colour → null: null, empty, monochrome-only, and no-hex.
        self::assertNull(ColorHelper::primaryHexFromSvg(null));
        self::assertNull(ColorHelper::primaryHexFromSvg(''));
        self::assertNull(ColorHelper::primaryHexFromSvg('<svg fill="#fff" stroke="#000000"/>'));
        self::assertNull(ColorHelper::primaryHexFromSvg('<svg><path d="M0 0h24"/></svg>'));
    }

    public function testRegisterColorSetRoundTrip(): void
    {
        $set = [
            'active' => ColorHelper::getPaletteColor('teal'),
            'inactive' => ColorHelper::getPaletteColor('gray'),
        ];

        try {
            self::assertFalse(ColorHelper::hasColorSet('__base_test_status'));

            ColorHelper::registerColorSet('__base_test_status', $set);

            self::assertTrue(ColorHelper::hasColorSet('__base_test_status'));
            self::assertSame($set, ColorHelper::getColorSet('__base_test_status'));
            self::assertSame($set['active'], ColorHelper::getSetColor('__base_test_status', 'active'));
        } finally {
            // ColorHelper::$colorSets is a per-process static, so a registered
            // set leaks into every subsequent test in the same PHPUnit run.
            // Drop the test set via reflection so the suite stays
            // order-independent.
            $reflection = new ReflectionClass(ColorHelper::class);
            $colorSets = $reflection->getProperty('colorSets');
            $current = $colorSets->getValue();
            unset($current['__base_test_status']);
            $colorSets->setValue(null, $current);
        }
    }

    public function testMixBlendsHexColors(): void
    {
        // weight 0 = pure A, weight 1 = pure B.
        self::assertSame('#FF0000', ColorHelper::mix('#FF0000', '#0000FF', 0.0));
        self::assertSame('#0000FF', ColorHelper::mix('#FF0000', '#0000FF', 1.0));

        // Midpoint blends each channel (round half away from zero).
        self::assertSame('#800080', ColorHelper::mix('#FF0000', '#0000FF', 0.5));

        // Darken-toward-base — the documented hero use (#FACC15 → 0.6 toward #0B1220).
        self::assertSame('#6B5C1C', ColorHelper::mix('#FACC15', '#0B1220', 0.6));

        // 3-digit shorthand and a missing leading '#' are accepted.
        self::assertSame('#808080', ColorHelper::mix('#FFF', '#000', 0.5));
        self::assertSame('#FF0000', ColorHelper::mix('FF0000', '0000FF', 0.0));

        // Weight is clamped to [0, 1].
        self::assertSame('#0000FF', ColorHelper::mix('#FF0000', '#0000FF', 2.0));
        self::assertSame('#FF0000', ColorHelper::mix('#FF0000', '#0000FF', -1.0));

        // Unparseable input falls back to the parseable side; both bad → black.
        self::assertSame('#0000FF', ColorHelper::mix('nope', '#0000FF', 0.5));
        self::assertSame('#FF0000', ColorHelper::mix('#FF0000', 'nope', 0.5));
        self::assertSame('#000000', ColorHelper::mix('nope', 'nope'));
    }

    public function testLuminance(): void
    {
        self::assertSame(255, ColorHelper::luminance('#FFFFFF'));
        self::assertSame(0, ColorHelper::luminance('#000000'));
        self::assertSame(30, ColorHelper::luminance('#1E1E1E'));
        self::assertSame(102, ColorHelper::luminance('#1A73E8'));
        self::assertSame(0, ColorHelper::luminance('not-a-hex'));
    }

    public function testWithAlpha(): void
    {
        self::assertSame('#1A73E8FF', ColorHelper::withAlpha('#1A73E8', 1.0));
        self::assertSame('#1A73E880', ColorHelper::withAlpha('#1A73E8', 0.5));
        self::assertSame('#FFFFFF00', ColorHelper::withAlpha('#FFF', 0.0));   // 3-digit expands

        // Alpha clamps to [0, 1].
        self::assertSame('#000000FF', ColorHelper::withAlpha('#000000', 2.0));
        self::assertSame('#00000000', ColorHelper::withAlpha('#000000', -1.0));

        // Unparseable colour falls back to black, alpha still applied.
        self::assertSame('#00000080', ColorHelper::withAlpha('nope', 0.5));
    }

    public function testIconColorRoles(): void
    {
        // accent = most saturated, ink = least-saturated non-accent (order-independent).
        self::assertSame(
            ['accent' => '#FFD138', 'ink' => '#1E1E1E'],
            ColorHelper::iconColorRoles('<svg><path fill="#FFD138"/><g fill="#1E1E1E"/></svg>'),
        );
        self::assertSame(
            ['accent' => '#1A73E8', 'ink' => '#FFFFFF'],
            ColorHelper::iconColorRoles('<svg><rect fill="#FFFFFF"/><path fill="#1A73E8"/></svg>'),
        );

        // Single-colour icons: ink falls back to white / near-black by contrast.
        self::assertSame(
            ['accent' => '#1A73E8', 'ink' => '#FFFFFF'],
            ColorHelper::iconColorRoles('<svg><path fill="#1A73E8"/></svg>'),
        );
        self::assertSame(
            ['accent' => '#FFD138', 'ink' => '#1E1E1E'],
            ColorHelper::iconColorRoles('<svg><path fill="#FFD138"/></svg>'),
        );

        // No usable colour -> null.
        self::assertNull(ColorHelper::iconColorRoles('<svg><path d="M0 0h24"/></svg>'));
        self::assertNull(ColorHelper::iconColorRoles(null));
        self::assertNull(ColorHelper::iconColorRoles(''));
    }
}
