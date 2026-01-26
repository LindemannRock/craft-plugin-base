# Changelog

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
