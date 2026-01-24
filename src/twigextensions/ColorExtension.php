<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\ColorHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Color Twig Extension
 *
 * Provides Twig functions for accessing centralized color definitions.
 *
 * Functions:
 * ```twig
 * {# Get a palette color by name #}
 * {% set teal = lrPaletteColor('teal') %}
 * {# Returns: {class: 'teal', color: '#14b8a6', rgb: '20, 184, 166', text: '#115e59'} #}
 *
 * {# Get entire color set #}
 * {% set colors = lrColorSet('status') %}
 * {# Returns: {enabled: {...}, disabled: {...}, ...} #}
 *
 * {# Get specific color from a set #}
 * {% set enabledColor = lrSetColor('status', 'enabled') %}
 * {# Returns: {class: 'teal', color: '#14b8a6', ...} #}
 *
 * {# Get neutral/unselected color #}
 * {% set neutral = lrNeutralColor() %}
 * {# Returns: '#aab6c1' #}
 *
 * {# Get color for filter display (shows color if selected, neutral if not) #}
 * {% set filterColor = lrFilterColor('status', 'enabled', currentFilter) %}
 * {# Returns: '#14b8a6' if currentFilter == 'enabled', else '#aab6c1' #}
 *
 * {# Check if color set exists #}
 * {% if lrHasColorSet('customSet') %}...{% endif %}
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class ColorExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Color';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            // Palette colors
            new TwigFunction('lrPaletteColor', [ColorHelper::class, 'getPaletteColor']),
            new TwigFunction('lrPaletteColorNames', [ColorHelper::class, 'getPaletteColorNames']),
            // Color sets
            new TwigFunction('lrColorSet', [ColorHelper::class, 'getColorSet']),
            new TwigFunction('lrSetColor', [ColorHelper::class, 'getSetColor']),
            new TwigFunction('lrHasColorSet', [ColorHelper::class, 'hasColorSet']),
            new TwigFunction('lrAvailableColorSets', [ColorHelper::class, 'getAvailableColorSets']),
            // Utility
            new TwigFunction('lrNeutralColor', [ColorHelper::class, 'getNeutralColor']),
            new TwigFunction('lrDefaultColor', [ColorHelper::class, 'getDefaultColor']),
            new TwigFunction('lrFilterColor', [ColorHelper::class, 'getFilterColor']),
            // Backwards compatibility (deprecated)
            new TwigFunction('lrColor', [ColorHelper::class, 'getSetColor']),
        ];
    }
}
