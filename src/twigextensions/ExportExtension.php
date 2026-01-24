<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\ExportHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Export Twig Extension
 *
 * Provides Twig functions for checking export format availability.
 *
 * Usage:
 * ```twig
 * {# Check if format is enabled #}
 * {% if lrExportEnabled('excel') %}
 *     <a href="...">Export as Excel</a>
 * {% endif %}
 *
 * {# Get all enabled formats #}
 * {% for format in lrExportFormats() %}
 *     <a href="{{ url('plugin/export', {format: format}) }}">{{ format|upper }}</a>
 * {% endfor %}
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class ExportExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Export';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrExportEnabled', [ExportHelper::class, 'isFormatEnabled']),
            new TwigFunction('lrExportFormats', [ExportHelper::class, 'getEnabledFormats']),
        ];
    }
}
