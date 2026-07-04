<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use lindemannrock\base\helpers\PluginThemeStyleHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Plugin theme style Twig Extension
 *
 * Exposes plugin-branded CSS custom property helpers to Twig templates.
 *
 * @since 5.34.0
 */
class PluginThemeStyleExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'LindemannRock Plugin Theme Style';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lrPluginHeroCssVars', [PluginThemeStyleHelper::class, 'heroCssVarsFromSvg']),
            new TwigFunction('lrPluginDocsShellCssVars', [PluginThemeStyleHelper::class, 'docsShellCssVarsFromSvg']),
            new TwigFunction('lrPluginDocsCssVars', [PluginThemeStyleHelper::class, 'docsCssVarsFromSvg']),
        ];
    }
}
