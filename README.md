# LindemannRock Plugin Base

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-plugin-base.svg)](https://packagist.org/packages/lindemannrock/craft-plugin-base)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-plugin-base.svg)](LICENSE.md)

Common utilities and building blocks for LindemannRock Craft CMS plugins.

## Features

- **Edition Support** — standardized Lite/Standard/Pro edition tiers for the Plugin Store
- **Settings Traits** — database persistence, config file overrides, custom display names
- **DateFormatHelper** — centralized date/time formatting with timezone-aware SQL expressions
- **DateRangeHelper** — standardized date range selection, bounds, and query filtering
- **ColorHelper** — 18-color palette and 15 built-in color sets for consistent styling
- **ExportHelper** — CSV, JSON, and Excel export with configurable format availability
- **GeoHelper** — ISO 3166-1 country lookups, dial codes, and phone validation
- **DbHelper** — DB-agnostic JSON extraction and GROUP_CONCAT
- **CsvImportHelper** — CSV upload parsing with configurable options
- **CpNavHelper** — CP subnav building with permission checks
- **Device Detection** — standardized UA parsing via Matomo DeviceDetector
- **GeoLookup** — IP geolocation via configurable providers (ip-api.com, ipapi.co, ipinfo.io)
- **CP Table Layout** — reusable table pages with filters, search, pagination, and AJAX refresh
- **CP Analytics Layout** — analytics dashboards with tabs, charts, stat boxes, and date filters
- **Twig Extensions** — 30+ filters and functions for dates, colors, exports, geo, and plugin detection
- **15 Twig Components** — badge, stat-box, info-box, export-menu, row-actions, filters, and more

## Requirements

- PHP 8.2+
- Craft CMS 5.0+

## Installation

```bash
composer require lindemannrock/craft-plugin-base
```

## Documentation

Full documentation is available in the [docs](docs/) folder.

## Support

- **Issues**: [GitHub Issues](https://github.com/LindemannRock/craft-plugin-base/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
