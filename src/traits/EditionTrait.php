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
 * Single-edition plugins (free or paid) do NOT need this trait: Craft gives
 * every plugin a default 'standard' edition, and price (including $0) is set
 * per edition in the Plugin Store, never in code. Only multi-edition plugins
 * use this trait.
 *
 * Edition Tiers (in order):
 * - STANDARD: Craft's default edition handle — the base edition. Not inherently
 *   free: the lower tier of a two-tier lineup can be free OR paid (e.g. Search
 *   Manager sells a paid Standard).
 * - PRO: full-featured top tier
 *
 * The suite's lineup is [STANDARD, PRO] (Standard free or paid). Additional
 * tiers (a mid tier between Standard and Pro, or a tier above Pro) get a
 * purpose-named handle added to this trait when actually decided — never
 * invented per-plugin, never named before the product decision exists.
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
 *     // Override editions for your tier model
 *     public static function editions(): array
 *     {
 *         return [
 *             self::EDITION_STANDARD,
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
 * @since 5.5.0
 */
trait EditionTrait
{
    /**
     * Default/base edition constant
     *
     * Craft's default edition handle. Not inherently free — the price
     * (including $0) is set per edition in the Plugin Store, never in code.
     * Use as the lower tier (free or paid) of a two-tier lineup.
     */
    public const EDITION_STANDARD = 'standard';

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
     * - Two tiers: return [self::EDITION_STANDARD, self::EDITION_PRO]
     *   (Standard free or paid — pricing lives in the Plugin Store, not code)
     * - Additional tiers: add a purpose-named handle to this trait first
     *   (never per-plugin)
     *
     * @return string[]
     */
    public static function editions(): array
    {
        // Default: Craft's single default edition (multi-edition plugins override)
        return [
            self::EDITION_STANDARD,
        ];
    }

    /**
     * Check if the current edition is Standard
     *
     * @return bool
     */
    public function isStandard(): bool
    {
        return $this->is(self::EDITION_STANDARD);
    }

    /**
     * Check if the current edition is Pro
     *
     * @return bool
     */
    public function isPro(): bool
    {
        return $this->is(self::EDITION_PRO);
    }

    /**
     * Check if the current edition is at least the specified edition
     *
     * Useful for features available to multiple tiers:
     * - isAtLeast(PRO) = true only for Pro (in a [STANDARD, PRO] lineup)
     *
     * @param string $edition The minimum required edition
     * @return bool
     */
    public function isAtLeast(string $edition): bool
    {
        return $this->is($edition, '>=');
    }

    /**
     * Check if the current edition is below the specified edition
     *
     * Useful for showing upgrade prompts:
     * - isBelow(PRO) = true for Standard (in a [STANDARD, PRO] lineup)
     *
     * @param string $edition The edition to compare against
     * @return bool
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
     */
    public function requireEdition(string $edition, ?string $featureName = null): void
    {
        if ($this->isAtLeast($edition)) {
            return;
        }

        $editionLabel = ucfirst($edition);

        if ($featureName !== null) {
            $message = Craft::t('lindemannrock-base', '{feature} requires the {edition} edition.', [
                'feature' => $featureName,
                'edition' => $editionLabel,
            ]);
        } else {
            $message = Craft::t('lindemannrock-base', 'This feature requires the {edition} edition.', [
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
     * @return string Capitalized edition name (e.g., "Standard", "Pro")
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
     * @return string The current edition (e.g., 'standard', 'pro')
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
     */
    public function hasMultipleEditions(): bool
    {
        return count(static::editions()) > 1;
    }

    /**
     * Compare two arbitrary editions (not the active one)
     *
     * Unlike `isAtLeast()` which compares against the active edition,
     * this compares any two edition strings against the editions() hierarchy.
     * Useful inside `getEditionFeatures()` to build comparison tables.
     *
     * @param string $edition The edition to check
     * @param string $minEdition The minimum required edition
     * @return bool True if $edition is at least $minEdition in the hierarchy
     */
    protected static function editionIsAtLeast(string $edition, string $minEdition): bool
    {
        $editions = static::editions();
        $editionIndex = array_search($edition, $editions, true);
        $minIndex = array_search($minEdition, $editions, true);

        if ($editionIndex === false || $minIndex === false) {
            return false;
        }

        return $editionIndex >= $minIndex;
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
     *     if (static::editionIsAtLeast($edition, self::EDITION_PRO)) {
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
     */
    public function hasFeature(string $featureName): bool
    {
        $features = $this->getEditionFeatures($this->edition);

        return $features[$featureName] ?? false;
    }
}
