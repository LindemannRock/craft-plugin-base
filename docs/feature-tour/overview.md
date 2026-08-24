# Feature Overview

LindemannRock Base is a shared toolkit that provides common utilities for all LindemannRock Craft CMS plugins. It eliminates code duplication and ensures a consistent experience across the plugin suite.

## What's Included

### PHP Helpers

Utility classes for common operations:

- **[ColorHelper](color-helper.md)** — 18 palette colors, 16 built-in color sets, and custom color set registration for badges and filters
- **[PluginThemeStyleHelper](plugin-theme-style-helper.md)** — icon-derived CSS variables for branded setup heroes and docs shell surfaces
- **[QrCodeRendererHelper](qr-code-renderer-helper.md)** — Craft-driver-aware Bacon QR Code PNG rendering with Imagick and a full solid-style GD backend
- **[DateFormatHelper](date-format-helper.md)** — Configurable date/time formatting for display, exports, and DB-agnostic timezone-aware SQL
- **[DateRangeHelper](date-range-helper.md)** — Standard date ranges (today, last 7 days, etc.) with bounds calculation and query integration
- **[ScheduleHelper](schedule-helper.md)** — Cron-style scheduling for recurring queue jobs (daily, daily2am, weekly, monthly, etc.) with TZ-aware next-run calculation, dropdown options, and validation allowlist
- **[RecurringQueueHelper](recurring-queue-helper.md)** — Deployment-safe ownership for recurring queue rows, with mutex-protected bootstrap dedup and duplicate collapse
- **[PortableQueueScheduler](portable-queue-scheduler.md)** — Absolute-target delayed scheduling with bounded SQS handoffs and native database-queue delays
- **[ExportHelper](export-helper.md)** — CSV, JSON, and Excel exports with configurable format availability and date formatting
- **[GeoHelper](geo-helper.md)** — Country names, dial codes, phone validation, and country select options
- **[PluginHelper](plugin-helper.md)** — Plugin bootstrap, cache paths/keys, Redis cache safeguard, and cross-plugin detection
- **[CacheHelper](cache-helper.md)** — Bounded Redis tracked-set cleanup plus streaming local cache file count/delete helpers
- **[Disposable cache storage](disposable-cache-storage.md)** — Resolve file/application-cache choices for durable and ephemeral hosts, with shared semantic settings presentation
- **[ScopedCache](scoped-cache.md)** — Backend-neutral finite-TTL storage with plugin/family/scope isolation and generation-based invalidation
- **[Install experience](install-experience.md)** — One-time CP welcome modal shown after install, with version metadata, branding, and preset-driven confetti
- **[AnalyticsIpHelper](analytics-ip-helper.md)** — One-step IP anonymization, hashing, and geo-lookup eligibility for analytics tracking
- **[JsonHelper](../developers/api-reference.md)** — `htmlSafeJson()` for safely embedding JSON in inline HTML/JS
- **[ExperimentalFeatureHelper](experimental-feature-helper.md)** — Env-flag gate for internal or launch-deferred features that must stay hidden until explicitly enabled
- **[BooleanHelper](boolean-helper.md)** — Normalize boolean-like config, env, style, and valueless HTML attribute values
- **[DbHelper](db-helper.md)** — DB-agnostic JSON extraction and GROUP_CONCAT
- **[GqlHelper](gql-helper.md)** — GraphQL schema permission checks plus shared `site` / `siteId` argument resolution
- **[YiiRedisConnectionHelper](yii-redis-connection-helper.md)** — Independently owned, non-persistent Yii Redis connections from Craft-compatible configuration
- **[Redis Database Diagnostics](../developers/console-commands.md#redis-database-diagnostics-since5370)** — Bounded, read-only point-in-time key counts for Craft's configured Redis-cache endpoint
- **[ConfigFileHelper](config-file-helper.md)** — Read handle-keyed sections from plugin config files and merge config-defined records with database-defined records
- **[CsvImportHelper](csv-import-helper.md)** — CSV file upload parsing with automatic delimiter detection
- **[CpNavHelper](cp-nav-helper.md)** — CP navigation building for plugin sub-pages
- **[Console Help](console-help.md)** — Plugin-level CLI help catalogs and focused command help
- **[LabelHelper](label-helper.md)** — Strip numbering and truncate long field labels for compact UI contexts
- **[SlugHandleHelper](slug-handle-helper.md)** — Normalize persisted slugs/handles and resolve database collisions with a shared suffix convention
- **[SafeSegmentHelper](safe-segment-helper.md)** — Normalize safe non-DB string fragments for filenames, cache/storage keys, and local config tokens
- **[AssetVolumeHelper](asset-volume-helper.md)** — Server-side validation that a submitted asset ID belongs to an allowed volume and the user holds the matching view permission
- **[StoragePathHelper + StoragePathValidator](storage-path-validator.md)** — Resolve and validate plugin storage paths, aliases, env vars, and webroot guards
- **[StorageVolumeHelper + StorageVolumeValidator](storage-path-validator.md#storage-volume-validator-since5260)** — Validate asset volumes used as plugin storage and block local volumes inside `@webroot`
- **[UrlSafetyHelper](url-safety-helper.md)** — Constrain a URL to a safe redirect target, and flag executable schemes (`javascript:`/`data:`/…) without blocking app deep links
- **[ContentSafetyHelper](content-safety-helper.md)** — Detect dangerous HTML/script markup (`<script>`, `on*=`, …) in free text before it's stored and rendered
- **[Settings validators](settings-validators.md)** — Yii validators for route prefixes, URL-or-path values, and template paths in plugin settings

### PHP Traits

Reusable traits for Settings models and plugin classes:

- **[SettingsPersistenceTrait](settings-persistence.md)** — Save/load settings to database tables instead of project config
- **[SettingsConfigTrait](settings-config.md)** — Config file overrides with lock icon indicators
- **[SettingsDisplayNameTrait](settings-display-name.md)** — Custom plugin display names
- **[Base settings traits](base-settings-traits.md)** — 7 traits + matching CP partials that centralize the per-plugin Settings boilerplate for `pluginName`, `logLevel`, `itemsPerPage`, date format overrides, date range, export-format toggles, and geo provider + API key
- **[EditionTrait](edition-support.md)** — Plugin editions (Standard/Pro)
- **[DeviceDetectionTrait](device-detection.md)** — User-agent parsing for device, browser, and OS detection with portable file/application caching
- **[GeoLookupTrait](geo-lookup.md)** — IP geolocation in service classes
- **[QueueTtrTrait](queue-ttr.md)** — Shared queue TTR (`getTtr()`) with per-job override support

### Twig Extensions

Filters and functions available in all CP templates after bootstrap:

- **Date/time filters** — `|lrDatetime`, `|lrCompactDatetime`, `|lrDate`, `|lrTime`, `|lrRelative`, `|lrToDateTimeString`, `|lrToApiString`, `|lrToFilenameString`
- **Date/time functions** — `lrNow()`, `lrIsToday()`, `lrIsPast()`, `lrIsFuture()`, `lrDateFormatConfig()`, `lrDefaultDateRange()`, `lrDateRangeOptions()`
- **Color functions** — `lrPaletteColor()`, `lrPaletteColorNames()`, `lrColorSet()`, `lrSetColor()`, `lrHasColorSet()`, `lrAvailableColorSets()`, `lrNeutralColor()`, `lrDefaultColor()`, `lrFilterColor()`
- **Export functions** — `lrExportEnabled()`, `lrExportFormats()`, `lrExportFormatOptions()`
- **Geo functions** — `lrCountries()`, `lrCountryName()`, `lrCountryDialCodeData()`, `lrDialCode()`, `lrCountryWithDialCode()`, `lrValidCountryCode()`
- **Plugin functions** — `lrPluginInstalled()`, `lrPluginEnabled()`, `lrPluginName()`
- **Plugin theme style functions** — `lrPluginHeroCssVars()`, `lrPluginDocsShellCssVars()`, `lrPluginDocsCssVars()`
- **Label filters** — `|lrShortLabel`

See [Twig Filters & Functions](../template-guides/twig-filters-functions.md) for the complete reference.

### Testing Utilities

Shared scaffolding for PHPUnit integration tests against a live Craft install:

- **[Testing](testing.md)** — `IntegrationTestCase` abstract base (component swap/restore, generic DB helpers, marker cleanup, fixture lifecycle helpers for elements/temp paths, queue drain, `cleanupExternalState()` hook), `bootstrap()` function (Craft console init with optional explicit project root), and `phpunit.xml.dist.template` for copy-once suite setup

### CP Layouts & Components

Reusable Twig templates for building consistent CP pages:

- **[CP Table Layout](../template-guides/cp-table-layout.md)** — Full-featured table pages with filters, search, pagination, bulk actions, and AJAX refresh
- **[CP Analytics Layout](../template-guides/cp-analytics-layout.md)** — Analytics dashboards with tabs, charts, stat boxes, and date filters
- **[CP Plugin Setup Layout](../template-guides/cp-plugin-setup-layout.md)** — Onboarding/readiness pages with setup tasks, status checks, commands, and next actions
- **[CP Utilities Layout](../template-guides/cp-utilities-layout.md)** — Utility pages with action sections and AJAX buttons
- **[CP Table Utility Layout](../template-guides/cp-table-utility-layout.md)** — Table variant for utility pages
- **[Components](../template-guides/components.md)** — Badge, info-box, setup-incomplete, setup-task, export-menu, row-actions, stat-box, dashboard-widget helpers, filter-status, filter-dropdown, phone-input
- **[Partials](../template-guides/partials.md)** — Analytics panel, CSV import, geo settings, cache storage, env command warnings, backup list

## Next Steps

- [Installation & Setup](../get-started/installation.md) — how the base module is installed and registered
- [Configuration](../get-started/configuration.md) — date/time formatting, export, and default date range settings
- [Bootstrapping](../developers/bootstrapping.md) — integrating the base module in your plugin
