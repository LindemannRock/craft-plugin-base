# QR Code Renderer Helper

`QrCodeRendererHelper` creates a Bacon QR Code PNG renderer that follows Craft's effective image-driver configuration. Use it when a plugin needs PNG bytes while preserving Bacon's solid-fill module and eye geometry across Imagick and GD environments.

## Create a PNG Renderer

Build the Bacon `RendererStyle` you need, pass it to the helper, and use the returned renderer with Bacon's `Writer`:

```php
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use lindemannrock\base\helpers\QrCodeRendererHelper;

$style = new RendererStyle(
    320,
    4,
    new DotsModule(DotsModule::LARGE),
    PointyEye::instance(),
    Fill::uniformColor(
        new Rgb(255, 255, 255),
        new Rgb(20, 32, 48),
    ),
);

$renderer = QrCodeRendererHelper::createPngRenderer($style);
$png = (new Writer($renderer))->writeString('https://example.com');
```

`$png` contains PNG bytes. You can return, upload, or store those bytes using the consumer plugin's normal workflow; rendering itself does not require a persistent runtime file.

## Driver Selection

The helper asks Craft's Images service which driver is effective:

1. When Imagick is effective, it uses Bacon's `ImagickImageBackEnd` in PNG mode.
2. When GD is effective, it uses Base's `GdImageBackEnd`.
3. When neither driver is effective, it throws a Bacon renderer `RuntimeException` instead of returning another format.

Selection is capability-based. It does not inspect hosting product names, installed packages, environment names, or filesystem behavior. It also does not use Asset transforms, substitute SVG output, or label SVG bytes as PNG.

## GD Solid-Style Support

The GD backend renders Bacon's full path geometry for solid fills, including:

- square, rounded, and dot modules;
- square, rounded, and pointed eyes;
- compound paths and eye-ring holes;
- independent background, foreground, and eye colors, including Bacon alpha colors; and
- Bacon scale, translation, rotation, and nested transform stacks.

It uses bounded supersampling and high-quality downsampling to keep curves and dots smooth. Identical inputs produce deterministic PNG bytes.

## Gradient Limitation

The GD backend does not support Bacon gradient fills. If a `RendererStyle` supplies a foreground gradient while GD is effective, rendering throws an explicit Bacon `InvalidArgumentException`. Configure a solid `Fill` when the renderer must work with GD.

Imagick remains Bacon's native backend when Craft selects Imagick, so its capabilities are unchanged.
