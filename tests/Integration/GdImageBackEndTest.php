<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use GdImage;
use lindemannrock\base\qr\GdImageBackEnd;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * @since 5.38.0
 */
final class GdImageBackEndTest extends IntegrationTestCase
{
    private const PNG_MAGIC = "\x89PNG\r\n\x1a\n";

    public function testRendersPngWithRequestedDimensionsMarginAndSolidColors(): void
    {
        $style = new RendererStyle(
            290,
            4,
            SquareModule::instance(),
            SquareEye::instance(),
            Fill::uniformColor(new Rgb(245, 246, 247), new Rgb(12, 34, 56)),
        );

        $png = $this->renderQr($style, 'base-gd-dimensions');
        $image = $this->decodePng($png);

        try {
            self::assertSame(self::PNG_MAGIC, substr($png, 0, 8));
            self::assertSame(290, imagesx($image));
            self::assertSame(290, imagesy($image));
            $this->assertPixelNear($image, 5, 5, [245, 246, 247]);
            $this->assertPixelNear($image, 38, 66, [12, 34, 56]);
        } finally {
            $this->releaseDecodedImage($image);
        }
    }

    public function testPreservesSquareRoundedAndDotModuleGeometry(): void
    {
        $square = $this->renderModulePath(SquareModule::instance());
        $rounded = $this->renderModulePath(new RoundnessModule(RoundnessModule::STRONG));
        $dots = $this->renderModulePath(new DotsModule(DotsModule::LARGE));

        try {
            $this->assertPixelNear($square, 42, 42, [0, 0, 0]);
            $this->assertPixelNear($rounded, 42, 42, [255, 255, 255]);
            $this->assertPixelNear($dots, 42, 42, [255, 255, 255]);

            $this->assertPixelNear($square, 80, 42, [0, 0, 0]);
            $this->assertPixelNear($rounded, 80, 42, [0, 0, 0]);
            $this->assertPixelNear($dots, 80, 42, [255, 255, 255]);

            foreach ([$square, $rounded, $dots] as $image) {
                $this->assertPixelNear($image, 60, 60, [0, 0, 0]);
                $this->assertPixelNear($image, 100, 60, [0, 0, 0]);
            }
        } finally {
            $this->releaseDecodedImage($square);
            $this->releaseDecodedImage($rounded);
            $this->releaseDecodedImage($dots);
        }
    }

    public function testPreservesSquareRoundedAndPointedEyeGeometry(): void
    {
        $square = $this->renderEye(SquareEye::instance());
        $rounded = $this->renderEye(SimpleCircleEye::instance());
        $pointed = $this->renderEye(PointyEye::instance());

        try {
            $this->assertPixelNear($square, 15, 15, [220, 20, 60]);
            $this->assertPixelNear($pointed, 15, 15, [255, 255, 255]);

            $this->assertPixelNear($square, 52, 52, [20, 70, 220]);
            $this->assertPixelNear($rounded, 52, 52, [255, 255, 255]);
            $this->assertPixelNear($rounded, 80, 80, [20, 70, 220]);
            $this->assertPixelNear($pointed, 80, 80, [20, 70, 220]);
        } finally {
            $this->releaseDecodedImage($square);
            $this->releaseDecodedImage($rounded);
            $this->releaseDecodedImage($pointed);
        }
    }

    public function testPreservesIndependentEyeColor(): void
    {
        $fill = Fill::withForegroundColor(
            new Rgb(255, 255, 255),
            new Rgb(0, 0, 0),
            new EyeFill(new Rgb(220, 20, 60), new Alpha(50, new Rgb(20, 70, 220))),
            EyeFill::inherit(),
            EyeFill::inherit(),
        );
        $style = new RendererStyle(290, 4, SquareModule::instance(), SquareEye::instance(), $fill);
        $image = $this->decodePng($this->renderQr($style, 'base-gd-eye-colors'));

        try {
            $this->assertPixelNear($image, 38, 66, [220, 20, 60]);
            $this->assertPixelNear($image, 47, 66, [255, 255, 255]);
            $this->assertPixelNear($image, 66, 66, [137, 162, 237], 8);
            $this->assertPixelNear($image, 197, 224, [0, 0, 0]);
        } finally {
            $this->releaseDecodedImage($image);
        }
    }

    public function testAppliesScaleTranslationRotationAndNestedTransformStack(): void
    {
        $backend = new GdImageBackEnd();
        $backend->new(200, new Rgb(255, 255, 255));
        $backend->scale(10);
        $backend->translate(5, 5);
        $backend->drawPathWithColor($this->rectanglePath(0, 0, 2, 1), new Rgb(220, 20, 60));

        $backend->push();
        $backend->translate(5, 0);
        $backend->rotate(90);
        $backend->drawPathWithColor($this->rectanglePath(0, 0, 2, 1), new Rgb(20, 70, 220));

        $backend->push();
        $backend->translate(0, -3);
        $backend->drawPathWithColor($this->rectanglePath(0, 0, 1, 1), new Rgb(20, 180, 80));
        $backend->pop();
        $backend->pop();

        $backend->translate(0, 5);
        $backend->drawPathWithColor($this->rectanglePath(0, 0, 1, 1), new Rgb(240, 170, 10));
        $image = $this->decodePng($backend->done());

        try {
            $this->assertPixelNear($image, 55, 55, [220, 20, 60]);
            $this->assertPixelNear($image, 95, 60, [20, 70, 220]);
            $this->assertPixelNear($image, 125, 55, [20, 180, 80]);
            $this->assertPixelNear($image, 55, 105, [240, 170, 10]);
            $this->assertPixelNear($image, 105, 105, [255, 255, 255]);
        } finally {
            $this->releaseDecodedImage($image);
        }
    }

    public function testPreservesCompoundPathHoles(): void
    {
        $backend = new GdImageBackEnd();
        $backend->new(160, new Rgb(20, 180, 80));
        $backend->scale(20);
        $backend->translate(4, 4);
        $backend->drawPathWithColor(SquareEye::instance()->getExternalPath(), new Rgb(220, 20, 60));
        $image = $this->decodePng($backend->done());

        try {
            $this->assertPixelNear($image, 15, 80, [220, 20, 60]);
            $this->assertPixelNear($image, 40, 80, [20, 180, 80]);
            $this->assertPixelNear($image, 80, 80, [20, 180, 80]);
            $this->assertPixelNear($image, 145, 80, [220, 20, 60]);
        } finally {
            $this->releaseDecodedImage($image);
        }
    }

    public function testRejectsUnsupportedGradientExplicitly(): void
    {
        $backend = new GdImageBackEnd();
        $backend->new(64, new Rgb(255, 255, 255));
        $gradient = new Gradient(
            new Rgb(0, 0, 0),
            new Rgb(255, 255, 255),
            GradientType::HORIZONTAL(),
        );

        try {
            $backend->drawPathWithGradient($this->rectanglePath(0, 0, 1, 1), $gradient, 0, 0, 1, 1);
            self::fail('Expected the GD backend to reject gradients.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('does not support gradients', $exception->getMessage());
        }

        $this->assertBackendIsReset($backend);
    }

    public function testRestoresOutputBuffersAndDestroysResourcesOnSuccess(): void
    {
        $backend = new InstrumentedGdImageBackEnd();
        $initialBufferLevel = ob_get_level();
        $backend->new(64, new Rgb(255, 255, 255));
        $backend->drawPathWithColor($this->rectanglePath(1, 1, 2, 2), new Rgb(0, 0, 0));

        $png = $backend->done();

        self::assertSame(self::PNG_MAGIC, substr($png, 0, 8));
        self::assertSame($initialBufferLevel, ob_get_level());
        self::assertSame($backend->createdImages, $backend->destroyedImages);
        self::assertGreaterThanOrEqual(3, $backend->destroyedImages);
        $this->assertBackendIsReset($backend);
    }

    public function testRestoresOutputBuffersAndDestroysResourcesOnFailure(): void
    {
        $backend = new InstrumentedGdImageBackEnd();
        $backend->failEncoding = true;
        $initialBufferLevel = ob_get_level();
        $backend->new(64, new Rgb(255, 255, 255));
        $backend->drawPathWithColor($this->rectanglePath(1, 1, 2, 2), new Rgb(0, 0, 0));

        try {
            $backend->done();
            self::fail('Expected the instrumented PNG encoder to fail.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Failed to finish the GD PNG image', $exception->getMessage());
        }

        self::assertSame($initialBufferLevel, ob_get_level());
        self::assertSame($backend->createdImages, $backend->destroyedImages);
        self::assertGreaterThanOrEqual(3, $backend->destroyedImages);
        $this->assertBackendIsReset($backend);
    }

    public function testBackendStateDoesNotLeakBetweenImages(): void
    {
        $backend = new GdImageBackEnd();

        $backend->new(64, new Rgb(220, 20, 60));
        $first = $backend->done();

        $backend->new(32, new Rgb(20, 70, 220));
        $second = $backend->done();

        $backend->new(32, new Rgb(20, 70, 220));
        $third = $backend->done();

        $firstImage = $this->decodePng($first);
        $secondImage = $this->decodePng($second);
        try {
            self::assertSame(64, imagesx($firstImage));
            self::assertSame(32, imagesx($secondImage));
            $this->assertPixelNear($firstImage, 10, 10, [220, 20, 60]);
            $this->assertPixelNear($secondImage, 10, 10, [20, 70, 220]);
            self::assertSame($second, $third, 'identical GD renders should produce deterministic PNG bytes');
        } finally {
            $this->releaseDecodedImage($firstImage);
            $this->releaseDecodedImage($secondImage);
        }
    }

    private function renderQr(RendererStyle $style, string $content): string
    {
        $writer = new Writer(new ImageRenderer($style, new GdImageBackEnd()));

        return $writer->writeString($content);
    }

    private function renderModulePath(ModuleInterface $module): GdImage
    {
        $matrix = new ByteMatrix(4, 3);
        $matrix->set(1, 1, 1);
        $matrix->set(2, 1, 1);

        $backend = new GdImageBackEnd();
        $backend->new(160, new Rgb(255, 255, 255));
        $backend->scale(40);
        $backend->drawPathWithColor($module->createPath($matrix), new Rgb(0, 0, 0));

        return $this->decodePng($backend->done());
    }

    private function renderEye(EyeInterface $eye): GdImage
    {
        $backend = new GdImageBackEnd();
        $backend->new(160, new Rgb(255, 255, 255));
        $backend->scale(20);
        $backend->translate(4, 4);
        $backend->drawPathWithColor($eye->getExternalPath(), new Rgb(220, 20, 60));
        $backend->drawPathWithColor($eye->getInternalPath(), new Rgb(20, 70, 220));

        return $this->decodePng($backend->done());
    }

    private function rectanglePath(float $x, float $y, float $width, float $height): Path
    {
        return (new Path())
            ->move($x, $y)
            ->line($x + $width, $y)
            ->line($x + $width, $y + $height)
            ->line($x, $y + $height)
            ->close();
    }

    private function decodePng(string $png): GdImage
    {
        self::assertSame(self::PNG_MAGIC, substr($png, 0, 8));
        $image = imagecreatefromstring($png);
        self::assertInstanceOf(GdImage::class, $image);

        return $image;
    }

    private function releaseDecodedImage(GdImage $image): void
    {
        if (PHP_VERSION_ID < 80500) {
            imagedestroy($image);
        }
    }

    /**
     * @param array{0: int, 1: int, 2: int} $expected
     */
    private function assertPixelNear(
        GdImage $image,
        int $x,
        int $y,
        array $expected,
        int $tolerance = 3,
    ): void {
        $pixel = imagecolorsforindex($image, imagecolorat($image, $x, $y));
        foreach (['red', 'green', 'blue'] as $index => $channel) {
            self::assertEqualsWithDelta(
                $expected[$index],
                $pixel[$channel],
                $tolerance,
                sprintf('unexpected %s channel at (%d,%d)', $channel, $x, $y),
            );
        }
    }

    private function assertBackendIsReset(GdImageBackEnd $backend): void
    {
        $property = new \ReflectionProperty(GdImageBackEnd::class, 'image');
        self::assertNull($property->getValue($backend));
    }
}

/**
 * @since 5.38.0
 */
final class InstrumentedGdImageBackEnd extends GdImageBackEnd
{
    public int $createdImages = 0;
    public int $destroyedImages = 0;
    public bool $failEncoding = false;

    protected function createImage(int $width, int $height): GdImage
    {
        $this->createdImages++;

        return parent::createImage($width, $height);
    }

    protected function encodePng(GdImage $image): bool
    {
        if ($this->failEncoding) {
            throw new \RuntimeException('Forced PNG encoding failure.');
        }

        return parent::encodePng($image);
    }

    protected function destroyImage(GdImage $image): void
    {
        $this->destroyedImages++;
        parent::destroyImage($image);
    }
}
