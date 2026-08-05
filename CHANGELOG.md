# Changelog

## [5.37.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.36.0...v5.37.0) (2026-08-05)


### Added

* add Redis database diagnostics ([5b460ca](https://github.com/LindemannRock/craft-plugin-base/commit/5b460caa007eb696a426f8ee48abd12708062938))
* **components:** make error-summary links reveal and focus invalid fields ([3dcf8c7](https://github.com/LindemannRock/craft-plugin-base/commit/3dcf8c7059ad73232e974bd423d3250a3b5cab4e))
* **helpers:** add error color to color set for status indicators ([b75fb57](https://github.com/LindemannRock/craft-plugin-base/commit/b75fb57214e775f136ebd3c592f84122078bff07))
* **helpers:** add independent Yii Redis connections ([da4b11d](https://github.com/LindemannRock/craft-plugin-base/commit/da4b11da985b4e84d823cabff19b47f9b5b81899))


### Fixed

* **helpers:** avoid caching incomplete date config during plugin loading ([9dd28c3](https://github.com/LindemannRock/craft-plugin-base/commit/9dd28c364aeecc39c3d54276276deab953c82e5e))
* secure settings persistence failure logs ([0677442](https://github.com/LindemannRock/craft-plugin-base/commit/0677442a5cdf0217307d0f3ceab765648e4ae39f))
* trust GitHub workspace for archive validation ([d2550cd](https://github.com/LindemannRock/craft-plugin-base/commit/d2550cd45800933ab426acfefe7238c1f7cf01fc))

## [5.36.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.35.0...v5.36.0) - 2026-07-22


### Added

* **components:** add task list styles to the setup component ([ee1c5e7](https://github.com/LindemannRock/craft-plugin-base/commit/ee1c5e7c1cf58e0f65d2f56090513c8c1155ea52))
* **editions:** add shared edition gate and upgrade prompt ([af67f59](https://github.com/LindemannRock/craft-plugin-base/commit/af67f599d0c826d168d3e7828bf52ba4880f4af4))
* register asset bundle for filter-status, info-box, stat-box, and status-icon templates ([02f0e7d](https://github.com/LindemannRock/craft-plugin-base/commit/02f0e7dd7ae48ff0176ff89f4350762a430e0541))


### Fixed

* correct edition tier documentation in EditionTrait ([33fbceb](https://github.com/LindemannRock/craft-plugin-base/commit/33fbceb21adb21831150c59b83f64f0c5b16c38f))

## [5.35.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.34.2...v5.35.0) - 2026-07-18


### Added

* add portable orderByNullsLast method for consistent NULL sorting ([420acbc](https://github.com/LindemannRock/craft-plugin-base/commit/420acbcfa9503b4534e14bee56a9a33e16ae3a97))
* **db-helper:** add boolToInt method for portable boolean aggregation ([f4c4f34](https://github.com/LindemannRock/craft-plugin-base/commit/f4c4f343ea00dc228a7396419b7351e9d809d180))
* **db-helper:** add existingColumn helper for upsert expressions ([e346f88](https://github.com/LindemannRock/craft-plugin-base/commit/e346f88e21fe04e239ef2645dc16533ea929c94c))
* **db-helper:** add SqlDialectLinter for SQL safety checks ([e346f88](https://github.com/LindemannRock/craft-plugin-base/commit/e346f88e21fe04e239ef2645dc16533ea929c94c))
* **helpers:** add DB-agnostic JSON extraction and casting methods ([6b19632](https://github.com/LindemannRock/craft-plugin-base/commit/6b1963224570750fe76fcc9c40dc36cd4eea1a73))
* **tests:** enhance DbHelperTest with additional MySQL dialect tests ([cb5cf34](https://github.com/LindemannRock/craft-plugin-base/commit/cb5cf343b0c9c52d384583138d7decbbd3da0d43))


### Fixed

* handle unbracketed camelCase columns and aliases in SQL literals ([87fd31a](https://github.com/LindemannRock/craft-plugin-base/commit/87fd31acb622552c44b4d2dd8b3e4a8cd39d0e35))

## [5.34.2](https://github.com/LindemannRock/craft-plugin-base/compare/v5.34.1...v5.34.2) - 2026-07-17


### Fixed

* **i18n:** correct translation category for error messages and quick actions ([13d9615](https://github.com/LindemannRock/craft-plugin-base/commit/13d9615e61b52a11a8a3b8cf718be9ed620e22e4))
* **i18n:** correct translations across multiple locales ([f1824e7](https://github.com/LindemannRock/craft-plugin-base/commit/f1824e7f027f57998f66bc6904ad2d77fab310e0))

## [5.34.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.34.0...v5.34.1) - 2026-07-06


### Fixed

* **settings:** treat null and empty string as valid for nullable attributes ([fff3155](https://github.com/LindemannRock/craft-plugin-base/commit/fff31554b27472f998cf6e12461ae596deb03c27))

## [5.34.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.33.0...v5.34.0) - 2026-07-05


### Added

* **cp:** add notices block for alert/info messages above header ([f042d8f](https://github.com/LindemannRock/craft-plugin-base/commit/f042d8f5909094ead1fb7b8e0d3977a1cfeaad96))
* **css:** add subheading and message styles for task components ([d66177f](https://github.com/LindemannRock/craft-plugin-base/commit/d66177fce9a90e69a50b612a2f3d4c9721bdd7f5))
* **i18n:** add 'Set status' translation key across multiple locales ([133990b](https://github.com/LindemannRock/craft-plugin-base/commit/133990b5bbea0eeed41c1abb5fa3c00eb1484024))
* **info-box:** add extraClass option for scoped styling ([ba44d81](https://github.com/LindemannRock/craft-plugin-base/commit/ba44d81dc14f6fb4829a85b9fec118967ae743e4))
* **setup:** add standardized setup layout and task components ([33df700](https://github.com/LindemannRock/craft-plugin-base/commit/33df700397bbfa53366f5cc5c2bb386108aea538))
* **status-icon:** add standalone semantic status icon component ([1002b99](https://github.com/LindemannRock/craft-plugin-base/commit/1002b99dd060121f18dbc74598c160b825dd6b8a))
* **testing:** replace project cache with isolated file cache for tests ([0134c0a](https://github.com/LindemannRock/craft-plugin-base/commit/0134c0a3b1c94a1dbb37ae3401781b65b3a769d4))
* **validators:** enhance template path validation with site mode check ([dcf69f8](https://github.com/LindemannRock/craft-plugin-base/commit/dcf69f8f704fe1fe70e47350b8fdae67a16d1a76))

## [5.33.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.32.0...v5.33.0) - 2026-07-03


### Added

* add unknown log level to color palette for better logging clarity ([9604842](https://github.com/LindemannRock/craft-plugin-base/commit/9604842f707e2932885cee7610ed3cf8b5bcea49))
* add validation rules for plugin name to prevent HTML and control characters ([fdc1b6f](https://github.com/LindemannRock/craft-plugin-base/commit/fdc1b6f59135509db634530b86b0986f811c117f))
* **cp:** add refresh pause functionality to table rows when expanded ([78fca71](https://github.com/LindemannRock/craft-plugin-base/commit/78fca71b8f0b3b51447db322654e694dc4f6c37c))
* **testing:** add user and permission helpers for integration tests ([0ee19ad](https://github.com/LindemannRock/craft-plugin-base/commit/0ee19ad9dad425b9e14df6f7d2d675995748173e))


### Fixed

* standardize query parameter handling across filters ([05c4a9e](https://github.com/LindemannRock/craft-plugin-base/commit/05c4a9e6dd17504732e4f43c3c8a62edf79d6200))

## [5.32.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.31.0...v5.32.0) - 2026-06-30


### Added

* add beforeQuickActions block for plugin-specific content ([7de81a2](https://github.com/LindemannRock/craft-plugin-base/commit/7de81a22f39bfcef1a4850f5e228dda1d2c7b79f))

## [5.31.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.30.0...v5.31.0) - 2026-06-30


### Added

* **cache:** add CacheHelper for plugin cache management ([557f75d](https://github.com/LindemannRock/craft-plugin-base/commit/557f75df40f4a8ed5c7dbef322b5cb7ae0833704))

## [5.30.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.29.0...v5.30.0) - 2026-06-28


### Added

* **cp:** add applyStoredSettings functionality on lr:refresh event ([3a46a23](https://github.com/LindemannRock/craft-plugin-base/commit/3a46a23594096a10d86b77d630edb4555a27eee9))

## [5.29.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.28.1...v5.29.0) - 2026-06-24


### Added

* **helpers:** add 'delete' status color to palette ([c5b41c6](https://github.com/LindemannRock/craft-plugin-base/commit/c5b41c69482585e06e8a000d5c58a3aea791c59c))
* support preserved table params and canonical table URLs ([33e718c](https://github.com/LindemannRock/craft-plugin-base/commit/33e718c755ae24528f86b4bc96120ffffa3366d7))

## [5.28.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.28.0...v5.28.1) - 2026-06-23


### Fixed

* correct number of built-in color sets in README ([ca610f4](https://github.com/LindemannRock/craft-plugin-base/commit/ca610f4da812f573645998848a250a336916050e))

## [5.28.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.27.0...v5.28.0) - 2026-06-23


### Added

* add min-width and overflow handling for dashboard widgets ([26024d9](https://github.com/LindemannRock/craft-plugin-base/commit/26024d9a5967ae5b70bcf45d04d0c64f8668dbda))
* **cli:** add smoke test scripts for Craft compatibility checks ([99b074d](https://github.com/LindemannRock/craft-plugin-base/commit/99b074da29ff4bfb5de29e602b4e6973bff725ba))
* **helpers:** add ExperimentalFeatureHelper to manage internal features ([d804418](https://github.com/LindemannRock/craft-plugin-base/commit/d804418ddb10ebb2871a9262ea5a185e8faa50bb))


### Fixed

* encode remaining CP config strings in JS ([76d2982](https://github.com/LindemannRock/craft-plugin-base/commit/76d298246eddd8b93603260345cb31f17eb3a7b0))
* **helpers:** handle exceptions when creating DateTime from string ([0d493d2](https://github.com/LindemannRock/craft-plugin-base/commit/0d493d21e7225ddfcb3b04ff5a01a1a1a5001892))
* resolve audit 18 environment and escaping findings ([538fa8a](https://github.com/LindemannRock/craft-plugin-base/commit/538fa8acdd97623c142658be1eeb46632d8c6072))

## [5.27.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.26.0...v5.27.0) - 2026-06-18


### Added

* add bulk actions and status menu components for cp-table ([abeda6f](https://github.com/LindemannRock/craft-plugin-base/commit/abeda6ff98aa0a011d26412117708f066b71cda8))
* add config override warning component for manual notices ([8175681](https://github.com/LindemannRock/craft-plugin-base/commit/81756810f0414d5ade85a1f885fa9d23353abce1))
* add copy input component for clipboard functionality ([7964f5e](https://github.com/LindemannRock/craft-plugin-base/commit/7964f5e94dd0473c969e3ad49211e5c63443e004))
* add dashboard widget components for empty state, footer, list, and stats ([a9822b1](https://github.com/LindemannRock/craft-plugin-base/commit/a9822b196b6e203b274386e75eaefbe4457395d2))
* add integration card partial for third-party plugin integration ([d2192fc](https://github.com/LindemannRock/craft-plugin-base/commit/d2192fc497adf1285d31ef2bd82a1ebf6dad71bb))
* add isHttpUrlWithHost method to validate URLs with hosts ([e0ca731](https://github.com/LindemannRock/craft-plugin-base/commit/e0ca731ca98c350ffd2f209bd75eccff58a21058))
* add lr-logo.svg icon for branding ([98008e3](https://github.com/LindemannRock/craft-plugin-base/commit/98008e3cca39fda3645de092444c352c40350b49))
* add support for opt-in extra URL schemes in UrlSafetyHelper ([0430f17](https://github.com/LindemannRock/craft-plugin-base/commit/0430f171e93d7cd4cf161682842a4cb29f4f529d))
* **chart:** enhance chart container with additional parameters and structure ([a5e4c46](https://github.com/LindemannRock/craft-plugin-base/commit/a5e4c468bfad490b2bdbaa1da0d46c10d1467702))
* **cp-table:** add pagination info and refresh functionality ([a515848](https://github.com/LindemannRock/craft-plugin-base/commit/a51584891791bc95c8feb488ea50141d0a159663))
* **cp:** add bulk actions and status menu components with selection count ([071f94f](https://github.com/LindemannRock/craft-plugin-base/commit/071f94fe1ae4c3eff16d067d5ba9cc946935eab4))
* **cp:** add select all checkbox functionality to bulk actions ([d6a3c04](https://github.com/LindemannRock/craft-plugin-base/commit/d6a3c04c616f467b998c21e67e1e8773268fc369))
* **cp:** add selection count display to bulk action and status menus ([9348a31](https://github.com/LindemannRock/craft-plugin-base/commit/9348a31dd99e4a3018633a886a19eaefecc4146c))
* **cp:** keep editable controls readable in selected-row state ([4f7ad33](https://github.com/LindemannRock/craft-plugin-base/commit/4f7ad337fe66f66163fd636b152a8cdd9e37ed29))
* **css:** add lr-break-word class to wrap long unbreakable strings ([57f69ad](https://github.com/LindemannRock/craft-plugin-base/commit/57f69ad821030df18d072f66692a19f673687c19))
* **css:** add lr-mt-10 class for 10px top margin ([bde5043](https://github.com/LindemannRock/craft-plugin-base/commit/bde50437b9af3d6dca64580b2792d42a90165888))
* **css:** add new utility classes for layout and spacing ([13c0f77](https://github.com/LindemannRock/craft-plugin-base/commit/13c0f7740a2125c22c037f6e2c0afb32fcad7900))
* **date-format:** add pluginHandle parameter for compact datetime formatting ([8238d9a](https://github.com/LindemannRock/craft-plugin-base/commit/8238d9a93f42e3a9b693f3963d86dec8bf6d2646))
* **device:** add client hints support for enhanced device detection ([2438849](https://github.com/LindemannRock/craft-plugin-base/commit/24388491e3a0a7d47d996b672ec8ae244e7ee432))
* **docs:** add icon SVG files for plugin assets ([e23d6c9](https://github.com/LindemannRock/craft-plugin-base/commit/e23d6c912e72d3d1229c6f97a64553f0d7cc994c))
* **gql:** add GqlHelper class for GraphQL query and type handling ([9dbbe64](https://github.com/LindemannRock/craft-plugin-base/commit/9dbbe646921c5f020987ec4b51991eef40954919))
* **helpers:** add ContentSafetyHelper to detect dangerous markup ([a1c0f37](https://github.com/LindemannRock/craft-plugin-base/commit/a1c0f37fd0aba2655d14af411e01959044e0af37))
* **helpers:** add hasDangerousScheme method to validate URL schemes ([b775c38](https://github.com/LindemannRock/craft-plugin-base/commit/b775c3813c47c168ece5be51b2cc8d628792be62))
* **helpers:** add lrLogoPaths method to retrieve logo SVG paths ([38212ad](https://github.com/LindemannRock/craft-plugin-base/commit/38212ad66a4c4d93477f1f866994983c8ecc219f))
* **helpers:** add luminance and withAlpha methods for color manipulation ([6d8a46a](https://github.com/LindemannRock/craft-plugin-base/commit/6d8a46a3d45251eebc2f4df4dce0f06f1950c7ba))
* **helpers:** add mix method to blend hex colors and parse hex strings ([60eacd4](https://github.com/LindemannRock/craft-plugin-base/commit/60eacd4a62c85afd5b4943c8ffcace1428b16c5a))
* **helpers:** add pluginHandle parameter for date formatting settings ([4181cac](https://github.com/LindemannRock/craft-plugin-base/commit/4181cac3ae1c99cc33ae129d3bcfc217382083b2))
* **helpers:** add primaryHexFromSvg method to extract color from SVG ([da46e85](https://github.com/LindemannRock/craft-plugin-base/commit/da46e85c01984e889089b86804207ee95320cbb1))
* **helpers:** add readIconSvg method to retrieve SVG from directory ([afd8083](https://github.com/LindemannRock/craft-plugin-base/commit/afd808361079e49a1079f7db8c1b66b22bfe2bc3))
* **helpers:** add RecurringQueueHelper for managing recurring jobs ([9fa2776](https://github.com/LindemannRock/craft-plugin-base/commit/9fa277681e7695853d1ba2701fa35a675f52fdfb))
* **i18n:** translate 'Export' and related terms in multiple languages ([c2a7cdb](https://github.com/LindemannRock/craft-plugin-base/commit/c2a7cdba3c04861e4d8b3d8f70e47d4c58d373ca))
* **import-export:** add styles for import-export preview tables and summary panel ([2480f00](https://github.com/LindemannRock/craft-plugin-base/commit/2480f00ad7a85c17aee4f35d3a8680b89cf4c214))
* **partials:** add env-command-error partial for missing env vars ([4e7b1bb](https://github.com/LindemannRock/craft-plugin-base/commit/4e7b1bbf21e66e6aecaa59d5706253f88520f022))
* **queue:** add RecurringQueueResult for job ownership management ([9539fad](https://github.com/LindemannRock/craft-plugin-base/commit/9539fad9338d523a080cf5616bbf83c1d3777286))
* **queue:** document RecurringQueueResult and ensurePending method return values ([9d706c2](https://github.com/LindemannRock/craft-plugin-base/commit/9d706c23e6bc0a62336945f5e2aa659491432654))
* **tests:** add integration tests for PluginHelper and color extraction ([629dcc8](https://github.com/LindemannRock/craft-plugin-base/commit/629dcc8519dd83185531b051482e5fea5420090d))


### Fixed

* reject hostless HTTP URLs in UrlSafetyHelper ([46b681a](https://github.com/LindemannRock/craft-plugin-base/commit/46b681a7f18919426eb7c80d1ba0393335e9038c))

## [5.26.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.25.0...v5.26.0) - 2026-06-07


### Added

* add act-static-analysis script for CI integration ([76abb22](https://github.com/LindemannRock/craft-plugin-base/commit/76abb229b8a5884b4697e94f2abcbd9eb8910c90))
* add additional date range options for analytics ([2ce49f2](https://github.com/LindemannRock/craft-plugin-base/commit/2ce49f208a914beb39590c2bfd75ab55aedbfdc3))
* add additional date range options for analytics ([75bfc18](https://github.com/LindemannRock/craft-plugin-base/commit/75bfc1806af7a654406d386e855ca1e7d3ca35fd))
* add export section header to cascade export format settings ([4fa8329](https://github.com/LindemannRock/craft-plugin-base/commit/4fa8329e1470d88d11db55946216a5583636e6e2))
* add normalizeSlug and bindSlugHandle functions for slug management ([5f3e84b](https://github.com/LindemannRock/craft-plugin-base/commit/5f3e84b1f87d0f5266545dd11a99b9017964d7fe))
* add pluginHandle parameter for explicit cascade settings in DateTimeExtension ([0524269](https://github.com/LindemannRock/craft-plugin-base/commit/0524269ce87d6df59b0ace9c35e95ba066a12556))
* add pluginHandle to device detection configuration ([a6f5a5e](https://github.com/LindemannRock/craft-plugin-base/commit/a6f5a5e556ad39199fa07d1b097535e4384672d5))
* add SettingsPostHelper and SettingsPostResult for handling settings POST values ([d9f165e](https://github.com/LindemannRock/craft-plugin-base/commit/d9f165e4f4e492ba0c837715607e70c9d1d9ae30))
* add StorageVolumeHelper and StorageVolumeValidator documentation ([e108e84](https://github.com/LindemannRock/craft-plugin-base/commit/e108e84d0ad435370b09aabe24e68e286640edfa))
* **cli:** add AbstractHelpController and ConsoleHelpHelper for CLI help ([777ae84](https://github.com/LindemannRock/craft-plugin-base/commit/777ae84305907682eb415df7fa0629dd5005b139))
* **css:** add padding to first orderable header button in data table ([6c7e53f](https://github.com/LindemannRock/craft-plugin-base/commit/6c7e53fb1d64f6a5719b89cf72210816f3efdfb1))
* **export:** add support for JSON and ZIP content exports ([981d381](https://github.com/LindemannRock/craft-plugin-base/commit/981d3811d7348e3e949430f1e3f2c4af097a4cd1))
* **export:** add unique name handling for ZIP entries and sanitize paths ([03479e5](https://github.com/LindemannRock/craft-plugin-base/commit/03479e530a2bd78def6521e5621334a5288c85fc))
* **helpers:** add additional date range options for analytics ([45d7535](https://github.com/LindemannRock/craft-plugin-base/commit/45d75358eaaa55a24b857b991ef102920fc7804a))
* **helpers:** add AssetVolumeHelper for asset ID validation ([982423b](https://github.com/LindemannRock/craft-plugin-base/commit/982423b8782a1b605b4c3cb19cdd1724f0df9a18))
* **helpers:** add clearConfigCache and formatCompactDatetimeFromSettings methods ([7d60204](https://github.com/LindemannRock/craft-plugin-base/commit/7d602045bdd3a57ff70e437b4fad667857808bff))
* **helpers:** add ConfigFileHelper for plugin configuration management ([5df977d](https://github.com/LindemannRock/craft-plugin-base/commit/5df977df692bb5333cd73cc4f9fca8d42d35e1f0))
* **helpers:** add includeYear parameter to date formatting methods ([a9c0758](https://github.com/LindemannRock/craft-plugin-base/commit/a9c075848964b8307bb0eecf6f8fc839a8ba24cc))
* **helpers:** add includeYear parameter to formatCompactDatetimeFromSettings ([4253bb6](https://github.com/LindemannRock/craft-plugin-base/commit/4253bb647b8d7d62130c9ed628832321257509e0))
* **helpers:** add new schedule identifiers and improve getOptions method ([c5a566d](https://github.com/LindemannRock/craft-plugin-base/commit/c5a566d1916a089924159685e77f275ad001026d))
* **helpers:** add optionNameWidth method for dynamic option padding ([f99b969](https://github.com/LindemannRock/craft-plugin-base/commit/f99b969594875939a1518573417db8e5189008b3))
* **helpers:** add pluginHandle parameter for cascading date format settings ([30502e6](https://github.com/LindemannRock/craft-plugin-base/commit/30502e60b9a6c768a4686a0229497605978f8166))
* **helpers:** add SafeSegmentHelper for normalizing string segments ([389f3af](https://github.com/LindemannRock/craft-plugin-base/commit/389f3af9cd61f7de34b946df8450b4cc22560342))
* **helpers:** add SlugHandleHelper for normalizing slugs and handles ([49c5952](https://github.com/LindemannRock/craft-plugin-base/commit/49c5952c33a4ad26552f88b48f0169960f4765e2))
* **helpers:** add StoragePathHelper for resolving storage paths ([ca91c36](https://github.com/LindemannRock/craft-plugin-base/commit/ca91c36462575c1497f89690972c16b0aec7329f))
* **helpers:** add UrlSafetyHelper for safe URL redirection ([bb467d3](https://github.com/LindemannRock/craft-plugin-base/commit/bb467d31b4e684fb47fb3b48a24ca4be1f019a94))
* **helpers:** expand validatePath method to support additional options ([3676e19](https://github.com/LindemannRock/craft-plugin-base/commit/3676e19037b59bd124944beae59c1074e6e10a67))
* **helpers:** support multiple command prefixes in console help output ([5144175](https://github.com/LindemannRock/craft-plugin-base/commit/51441757f7e7f1e03d38b632bf8a6f1a893db5ab))
* **helpers:** treat posted empty string as false for non-nullable booleans ([ec938f8](https://github.com/LindemannRock/craft-plugin-base/commit/ec938f81fe11df3fe988d743d89b94d6a50572a8))
* **i18n:** add additional date range options translations ([2fd5885](https://github.com/LindemannRock/craft-plugin-base/commit/2fd5885a9104ee36f007d4a40504adb29486e8aa))
* **i18n:** add export-related error messages in multiple languages ([f23f4ad](https://github.com/LindemannRock/craft-plugin-base/commit/f23f4adeee186fde625c3781ce83d5daa2f011fa))
* **i18n:** add new Arabic translations for CSV import history ([2df2f0a](https://github.com/LindemannRock/craft-plugin-base/commit/2df2f0a29cc755458993251bd3d7f072d535e377))
* **i18n:** add new schedule options for plugin configuration ([0a3bd15](https://github.com/LindemannRock/craft-plugin-base/commit/0a3bd15b0c62f5ad07363e5285ee4a88e8790348))
* **i18n:** add new translations for analytics and refreshing ([15acf02](https://github.com/LindemannRock/craft-plugin-base/commit/15acf02e9b745db0b5ba74f14e9ae8eb6ab78fee))
* **i18n:** add storage volume validation messages in multiple languages ([1b394c2](https://github.com/LindemannRock/craft-plugin-base/commit/1b394c2170783b5ca0fc73824f01adb8a33bb6ef))
* **i18n:** add validation message for string type across locales ([3f86893](https://github.com/LindemannRock/craft-plugin-base/commit/3f868931651f8f3e756d34f28e2aedf905f57e0d))
* **i18n:** add validation messages for various data types in translations ([825e8be](https://github.com/LindemannRock/craft-plugin-base/commit/825e8bef773386d8c703dbc8db4d0f8ed9f4d695))
* **i18n:** rename base plugin overrides to base settings overrides in translations ([828d895](https://github.com/LindemannRock/craft-plugin-base/commit/828d895e3a1a749774782f87874ed528da377282))
* **i18n:** update translation keys for app context to plugin context ([0884007](https://github.com/LindemannRock/craft-plugin-base/commit/088400799e9ec43d3f9d8f4b6ac360e75f7ca3db))
* **import-export:** add multi-format export support and configuration merging ([fd4b98b](https://github.com/LindemannRock/craft-plugin-base/commit/fd4b98b90e5c28536d48a4aaa8d42575737c0a11))
* **settings:** add attribute validation for saveToDatabase method ([607d0ac](https://github.com/LindemannRock/craft-plugin-base/commit/607d0ac57a90beffc2522abd949812ea13fa7920))
* **tests:** add comprehensive tests for ExportHelper functionality ([3f7ec67](https://github.com/LindemannRock/craft-plugin-base/commit/3f7ec675b138e243b3af9558a0205b9749fbef2f))
* **tests:** add date format configuration tests for DateFormatHelper ([41e7944](https://github.com/LindemannRock/craft-plugin-base/commit/41e794476885ae208e917c87df649797e841da76))
* **tests:** add DateRangeHelperTest for week start and bounds validation ([73e3207](https://github.com/LindemannRock/craft-plugin-base/commit/73e32071a0c5b1dabebc2764893ef501ba45e11c))
* **tests:** add explicit plugin handle test for date format config ([47bff76](https://github.com/LindemannRock/craft-plugin-base/commit/47bff76b2a7965e5def9e3d11dec1aac5d393ecc))
* **tests:** add helpers for element tracking and temporary path cleanup ([756e070](https://github.com/LindemannRock/craft-plugin-base/commit/756e07094da4a073b0205503e89a89251754f925))
* **tests:** add integration tests for SettingsPersistenceTrait and SettingsPostHelper ([ba6af4b](https://github.com/LindemannRock/craft-plugin-base/commit/ba6af4b77e34f4e9147f6eb65df3a5f088733811))
* **tests:** add test for allowing webroot when prevention is disabled ([6468140](https://github.com/LindemannRock/craft-plugin-base/commit/646814059490cf9ac926c674dc75932f11fc774a))
* **tests:** add test for compact datetime formatting with year inclusion ([62e3651](https://github.com/LindemannRock/craft-plugin-base/commit/62e365171205854c714ee00883000fe9f8b4e8d2))
* **tests:** add tests for compact datetime formatting and config cache clearing ([7403aa3](https://github.com/LindemannRock/craft-plugin-base/commit/7403aa30706679ecba4b2b5aa417f3923b73897d))
* **tests:** add tests for format normalization and config merging ([b87dedc](https://github.com/LindemannRock/craft-plugin-base/commit/b87dedc6b6e35f9a4ea64f612092fbdb0326ab71))
* **tests:** add validation tests for allowed aliases and webroot paths ([d280783](https://github.com/LindemannRock/craft-plugin-base/commit/d28078314e00843e2cb26c59780af1bb24b56d3b))
* **tests:** add zipContent test for entry name sanitization and subfolder preservation ([928f182](https://github.com/LindemannRock/craft-plugin-base/commit/928f1829ed18c3eaeb81259b8e352834b02c37b0))
* **tests:** document test methods for compact datetime formatting ([ea7bd46](https://github.com/LindemannRock/craft-plugin-base/commit/ea7bd46c5a9802a5ca5288a29ea359d17775a3d3))
* **validators:** add StorageVolumeValidator for asset volume UID validation ([5f048bc](https://github.com/LindemannRock/craft-plugin-base/commit/5f048bc27ed0ba96c7f24de2d470c207321772d8))


### Fixed

* **analytics:** serialize dateRange for JavaScript configuration ([24bea18](https://github.com/LindemannRock/craft-plugin-base/commit/24bea184b89e4bc88d29a551fd3d6ecaca6ea9e1))
* **cli:** return ExitCode::OK for CLI help command ([5ff2974](https://github.com/LindemannRock/craft-plugin-base/commit/5ff29743b9438d7f24b8e7ffa80e846ca22f6cc7))
* **css:** prevent pointer events on unified card link for better hover behavior ([cdec9c4](https://github.com/LindemannRock/craft-plugin-base/commit/cdec9c48d172f8c36b8d2fa780d824162e2bd21c))
* **export:** enhance error messages for JSON and Excel exports ([c991a62](https://github.com/LindemannRock/craft-plugin-base/commit/c991a62dee87a3b327e57a55aff7b9a442f23393))
* **export:** handle temporary file creation failure in Excel export ([3124d06](https://github.com/LindemannRock/craft-plugin-base/commit/3124d0672b763c25019224c49c4f964bd9254dc0))
* **helpers:** enhance normalizeString to include error message for invalid input ([65f0271](https://github.com/LindemannRock/craft-plugin-base/commit/65f027183b1883792318f7e667fe861e6b3d4f8f))
* **i18n:** correct German translations for analytics and date range settings ([ad70749](https://github.com/LindemannRock/craft-plugin-base/commit/ad707495374c912afb8391890c00ecfd736ffcea))
* **i18n:** correct Portuguese translations for plugin messages ([e60f0a8](https://github.com/LindemannRock/craft-plugin-base/commit/e60f0a8bc62b94b363fed5c51ece41831e852e50))
* **i18n:** correct punctuation in Japanese translation strings ([37b1721](https://github.com/LindemannRock/craft-plugin-base/commit/37b17215d7b75e8b4a0d521b139ffce02b3001dd))
* serialize action URL in AJAX button handler ([06d48f9](https://github.com/LindemannRock/craft-plugin-base/commit/06d48f91cbc9f861ea735218a42061b3821d6b49))
* serialize currentSiteId for JavaScript configuration ([a030a66](https://github.com/LindemannRock/craft-plugin-base/commit/a030a66c4dc9d134f9b051ccbb933fbf7a75f8e8))
* serialize currentSort and currentDir for JavaScript configuration ([42e9f74](https://github.com/LindemannRock/craft-plugin-base/commit/42e9f7481a497f9cf364661d37c58203283189f0))
* serialize currentSort and currentDir for JavaScript configuration ([ed59be6](https://github.com/LindemannRock/craft-plugin-base/commit/ed59be67b8bdb6bd97b79a0d7af508126b85881b))
* **validator:** prevent webroot paths in StoragePathValidator ([0159c00](https://github.com/LindemannRock/craft-plugin-base/commit/0159c004a8256952d5f00b9153f6a9fc6204b6d3))

## [5.25.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.24.0...v5.25.0) - 2026-05-21


### Added

* add unified card component for flexible analytics displays ([d8026dc](https://github.com/LindemannRock/craft-plugin-base/commit/d8026dc2a8581fed8c37c5684f4248203135defa))
* **color:** add log source color definitions for improved clarity ([ad8b37e](https://github.com/LindemannRock/craft-plugin-base/commit/ad8b37e2a2d4387a8d70080f17a387916f78e1aa))
* **color:** update color definitions for orange, red, blue, green ([23832db](https://github.com/LindemannRock/craft-plugin-base/commit/23832dbbcad0fd3c0541368074213acd39fe4552))
* **cp-table:** implement single delegated event listener for row actions ([ce1322b](https://github.com/LindemannRock/craft-plugin-base/commit/ce1322b6560556614d8b031065264c6f689edb6f))
* **css:** add neutral row styles for table components ([ee11552](https://github.com/LindemannRock/craft-plugin-base/commit/ee11552c72d15ec3a17c081893c534881f6c6197))
* **css:** add styles for disabled menu items in row-action menu ([ceff47e](https://github.com/LindemannRock/craft-plugin-base/commit/ceff47ed14a84ab72ba94004c457b7fc798a41f9))
* **date-format:** add lrDateFormatConfig function for date format configuration ([f299508](https://github.com/LindemannRock/craft-plugin-base/commit/f299508b9c43fcd00d60d30ec130eb717a6063b6))
* **date-format:** display effective values for overridden date format settings ([41af080](https://github.com/LindemannRock/craft-plugin-base/commit/41af08029069e8c4887159365262f15565848e99))
* **date-format:** implement date format settings and helper methods ([d148776](https://github.com/LindemannRock/craft-plugin-base/commit/d1487768be56a31241e2f5c554502282dd694492))
* **date-range:** add date range settings partial for plugin configuration ([e6eacd0](https://github.com/LindemannRock/craft-plugin-base/commit/e6eacd03e1cd77fe3c483ab7843e2d75828175af))
* **date-range:** add DateRangeSettingsTrait for customizable date range ([ccbcab1](https://github.com/LindemannRock/craft-plugin-base/commit/ccbcab1a6fa6b99108827075d2830b3dadf866a3))
* **date-range:** update default date range selection logic to reflect effective value ([bfedf51](https://github.com/LindemannRock/craft-plugin-base/commit/bfedf512674806d9419e1823c2cb7b9770bbc549))
* **db-helper:** add castToText method for DB-agnostic type casting ([e441ad0](https://github.com/LindemannRock/craft-plugin-base/commit/e441ad062b82c49167ac4dd43daac55b6db3ce8d))
* **db:** add support for Craft table-prefix syntax in jsonExtract ([4bd5949](https://github.com/LindemannRock/craft-plugin-base/commit/4bd594961ab6948d42fc6cd8e57a721ea546826f))
* **db:** enhance jsonExtract to support Craft table-prefix syntax ([f2394c6](https://github.com/LindemannRock/craft-plugin-base/commit/f2394c67ad0c0a8c2c45aae223944464057b262a))
* **export:** add configKey to export format settings for better configuration management ([d2bc827](https://github.com/LindemannRock/craft-plugin-base/commit/d2bc827310442ead93c7c7b66820149793b7915e))
* **export:** add content-only methods for CSV and Excel exports ([044c319](https://github.com/LindemannRock/craft-plugin-base/commit/044c31925446f65a695f57bc200222ca4dba4f3f))
* **export:** add export format settings partial for plugin configuration ([ddf6f70](https://github.com/LindemannRock/craft-plugin-base/commit/ddf6f70dafd3957f3ceaf78e2a2d2d16fed80294))
* **export:** add ExportFormatSettingsTrait for customizable export options ([8665c1e](https://github.com/LindemannRock/craft-plugin-base/commit/8665c1e2681830ca27b28ad174ae1a1ac5f46265))
* **export:** add shortKey to export format settings for better configuration ([4f37c31](https://github.com/LindemannRock/craft-plugin-base/commit/4f37c31659a6cb6a037f0320dee6d1085637bb18))
* **export:** enhance export configuration resolution with layered approach ([ab34320](https://github.com/LindemannRock/craft-plugin-base/commit/ab34320d401d3dfbf0bb376894c51869febee517))
* **export:** implement config cache for export settings retrieval ([1c66e23](https://github.com/LindemannRock/craft-plugin-base/commit/1c66e2328052a398567b5b1f4dd0b14e5fed31ae))
* **geo-settings:** add geo detection provider settings partial with dynamic UI updates ([f7b1ee7](https://github.com/LindemannRock/craft-plugin-base/commit/f7b1ee761099cb1ed7ff08aac354970b741fab31))
* **geo-settings:** add GeoSettingsTrait for centralized validation and labels ([598c951](https://github.com/LindemannRock/craft-plugin-base/commit/598c951b820f41f67f4002e1798516f506c46ab8))
* **i18n:** add base plugin overrides translations for multiple languages ([ee9241c](https://github.com/LindemannRock/craft-plugin-base/commit/ee9241c2fd8c2cad9d91706144bedecc722895ae))
* **i18n:** add date format settings translations for multiple languages ([b6fc81b](https://github.com/LindemannRock/craft-plugin-base/commit/b6fc81b5d820febf79289fa7a4ff641099f8ea86))
* **i18n:** add IP hash salt error messages in multiple languages ([09b9975](https://github.com/LindemannRock/craft-plugin-base/commit/09b99756498a6b97792c9674f2a85534871c02a0))
* **i18n:** add new date range and export format translations ([3152e63](https://github.com/LindemannRock/craft-plugin-base/commit/3152e6332733c6fb2a4eb09315b7b548ca77b837))
* **i18n:** add schedule options translations for multiple languages ([85c796f](https://github.com/LindemannRock/craft-plugin-base/commit/85c796ff1e72f0c0471c9145b3093c730f9696c0))
* **i18n:** add translation issue template for reporting translation problems ([e4e1a60](https://github.com/LindemannRock/craft-plugin-base/commit/e4e1a60392482105e35b28fec4530bd0b9d314c0))
* **i18n:** add translations for 'Items Per Page' in multiple languages ([e452ba9](https://github.com/LindemannRock/craft-plugin-base/commit/e452ba9f9cb859d6288033fd72679390c24aac02))
* **i18n:** update geo provider settings references in translations ([d9b67e1](https://github.com/LindemannRock/craft-plugin-base/commit/d9b67e1ede5ff3e310cda6dd8d5cd79742979740))
* **i18n:** update translations for various languages with new plugin settings ([c59ae6e](https://github.com/LindemannRock/craft-plugin-base/commit/c59ae6e453d806b783f7faf04ddda31defea03db))
* **i18n:** update translations to include global customization instructions ([deaa105](https://github.com/LindemannRock/craft-plugin-base/commit/deaa105b162c5e19f63e2513d8d826573a7f6ccd))
* **import-history:** add import history partial with clear logs functionality ([1422680](https://github.com/LindemannRock/craft-plugin-base/commit/14226807f1683ae56470a0aae2504b9167773736))
* **import-history:** add import history partial with clear logs functionality ([1d5d793](https://github.com/LindemannRock/craft-plugin-base/commit/1d5d793fa04085a3e373aa12ebd83c41b4448db1))
* **ip-salt-error:** enhance warning info-box for missing IP hash salt ([c1badd5](https://github.com/LindemannRock/craft-plugin-base/commit/c1badd5042d235b7dc5bc9b79463b50f2c6346a6))
* **items-per-page:** add Items Per Page field partial for plugin settings ([26c9b4c](https://github.com/LindemannRock/craft-plugin-base/commit/26c9b4c21ad12c4aa4da2fa715afd1c4af3c4227))
* **items-per-page:** add ItemsPerPageSettingsTrait for consistent paging control ([f576032](https://github.com/LindemannRock/craft-plugin-base/commit/f576032ec63b508ca11059acaf6965a9eba6fef4))
* **log-level:** add LogLevelSettingsTrait for standardized log level management ([64b5136](https://github.com/LindemannRock/craft-plugin-base/commit/64b51364c04d23a543e1f7472244526485158055))
* **log-level:** add styles for log level rows with color set integration ([1f26bbd](https://github.com/LindemannRock/craft-plugin-base/commit/1f26bbd2c4cdb372cceff866e7a838e894f25216))
* **log-level:** add styles for log level rows with dynamic tinting ([4239510](https://github.com/LindemannRock/craft-plugin-base/commit/42395104bf6023d5c260e909c20ffc9fd6fdacaf))
* make isDangerousValue public for broader access ([6e694f6](https://github.com/LindemannRock/craft-plugin-base/commit/6e694f63632f23456833de2d498bb8a948d094b7))
* **overrides:** add support for dateRange and exports sub-partials in base overrides ([8ebde81](https://github.com/LindemannRock/craft-plugin-base/commit/8ebde812e366892679b6be21dc70dba4c16e8475))
* **overrides:** implement base plugin overrides partial for date formatting settings ([ea32ade](https://github.com/LindemannRock/craft-plugin-base/commit/ea32ade8fa665dc253f62c251e975259b1784083))
* **row-actions:** add support for disabled menu items with tooltip text ([781cd99](https://github.com/LindemannRock/craft-plugin-base/commit/781cd99e3def3374fbb16f493643dc20f6837772))
* **schedule:** add ScheduleHelper for recurring-job scheduling ([6890036](https://github.com/LindemannRock/craft-plugin-base/commit/68900366103a2551e06937406319442fc6c3e5f4))
* **secret-reveal:** add one-time secret reveal component with copy functionality ([aae0237](https://github.com/LindemannRock/craft-plugin-base/commit/aae02372e139606a83e47e7cc0b02d1c60cdc59f))
* **settings:** add various settings partials for plugin configuration ([7e610f0](https://github.com/LindemannRock/craft-plugin-base/commit/7e610f033ae2816dcae1fa0a320b4989ecd39882))
* **testing:** add integration testing framework and bootstrap files ([781b433](https://github.com/LindemannRock/craft-plugin-base/commit/781b433db234fdc14d7bbefba3004bfa8eabb157))
* **testing:** add StubConsoleRequest and StubWebRequest for testing ([09d2a0d](https://github.com/LindemannRock/craft-plugin-base/commit/09d2a0df142e38db41b47f4048c47637afa1c71a))
* **tests:** add integration tests for various helper classes ([1bbf887](https://github.com/LindemannRock/craft-plugin-base/commit/1bbf887c729169e540ccaf0826b487d24865e154))
* **traits:** add PluginNameSettingsTrait for centralized plugin name management ([b2edeae](https://github.com/LindemannRock/craft-plugin-base/commit/b2edeaee3c1a8609cffd30277ace30d3b3cb5025))


### Fixed

* **cp-table:** update new button permission check logic ([407ee5b](https://github.com/LindemannRock/craft-plugin-base/commit/407ee5bda7c05235621a42c75f4ef4d2f3e74214))
* **i18n:** align 43 cross-plugin shared translations + IT "API Key" translation ([f365126](https://github.com/LindemannRock/craft-plugin-base/commit/f3651266c1c9a696d52d79a480e2b108a231ac3c))
* **i18n:** correct German and Japanese translations for backup messages ([12aca74](https://github.com/LindemannRock/craft-plugin-base/commit/12aca748d735386684884bf9014c576cc6df5954))
* **i18n:** correct German translation for "All time" string ([ed6b1c8](https://github.com/LindemannRock/craft-plugin-base/commit/ed6b1c8eac3efa3974cb9f60ebb7a4793afe3f8b))
* **i18n:** correct Italian translation for API Key string ([166a1fb](https://github.com/LindemannRock/craft-plugin-base/commit/166a1fb7a02c5ec2a996a87fc452d9197116b923))
* **traits:** update version annotation for DateFormatSettingsTrait ([bb59cd0](https://github.com/LindemannRock/craft-plugin-base/commit/bb59cd0fac423b8a3f61d1296f307caf09289a5d))
* update info-box message to include global customization instructions ([9c2dc14](https://github.com/LindemannRock/craft-plugin-base/commit/9c2dc14517393ad82bc1549f27fbf3390e72bcd4))

## [5.24.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.23.0...v5.24.0) - 2026-05-09


### Features

* **boolean-helper:** add BooleanHelper for normalizing boolean-like values ([9a3578f](https://github.com/LindemannRock/craft-plugin-base/commit/9a3578f7b6a009b2e82908c420f4175d1e9a5e23))

## [5.23.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.22.0...v5.23.0) - 2026-05-06


### Features

* add plugin credit functionality with dynamic color changes ([a5a2915](https://github.com/LindemannRock/craft-plugin-base/commit/a5a29152f6ee4b4be7649007b611b3f19d76b50f))
* **DateFormatHelper:** improve date handling with timezone support ([7691f31](https://github.com/LindemannRock/craft-plugin-base/commit/7691f3160c93be3042c90db4da75b6f8381503d4))
* **DateRangeHelper:** enhance default date range retrieval logic ([1d5a017](https://github.com/LindemannRock/craft-plugin-base/commit/1d5a017d43d6b5a26f0764f4d3468009cc093699))
* **db:** support nested JSON extraction with array paths in jsonExtract ([3f64556](https://github.com/LindemannRock/craft-plugin-base/commit/3f64556dce41481e10edb32eddd277b2fedf2b39))
* **helper:** add method to retrieve Redis cache with logging for misconfigurations ([0a0c31f](https://github.com/LindemannRock/craft-plugin-base/commit/0a0c31f619200ae11b51ce607b244c19837f6ffb))
* **translations:** add geo provider settings translations for multiple languages ([94b1164](https://github.com/LindemannRock/craft-plugin-base/commit/94b1164392006cbd919468460e29ec296377638c))


### Bug Fixes

* **ExportHelper:** handle failure in reading generated ZIP file ([64c9e43](https://github.com/LindemannRock/craft-plugin-base/commit/64c9e43aea85fc2beb33bbbfa8b14d4db15243ad))

## [5.22.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.21.0...v5.22.0) - 2026-04-25


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

## [5.21.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.20.1...v5.21.0) - 2026-04-02


### Features

* **translations:** add Arabic, Spanish, French, and Dutch translations for plugin messages ([0188418](https://github.com/LindemannRock/craft-plugin-base/commit/0188418b4f53ae5415914a003c033bed904d0db9))
* **translations:** add Danish, Italian, Japanese, Norwegian, Portuguese, and Swedish translations for plugin messages ([53147eb](https://github.com/LindemannRock/craft-plugin-base/commit/53147eb491eb344eb76b8434f4b52de6d2c60874))
* **translations:** add German translations for plugin messages ([a1767d1](https://github.com/LindemannRock/craft-plugin-base/commit/a1767d192a66fe7bd2533b3df930a5801aef0258))


### Bug Fixes

* **base:** register translation category for shared UI copy ([06c6419](https://github.com/LindemannRock/craft-plugin-base/commit/06c6419ac6cc304c810d1a6e870dbfa4b4a2ef6d))
* **ExportHelper:** update error message translation for export failure ([d5ca474](https://github.com/LindemannRock/craft-plugin-base/commit/d5ca4748d3aab6542dc7fcb184c063df2f395c58))
* **install-experience:** translate static text in install experience ([5ffd896](https://github.com/LindemannRock/craft-plugin-base/commit/5ffd89655ea035409f207c932993c58abcc787c8))

## [5.20.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.20.0...v5.20.1) - 2026-03-26


### Bug Fixes

* **badge:** add nowrap style to status label spans ([5b3e1d6](https://github.com/LindemannRock/craft-plugin-base/commit/5b3e1d60a6b0d41addb2a5a83fd3bba391bbc265))

## [5.20.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.19.1...v5.20.0) - 2026-03-17


### Features

* **AnalyticsIpHelper:** add IP preprocessing and anonymization helper class ([e71a144](https://github.com/LindemannRock/craft-plugin-base/commit/e71a144014e143a0975e4dc240d945513f9a1d4b))

## [5.19.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.19.0...v5.19.1) - 2026-03-17


### Bug Fixes

* **PluginHelper:** simplify global variable registration in Twig ([b988d8b](https://github.com/LindemannRock/craft-plugin-base/commit/b988d8bf3a2bc7aa34451cc69346b31e72d109aa))

## [5.19.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.18.0...v5.19.0) - 2026-03-17


### Features

* add import history partial with parameters and structure ([ab1e03b](https://github.com/LindemannRock/craft-plugin-base/commit/ab1e03b4a0fcdbe3de8f488c33f8321245ced836))
* **helpers:** add JsonHelper class for safe JSON encoding ([e6585f8](https://github.com/LindemannRock/craft-plugin-base/commit/e6585f85a78e711fc40885e07f6c88e86d80a563))
* **install-experience:** add install experience assets and functionality ([e6b6f02](https://github.com/LindemannRock/craft-plugin-base/commit/e6b6f022f3583cd8b03155a029ed95e2176289c0))


### Miscellaneous Chores

* **assets:** add package.json for asset management and build scripts ([dea148c](https://github.com/LindemannRock/craft-plugin-base/commit/dea148c4c14da9e78b882c0f6be3b57947a33830))
* **package:** update dependencies and add build script ([91f847c](https://github.com/LindemannRock/craft-plugin-base/commit/91f847c50be5f9475f4b54cea1dc98d82860ff33))

## [5.18.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.17.0...v5.18.0) - 2026-03-04


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

## [5.17.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.16.0...v5.17.0) - 2026-02-22


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

## [5.16.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.15.0...v5.16.0) - 2026-02-17


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

## [5.15.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.14.0...v5.15.0) - 2026-02-07


### Features

* Add DateFormatHelper and DbHelper for date and database operations ([34eb74b](https://github.com/LindemannRock/craft-plugin-base/commit/34eb74b5c92610f1164b74a329b1072b435e1da9))
* **DbHelper:** enhance jsonExtract and add groupConcat for DB-agnostic SQL ([25fbfb8](https://github.com/LindemannRock/craft-plugin-base/commit/25fbfb836cf4e71fbbc71ebae7e785e0c294958d))

## [5.14.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.13.0...v5.14.0) - 2026-02-05


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

## [5.13.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.12.0...v5.13.0) - 2026-01-28


### Features

* **analytics-panel:** add new analytics panel component ([575ed24](https://github.com/LindemannRock/craft-plugin-base/commit/575ed248717e22e8cbd2da24a81243a016e333a8))
* **datetime:** enhance DateTimeHelper and DateTimeExtension with UTC handling ([70c7039](https://github.com/LindemannRock/craft-plugin-base/commit/70c7039247398fd63e396839ee9a90a46c6cf65f))
* **export:** add format aliases for Excel export compatibility ([ff71856](https://github.com/LindemannRock/craft-plugin-base/commit/ff718560a3953fc8e86a98469fa0bc454963ea99))
* **phone-input:** add reusable phone input component with country code detection ([bb74180](https://github.com/LindemannRock/craft-plugin-base/commit/bb74180583c195fdb22dcf5a0513dafeb7bb6aa8))
* **table:** enhance bulk actions and item selection logic ([575ed24](https://github.com/LindemannRock/craft-plugin-base/commit/575ed248717e22e8cbd2da24a81243a016e333a8))

## [5.12.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.11.0...v5.12.0) - 2026-01-28


### Features

* add Geo Twig extension and health status colors to ColorHelper ([2e40f7e](https://github.com/LindemannRock/craft-plugin-base/commit/2e40f7edecafb54b4ee64fe071964d4349158dd3))


### Bug Fixes

* add phpspreadsheet ^5.0 support for Craft 5.9 compatibility ([4e4c596](https://github.com/LindemannRock/craft-plugin-base/commit/4e4c59688fcbc236b2da569340cc053f39adf806))

## [5.11.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.10.1...v5.11.0) - 2026-01-27


### Features

* add message status colors to ColorHelper ([13a8bfc](https://github.com/LindemannRock/craft-plugin-base/commit/13a8bfc9bfe63b33ad123600e8ea64214b919626))

## [5.10.1](https://github.com/LindemannRock/craft-plugin-base/compare/v5.10.0...v5.10.1) - 2026-01-26


### Bug Fixes

* remove premature getTwig call in Twig extension registration ([36f07f4](https://github.com/LindemannRock/craft-plugin-base/commit/36f07f458a1fabfce95e60b05dc2f1d1c4256972))

## [5.10.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.9.0...v5.10.0) - 2026-01-26


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

## [5.9.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.8.0...v5.9.0) - 2026-01-24


### Features

* add export format options and enhance sidebar content in templates ([bd9e5b8](https://github.com/LindemannRock/craft-plugin-base/commit/bd9e5b809e5b5fcb0c1e8f19ca6480f0f1932953))

## [5.8.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.7.0...v5.8.0) - 2026-01-24


### Features

* add centralized helpers, reusable CP layouts, and export functionality ([1c1eb7d](https://github.com/LindemannRock/craft-plugin-base/commit/1c1eb7d50094038870c5902dfbabf3537fad1e07))

## [5.7.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.6.0...v5.7.0) - 2026-01-21


### Features

* add phone dial code utilities to GeoHelper class ([80923af](https://github.com/LindemannRock/craft-plugin-base/commit/80923afce378445b3a2f7d629265605f7478bb6d))
* enhance info-box component with additional options and styling ([855e1fc](https://github.com/LindemannRock/craft-plugin-base/commit/855e1fcd24b8d6d09bf224dbe0f7142a7fcad842))
* implement geo IP lookup and provider configuration classes ([9ffc6a6](https://github.com/LindemannRock/craft-plugin-base/commit/9ffc6a63c6f42b3d9a3ca62da11fde66a38ca080))

## [5.6.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.5.0...v5.6.0) - 2026-01-18


### Features

* add stretch option to info-box component for full-width display ([25570cb](https://github.com/LindemannRock/craft-plugin-base/commit/25570cbaaa20a700c5ad057ff3df0f938b870073))
* enhance info-box component with margin and background options ([bf1eb6a](https://github.com/LindemannRock/craft-plugin-base/commit/bf1eb6aa9897dcc5b6d8a62996a5d20f7c4ce7a6))

## [5.5.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.4.0...v5.5.0) - 2026-01-16


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

## [5.4.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.3.0...v5.4.0) - 2026-01-12


### Features

* enhance getDisplayName method to preserve acronyms during singularization ([f664813](https://github.com/LindemannRock/craft-plugin-base/commit/f66481306104ebb972e0701ccb1cef7b00cb4f7d))

## [5.3.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.2.0...v5.3.0) - 2026-01-10


### Features

* add GeoHelper for country code to name conversion ([d0ec0b1](https://github.com/LindemannRock/craft-plugin-base/commit/d0ec0b12bdae04b2042488a732c0e8882ad343ec))


### Miscellaneous Chores

* update README to include GeoHelper usage and functionality ([949d764](https://github.com/LindemannRock/craft-plugin-base/commit/949d764c767c5f02007828e21ce9fcc93c92b803))

## [5.2.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.1.0...v5.2.0) - 2026-01-06


### Features

* register global variable directly via Twig in PluginHelper ([35e31b2](https://github.com/LindemannRock/craft-plugin-base/commit/35e31b2fcdacb90b1537ee48e150d9d3d8acc76c))

## [5.1.0](https://github.com/LindemannRock/craft-plugin-base/compare/v5.0.0...v5.1.0) - 2026-01-05


### Features

* add floatFields method for type conversion in SettingsPersistenceTrait ([180b8b2](https://github.com/LindemannRock/craft-plugin-base/commit/180b8b2694a1134d2e85c9cff98facada6e07d56))

## 5.0.0 - 2026-01-05


### Features

* initial LindemannRock Plugin Base implementation ([947840f](https://github.com/LindemannRock/craft-plugin-base/commit/947840f5c1861781381edec8d3ca79dcca72a27d))
