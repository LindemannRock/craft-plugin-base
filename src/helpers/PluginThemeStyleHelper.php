<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Builds plugin-branded CSS custom properties from plugin icon colors.
 *
 * @since 5.34.0
 */
class PluginThemeStyleHelper
{
    private const FALLBACK_ACCENT = '#8B5CF6';
    private const FALLBACK_INK = '#FFFFFF';

    /**
     * Build CSS custom properties for plugin-branded hero surfaces from an icon SVG.
     */
    public static function heroCssVarsFromSvg(?string $svg, string $style = 'lighter', ?string $fallbackAccent = null): string
    {
        $theme = self::themeFromSvg($svg, $style, $fallbackAccent);
        $ink = $theme['ink'];
        $darkInk = $theme['darkInk'];

        return implode(';', [
            '--plugin-hero-accent:' . $theme['accent'],
            '--plugin-hero-ink:' . $ink,
            '--plugin-hero-ink-muted:' . ColorHelper::withAlpha($ink, 0.78),
            '--plugin-hero-credit:' . ColorHelper::withAlpha($ink, 0.56),
            '--plugin-hero-from:' . $theme['from'],
            '--plugin-hero-to:' . $theme['to'],
            '--plugin-hero-shadow:' . $theme['shadow'],
            '--plugin-hero-button-text:' . $theme['buttonText'],
            '--plugin-hero-soft-surface:' . ColorHelper::withAlpha($ink, $darkInk ? 0.08 : 0.16),
            '--plugin-hero-badge-bg:' . $theme['badgeBackground'],
            '--plugin-hero-badge-border:' . $theme['badgeBorder'],
            '--plugin-hero-badge-text:' . $ink,
        ]);
    }

    /**
     * Build CSS custom properties for docs shell surfaces from an icon SVG.
     */
    public static function docsShellCssVarsFromSvg(?string $svg, ?string $fallbackAccent = null): string
    {
        $theme = self::themeFromSvg($svg, 'lighter', $fallbackAccent);
        $accent = $theme['accent'];
        $ink = $theme['ink'];
        $darkInk = $theme['darkInk'];
        $ld = static fn(string $light, string $dark): string => 'light-dark(' . $light . ',' . $dark . ')';

        $lightText = $darkInk ? ColorHelper::mix($ink, '#000000', 0.08) : ColorHelper::mix($accent, '#000000', 0.82);
        $lightMuted = ColorHelper::mix($lightText, '#FFFFFF', 0.36);
        $darkText = $darkInk ? ColorHelper::mix($accent, '#FFFFFF', 0.88) : ColorHelper::mix($ink, '#FFFFFF', 0.08);
        $darkMuted = ColorHelper::mix($darkText, '#000000', 0.34);

        $lightPage = ColorHelper::mix($accent, '#FFFFFF', 0.99);
        $lightBg = ColorHelper::mix($accent, '#FFFFFF', 0.97);
        $lightBgSecondary = ColorHelper::mix($accent, '#FFFFFF', 0.90);
        $lightBgHover = ColorHelper::mix($accent, '#FFFFFF', 0.86);
        $lightHairline = ColorHelper::mix($accent, '#FFFFFF', 0.70);

        $darkPage = ColorHelper::mix($accent, '#000000', 0.88);
        $darkBg = ColorHelper::mix($accent, '#000000', 0.80);
        $darkBgSecondary = ColorHelper::mix($accent, '#000000', 0.72);
        $darkBgHover = ColorHelper::mix($accent, '#000000', 0.62);
        $darkHairline = ColorHelper::mix($accent, '#000000', 0.54);

        $shellAccent = self::readableBrandColor($accent, $lightPage, false);
        $shellAccentDark = self::readableBrandColor(ColorHelper::mix($shellAccent, '#000000', 0.12), $lightBgSecondary, false);
        $shellAccentHover = self::readableBrandColor(ColorHelper::mix($shellAccentDark, '#000000', 0.10), $lightBgHover, false);
        $shellAccentLight = self::readableBrandColor($accent, $darkPage, true);
        $shellAccentLighter = self::readableBrandColor(ColorHelper::mix($shellAccentLight, '#FFFFFF', 0.12), $darkBgSecondary, true);
        $shellAccentLightHover = self::readableBrandColor(ColorHelper::mix($shellAccentLighter, '#FFFFFF', 0.10), $darkBgHover, true);
        $shellButtonTextLight = self::readableTextFor($shellAccent);
        $shellButtonTextDark = self::readableTextFor($shellAccentLight);

        $codeLightBg = ColorHelper::mix($accent, '#FFFFFF', 0.84);
        $codeDarkBg = ColorHelper::mix($accent, '#000000', 0.74);
        $preLightBg = ColorHelper::mix($accent, '#000000', 0.80);
        $preDarkBg = ColorHelper::mix($accent, '#000000', 0.92);
        $preLightText = ColorHelper::mix($accent, '#FFFFFF', 0.90);
        $preDarkText = ColorHelper::mix($accent, '#FFFFFF', 0.82);

        $positiveLight = '#047857';
        $positiveDark = '#6ee7b7';
        $infoLight = $shellAccent;
        $infoDark = $shellAccentLight;
        $warningLight = '#92400e';
        $warningDark = '#fbbf24';
        $dangerLight = '#991b1b';
        $dangerDark = '#f87171';

        return implode(';', [
            '--plugin-shell-bg:' . $ld($lightBg, $darkBg),
            '--plugin-shell-bg-secondary:' . $ld($lightBgSecondary, $darkBgSecondary),
            '--plugin-shell-bg-hover:' . $ld($lightBgHover, $darkBgHover),
            '--plugin-shell-bg-page:' . $ld($lightPage, $darkPage),
            '--plugin-shell-text:' . $ld($lightText, $darkText),
            '--plugin-shell-text-secondary:' . $ld($lightMuted, $darkMuted),
            '--plugin-shell-text-muted:' . $ld(ColorHelper::withAlpha($lightText, 0.68), ColorHelper::withAlpha($darkText, 0.68)),
            '--plugin-shell-text-disabled:' . $ld(ColorHelper::withAlpha($lightText, 0.44), ColorHelper::withAlpha($darkText, 0.44)),
            '--plugin-shell-hairline:' . $ld(ColorHelper::withAlpha($lightHairline, 0.86), ColorHelper::withAlpha($darkHairline, 0.92)),
            '--plugin-shell-hairline-light:' . $ld(ColorHelper::withAlpha($lightText, 0.18), ColorHelper::withAlpha($darkText, 0.18)),
            '--plugin-shell-accent:' . $ld($shellAccent, $shellAccentLight),
            '--plugin-shell-accent-dark:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-accent-bg:' . $ld(ColorHelper::withAlpha($accent, 0.18), ColorHelper::withAlpha($accent, 0.20)),
            '--plugin-shell-accent-hover:' . $ld($shellAccentHover, $shellAccentLightHover),
            '--plugin-shell-focus:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-selection-bg:' . $ld(ColorHelper::withAlpha($accent, 0.30), ColorHelper::withAlpha($accent, 0.34)),
            '--plugin-shell-selection-text:' . $ld($lightText, $darkText),
            '--plugin-shell-caret:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-accent-input:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-code:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-code-bg:' . $ld(ColorHelper::withAlpha($codeLightBg, 0.78), ColorHelper::withAlpha($codeDarkBg, 0.86)),
            '--plugin-shell-code-border:' . $ld(ColorHelper::withAlpha($lightText, 0.16), ColorHelper::withAlpha($darkText, 0.16)),
            '--plugin-shell-pre-code:' . $ld($preLightText, $preDarkText),
            '--plugin-shell-pre-bg:' . $ld($preLightBg, $preDarkBg),
            '--plugin-shell-since-bg:' . $ld(ColorHelper::withAlpha($accent, 0.16), ColorHelper::withAlpha($accent, 0.22)),
            '--plugin-shell-since-text:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-btn-bg:' . $ld($shellAccent, $shellAccentLight),
            '--plugin-shell-btn-hover:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-btn-text:' . $ld($shellButtonTextLight, $shellButtonTextDark),
            '--plugin-shell-scrollbar:' . $ld($lightHairline, $darkHairline),
            '--plugin-shell-release-latest-bg:' . $ld(ColorHelper::withAlpha('#10B981', 0.16), ColorHelper::withAlpha('#10B981', 0.22)),
            '--plugin-shell-release-latest-text:' . $ld($positiveLight, $positiveDark),
            '--plugin-shell-changelog-link:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-changelog-link-hover:' . $ld($shellAccentHover, $shellAccentLightHover),
            '--plugin-shell-callout-note:' . $ld($infoLight, $infoDark),
            '--plugin-shell-callout-tip:' . $ld($positiveLight, $positiveDark),
            '--plugin-shell-callout-warning:' . $ld($warningLight, $warningDark),
            '--plugin-shell-callout-caution:' . $ld($dangerLight, $dangerDark),
            '--plugin-shell-callout-important:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-change-added:' . $ld($positiveLight, $positiveDark),
            '--plugin-shell-change-added-border:' . $ld(ColorHelper::withAlpha('#10B981', 0.72), ColorHelper::withAlpha('#34D399', 0.74)),
            '--plugin-shell-change-improved:' . $ld($infoLight, $infoDark),
            '--plugin-shell-change-improved-border:' . $ld(ColorHelper::withAlpha($accent, 0.72), ColorHelper::withAlpha($accent, 0.74)),
            '--plugin-shell-change-fixed:' . $ld($warningLight, $warningDark),
            '--plugin-shell-change-fixed-border:' . $ld(ColorHelper::withAlpha('#F59E0B', 0.72), ColorHelper::withAlpha('#FBBF24', 0.74)),
            '--plugin-shell-change-removed:' . $ld($dangerLight, $dangerDark),
            '--plugin-shell-change-removed-border:' . $ld(ColorHelper::withAlpha('#EF4444', 0.72), ColorHelper::withAlpha('#F87171', 0.74)),
            '--plugin-shell-copy-btn-bg:' . $ld(ColorHelper::mix($accent, '#000000', 0.74), ColorHelper::mix($accent, '#000000', 0.72)),
            '--plugin-shell-copy-btn-color:' . $ld($preLightText, $preDarkText),
            '--plugin-shell-copy-btn-hover-bg:' . $ld(ColorHelper::mix($accent, '#000000', 0.64), ColorHelper::mix($accent, '#000000', 0.62)),
            '--plugin-shell-copy-btn-focus-color:' . $ld($shellAccentDark, $shellAccentLighter),
            '--plugin-shell-code-bg-token:' . $ld($preLightBg, $preDarkBg),
            '--plugin-shell-code-fg-token:' . $ld($preLightText, $preDarkText),
        ]);
    }

    /**
     * Build hero and docs shell CSS custom properties from an icon SVG.
     */
    public static function docsCssVarsFromSvg(?string $svg, string $style = 'lighter', ?string $fallbackAccent = null): string
    {
        return self::heroCssVarsFromSvg($svg, $style, $fallbackAccent)
            . ';'
            . self::docsShellCssVarsFromSvg($svg, $fallbackAccent);
    }

    /**
     * @return array{
     *     accent: string,
     *     ink: string,
     *     darkInk: bool,
     *     from: string,
     *     to: string,
     *     shadow: string,
     *     buttonText: string,
     *     badgeBackground: string,
     *     badgeBorder: string
     * }
     */
    private static function themeFromSvg(?string $svg, string $style, ?string $fallbackAccent): array
    {
        $roles = ColorHelper::iconColorRoles($svg);
        $accent = $roles !== null ? $roles['accent'] : ($fallbackAccent ?: self::FALLBACK_ACCENT);
        $ink = $roles !== null ? $roles['ink'] : self::FALLBACK_INK;

        if (!in_array($style, ['lighter', 'primary', 'deeper', 'diagonal'], true)) {
            $style = 'lighter';
        }

        $darkInk = ColorHelper::luminance($ink) < 128;
        $darken = static fn(int $amount): string => ColorHelper::mix($accent, '#000000', $amount / 100);
        $lighten = static fn(int $amount): string => ColorHelper::mix($accent, '#FFFFFF', $amount / 100);

        if ($darkInk) {
            [$from, $to] = match ($style) {
                'primary', 'diagonal' => [$darken(62), $lighten(8)],
                'deeper' => [$darken(58), $darken(8)],
                default => [$darken(45), $lighten(8)],
            };
        } else {
            [$from, $to] = match ($style) {
                'primary', 'diagonal' => [$darken(45), $darken(84)],
                'deeper' => [$darken(55), $darken(90)],
                default => [$darken(32), $darken(78)],
            };
        }

        return [
            'accent' => $accent,
            'ink' => $ink,
            'darkInk' => $darkInk,
            'from' => $from,
            'to' => $to,
            'shadow' => ColorHelper::mix($accent, '#000000', 0.82),
            'buttonText' => $darkInk ? $to : $from,
            'badgeBackground' => $darkInk ? ColorHelper::withAlpha('#FFFFFF', 0.34) : ColorHelper::withAlpha($ink, 0.18),
            'badgeBorder' => $darkInk ? ColorHelper::withAlpha($ink, 0.10) : ColorHelper::withAlpha($ink, 0.22),
        ];
    }

    private static function readableBrandColor(string $accent, string $background, bool $towardLight, float $minimumRatio = 4.5): string
    {
        $target = $towardLight ? '#FFFFFF' : '#000000';

        for ($step = 0; $step <= 100; $step += 4) {
            $candidate = ColorHelper::mix($accent, $target, $step / 100);
            if (self::contrastRatio($candidate, $background) >= $minimumRatio) {
                return $candidate;
            }
        }

        $fallback = $towardLight ? '#F9FAFB' : '#111827';
        if (self::contrastRatio($fallback, $background) >= $minimumRatio) {
            return $fallback;
        }

        return $towardLight ? '#FFFFFF' : '#000000';
    }

    private static function readableTextFor(string $background): string
    {
        $light = '#FFFFFF';
        $dark = '#111827';

        return self::contrastRatio($light, $background) >= self::contrastRatio($dark, $background)
            ? $light
            : $dark;
    }

    private static function contrastRatio(string $foreground, string $background): float
    {
        $light = max(self::relativeLuminance($foreground), self::relativeLuminance($background));
        $dark = min(self::relativeLuminance($foreground), self::relativeLuminance($background));

        return ($light + 0.05) / ($dark + 0.05);
    }

    private static function relativeLuminance(string $hex): float
    {
        $rgb = self::hexToRgb($hex);
        if ($rgb === null) {
            return 0.0;
        }

        $channels = array_map(
            static function(int $channel): float {
                $value = $channel / 255;

                return $value <= 0.03928
                    ? $value / 12.92
                    : (($value + 0.055) / 1.055) ** 2.4;
            },
            $rgb,
        );

        return $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
    }

    /**
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
}
