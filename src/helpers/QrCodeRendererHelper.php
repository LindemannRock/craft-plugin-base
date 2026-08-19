<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Craft;
use lindemannrock\base\qr\GdImageBackEnd;

/**
 * Creates Bacon QR Code PNG renderers for Craft's effective image driver.
 *
 * @since 5.38.0
 */
final class QrCodeRendererHelper
{
    /**
     * Create a PNG renderer using Craft's effective Imagick or GD driver.
     */
    public static function createPngRenderer(RendererStyle $style): ImageRenderer
    {
        $images = Craft::$app->getImages();

        if ($images->getIsImagick()) {
            return new ImageRenderer($style, new ImagickImageBackEnd('png'));
        }

        if ($images->getIsGd()) {
            return new ImageRenderer($style, new GdImageBackEnd());
        }

        throw new RuntimeException(
            'PNG QR rendering requires Craft\'s effective image driver to be Imagick or GD.',
        );
    }
}
