# Requirements

## System Requirements

| Requirement | Version |
|-------------|---------|
| [Craft CMS](https://craftcms.com/) | 5.10+ |
| [PHP](https://php.net/) | 8.2+ |

## Module Type

LindemannRock Base is a **Yii2 module**, not a standalone Craft plugin. It does not appear in the Craft Plugin Store or the CP plugin list. Instead, it is required as a Composer dependency by other LindemannRock plugins and registered automatically during bootstrap.

You do not need to install or enable it manually — any LindemannRock plugin that depends on it will include it in its own `composer.json` requirements.

## Dependencies

The following packages are installed automatically via Composer:

| Package | Version | Purpose |
|---------|---------|---------|
| [matomo/device-detector](https://github.com/matomo-org/device-detector) | 6.5+ | User-agent parsing for device detection (DeviceDetectionTrait) |
| [phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) | 3.0+ or 5.0+ | Excel (.xlsx) export support (ExportHelper) |
| [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) | 2.0+ | Yii Redis connection support (YiiRedisConnectionHelper) |
