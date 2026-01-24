<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\twigextensions;

use craft\base\PluginInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Plugin Name Twig Extension
 *
 * Provides centralized access to plugin name variations in Twig templates.
 * Generic version that works with any LindemannRock plugin.
 *
 * Usage in templates:
 * ```twig
 * {{ redirectHelper.displayName }}             {# "Redirect" #}
 * {{ redirectHelper.pluralDisplayName }}       {# "Redirects" #}
 * {{ redirectHelper.fullName }}                {# "Redirect Manager" #}
 * {{ redirectHelper.lowerDisplayName }}        {# "redirect" #}
 * {{ redirectHelper.pluralLowerDisplayName }}  {# "redirects" #}
 * ```
 *
 * IMPORTANT: Each plugin should use a UNIQUE variable name to avoid conflicts
 * when multiple plugins are installed:
 * - redirect-manager: 'redirectHelper'
 * - search-manager: 'searchHelper'
 * - icon-manager: 'iconHelper'
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class PluginNameExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var PluginInterface The plugin instance
     */
    private PluginInterface $plugin;

    /**
     * @var string The Twig global variable name
     */
    private string $variableName;

    /**
     * Constructor
     *
     * @param PluginInterface $plugin The plugin instance
     * @param string $variableName The Twig global variable name (e.g., 'redirectHelper')
     * @since 5.0.0
     */
    public function __construct(PluginInterface $plugin, string $variableName)
    {
        $this->plugin = $plugin;
        $this->variableName = $variableName;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->plugin->name . ' - Plugin Name Helper';
    }

    /**
     * @inheritdoc
     */
    public function getGlobals(): array
    {
        return [
            $this->variableName => new PluginNameHelper($this->plugin),
        ];
    }
}
