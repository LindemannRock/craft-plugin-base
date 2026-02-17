# Requirements

## Craft CMS

LindemannRock Base requires Craft CMS 5.0 or greater.

## PHP

LindemannRock Base requires PHP 8.2 or greater.

## Module Type

LindemannRock Base is a **Yii2 module**, not a standalone Craft plugin. It does not appear in the Craft Plugin Store or the CP plugin list. Instead, it is required as a Composer dependency by other LindemannRock plugins and registered automatically during bootstrap.

You do not need to install or enable it manually — any LindemannRock plugin that depends on it will include it in its own `composer.json` requirements.

## Dependencies

The following packages are installed automatically via Composer:

| Package | Purpose |
|---------|---------|
| `matomo/device-detector` ^6.4 | User-agent parsing for device detection (DeviceDetectionTrait) |
| `phpoffice/phpspreadsheet` ^3.0 or ^5.0 | Excel (.xlsx) export support (ExportHelper) |
