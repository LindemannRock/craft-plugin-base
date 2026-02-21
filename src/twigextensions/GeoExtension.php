<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\GeoHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Geo Twig Extension
 *
 * Provides Twig functions for country and phone number utilities.
 *
 * Usage:
 * ```twig
 * {# Get all countries for a select field #}
 * {% for code, name in lrCountries() %}
 *     <option value="{{ code }}">{{ name }}</option>
 * {% endfor %}
 *
 * {# Get country name by code #}
 * {{ lrCountryName('US') }}  {# United States #}
 *
 * {# Get all countries with dial codes as structured data #}
 * {% set data = lrCountryDialCodeData() %}
 * {# [{countryCode: 'KW', dialCode: '965', countryName: 'Kuwait'}, ...] #}
 * {% for item in data %}
 *     {{ item.countryCode }} +{{ item.dialCode }} - {{ item.countryName }}
 * {% endfor %}
 *
 * {# Single lookups #}
 * {{ lrDialCode('US') }}              {# 1 #}
 * {{ lrCountryWithDialCode('KW') }}   {# Kuwait (+965) #}
 *
 * {# Validation #}
 * {% if lrValidCountryCode('US') %}...{% endif %}
 * ```
 *
 * @author LindemannRock
 * @since 5.12.0
 */
class GeoExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Geo';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrCountries', [GeoHelper::class, 'getAllCountries']),
            new TwigFunction('lrCountryName', [GeoHelper::class, 'getCountryName']),
            new TwigFunction('lrCountryDialCodeData', [GeoHelper::class, 'getCountryDialCodeData']),
            new TwigFunction('lrDialCode', [GeoHelper::class, 'getDialCode']),
            new TwigFunction('lrCountryWithDialCode', [GeoHelper::class, 'getCountryWithDialCode']),
            new TwigFunction('lrValidCountryCode', [GeoHelper::class, 'isValidCountryCode']),
        ];
    }
}
