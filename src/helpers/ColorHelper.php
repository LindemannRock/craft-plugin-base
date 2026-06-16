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
        'class' => 'default',
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
            'color' => '#11a697',
            'rgb' => '17, 166, 151',
            'text' => '#134e4a',
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
            'color' => '#fb923c',
            'rgb' => '251, 146, 60',
            'text' => '#9a3412',
        ],
        'red' => [
            'class' => 'red',
            'color' => '#dc2626',
            'rgb' => '220, 38, 38',
            'text' => '#7f1d1d',
        ],
        'blue' => [
            'class' => 'blue',
            'color' => '#2563eb',
            'rgb' => '37, 99, 235',
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
            'color' => '#16a34a',
            'rgb' => '22, 163, 74',
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
            'color' => '#059669',
            'rgb' => '5, 150, 105',
            'text' => '#064e3b',
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
            // ENVIRONMENT TYPE - development = orange, staging = purple, production = blue
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
                'info' => self::PALETTE['blue'],
                'warning' => self::PALETTE['orange'],
                'error' => self::PALETTE['red'],
            ],

            // =================================================================
            // LOG SOURCE - web = pink, queue = indigo, console = teal,
            //              php-errors = rose, plugin = gray
            // Companion to logLevel; chosen so a source dot never shares a hue
            // with a logLevel dot when both render in the same row.
            // =================================================================
            'logSource' => [
                'web' => self::PALETTE['pink'],
                'queue' => self::PALETTE['indigo'],
                'console' => self::PALETTE['teal'],
                'php-errors' => self::PALETTE['rose'],
                'plugin' => self::PALETTE['gray'],
            ],

            // =================================================================
            // PLUGIN STATUS - active = teal, disabled = gray, notInstalled = red
            // dot uses Craft's semantic classes (enabled/disabled/off) not color names
            // =================================================================
            'pluginStatus' => [
                'active' => array_merge(self::PALETTE['teal'], ['dot' => 'enabled']),
                'disabled' => array_merge(self::PALETTE['gray'], ['dot' => 'disabled']),
                'notInstalled' => array_merge(self::PALETTE['red'], ['dot' => 'off']),
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
            // EXPORT FORMAT - xlsx = lime, csv = cyan, json = purple, zip = amber
            // =================================================================
            'exportFormat' => [
                'xlsx' => self::PALETTE['lime'],
                'csv' => self::PALETTE['cyan'],
                'json' => self::PALETTE['purple'],
                'zip' => self::PALETTE['amber'],
            ],

            // =================================================================
            // MESSAGE STATUS - pending = orange, sent = teal, delivered = green, failed = red
            // =================================================================
            'messageStatus' => [
                'pending' => array_merge(self::PALETTE['orange'], ['dot' => 'pending']),
                'sent' => array_merge(self::PALETTE['teal'], ['dot' => 'enabled']),
                'delivered' => array_merge(self::PALETTE['green'], ['dot' => 'on']),
                'failed' => array_merge(self::PALETTE['red'], ['dot' => 'off']),
            ],

            // =================================================================
            // HEALTH STATUS - ok = green, low = yellow, high = red
            // For health checks, sync status, discrepancy levels, etc.
            // =================================================================
            'healthStatus' => [
                'ok' => array_merge(self::PALETTE['green'], ['dot' => 'on']),
                'low' => array_merge(self::PALETTE['yellow'], ['dot' => 'pending']),
                'high' => array_merge(self::PALETTE['red'], ['dot' => 'off']),
            ],

            // =================================================================
            // BACKUP REASON - import/restore/manual/scheduled
            // =================================================================
            'backupReason' => [
                'import' => self::PALETTE['blue'],
                'restore' => self::PALETTE['orange'],
                'manual' => self::PALETTE['cyan'],
                'scheduled' => self::PALETTE['purple'],
                'clean' => self::PALETTE['yellow'],
                'clear' => self::PALETTE['red'],
                'maintenance' => self::PALETTE['gray'],
                'other' => self::PALETTE['gray'],
            ],
        ];

        self::$initialized = true;
    }

    /**
     * Get a color from the palette by name
     *
     * @param string $name The color name (teal, gray, orange, red, blue, pink, etc.)
     * @return array{class: string, color: string, rgb: string, text: string}
     */
    public static function getPaletteColor(string $name): array
    {
        return self::PALETTE[$name] ?? self::DEFAULT_COLOR;
    }

    /**
     * Get all available palette color names
     *
     * @return string[]
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
     */
    public static function getNeutralColor(): string
    {
        return self::NEUTRAL_COLOR;
    }

    /**
     * Get default color for unknown values
     *
     * @return array{color: string, rgb: string, text: string}
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

    /**
     * Extract the first non-white/non-black hex color from an SVG string.
     *
     * @param string|null $svg
     * @return string|null
     * @since 5.27.0
     */
    public static function primaryHexFromSvg(?string $svg): ?string
    {
        if (!is_string($svg) || $svg === '') {
            return null;
        }

        if (!preg_match_all('/#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $svg, $matches)) {
            return null;
        }

        foreach ($matches[0] as $color) {
            $normalized = strtoupper($color);
            if (in_array($normalized, ['#FFF', '#FFFFFF', '#000', '#000000'], true)) {
                continue;
            }

            return $normalized;
        }

        return null;
    }

    /**
     * Blend two hex colors, returning hex A mixed toward hex B by $weight
     * (0.0 = pure A, 1.0 = pure B). Darken a color by mixing it toward a dark
     * base, e.g. `ColorHelper::mix('#FACC15', '#0B1220', 0.6)`.
     *
     * Accepts 3- or 6-digit hex with or without a leading `#`; returns an
     * uppercase `#RRGGBB`. Falls back to whichever input is parseable when the
     * other is not.
     *
     * @since 5.27.0
     */
    public static function mix(string $hexA, string $hexB, float $weight = 0.5): string
    {
        $a = self::hexToRgb($hexA);
        $b = self::hexToRgb($hexB);

        if ($a === null) {
            return $b === null ? '#000000' : self::rgbToHex($b);
        }
        if ($b === null) {
            return self::rgbToHex($a);
        }

        $weight = max(0.0, min(1.0, $weight));

        return self::rgbToHex([
            (int) round($a[0] * (1 - $weight) + $b[0] * $weight),
            (int) round($a[1] * (1 - $weight) + $b[1] * $weight),
            (int) round($a[2] * (1 - $weight) + $b[2] * $weight),
        ]);
    }

    /**
     * Perceived luminance of a hex colour on a 0–255 scale (Rec. 601 weights),
     * for deciding light-vs-dark contrast. Unparseable input returns 0.
     *
     * @since 5.27.0
     */
    public static function luminance(string $hex): int
    {
        $rgb = self::hexToRgb($hex);
        if ($rgb === null) {
            return 0;
        }

        return (int) round(($rgb[0] * 299 + $rgb[1] * 587 + $rgb[2] * 114) / 1000);
    }

    /**
     * Append an alpha channel to a hex colour, returning `#RRGGBBAA` — for dimmed
     * text (e.g. a subtitle at 78% of the title colour). $alpha is clamped to
     * 0.0–1.0; unparseable input falls back to opaque black.
     *
     * @since 5.27.0
     */
    public static function withAlpha(string $hex, float $alpha): string
    {
        $base = self::mix($hex, $hex, 0.0);   // normalise to #RRGGBB
        $byte = (int) round(max(0.0, min(1.0, $alpha)) * 255);

        return $base . sprintf('%02X', $byte);
    }

    /**
     * Read the two brand roles out of icon SVG markup:
     *  - `accent`: the most saturated colour (the badge / fill);
     *  - `ink`: the least-saturated non-accent colour (the glyph).
     *
     * When the icon carries only one colour, `ink` falls back to white or a
     * near-black by contrast with the accent. Returns null when the markup has
     * no usable colour at all.
     *
     * @return array{accent: string, ink: string}|null
     * @since 5.27.0
     */
    public static function iconColorRoles(?string $svg): ?array
    {
        if (!is_string($svg) || $svg === '') {
            return null;
        }

        if (!preg_match_all('/#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $svg, $matches)) {
            return null;
        }

        // Unique, normalised, order-preserving list of colours.
        $colors = [];
        foreach ($matches[0] as $hex) {
            $colors[self::mix($hex, $hex, 0.0)] = true;
        }
        $colors = array_keys($colors);

        // Accent = highest saturation (first wins ties).
        $accent = $colors[0];
        $accentSat = self::saturation($accent);
        foreach ($colors as $c) {
            $s = self::saturation($c);
            if ($s > $accentSat) {
                $accent = $c;
                $accentSat = $s;
            }
        }

        // Ink = lowest saturation among the rest (the neutral glyph colour).
        $ink = null;
        $inkSat = PHP_INT_MAX;
        foreach ($colors as $c) {
            if ($c === $accent) {
                continue;
            }
            $s = self::saturation($c);
            if ($s < $inkSat) {
                $ink = $c;
                $inkSat = $s;
            }
        }

        if ($ink === null) {
            $ink = self::luminance($accent) < 128 ? '#FFFFFF' : '#1E1E1E';
        }

        return ['accent' => $accent, 'ink' => $ink];
    }

    /**
     * Parse a 3- or 6-digit hex string (optional leading `#`) into an [r, g, b] triple.
     *
     * @return array{0: int, 1: int, 2: int}|null
     */
    private static function hexToRgb(string $hex): ?array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Format an [r, g, b] triple as an uppercase `#RRGGBB` string (channels clamped to 0–255).
     *
     * @param array{0: int, 1: int, 2: int} $rgb
     */
    private static function rgbToHex(array $rgb): string
    {
        return sprintf(
            '#%02X%02X%02X',
            max(0, min(255, $rgb[0])),
            max(0, min(255, $rgb[1])),
            max(0, min(255, $rgb[2])),
        );
    }

    /**
     * Chroma (max − min channel) of a hex colour on 0–255, used as a saturation
     * proxy. 0 = neutral grey/black/white. Unparseable input returns 0.
     */
    private static function saturation(string $hex): int
    {
        $rgb = self::hexToRgb($hex);
        if ($rgb === null) {
            return 0;
        }

        return max($rgb) - min($rgb);
    }
}
