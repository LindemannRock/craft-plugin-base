<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Craft;
use craft\services\Images;
use lindemannrock\base\helpers\QrCodeRendererHelper;
use lindemannrock\base\qr\GdImageBackEnd;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * @since 5.38.0
 */
final class QrCodeRendererHelperTest extends IntegrationTestCase
{
    public function testUsesImagickBackendForEffectiveImagickDriver(): void
    {
        if (!class_exists(\Imagick::class)) {
            self::markTestSkipped('Imagick is unavailable, so Bacon cannot construct its Imagick PNG backend.');
        }

        $renderer = $this->createRendererForDriver(imagick: true, gd: true);

        self::assertInstanceOf(ImagickImageBackEnd::class, $this->rendererBackend($renderer));
        $this->assertRendererOutputsPng($renderer);
    }

    public function testUsesGdBackendForEffectiveGdDriver(): void
    {
        $renderer = $this->createRendererForDriver(imagick: false, gd: true);

        self::assertInstanceOf(GdImageBackEnd::class, $this->rendererBackend($renderer));
        $this->assertRendererOutputsPng($renderer);
    }

    public function testRejectsMissingSupportedImageDriver(): void
    {
        $original = Craft::$app->getImages();
        Craft::$app->set('images', new EffectiveDriverImages(false, false));

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('effective image driver to be Imagick or GD');
            QrCodeRendererHelper::createPngRenderer(new RendererStyle(120));
        } finally {
            Craft::$app->set('images', $original);
        }
    }

    private function createRendererForDriver(bool $imagick, bool $gd): ImageRenderer
    {
        $original = Craft::$app->getImages();
        Craft::$app->set('images', new EffectiveDriverImages($imagick, $gd));

        try {
            return QrCodeRendererHelper::createPngRenderer(new RendererStyle(120));
        } finally {
            Craft::$app->set('images', $original);
        }
    }

    private function rendererBackend(ImageRenderer $renderer): ImageBackEndInterface
    {
        $property = new \ReflectionProperty(ImageRenderer::class, 'imageBackEnd');
        $backend = $property->getValue($renderer);
        self::assertInstanceOf(ImageBackEndInterface::class, $backend);

        return $backend;
    }

    private function assertRendererOutputsPng(ImageRenderer $renderer): void
    {
        $png = (new Writer($renderer))->writeString('base-effective-driver');
        $dimensions = getimagesizefromstring($png);

        self::assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
        self::assertIsArray($dimensions);
        self::assertSame(120, $dimensions[0]);
        self::assertSame(120, $dimensions[1]);
        self::assertSame('image/png', $dimensions['mime']);
    }
}

/**
 * @since 5.38.0
 */
final class EffectiveDriverImages extends Images
{
    public function __construct(
        private readonly bool $imagick,
        private readonly bool $gd,
    ) {
        parent::__construct();
    }

    public function init(): void
    {
    }

    public function getIsImagick(): bool
    {
        return $this->imagick;
    }

    public function getIsGd(): bool
    {
        return $this->gd;
    }
}
