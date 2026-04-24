<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\LabelHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Label Twig Extension
 *
 * Provides Twig filters for formatting user-facing labels.
 *
 * Filters:
 * ```twig
 * {{ field.label|lrShortLabel }}          {# strips numbering and truncates to 60 chars #}
 * {{ field.label|lrShortLabel(80) }}      {# custom max length #}
 * ```
 *
 * @since 5.22.0
 */
class LabelExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Label';
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('lrShortLabel', [LabelHelper::class, 'shorten']),
        ];
    }
}
