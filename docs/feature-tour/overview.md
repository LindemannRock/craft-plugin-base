# Feature Overview

LindemannRock Base is a shared toolkit that provides common utilities for all LindemannRock Craft CMS plugins. It eliminates code duplication and ensures a consistent experience across the plugin suite.

## What's Included

### PHP Helpers

Utility classes for common operations:

- **[ColorHelper](color-helper.md)** — 18 palette colors, 15 built-in color sets, and custom color set registration for badges and filters
- **[DateFormatHelper](date-format-helper.md)** — Configurable date/time formatting for display, exports, and DB-agnostic timezone-aware SQL
- **[DateRangeHelper](date-range-helper.md)** — Standard date ranges (today, last 7 days, etc.) with bounds calculation and query integration
- **[ScheduleHelper](schedule-helper.md)** — Cron-style scheduling for recurring queue jobs (daily, daily2am, weekly, monthly, etc.) with TZ-aware next-run calculation, dropdown options, and validation allowlist
- **[ExportHelper](export-helper.md)** — CSV, JSON, and Excel exports with configurable format availability and date formatting
- **[GeoHelper](geo-helper.md)** — Country names, dial codes, phone validation, and country select options
- **[PluginHelper](plugin-helper.md)** — Plugin bootstrap, cache paths/keys, Redis cache safeguard, and cross-plugin detection
- **Shared install experience** — One-time CP install modal with version metadata, branding, and preset-driven confetti
- **[BooleanHelper](boolean-helper.md)** — Normalize boolean-like config, env, POST, and HTML attribute values
- **[DbHelper](db-helper.md)** — DB-agnostic JSON extraction and GROUP_CONCAT
- **[CsvImportHelper](csv-import-helper.md)** — CSV file upload parsing with automatic delimiter detection
- **[CpNavHelper](cp-nav-helper.md)** — CP navigation building for plugin sub-pages
- **[LabelHelper](label-helper.md)** — Strip numbering and truncate long field labels for compact UI contexts

### PHP Traits

Reusable traits for Settings models and plugin classes:

- **[SettingsPersistenceTrait](settings-persistence.md)** — Save/load settings to database tables instead of project config
- **[SettingsConfigTrait](settings-config.md)** — Config file overrides with lock icon indicators
- **[SettingsDisplayNameTrait](settings-display-name.md)** — Custom plugin display names
- **[Base settings traits](base-settings-traits.md)** — 7 traits + matching CP partials that centralize the per-plugin Settings boilerplate for `pluginName`, `logLevel`, `itemsPerPage`, date format overrides, date range, export-format toggles, and geo provider + API key
- **[EditionTrait](edition-support.md)** — Plugin editions (Lite/Standard/Pro)
- **[DeviceDetectionTrait](device-detection.md)** — User-agent parsing for device, browser, and OS detection
- **[GeoLookupTrait](geo-lookup.md)** — IP geolocation in service classes
- **[QueueTtrTrait](queue-ttr.md)** — Shared queue TTR (`getTtr()`) with per-job override support

### Twig Extensions

Filters and functions available in all CP templates after bootstrap:

- **Date/time filters** — `|lrDatetime`, `|lrCompactDatetime`, `|lrDate`, `|lrTime`, `|lrShortDate`, `|lrRelative`, `|lrToDateTimeString`, `|lrToApiString`, `|lrToFilenameString`
- **Date/time functions** — `lrNow()`, `lrIsToday()`, `lrIsPast()`, `lrIsFuture()`, `lrDefaultDateRange()`, `lrDateRangeOptions()`
- **Color functions** — `lrPaletteColor()`, `lrPaletteColorNames()`, `lrColorSet()`, `lrSetColor()`, `lrHasColorSet()`, `lrAvailableColorSets()`, `lrNeutralColor()`, `lrDefaultColor()`, `lrFilterColor()`
- **Export functions** — `lrExportEnabled()`, `lrExportFormats()`, `lrExportFormatOptions()`
- **Geo functions** — `lrCountries()`, `lrCountryName()`, `lrCountryDialCodeData()`, `lrDialCode()`, `lrCountryWithDialCode()`, `lrValidCountryCode()`
- **Plugin functions** — `lrPluginInstalled()`, `lrPluginEnabled()`, `lrPluginName()`
- **Label filters** — `|lrShortLabel`

See [Twig Filters & Functions](../template-guides/twig-filters-functions.md) for the complete reference.

### Testing Utilities

Shared scaffolding for PHPUnit integration tests against a live Craft install:

- **[Testing](testing.md)** — `IntegrationTestCase` abstract base (component swap/restore, generic DB helpers, marker cleanup, queue drain, `cleanupExternalState()` hook), `bootstrap()` function (Craft console init with optional explicit project root), and `phpunit.xml.dist.template` for copy-once suite setup

See the cross-plugin maintainer guide in [`plugins/_docs/guides/testing.md`](../../../_docs/guides/testing.md) for the workflow recipe.

### CP Layouts & Components

Reusable Twig templates for building consistent CP pages:

- **[CP Table Layout](../template-guides/cp-table-layout.md)** — Full-featured table pages with filters, search, pagination, bulk actions, and AJAX refresh
- **[CP Analytics Layout](../template-guides/cp-analytics-layout.md)** — Analytics dashboards with tabs, charts, stat boxes, and date filters
- **[CP Utilities Layout](../template-guides/cp-utilities-layout.md)** — Utility pages with action sections and AJAX buttons
- **[CP Table Utility Layout](../template-guides/cp-table-utility-layout.md)** — Table variant for utility pages
- **[Components](../template-guides/components.md)** — Badge, info-box, export-menu, row-actions, stat-box, filter-status, filter-dropdown, phone-input
- **[Partials](../template-guides/partials.md)** — Analytics panel, CSV import, geo settings, backup list

## Next Steps

- [Installation & Setup](../get-started/installation.md) — how the base module is installed and registered
- [Configuration](../get-started/configuration.md) — date/time formatting, export, and default date range settings
- [Bootstrapping](../developers/bootstrapping.md) — integrating the base module in your plugin
