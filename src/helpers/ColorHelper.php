<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Color Helper
 *
 * Centralized color definitions for badges, filters, and status indicators.
 * Provides universal color sets and allows plugins to register their own.
 *
 * Each color entry contains:
 * - class: CSS class name (teal, gray, orange, red, blue, pink, etc.)
 * - color: Solid hex color for dots/indicators
 * - rgb: RGB values for semi-transparent backgrounds
 * - text: Dark text color for readability
 *
 * Usage:
 * ```php
 * use lindemannrock\base\helpers\ColorHelper;
 *
 * // Get a palette color by name
 * $teal = ColorHelper::getPaletteColor('teal');
 * // Returns: ['class' => 'teal', 'color' => '#14b8a6', 'rgb' => '20, 184, 166', 'text' => '#115e59']
 *
 * // Get specific color from a set
 * $enabledColor = ColorHelper::getSetColor('status', 'enabled');
 *
 * // Register plugin-specific colors using palette colors
 * ColorHelper::registerColorSet('myStatus', [
 *     'active' => ColorHelper::getPaletteColor('teal'),
 *     'inactive' => ColorHelper::getPaletteColor('gray'),
 * ]);
 * ```
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class ColorHelper
{
    /**
     * Neutral/unselected color used for filter items that are not selected
     */
    public const NEUTRAL_COLOR = '#aab6c1';

    /**
     * Default color for unknown values
     */
    public const DEFAULT_COLOR = [
        'color' => '#9aa5b1',
        'rgb' => '154, 165, 177',
        'text' => '#374151',
    ];

    /**
     * Color palette with CSS classes and hex values
     *
     * Includes Craft's Tailwind-based colors (500 for dot, 800/900 for text).
     * Custom colors can be added here - they just won't have a CSS class.
     *
     * @var array<string, array{class: string, color: string, rgb: string, text: string}>
     */
    public const PALETTE = [
        'teal' => [
            'class' => 'teal',
            'color' => '#14b8a6',
            'rgb' => '20, 184, 166',
            'text' => '#115e59',
        ],
        'cyan' => [
            'class' => 'cyan',
            'color' => '#06b6d4',
            'rgb' => '6, 182, 212',
            'text' => '#155e75',
        ],
        'gray' => [
            'class' => 'gray',
            'color' => '#6b7280',
            'rgb' => '107, 114, 128',
            'text' => '#374151',
        ],
        'orange' => [
            'class' => 'orange',
            'color' => '#f97316',
            'rgb' => '249, 115, 22',
            'text' => '#9a3412',
        ],
        'red' => [
            'class' => 'red',
            'color' => '#ef4444',
            'rgb' => '239, 68, 68',
            'text' => '#7f1d1d',
        ],
        'blue' => [
            'class' => 'blue',
            'color' => '#3b82f6',
            'rgb' => '59, 130, 246',
            'text' => '#1e3a8a',
        ],
        'pink' => [
            'class' => 'pink',
            'color' => '#ec4899',
            'rgb' => '236, 72, 153',
            'text' => '#9d174d',
        ],
        'purple' => [
            'class' => 'purple',
            'color' => '#a855f7',
            'rgb' => '168, 85, 247',
            'text' => '#6b21a8',
        ],
        'green' => [
            'class' => 'green',
            'color' => '#22c55e',
            'rgb' => '34, 197, 94',
            'text' => '#166534',
        ],
        'yellow' => [
            'class' => 'yellow',
            'color' => '#eab308',
            'rgb' => '234, 179, 8',
            'text' => '#854d0e',
        ],
        'amber' => [
            'class' => 'amber',
            'color' => '#f59e0b',
            'rgb' => '245, 158, 11',
            'text' => '#92400e',
        ],
        'emerald' => [
            'class' => 'emerald',
            'color' => '#10b981',
            'rgb' => '16, 185, 129',
            'text' => '#065f46',
        ],
        'indigo' => [
            'class' => 'indigo',
            'color' => '#6366f1',
            'rgb' => '99, 102, 241',
            'text' => '#3730a3',
        ],
        'violet' => [
            'class' => 'violet',
            'color' => '#8b5cf6',
            'rgb' => '139, 92, 246',
            'text' => '#5b21b6',
        ],
        'fuchsia' => [
            'class' => 'fuchsia',
            'color' => '#d946ef',
            'rgb' => '217, 70, 239',
            'text' => '#86198f',
        ],
        'rose' => [
            'class' => 'rose',
            'color' => '#f43f5e',
            'rgb' => '244, 63, 94',
            'text' => '#9f1239',
        ],
        'lime' => [
            'class' => 'lime',
            'color' => '#84cc16',
            'rgb' => '132, 204, 22',
            'text' => '#3f6212',
        ],
        'sky' => [
            'class' => 'sky',
            'color' => '#0ea5e9',
            'rgb' => '14, 165, 233',
            'text' => '#075985',
        ],
    ];

    /**
     * @var array<string, array<string, array{class?: string, dot?: string, color: string, rgb: string, text: string}>> Color sets
     */
    private static array $colorSets = [];

    /**
     * Initialize default color sets
     */
    private static bool $initialized = false;

    /**
     * Initialize default color sets using CRAFT_COLORS
     */
    private static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$colorSets = [
            // =================================================================
            // STATUS - matches Craft's default status classes
            // class = label background color, dot = inner status dot class
            // =================================================================
            'status' => [
                'enabled' => array_merge(self::PALETTE['teal'], ['dot' => 'enabled']),
                'disabled' => array_merge(self::PALETTE['gray'], ['dot' => 'disabled']),
                'pending' => array_merge(self::PALETTE['orange'], ['dot' => 'pending']),
                'expired' => array_merge(self::PALETTE['red'], ['dot' => 'expired']),
                'live' => array_merge(self::PALETTE['teal'], ['dot' => 'live']),
                'on' => array_merge(self::PALETTE['green'], ['dot' => 'on']),
                'off' => array_merge(self::PALETTE['red'], ['dot' => 'off']),
            ],

            // =================================================================
            // YES/NO - yes/true = green, no/false = red
            // =================================================================
            'yesNo' => [
                'yes' => self::PALETTE['green'],
                'no' => self::PALETTE['red'],
                'true' => self::PALETTE['green'],
                'false' => self::PALETTE['red'],
            ],

            // =================================================================
            // HANDLED STATUS - yes/true = green, no/false = red
            // =================================================================
            'handled' => [
                'yes' => self::PALETTE['green'],
                'no' => self::PALETTE['red'],
                'true' => self::PALETTE['green'],
                'false' => self::PALETTE['red'],
            ],

            // =================================================================
            // CONFIG SOURCE - config = orange, database = blue
            // =================================================================
            'configSource' => [
                'config' => self::PALETTE['orange'],
                'database' => self::PALETTE['blue'],
            ],

            // =================================================================
            // ENVIRONMENT TYPE - development = pink, staging = orange, production = blue
            // =================================================================
            'environmentType' => [
                'development' => self::PALETTE['orange'],
                'staging' => self::PALETTE['purple'],
                'production' => self::PALETTE['blue'],
            ],

            // =================================================================
            // PRIORITY LEVELS - low = gray, normal = blue, high = orange, critical = red
            // =================================================================
            'priority' => [
                'low' => self::PALETTE['gray'],
                'normal' => self::PALETTE['blue'],
                'high' => self::PALETTE['orange'],
                'critical' => self::PALETTE['red'],
            ],

            // =================================================================
            // HTTP STATUS - success = green, redirect = blue, client_error = rose, server_error = red
            // =================================================================
            'httpStatus' => [
                'success' => self::PALETTE['green'],
                'redirect' => self::PALETTE['blue'],
                'client_error' => self::PALETTE['rose'],
                'server_error' => self::PALETTE['red'],
            ],

            // =================================================================
            // LOG LEVEL - debug = purple, info = sky, warning = orange, error = red
            // =================================================================
            'logLevel' => [
                'debug' => self::PALETTE['purple'],
                'info' => self::PALETTE['sky'],
                'warning' => self::PALETTE['orange'],
                'error' => self::PALETTE['red'],
            ],

            // =================================================================
            // PLUGIN STATUS - active = teal, disabled = gray, notInstalled = red
            // =================================================================
            'pluginStatus' => [
                'active' => self::PALETTE['teal'],
                'disabled' => self::PALETTE['gray'],
                'notInstalled' => self::PALETTE['red'],
            ],

            // =================================================================
            // EXPORT/JOB STATUS - pending = orange, processing = blue, completed = teal, failed = red
            // =================================================================
            'exportStatus' => [
                'pending' => self::PALETTE['orange'],
                'processing' => self::PALETTE['blue'],
                'completed' => self::PALETTE['teal'],
                'failed' => self::PALETTE['red'],
            ],

            // =================================================================
            // TRIGGER TYPE - manual = gray, scheduled = purple, api = indigo
            // =================================================================
            'triggerType' => [
                'manual' => self::PALETTE['gray'],
                'scheduled' => self::PALETTE['purple'],
                'api' => self::PALETTE['indigo'],
            ],

            // =================================================================
            // EXPORT FORMAT - xlsx = green, csv = cyan, json = amber
            // =================================================================
            'exportFormat' => [
                'xlsx' => self::PALETTE['green'],
                'csv' => self::PALETTE['cyan'],
                'json' => self::PALETTE['amber'],
            ],
        ];

        self::$initialized = true;
    }

    /**
     * Get a color from the palette by name
     *
     * @param string $name The color name (teal, gray, orange, red, blue, pink, etc.)
     * @return array{class: string, color: string, rgb: string, text: string}
     * @since 5.8.0
     */
    public static function getPaletteColor(string $name): array
    {
        return self::PALETTE[$name] ?? self::DEFAULT_COLOR;
    }

    /**
     * Get all available palette color names
     *
     * @return string[]
     * @since 5.8.0
     */
    public static function getPaletteColorNames(): array
    {
        return array_keys(self::PALETTE);
    }

    /**
     * Get a complete color set by name
     *
     * @param string $setName Name of the color set
     * @return array<string, array{color: string, rgb: string, text: string}> Color set or empty array
     * @since 5.8.0
     */
    public static function getColorSet(string $setName): array
    {
        self::initialize();
        return self::$colorSets[$setName] ?? [];
    }

    /**
     * Get a specific color from a set
     *
     * @param string $setName Name of the color set
     * @param string $key Key within the color set
     * @return array{color: string, rgb: string, text: string} Color definition or default
     * @since 5.8.0
     */
    public static function getSetColor(string $setName, string $key): array
    {
        self::initialize();
        return self::$colorSets[$setName][$key] ?? self::DEFAULT_COLOR;
    }

    /**
     * Get the neutral/unselected color
     *
     * Used for filter items that are not currently selected
     *
     * @return string Hex color code
     * @since 5.8.0
     */
    public static function getNeutralColor(): string
    {
        return self::NEUTRAL_COLOR;
    }

    /**
     * Get default color for unknown values
     *
     * @return array{color: string, rgb: string, text: string}
     * @since 5.8.0
     */
    public static function getDefaultColor(): array
    {
        return self::DEFAULT_COLOR;
    }

    /**
     * Check if a color set exists
     *
     * @param string $setName Name of the color set
     * @return bool
     * @since 5.8.0
     */
    public static function hasColorSet(string $setName): bool
    {
        self::initialize();
        return isset(self::$colorSets[$setName]);
    }

    /**
     * Get all available color set names
     *
     * @return string[]
     * @since 5.8.0
     */
    public static function getAvailableColorSets(): array
    {
        self::initialize();
        return array_keys(self::$colorSets);
    }

    /**
     * Register a custom color set at runtime
     *
     * Allows plugins to add their own color sets via PluginHelper::bootstrap()
     * or directly via this method.
     *
     * @param string $setName Name of the color set
     * @param array<string, array{class?: string, color: string, rgb: string, text: string}> $colors Color definitions
     * @since 5.8.0
     */
    public static function registerColorSet(string $setName, array $colors): void
    {
        self::initialize();
        self::$colorSets[$setName] = $colors;
    }

    /**
     * Get color for filter display
     *
     * Returns the actual color if selected, or neutral color if not.
     *
     * @param string $setName Name of the color set
     * @param string $value Filter value
     * @param string|null $currentFilter Currently selected filter value
     * @return string Hex color code
     * @since 5.8.0
     */
    public static function getFilterColor(string $setName, string $value, ?string $currentFilter): string
    {
        self::initialize();
        if ($currentFilter === $value) {
            $color = self::$colorSets[$setName][$value] ?? null;
            return $color['color'] ?? self::DEFAULT_COLOR['color'];
        }

        return self::NEUTRAL_COLOR;
    }
}
