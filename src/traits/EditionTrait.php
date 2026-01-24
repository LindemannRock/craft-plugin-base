<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

use Craft;
use yii\web\ForbiddenHttpException;

/**
 * Edition Trait
 *
 * Provides standardized edition support for LindemannRock plugins.
 * Implements Craft's plugin edition system with consistent naming and helper methods.
 *
 * Edition Tiers (in order):
 * - STANDARD: Free tier (use for free-only plugins or free tier of tiered plugins)
 * - LITE: Entry-level paid tier
 * - PRO: Full-featured paid tier
 *
 * Not all plugins need all tiers. Common configurations:
 * - Free-only: [STANDARD] - can add PRO later without renaming
 * - Two paid tiers: [LITE, PRO]
 * - Free + paid: [STANDARD, PRO]
 * - Three tiers: [STANDARD, LITE, PRO]
 *
 * Requirements:
 * - Using class must extend craft\base\Plugin
 *
 * Usage:
 * ```php
 * class MyPlugin extends Plugin
 * {
 *     use EditionTrait;
 *
 *     // Optional: Override editions for your tier model
 *     public static function editions(): array
 *     {
 *         return [
 *             self::EDITION_LITE,
 *             self::EDITION_PRO,
 *         ];
 *     }
 * }
 *
 * // Check editions
 * if (MyPlugin::getInstance()->isPro()) {
 *     // Pro-only feature
 * }
 *
 * // Gate a controller action
 * public function actionCloudBackup(): Response
 * {
 *     MyPlugin::getInstance()->requireEdition(MyPlugin::EDITION_PRO);
 *     // ... pro-only code
 * }
 *
 * // In Twig templates
 * {% if plugin.isPro() %}
 *     {# Pro features #}
 * {% else %}
 *     {# Upgrade prompt #}
 * {% endif %}
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
trait EditionTrait
{
    /**
     * Free tier edition constant
     *
     * Use for free-only plugins or as the free tier in a tiered plugin.
     * Named "standard" (not "free") to sound professional and allow
     * adding paid tiers later without renaming.
     */
    public const EDITION_STANDARD = 'standard';

    /**
     * Entry-level paid tier edition constant
     *
     * Use as the lower paid tier when offering two paid options.
     * Typically includes core functionality without advanced features.
     */
    public const EDITION_LITE = 'lite';

    /**
     * Full-featured paid tier edition constant
     *
     * Use as the top tier with all features enabled.
     * Should include everything from lower tiers plus premium features.
     */
    public const EDITION_PRO = 'pro';

    /**
     * Returns all available editions for this plugin
     *
     * Order matters - editions are compared by position in the array.
     * First = lowest tier, last = highest tier.
     *
     * Override this method in your plugin to define your tier model:
     * - Free-only: return [self::EDITION_STANDARD]
     * - Two paid: return [self::EDITION_LITE, self::EDITION_PRO]
     * - Free + paid: return [self::EDITION_STANDARD, self::EDITION_PRO]
     * - Three tiers: return [self::EDITION_STANDARD, self::EDITION_LITE, self::EDITION_PRO]
     *
     * @return string[]
     * @since 5.0.0
     */
    public static function editions(): array
    {
        // Default: single free edition (override in plugin for paid tiers)
        return [
            self::EDITION_STANDARD,
        ];
    }

    /**
     * Check if the current edition is Standard (free tier)
     *
     * @return bool
     * @since 5.0.0
     */
    public function isStandard(): bool
    {
        return $this->is(self::EDITION_STANDARD);
    }

    /**
     * Check if the current edition is Lite
     *
     * @return bool
     * @since 5.0.0
     */
    public function isLite(): bool
    {
        return $this->is(self::EDITION_LITE);
    }

    /**
     * Check if the current edition is Pro
     *
     * @return bool
     * @since 5.0.0
     */
    public function isPro(): bool
    {
        return $this->is(self::EDITION_PRO);
    }

    /**
     * Check if the current edition is at least the specified edition
     *
     * Useful for features available to multiple tiers:
     * - isAtLeast(LITE) = true for Lite and Pro
     * - isAtLeast(PRO) = true only for Pro
     *
     * @param string $edition The minimum required edition
     * @return bool
     * @since 5.0.0
     */
    public function isAtLeast(string $edition): bool
    {
        return $this->is($edition, '>=');
    }

    /**
     * Check if the current edition is below the specified edition
     *
     * Useful for showing upgrade prompts:
     * - isBelow(PRO) = true for Standard and Lite
     * - isBelow(LITE) = true only for Standard
     *
     * @param string $edition The edition to compare against
     * @return bool
     * @since 5.0.0
     */
    public function isBelow(string $edition): bool
    {
        return $this->is($edition, '<');
    }

    /**
     * Require a minimum edition, throwing an exception if not met
     *
     * Use in controller actions to gate Pro-only features:
     * ```php
     * public function actionAdvancedExport(): Response
     * {
     *     MyPlugin::getInstance()->requireEdition(MyPlugin::EDITION_PRO);
     *     // ... pro-only code
     * }
     * ```
     *
     * @param string $edition The minimum required edition
     * @param string|null $featureName Optional feature name for error message
     * @throws ForbiddenHttpException If the current edition is below the required edition
     * @since 5.0.0
     */
    public function requireEdition(string $edition, ?string $featureName = null): void
    {
        if ($this->isAtLeast($edition)) {
            return;
        }

        $editionLabel = ucfirst($edition);

        if ($featureName !== null) {
            $message = Craft::t('app', '{feature} requires the {edition} edition.', [
                'feature' => $featureName,
                'edition' => $editionLabel,
            ]);
        } else {
            $message = Craft::t('app', 'This feature requires the {edition} edition.', [
                'edition' => $editionLabel,
            ]);
        }

        throw new ForbiddenHttpException($message);
    }

    /**
     * Get the display name for an edition
     *
     * Returns a human-readable, capitalized edition name.
     * Useful for UI display and error messages.
     *
     * @param string|null $edition Edition constant, or null for current edition
     * @return string Capitalized edition name (e.g., "Standard", "Lite", "Pro")
     * @since 5.0.0
     */
    public function getEditionName(?string $edition = null): string
    {
        if ($edition === null) {
            $edition = $this->edition;
        }

        return ucfirst($edition);
    }

    /**
     * Get the current edition handle
     *
     * @return string The current edition (e.g., 'standard', 'lite', 'pro')
     * @since 5.0.0
     */
    public function getEditionHandle(): string
    {
        return $this->edition;
    }

    /**
     * Check if this plugin has multiple editions
     *
     * Useful for conditionally showing edition-related UI.
     *
     * @return bool True if more than one edition is available
     * @since 5.0.0
     */
    public function hasMultipleEditions(): bool
    {
        return count(static::editions()) > 1;
    }

    /**
     * Get features available in a specific edition
     *
     * Override this method to provide edition comparison data for UI:
     * ```php
     * public function getEditionFeatures(string $edition): array
     * {
     *     $features = [
     *         'Basic translations' => true,
     *         'CSV export' => true,
     *     ];
     *
     *     if ($this->is($edition, '>=', self::EDITION_PRO)) {
     *         $features['Cloud backups'] = true;
     *         $features['CLI commands'] = true;
     *     }
     *
     *     return $features;
     * }
     * ```
     *
     * @param string $edition The edition to get features for
     * @return array<string, bool> Feature names mapped to availability
     * @since 5.0.0
     */
    public function getEditionFeatures(string $edition): array
    {
        // Override in plugin to provide feature list
        return [];
    }

    /**
     * Check if a specific feature is available in the current edition
     *
     * Requires getEditionFeatures() to be implemented.
     *
     * @param string $featureName The feature to check
     * @return bool True if the feature is available
     * @since 5.0.0
     */
    public function hasFeature(string $featureName): bool
    {
        $features = $this->getEditionFeatures($this->edition);

        return $features[$featureName] ?? false;
    }
}
