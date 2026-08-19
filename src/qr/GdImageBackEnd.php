<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\qr;

use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\TransformationMatrix;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use GdImage;
use Throwable;

/**
 * Bacon QR Code path backend that renders solid-fill PNG images with GD.
 *
 * @since 5.38.0
 */
class GdImageBackEnd implements ImageBackEndInterface
{
    private const MAX_SUPERSAMPLE_FACTOR = 4;
    private const MAX_SUPERSAMPLED_DIMENSION = 4096;
    private const CURVE_SEGMENT_PIXELS = 1.5;
    private const MAX_CURVE_SEGMENTS = 96;

    private ?GdImage $image = null;
    private int $outputSize = 0;
    private int $supersampleFactor = 1;

    /**
     * @var list<TransformationMatrix>
     */
    private array $matrices = [];

    private int $matrixIndex = 0;

    public function __construct()
    {
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            throw new RuntimeException('The GD extension is required to use GdImageBackEnd.');
        }
    }

    public function __destruct()
    {
        $this->releaseActiveImage();
    }

    public function new(int $size, ColorInterface $backgroundColor): void
    {
        $this->releaseActiveImage();

        if ($size < 1) {
            throw new InvalidArgumentException('Image size must be greater than zero.');
        }

        $factor = max(
            1,
            min(self::MAX_SUPERSAMPLE_FACTOR, intdiv(self::MAX_SUPERSAMPLED_DIMENSION, $size)),
        );
        $canvasSize = $size * $factor;
        $image = null;

        try {
            $image = $this->createImage($canvasSize, $canvasSize);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $background = $this->allocateColor($image, $backgroundColor);
            if (!imagefilledrectangle($image, 0, 0, $canvasSize - 1, $canvasSize - 1, $background)) {
                throw new RuntimeException('Failed to fill the GD image background.');
            }

            imagealphablending($image, true);
            $this->image = $image;
            $this->outputSize = $size;
            $this->supersampleFactor = $factor;
            $this->matrices = [TransformationMatrix::scale((float)$factor)];
            $this->matrixIndex = 0;
        } catch (Throwable $exception) {
            if ($image instanceof GdImage) {
                $this->destroyImage($image);
            }
            $this->clearState();

            if ($exception instanceof RuntimeException || $exception instanceof InvalidArgumentException) {
                throw $exception;
            }

            throw new RuntimeException('Failed to start the GD image.', 0, $exception);
        }
    }

    public function scale(float $size): void
    {
        $this->assertImageStarted();
        $this->matrices[$this->matrixIndex] = $this->matrices[$this->matrixIndex]
            ->multiply(TransformationMatrix::scale($size));
    }

    public function translate(float $x, float $y): void
    {
        $this->assertImageStarted();
        $this->matrices[$this->matrixIndex] = $this->matrices[$this->matrixIndex]
            ->multiply(TransformationMatrix::translate($x, $y));
    }

    public function rotate(int $degrees): void
    {
        $this->assertImageStarted();
        $this->matrices[$this->matrixIndex] = $this->matrices[$this->matrixIndex]
            ->multiply(TransformationMatrix::rotate($degrees));
    }

    public function push(): void
    {
        $this->assertImageStarted();
        $this->matrices[] = $this->matrices[$this->matrixIndex];
        $this->matrixIndex++;
    }

    public function pop(): void
    {
        $this->assertImageStarted();
        if ($this->matrixIndex === 0) {
            $this->releaseActiveImage();

            throw new RuntimeException('The transformation stack is empty.');
        }

        array_pop($this->matrices);
        $this->matrixIndex--;
    }

    public function drawPathWithColor(Path $path, ColorInterface $color): void
    {
        $image = $this->assertImageStarted();
        $width = imagesx($image);
        $height = imagesy($image);
        $overlay = null;

        try {
            $contours = $this->flattenPath($path);
            if ($contours === []) {
                return;
            }

            $overlay = $this->createImage($width, $height);
            imagealphablending($overlay, false);
            imagesavealpha($overlay, true);

            $transparent = imagecolorallocatealpha($overlay, 0, 0, 0, 127);
            if ($transparent === false) {
                throw new RuntimeException('Failed to allocate a transparent GD color.');
            }
            if (!imagefilledrectangle($overlay, 0, 0, $width - 1, $height - 1, $transparent)) {
                throw new RuntimeException('Failed to initialize the GD path layer.');
            }

            $fill = $this->allocateColor($overlay, $color);
            $rankedContours = [];
            foreach ($contours as $index => $contour) {
                $rankedContours[] = [
                    'contour' => $contour,
                    'depth' => $this->contourDepth($contour, $contours, $index),
                ];
            }

            usort(
                $rankedContours,
                static fn(array $left, array $right): int => $left['depth'] <=> $right['depth'],
            );

            foreach ($rankedContours as $rankedContour) {
                $points = $this->gdPolygonPoints($rankedContour['contour']);
                if (count($points) < 6) {
                    continue;
                }

                $polygonColor = $rankedContour['depth'] % 2 === 0 ? $fill : $transparent;
                if (!imagefilledpolygon($overlay, $points, $polygonColor)) {
                    throw new RuntimeException('Failed to draw a GD path contour.');
                }
            }

            imagealphablending($image, true);
            if (!imagecopy($image, $overlay, 0, 0, 0, 0, $width, $height)) {
                throw new RuntimeException('Failed to composite the GD path layer.');
            }
        } catch (Throwable $exception) {
            $this->releaseActiveImage();

            if ($exception instanceof RuntimeException || $exception instanceof InvalidArgumentException) {
                throw $exception;
            }

            throw new RuntimeException('Failed to draw the GD path.', 0, $exception);
        } finally {
            if ($overlay instanceof GdImage) {
                $this->destroyImage($overlay);
            }
        }
    }

    public function drawPathWithGradient(
        Path $path,
        Gradient $gradient,
        float $x,
        float $y,
        float $width,
        float $height,
    ): void {
        $this->assertImageStarted();
        $this->releaseActiveImage();

        throw new InvalidArgumentException(
            'GdImageBackEnd does not support gradients; configure a solid Bacon QR fill.',
        );
    }

    public function done(): string
    {
        $source = $this->assertImageStarted();
        $output = $source;
        $bufferLevel = null;

        try {
            if ($this->supersampleFactor > 1) {
                $output = $this->createImage($this->outputSize, $this->outputSize);
                imagealphablending($output, false);
                imagesavealpha($output, true);

                $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
                if ($transparent === false) {
                    throw new RuntimeException('Failed to allocate a transparent GD color.');
                }
                if (!imagefilledrectangle(
                    $output,
                    0,
                    0,
                    $this->outputSize - 1,
                    $this->outputSize - 1,
                    $transparent,
                )) {
                    throw new RuntimeException('Failed to initialize the GD output image.');
                }
                if (!imagecopyresampled(
                    $output,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $this->outputSize,
                    $this->outputSize,
                    imagesx($source),
                    imagesy($source),
                )) {
                    throw new RuntimeException('Failed to downsample the GD QR image.');
                }
            }

            $bufferLevel = ob_get_level();
            if (!ob_start()) {
                throw new RuntimeException('Failed to start PNG output buffering.');
            }

            if (!$this->encodePng($output)) {
                throw new RuntimeException('Failed to encode the GD QR image as PNG.');
            }

            $blob = ob_get_clean();
            if (!is_string($blob)) {
                throw new RuntimeException('Failed to read the encoded GD PNG output.');
            }

            return $blob;
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException || $exception instanceof InvalidArgumentException) {
                throw $exception;
            }

            throw new RuntimeException('Failed to finish the GD PNG image.', 0, $exception);
        } finally {
            if ($bufferLevel !== null) {
                while (ob_get_level() > $bufferLevel) {
                    ob_end_clean();
                }
            }

            if ($output !== $source) {
                $this->destroyImage($output);
            }
            $this->destroyImage($source);
            $this->clearState();
        }
    }

    protected function createImage(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        if (!$image instanceof GdImage) {
            throw new RuntimeException(sprintf('Failed to create a %dx%d GD image.', $width, $height));
        }

        return $image;
    }

    protected function encodePng(GdImage $image): bool
    {
        return imagepng($image, null, 9, PNG_ALL_FILTERS);
    }

    protected function destroyImage(GdImage $image): void
    {
        if (PHP_VERSION_ID < 80500) {
            imagedestroy($image);
        }
    }

    private function assertImageStarted(): GdImage
    {
        if (!$this->image instanceof GdImage) {
            throw new RuntimeException('No image has been started.');
        }

        return $this->image;
    }

    /**
     * @return list<list<array{x: float, y: float}>>
     */
    private function flattenPath(Path $path): array
    {
        $contours = [];
        $contour = [];
        $logicalX = 0.0;
        $logicalY = 0.0;
        $startX = 0.0;
        $startY = 0.0;

        foreach ($path as $operation) {
            if ($operation instanceof Move) {
                $this->appendContour($contours, $contour);
                $logicalX = $operation->getX();
                $logicalY = $operation->getY();
                $startX = $logicalX;
                $startY = $logicalY;
                $contour = [$this->transformPoint($logicalX, $logicalY)];
                continue;
            }

            if ($operation instanceof Line) {
                $logicalX = $operation->getX();
                $logicalY = $operation->getY();
                $contour[] = $this->transformPoint($logicalX, $logicalY);
                continue;
            }

            if ($operation instanceof Curve) {
                $this->appendCurvePoints($contour, $logicalX, $logicalY, $operation);
                $logicalX = $operation->getX3();
                $logicalY = $operation->getY3();
                continue;
            }

            if ($operation instanceof EllipticArc) {
                foreach ($operation->toCurves($logicalX, $logicalY) as $arcOperation) {
                    if ($arcOperation instanceof Line) {
                        $logicalX = $arcOperation->getX();
                        $logicalY = $arcOperation->getY();
                        $contour[] = $this->transformPoint($logicalX, $logicalY);
                        continue;
                    }

                    $this->appendCurvePoints($contour, $logicalX, $logicalY, $arcOperation);
                    $logicalX = $arcOperation->getX3();
                    $logicalY = $arcOperation->getY3();
                }
                continue;
            }

            if ($operation instanceof Close) {
                $logicalX = $startX;
                $logicalY = $startY;
                $this->appendContour($contours, $contour);
                continue;
            }

            throw new RuntimeException('Unexpected draw operation: ' . get_class($operation));
        }

        $this->appendContour($contours, $contour);

        return $contours;
    }

    /**
     * @param list<array{x: float, y: float}> $contour
     */
    private function appendCurvePoints(array &$contour, float $fromX, float $fromY, Curve $curve): void
    {
        $steps = $this->curveSteps($fromX, $fromY, $curve);
        for ($step = 1; $step <= $steps; $step++) {
            $t = $step / $steps;
            $inverse = 1 - $t;
            $x = ($inverse ** 3 * $fromX)
                + (3 * $inverse ** 2 * $t * $curve->getX1())
                + (3 * $inverse * $t ** 2 * $curve->getX2())
                + ($t ** 3 * $curve->getX3());
            $y = ($inverse ** 3 * $fromY)
                + (3 * $inverse ** 2 * $t * $curve->getY1())
                + (3 * $inverse * $t ** 2 * $curve->getY2())
                + ($t ** 3 * $curve->getY3());
            $contour[] = $this->transformPoint($x, $y);
        }
    }

    private function curveSteps(float $fromX, float $fromY, Curve $curve): int
    {
        $points = [
            $this->transformPoint($fromX, $fromY),
            $this->transformPoint($curve->getX1(), $curve->getY1()),
            $this->transformPoint($curve->getX2(), $curve->getY2()),
            $this->transformPoint($curve->getX3(), $curve->getY3()),
        ];
        $length = 0.0;
        for ($index = 1; $index < count($points); $index++) {
            $length += hypot(
                $points[$index]['x'] - $points[$index - 1]['x'],
                $points[$index]['y'] - $points[$index - 1]['y'],
            );
        }

        return max(4, min(self::MAX_CURVE_SEGMENTS, (int)ceil($length / self::CURVE_SEGMENT_PIXELS)));
    }

    /**
     * @return array{x: float, y: float}
     */
    private function transformPoint(float $x, float $y): array
    {
        [$transformedX, $transformedY] = $this->matrices[$this->matrixIndex]->apply($x, $y);

        return ['x' => $transformedX, 'y' => $transformedY];
    }

    /**
     * @param list<list<array{x: float, y: float}>> $contours
     * @param list<array{x: float, y: float}> $contour
     */
    private function appendContour(array &$contours, array &$contour): void
    {
        if (count($contour) >= 3) {
            $contours[] = $contour;
        }
        $contour = [];
    }

    /**
     * @param list<array{x: float, y: float}> $contour
     * @param list<list<array{x: float, y: float}>> $contours
     */
    private function contourDepth(array $contour, array $contours, int $contourIndex): int
    {
        $probe = $this->interiorPoint($contour);
        $depth = 0;

        foreach ($contours as $index => $candidate) {
            if ($index === $contourIndex) {
                continue;
            }

            if ($this->pointInPolygon($probe, $candidate)) {
                $depth++;
            }
        }

        return $depth;
    }

    /**
     * Find a point strictly inside a simple contour using horizontal scanlines.
     *
     * @param list<array{x: float, y: float}> $contour
     * @return array{x: float, y: float}
     */
    private function interiorPoint(array $contour): array
    {
        $yValues = array_column($contour, 'y');
        $minimumY = min($yValues);
        $height = max($yValues) - $minimumY;

        foreach ([0.5, 0.371, 0.629, 0.233, 0.767] as $fraction) {
            $y = $minimumY + ($height * $fraction);
            $intersections = [];
            $previous = count($contour) - 1;

            foreach ($contour as $index => $point) {
                $previousPoint = $contour[$previous];
                if (($point['y'] > $y) !== ($previousPoint['y'] > $y)) {
                    $intersections[] = $point['x']
                        + (($y - $point['y']) * ($previousPoint['x'] - $point['x'])
                            / ($previousPoint['y'] - $point['y']));
                }
                $previous = $index;
            }

            sort($intersections, SORT_NUMERIC);
            if (count($intersections) >= 2) {
                $span = $intersections[1] - $intersections[0];

                return [
                    'x' => $intersections[0] + min(0.25, $span / 2),
                    'y' => $y,
                ];
            }
        }

        return $contour[0];
    }

    /**
     * @param array{x: float, y: float} $point
     * @param list<array{x: float, y: float}> $polygon
     */
    private function pointInPolygon(array $point, array $polygon): bool
    {
        $inside = false;
        $previous = count($polygon) - 1;

        foreach ($polygon as $index => $currentPoint) {
            $previousPoint = $polygon[$previous];
            $crosses = ($currentPoint['y'] > $point['y']) !== ($previousPoint['y'] > $point['y']);
            if ($crosses) {
                $intersectionX = ($previousPoint['x'] - $currentPoint['x'])
                    * ($point['y'] - $currentPoint['y'])
                    / ($previousPoint['y'] - $currentPoint['y'])
                    + $currentPoint['x'];
                if ($point['x'] < $intersectionX) {
                    $inside = !$inside;
                }
            }

            $previous = $index;
        }

        return $inside;
    }

    /**
     * @param list<array{x: float, y: float}> $contour
     * @return list<int>
     */
    private function gdPolygonPoints(array $contour): array
    {
        $points = [];
        $lastX = null;
        $lastY = null;

        foreach ($contour as $point) {
            $x = (int)round($point['x']);
            $y = (int)round($point['y']);
            if ($x === $lastX && $y === $lastY) {
                continue;
            }

            $points[] = $x;
            $points[] = $y;
            $lastX = $x;
            $lastY = $y;
        }

        if (count($points) >= 4
            && $points[0] === $points[count($points) - 2]
            && $points[1] === $points[count($points) - 1]
        ) {
            array_pop($points);
            array_pop($points);
        }

        return $points;
    }

    private function allocateColor(GdImage $image, ColorInterface $color): int
    {
        $opacity = 100;
        if ($color instanceof Alpha) {
            $opacity = $color->getAlpha();
            $color = $color->getBaseColor();
        }

        $rgb = $color->toRgb();
        $allocated = imagecolorallocatealpha(
            $image,
            $rgb->getRed(),
            $rgb->getGreen(),
            $rgb->getBlue(),
            (int)round((100 - $opacity) / 100 * 127),
        );
        if ($allocated === false) {
            throw new RuntimeException('Failed to allocate a GD color.');
        }

        return $allocated;
    }

    private function releaseActiveImage(): void
    {
        if ($this->image instanceof GdImage) {
            $this->destroyImage($this->image);
        }

        $this->clearState();
    }

    private function clearState(): void
    {
        $this->image = null;
        $this->outputSize = 0;
        $this->supersampleFactor = 1;
        $this->matrices = [];
        $this->matrixIndex = 0;
    }
}
