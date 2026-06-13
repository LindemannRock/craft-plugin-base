<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\tests\Integration\fixtures\PluginNoIcon\StubPluginNoIcon;
use lindemannrock\base\tests\Integration\fixtures\PluginWithIcon\StubPluginWithIcon;
use ReflectionClass;

// Fixtures live under autoload-dev, which is not registered when base is
// consumed through the shared vendor, so load them explicitly.
require_once __DIR__ . '/fixtures/PluginWithIcon/StubPluginWithIcon.php';
require_once __DIR__ . '/fixtures/PluginNoIcon/StubPluginNoIcon.php';

/**
 * Pins the contract for {@see PluginHelper::getIconSvg()}.
 *
 * @since 5.27.0
 */
final class PluginHelperTest extends IntegrationTestCase
{
    public function testGetIconSvgReadsSrcIconSvg(): void
    {
        // newInstanceWithoutConstructor avoids Craft module wiring; getIconSvg
        // only reflects on the class file location, never calls plugin methods.
        $reflection = new ReflectionClass(StubPluginWithIcon::class);
        $plugin = $reflection->newInstanceWithoutConstructor();

        $expected = trim((string)file_get_contents(dirname((string)$reflection->getFileName()) . '/icon.svg'));

        self::assertSame($expected, PluginHelper::getIconSvg($plugin));
    }

    public function testGetIconSvgReturnsNullWhenIconMissing(): void
    {
        $plugin = (new ReflectionClass(StubPluginNoIcon::class))->newInstanceWithoutConstructor();

        self::assertNull(PluginHelper::getIconSvg($plugin));
    }

    public function testLrLogoFilePointsAtTheCanonicalAsset(): void
    {
        $file = PluginHelper::lrLogoFile();

        self::assertStringEndsWith('/src/icons/lr-logo.svg', $file);
        self::assertFileExists($file);
    }

    public function testLrLogoPathsAreTheBareLogoPaths(): void
    {
        $paths = PluginHelper::lrLogoPaths();

        // Exactly the two logo paths, with no surrounding <svg>/<g> wrapper —
        // the caller supplies those (with its own viewBox/fill).
        self::assertSame(2, substr_count($paths, '<path'));
        self::assertStringNotContainsString('<svg', $paths);
        self::assertStringNotContainsString('<g', $paths);

        // The geometry is the same data the canonical file carries.
        $svg = (string)file_get_contents(PluginHelper::lrLogoFile());
        self::assertStringContainsString('M1.01 19.05', $paths);
        self::assertStringContainsString('M1.01 19.05', $svg);
    }
}
