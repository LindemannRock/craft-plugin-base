<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\PluginHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Plugin Twig Extension
 *
 * Provides Twig functions for plugin detection and name lookup.
 *
 * Usage:
 * ```twig
 * {# Check if plugin is enabled #}
 * {% if lrPluginEnabled('formie') %}
 *     <p>Formie is available!</p>
 * {% endif %}
 *
 * {# Get plugin display name (respects custom pluginName setting) #}
 * {{ lrPluginName('redirect-manager') }}
 *
 * {# With fallback for missing plugins #}
 * {{ lrPluginName(item.sourcePlugin, item.sourcePlugin|replace({'-': ' '})|title) }}
 * ```
 *
 * @author LindemannRock
 * @since 5.9.0
 */
class PluginExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Plugin';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrPluginEnabled', [PluginHelper::class, 'isPluginEnabled']),
            new TwigFunction('lrPluginName', [PluginHelper::class, 'getPluginName']),
        ];
    }
}
