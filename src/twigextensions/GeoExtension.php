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
 * {# Get dial code options for phone fields #}
 * {% for option in lrDialCodes() %}
 *     <option value="{{ option.value }}">{{ option.label }}</option>
 * {% endfor %}
 *
 * {# Get dial code for a country #}
 * {{ lrDialCode('US') }}  {# +1 #}
 * ```
 *
 * @author LindemannRock
 * @since 5.11.0
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
            new TwigFunction('lrDialCodes', [GeoHelper::class, 'getCountryDialCodeOptions']),
            new TwigFunction('lrDialCode', [GeoHelper::class, 'getDialCode']),
        ];
    }
}
