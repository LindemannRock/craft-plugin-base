# Changelog

## [5.22.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.21.0...v5.22.0) (2026-04-25)


### Features

* add config override merge helper for settings models ([6d70416](https://github.com/LindemannRock/craft-plugin-base/commit/6d704168e7a9d2a74c65985ee7055d1a0895fff1))
* **css:** add flex properties to chart containers for better layout ([b014951](https://github.com/LindemannRock/craft-plugin-base/commit/b01495182124636f68db7160d402f1f4ed6e7b10))
* **css:** add margin utility classes for consistent spacing ([f42203b](https://github.com/LindemannRock/craft-plugin-base/commit/f42203bc2ffb7e00f60e4fca5f35c4d61a6be6c2))
* **twigextensions:** add LabelExtension for formatting user-facing labels ([9c6b7b8](https://github.com/LindemannRock/craft-plugin-base/commit/9c6b7b81986f8c49d8c20b5f76637ce10b2e6435))


### Bug Fixes

* **cp:** correct data attribute usage in custom filter options ([67a843e](https://github.com/LindemannRock/craft-plugin-base/commit/67a843e7e30b4385756fc17d5542e61a28c7fe5b))
* **css:** increase max dimensions for chart center canvas ([d78743c](https://github.com/LindemannRock/craft-plugin-base/commit/d78743c2186183aa8f7d410e64a68280501589ac))
* drop PAT requirement for release-please — use built-in GITHUB_TOKEN ([bafd0f6](https://github.com/LindemannRock/craft-plugin-base/commit/bafd0f6a9f3a387f58188e0c450b3c3fd9f3f00d))
* **helper:** update version in applyConfigOverridesToSettings method ([c09cd6b](https://github.com/LindemannRock/craft-plugin-base/commit/c09cd6b77c151283f5680bb73bbbeff735cb93b9))

## [5.21.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.20.1...v5.21.0) (2026-04-02)


### Features

* **translations:** add Arabic, Spanish, French, and Dutch translations for plugin messages ([0188418](https://github.com/LindemannRock/craft-plugin-base/commit/0188418b4f53ae5415914a003c033bed904d0db9))
* **translations:** add Danish, Italian, Japanese, Norwegian, Portuguese, and Swedish translations for plugin messages ([53147eb](https://github.com/LindemannRock/craft-plugin-base/commit/53147eb491eb344eb76b8434f4b52de6d2c60874))
* **translations:** add German translations for plugin messages ([a1767d1](https://github.com/LindemannRock/craft-plugin-base/commit/a1767d192a66fe7bd2533b3df930a5801aef0258))


### Bug Fixes

* **base:** register translation category for shared UI copy ([06c6419](https://github.com/LindemannRock/craft-plugin-base/commit/06c6419ac6cc304c810d1a6e870dbfa4b4a2ef6d))
* **ExportHelper:** update error message translation for export failure ([d5ca474](https://github.com/LindemannRock/craft-plugin-base/commit/d5ca4748d3aab6542dc7fcb184c063df2f395c58))
* **install-experience:** translate static text in install experience ([5ffd896](https://github.com/LindemannRock/craft-plugin-base/commit/5ffd89655ea035409f207c932993c58abcc787c8))

## [5.20.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.20.0...v5.20.1) (2026-03-26)


### Bug Fixes

* **badge:** add nowrap style to status label spans ([5b3e1d6](https://github.com/LindemannRock/craft-plugin-base/commit/5b3e1d60a6b0d41addb2a5a83fd3bba391bbc265))

## [5.20.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.19.1...v5.20.0) (2026-03-17)


### Features

* **AnalyticsIpHelper:** add IP preprocessing and anonymization helper class ([e71a144](https://github.com/LindemannRock/craft-plugin-base/commit/e71a144014e143a0975e4dc240d945513f9a1d4b))

## [5.19.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.19.0...v5.19.1) (2026-03-17)


### Bug Fixes

* **PluginHelper:** simplify global variable registration in Twig ([b988d8b](https://github.com/LindemannRock/craft-plugin-base/commit/b988d8bf3a2bc7aa34451cc69346b31e72d109aa))

## [5.19.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.18.0...v5.19.0) (2026-03-17)


### Features

* add import history partial with parameters and structure ([ab1e03b](https://github.com/LindemannRock/craft-plugin-base/commit/ab1e03b4a0fcdbe3de8f488c33f8321245ced836))
* **helpers:** add JsonHelper class for safe JSON encoding ([e6585f8](https://github.com/LindemannRock/craft-plugin-base/commit/e6585f85a78e711fc40885e07f6c88e86d80a563))
* **install-experience:** add install experience assets and functionality ([e6b6f02](https://github.com/LindemannRock/craft-plugin-base/commit/e6b6f022f3583cd8b03155a029ed95e2176289c0))


### Miscellaneous Chores

* **assets:** add package.json for asset management and build scripts ([dea148c](https://github.com/LindemannRock/craft-plugin-base/commit/dea148c4c14da9e78b882c0f6be3b57947a33830))
* **package:** update dependencies and add build script ([91f847c](https://github.com/LindemannRock/craft-plugin-base/commit/91f847c50be5f9475f4b54cea1dc98d82860ff33))

## [5.18.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.17.0...v5.18.0) (2026-03-04)


### Features

* **traits:** add QueueTtrTrait for shared TTR implementation in queue jobs ([9c893fb](https://github.com/LindemannRock/craft-plugin-base/commit/9c893fb2bb03c3463a20cbcbace4d803d5bba688))
* **validators:** add RoutePrefixValidator, TemplatePathValidator, and UrlOrPathValidator ([8f2c741](https://github.com/LindemannRock/craft-plugin-base/commit/8f2c741ddf33b9403c5b6924f32ff2c3c227ff6b))
* **validators:** add StoragePathValidator for validating storage paths ([38a58f1](https://github.com/LindemannRock/craft-plugin-base/commit/38a58f1c44f63d5c4234530f281d05b900e2854b))


### Bug Fixes

* **ColorHelper:** update color mappings for backup reasons ([21b69ae](https://github.com/LindemannRock/craft-plugin-base/commit/21b69ae326bf416fb3fe2caa7b6b728f2b1b0c57))
* **cp-table:** enhance URL parameter handling for filter groups ([fe824f9](https://github.com/LindemannRock/craft-plugin-base/commit/fe824f9b43d7b72078af4fbd0946da76e07bdd73))
* **DbHelper:** update groupConcat to accept Expression type ([7091bfb](https://github.com/LindemannRock/craft-plugin-base/commit/7091bfbcdc6041ce263ca2623a5337ccc5738e73))
* **geo-settings:** add error handling for geoProvider and geoApiKey fields ([7708145](https://github.com/LindemannRock/craft-plugin-base/commit/7708145a391e761025e77d0c2e83bf8420129326))
* **geo-settings:** update geo provider options with translation support ([6968184](https://github.com/LindemannRock/craft-plugin-base/commit/6968184288b08c1bce4acacc78d524a8d0e97cd2))
* normalize error-summary partial i18n domain handling ([9d465d3](https://github.com/LindemannRock/craft-plugin-base/commit/9d465d3a03dc7a4fa35829d320ef35ef2726ca41))
* **traits:** improve display name handling in SettingsDisplayNameTrait ([9f95e26](https://github.com/LindemannRock/craft-plugin-base/commit/9f95e265ce67bcf3b239985e4426e031622100ec))
* **traits:** improve logging configuration in DeviceDetectionTrait ([c941435](https://github.com/LindemannRock/craft-plugin-base/commit/c941435e37137b903199d0831b446eb05f0d965f))
* **validators:** enhance StoragePathValidator error handling ([ccf5f1d](https://github.com/LindemannRock/craft-plugin-base/commit/ccf5f1dbf3af74fa2cbeae147670c4d414476184))

## [5.17.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.16.0...v5.17.0) (2026-02-22)


### Features

* **CsvImportHelper:** add stripFormulaEscapePrefix method documentation ([5838bbd](https://github.com/LindemannRock/craft-plugin-base/commit/5838bbd04cdab39f03319b7ddf3498fc6bb87d5e))


### Bug Fixes

* **badge.twig:** correct function call for value color fallback ([f88df98](https://github.com/LindemannRock/craft-plugin-base/commit/f88df98412632ce2b482ba12542e887d44492876))
* **ColorHelper:** add default class to DEFAULT_COLOR constant ([b10df2f](https://github.com/LindemannRock/craft-plugin-base/commit/b10df2f3284863905005e7ab6ef380edb42b6a94))
* **composer.json:** update handle to match naming convention ([45e6e1e](https://github.com/LindemannRock/craft-plugin-base/commit/45e6e1e46883f0f469a3b24c7f290724e86cf38a))
* **CsvImportHelper:** clarify handling of single-column CSVs in parseUpload ([0066155](https://github.com/LindemannRock/craft-plugin-base/commit/0066155749e6aeb1bb75d53fc37d8e5b97e99f0a))
* **DateRangeHelper:** adjust date calculations to use Craft's timezone ([50f9a4e](https://github.com/LindemannRock/craft-plugin-base/commit/50f9a4e97f0763bcc3cd18f43df21fe166d193bd))
* **DeviceDetection:** ensure atomic file writes for cached device info ([eec3b4b](https://github.com/LindemannRock/craft-plugin-base/commit/eec3b4bf0f85bac4c21676c59297c84c022b4462))
* **ExportHelper:** handle errors when reading generated Excel files ([7aa4318](https://github.com/LindemannRock/craft-plugin-base/commit/7aa4318fb224d8cd1274d4883bb4127eabf0ca21))
* **ExportHelper:** prevent formula injection in Excel exports ([3a63c03](https://github.com/LindemannRock/craft-plugin-base/commit/3a63c036209bfb715cdc941693330f05d96ce736))
* **GeoLookup:** redact API key and IP address in sanitized URLs ([a0f38b7](https://github.com/LindemannRock/craft-plugin-base/commit/a0f38b7ed838e4bac85f7c69ebdae5da2b11d033))
* **PluginHelper:** sanitize plugin ID for config file path ([4b621c4](https://github.com/LindemannRock/craft-plugin-base/commit/4b621c41949a363c54574fe902d366ff95a3efbc))
* **SettingsDisplayNameTrait:** update display name exceptions ([ad35df0](https://github.com/LindemannRock/craft-plugin-base/commit/ad35df0455d79e3269b6515f1120805e926298c3))
* **SettingsPersistenceTrait:** handle JSON encoding errors in saveToDatabase ([03d2e5e](https://github.com/LindemannRock/craft-plugin-base/commit/03d2e5e7615a2feeba1fc2bc2848ae1415890bdd))

## [5.16.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.15.0...v5.16.0) (2026-02-17)


### Features

* **export-menu:** enhance export functionality with CSRF protection ([c22dad6](https://github.com/LindemannRock/craft-plugin-base/commit/c22dad6530def5bbf396d574f4236d44f0169563))
* **GeoHelper, GeoExtension:** add getCountryDialCodeData function and update Twig extension ([89ced6f](https://github.com/LindemannRock/craft-plugin-base/commit/89ced6f9bee73fdd4d0cd986763615a453fb5be1))
* **unified-card:** add optional HTML id attribute for JS targeting ([761cdaf](https://github.com/LindemannRock/craft-plugin-base/commit/761cdaffbd597200653f3d17c7ddaf75c4b58f2e))


### Bug Fixes

* **DateFormatHelper:** ensure UTC timezone is used for date parsing ([11b899d](https://github.com/LindemannRock/craft-plugin-base/commit/11b899d0c65a85d2affe55e44f52eb6c4b7219ba))
* **DbHelper:** add input validation to prevent SQL injection ([1a818e3](https://github.com/LindemannRock/craft-plugin-base/commit/1a818e386b680fa0bd1845551d293bf14622a56e))
* **ExportHelper:** convert UTC dates to Craft site timezone for exports ([acbc753](https://github.com/LindemannRock/craft-plugin-base/commit/acbc75348633e831680f4f485b2cdec583d2a871))
* **SettingsConfigTrait:** correct log level handling when devMode is disabled ([054bab6](https://github.com/LindemannRock/craft-plugin-base/commit/054bab6bf508d0102ba682bd69c14d7e43544de1))


### Miscellaneous Chores

* add .gitattributes with export-ignore for Packagist distribution ([a824c9f](https://github.com/LindemannRock/craft-plugin-base/commit/a824c9f76595acffa55dbfa254a170dd42cb34a7))

## [5.15.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.14.0...v5.15.0) (2026-02-07)


### Features

* Add DateFormatHelper and DbHelper for date and database operations ([34eb74b](https://github.com/LindemannRock/craft-plugin-base/commit/34eb74b5c92610f1164b74a329b1072b435e1da9))
* **DbHelper:** enhance jsonExtract and add groupConcat for DB-agnostic SQL ([25fbfb8](https://github.com/LindemannRock/craft-plugin-base/commit/25fbfb836cf4e71fbbc71ebae7e785e0c294958d))

## [5.14.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.13.0...v5.14.0) (2026-02-05)


### Features

* Add CP Utilities layout and components ([3de42a2](https://github.com/LindemannRock/craft-plugin-base/commit/3de42a27cc6fd0be801404d8a0e750af10c3a9eb))
* **analytics-panel:** enhance loading states and AJAX content replacement ([39da043](https://github.com/LindemannRock/craft-plugin-base/commit/39da043864f92a87ab30f1abdcfb2188876766a6))
* **backup:** add backup reason handling and CSV import form ([20ccc71](https://github.com/LindemannRock/craft-plugin-base/commit/20ccc7154bef61b24dcbe29c31361126e64aee5f))
* **config:** add centralized configuration for date formatting and export options ([8bbc5cc](https://github.com/LindemannRock/craft-plugin-base/commit/8bbc5cc53e80508a914802298f02bc9e67f6a674))
* **cp-nav:** add CpNavHelper for centralized permission and settings checks ([306ef82](https://github.com/LindemannRock/craft-plugin-base/commit/306ef82c2081f28ddf3644829a59055b14ef2725))
* **csv-import:** add CsvImportHelper for CSV file upload parsing and validation ([b005405](https://github.com/LindemannRock/craft-plugin-base/commit/b005405031fee366051837b241467a60056be708))
* **csv-import:** refactor CsvImportHelper to use constants for max rows and bytes ([71bebb5](https://github.com/LindemannRock/craft-plugin-base/commit/71bebb594728958a6a84f3b6b8f2681100816398))
* **device-detection:** add DeviceDetection and DeviceDetectionTrait for user-agent parsing ([bd36d78](https://github.com/LindemannRock/craft-plugin-base/commit/bd36d78ba4316290ef085ee0d4bc60596e3596b0))
* **device-detection:** add model mapping and cache key generation methods ([2ecdc2b](https://github.com/LindemannRock/craft-plugin-base/commit/2ecdc2b47f800fcabd002af157e0b29cb9fd8014))
* **device-detection:** enhance language detection capabilities ([defb47f](https://github.com/LindemannRock/craft-plugin-base/commit/defb47f4031891cf655cdb8aead1977e1e23e5ca))
* **device-detection:** enhance logging capabilities in DeviceDetection and DeviceDetectionTrait ([a0273b1](https://github.com/LindemannRock/craft-plugin-base/commit/a0273b1266aa67b73e0b46ac4d6ac3ddfadee8bf))
* enhance export menu and analytics panel functionality ([329fb0a](https://github.com/LindemannRock/craft-plugin-base/commit/329fb0a6cc4b98393f9ce0f73edac5515e154152))
* **export-helper:** add functionality to export multiple files as a ZIP archive ([49c222d](https://github.com/LindemannRock/craft-plugin-base/commit/49c222d758dc75d2487f6d01eb52ae4327f59a46))
* **import-csv:** enhance CSV import functionality with mode switch and descriptions ([9b345bd](https://github.com/LindemannRock/craft-plugin-base/commit/9b345bd97691af9d778d7765b36dff47eedc4bdd))
* **plugin-helper:** add logMenu option for customizable log sidebar navigation ([9ffdc3c](https://github.com/LindemannRock/craft-plugin-base/commit/9ffdc3c825829737bb0396f1d2de1d3be2de4fcf))
* **plugin-helper:** update permissions for log viewing in PluginHelper ([e5ace60](https://github.com/LindemannRock/craft-plugin-base/commit/e5ace60687133178048e5f3176b09e0ccb2e55a5))
* **PluginHelper:** add automatic translation registration functionality ([36e066a](https://github.com/LindemannRock/craft-plugin-base/commit/36e066a8085591e2e7d1b33d6b84d35c29c7b6ab))
* **table:** add row class customization for dynamic styling ([fe04c2e](https://github.com/LindemannRock/craft-plugin-base/commit/fe04c2e527014ed7e33e0d43695864ac404fcb9e))
* **unified-card:** add clickable card functionality with hover effects ([68a3c02](https://github.com/LindemannRock/craft-plugin-base/commit/68a3c02336d86780472312a6dd6aff90de80b857))
* **unified-cards:** add responsive column layouts for unified cards ([5811b07](https://github.com/LindemannRock/craft-plugin-base/commit/5811b074d0a5fa59e9bd179424dc7c1044e5d421))


### Bug Fixes

* **csv-import:** strip UTF-8 BOM from first header to prevent parsing issues ([a7e3554](https://github.com/LindemannRock/craft-plugin-base/commit/a7e355438cce12e22a8fb587313886295d8b8582))


### Miscellaneous Chores

* **composer:** update matomo/device-detector dependency to ^6.4 ([7a56ba2](https://github.com/LindemannRock/craft-plugin-base/commit/7a56ba2f989345133f74408f64f280fb2a7c1b8c))
* **PluginHelper:** update version annotation for getCacheKeySet method to 5.14.0 ([fcad3c8](https://github.com/LindemannRock/craft-plugin-base/commit/fcad3c84adbb75f29146662f843afeed71b04c99))

## [5.13.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.12.0...v5.13.0) (2026-01-28)


### Features

* **analytics-panel:** add new analytics panel component ([575ed24](https://github.com/LindemannRock/craft-plugin-base/commit/575ed248717e22e8cbd2da24a81243a016e333a8))
* **datetime:** enhance DateTimeHelper and DateTimeExtension with UTC handling ([70c7039](https://github.com/LindemannRock/craft-plugin-base/commit/70c7039247398fd63e396839ee9a90a46c6cf65f))
* **export:** add format aliases for Excel export compatibility ([ff71856](https://github.com/LindemannRock/craft-plugin-base/commit/ff718560a3953fc8e86a98469fa0bc454963ea99))
* **phone-input:** add reusable phone input component with country code detection ([bb74180](https://github.com/LindemannRock/craft-plugin-base/commit/bb74180583c195fdb22dcf5a0513dafeb7bb6aa8))
* **table:** enhance bulk actions and item selection logic ([575ed24](https://github.com/LindemannRock/craft-plugin-base/commit/575ed248717e22e8cbd2da24a81243a016e333a8))

## [5.12.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.11.0...v5.12.0) (2026-01-28)


### Features

* add Geo Twig extension and health status colors to ColorHelper ([2e40f7e](https://github.com/LindemannRock/craft-plugin-base/commit/2e40f7edecafb54b4ee64fe071964d4349158dd3))


### Bug Fixes

* add phpspreadsheet ^5.0 support for Craft 5.9 compatibility ([4e4c596](https://github.com/LindemannRock/craft-plugin-base/commit/4e4c59688fcbc236b2da569340cc053f39adf806))

## [5.11.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.10.1...v5.11.0) (2026-01-27)


### Features

* add message status colors to ColorHelper ([13a8bfc](https://github.com/LindemannRock/craft-plugin-base/commit/13a8bfc9bfe63b33ad123600e8ea64214b919626))

## [5.10.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.10.0...v5.10.1) (2026-01-26)


### Bug Fixes

* remove premature getTwig call in Twig extension registration ([36f07f4](https://github.com/LindemannRock/craft-plugin-base/commit/36f07f458a1fabfce95e60b05dc2f1d1c4256972))

## [5.10.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.9.0...v5.10.0) (2026-01-26)


### Features

* add method to retrieve plugin display name with fallback option ([01965a2](https://github.com/LindemannRock/craft-plugin-base/commit/01965a2864d994b1155544198c24f2546a118383))
* add plugin detection helpers for checking installation and enabled status ([bdfa720](https://github.com/LindemannRock/craft-plugin-base/commit/bdfa720c8b8ec559f76f4c5d13e3cf3129eb8da6))
* add plugin status definitions with color coding for active, disabled, and not installed states ([be9440e](https://github.com/LindemannRock/craft-plugin-base/commit/be9440e2120c60df4572bfc665e8caf029dc0824))
* add PluginExtension for plugin detection and name lookup in Twig ([579d1dc](https://github.com/LindemannRock/craft-plugin-base/commit/579d1dc6da8cd7b7cc343e7734d3a5c9b604a497))
* add Status Dot Component for rendering status indicators with customizable options ([aaed810](https://github.com/LindemannRock/craft-plugin-base/commit/aaed81007b9282a462c645b0bc0b5a7297fc4563))
* enhance table layout with view button for column visibility and sorting options ([66f1179](https://github.com/LindemannRock/craft-plugin-base/commit/66f11793b184f92206dbef75cb70d1bb775e1171))


### Bug Fixes

* plugin status colors with additional dot indicators for active, disabled, and not installed states ([9a41f7f](https://github.com/LindemannRock/craft-plugin-base/commit/9a41f7f31dada2aff2ffe1989d9cc0ce0ee06ca9))
* **security:** address export and config vulnerabilities in base plugin ([1224bf0](https://github.com/LindemannRock/craft-plugin-base/commit/1224bf07e231a492491e2242883228c1db6be353))

## [5.9.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.8.0...v5.9.0) (2026-01-24)


### Features

* add export format options and enhance sidebar content in templates ([bd9e5b8](https://github.com/LindemannRock/craft-plugin-base/commit/bd9e5b809e5b5fcb0c1e8f19ca6480f0f1932953))

## [5.8.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.7.0...v5.8.0) (2026-01-24)


### Features

* add centralized helpers, reusable CP layouts, and export functionality ([1c1eb7d](https://github.com/LindemannRock/craft-plugin-base/commit/1c1eb7d50094038870c5902dfbabf3537fad1e07))

## [5.7.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.6.0...v5.7.0) (2026-01-21)


### Features

* add phone dial code utilities to GeoHelper class ([80923af](https://github.com/LindemannRock/craft-plugin-base/commit/80923afce378445b3a2f7d629265605f7478bb6d))
* enhance info-box component with additional options and styling ([855e1fc](https://github.com/LindemannRock/craft-plugin-base/commit/855e1fcd24b8d6d09bf224dbe0f7142a7fcad842))
* implement geo IP lookup and provider configuration classes ([9ffc6a6](https://github.com/LindemannRock/craft-plugin-base/commit/9ffc6a63c6f42b3d9a3ca62da11fde66a38ca080))

## [5.6.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.5.0...v5.6.0) (2026-01-18)


### Features

* add stretch option to info-box component for full-width display ([25570cb](https://github.com/LindemannRock/craft-plugin-base/commit/25570cbaaa20a700c5ad057ff3df0f938b870073))
* enhance info-box component with margin and background options ([bf1eb6a](https://github.com/LindemannRock/craft-plugin-base/commit/bf1eb6aa9897dcc5b6d8a62996a5d20f7c4ce7a6))

## [5.5.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.4.0...v5.5.0) (2026-01-16)


### Features

* add cache path helpers for plugin caching functionality ([9bcb720](https://github.com/LindemannRock/craft-plugin-base/commit/9bcb72040b12d8dc919b7a6778ef13a1f05b1133))
* add cache path methods for plugin caching functionality ([6451dd9](https://github.com/LindemannRock/craft-plugin-base/commit/6451dd993d98ac70b99a21f4b2c9048f90a61d9e))
* add EditionTrait for standardized plugin edition support ([166859d](https://github.com/LindemannRock/craft-plugin-base/commit/166859d1e3584b30014b71025cdf22e7995cb18e))
* enhance README with detailed Edition Support usage and examples ([31ff490](https://github.com/LindemannRock/craft-plugin-base/commit/31ff490d38d13b2e79eb7a9b678c8da7644ce406))
* update PluginHelper bootstrap method to include download permissions and improve logging configuration ([f01e89a](https://github.com/LindemannRock/craft-plugin-base/commit/f01e89a32037a04df2784ebe444e587ed8582169))


### Bug Fixes

* improve pluralization logic in getDisplayName method for case-insensitivity ([5f1be30](https://github.com/LindemannRock/craft-plugin-base/commit/5f1be30afbe48846c58ae3e6592462bd377260bf))


### Miscellaneous Chores

* add cache path helpers for consistent plugin caching structure ([a81696c](https://github.com/LindemannRock/craft-plugin-base/commit/a81696ccf2de86bbddd49c93acc208719ea1da54))

## [5.4.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.3.0...v5.4.0) (2026-01-12)


### Features

* enhance getDisplayName method to preserve acronyms during singularization ([f664813](https://github.com/LindemannRock/craft-plugin-base/commit/f66481306104ebb972e0701ccb1cef7b00cb4f7d))

## [5.3.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.2.0...v5.3.0) (2026-01-10)


### Features

* add GeoHelper for country code to name conversion ([d0ec0b1](https://github.com/LindemannRock/craft-plugin-base/commit/d0ec0b12bdae04b2042488a732c0e8882ad343ec))


### Miscellaneous Chores

* update README to include GeoHelper usage and functionality ([949d764](https://github.com/LindemannRock/craft-plugin-base/commit/949d764c767c5f02007828e21ce9fcc93c92b803))

## [5.2.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.1.0...v5.2.0) (2026-01-06)


### Features

* register global variable directly via Twig in PluginHelper ([35e31b2](https://github.com/LindemannRock/craft-plugin-base/commit/35e31b2fcdacb90b1537ee48e150d9d3d8acc76c))

## [5.1.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.0.0...v5.1.0) (2026-01-05)


### Features

* add floatFields method for type conversion in SettingsPersistenceTrait ([180b8b2](https://github.com/LindemannRock/craft-plugin-base/commit/180b8b2694a1134d2e85c9cff98facada6e07d56))

## 5.0.0 (2026-01-05)


### Features

* initial LindemannRock Plugin Base implementation ([947840f](https://github.com/LindemannRock/craft-plugin-base/commit/947840f5c1861781381edec8d3ca79dcca72a27d))
