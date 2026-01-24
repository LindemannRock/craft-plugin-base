<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

/**
 * Settings Display Name Trait
 *
 * Provides standardized plugin name helper methods for Settings models.
 * These methods ensure consistent naming throughout the plugin UI.
 *
 * Requirements:
 * - Using class must have a public string $pluginName property
 *
 * Usage:
 * ```php
 * class Settings extends Model
 * {
 *     use SettingsDisplayNameTrait;
 *     public string $pluginName = 'Redirect Manager';
 * }
 *
 * $settings->getDisplayName();        // "Redirect"
 * $settings->getFullName();           // "Redirect Manager"
 * $settings->getPluralDisplayName();  // "Redirects"
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
trait SettingsDisplayNameTrait
{
    /**
     * Get display name (singular, without "Manager")
     *
     * Strips "Manager" and singularizes the plugin name for use in UI labels.
     * Acronyms (all-uppercase words) are not singularized.
     *
     * Examples:
     * - "Redirect Manager" -> "Redirect"
     * - "Short Links" -> "Short Link"
     * - "Search Manager" -> "Search"
     * - "Icons" -> "Icon"
     * - "SMS Manager" -> "SMS" (acronym preserved)
     *
     * @return string
     * @since 5.0.0
     */
    public function getDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name and trim whitespace
        $name = trim(str_replace([' Manager', ' manager'], '', $this->pluginName));

        // Singularize by removing trailing 's' if present
        // But only if:
        // - The word is more than 2 characters (don't change "As" to "A")
        // - Not if it ends in 'ss' (e.g., "Class" should stay "Class")
        // - Not if it's all uppercase (acronyms like "SMS" should stay "SMS")
        $isAcronym = strtoupper($name) === $name && strlen($name) > 1;
        if (strlen($name) > 2 && str_ends_with($name, 's') && !str_ends_with($name, 'ss') && !$isAcronym) {
            $name = substr($name, 0, -1);
        }

        return $name;
    }

    /**
     * Get full plugin name (as configured, with "Manager" if present)
     *
     * Returns the plugin name exactly as configured in settings.
     *
     * Examples:
     * - "Redirect Manager" -> "Redirect Manager"
     * - "Short Links" -> "Short Links"
     *
     * @return string
     * @since 5.0.0
     */
    public function getFullName(): string
    {
        return trim($this->pluginName);
    }

    /**
     * Get plural display name (without "Manager")
     *
     * Strips "Manager" from the plugin name but keeps/adds plural form.
     *
     * Examples:
     * - "Redirect Manager" -> "Redirects"
     * - "Short Links" -> "Short Links"
     * - "Icon Manager" -> "Icons"
     *
     * @return string
     * @since 5.0.0
     */
    public function getPluralDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name and trim whitespace
        $name = trim(str_replace([' Manager', ' manager'], '', $this->pluginName));

        // Add 's' if not already ending in 's' (case-insensitive check)
        if (!str_ends_with(strtolower($name), 's')) {
            $name .= 's';
        }

        return $name;
    }

    /**
     * Get lowercase display name (singular, without "Manager")
     *
     * Lowercase version of getDisplayName() for use in messages, handles, etc.
     *
     * Examples:
     * - "Redirect Manager" -> "redirect"
     * - "Short Links" -> "short link"
     *
     * @return string
     * @since 5.0.0
     */
    public function getLowerDisplayName(): string
    {
        return strtolower($this->getDisplayName());
    }

    /**
     * Get lowercase plural display name (without "Manager")
     *
     * Lowercase version of getPluralDisplayName() for use in messages, handles, etc.
     *
     * Examples:
     * - "Redirect Manager" -> "redirects"
     * - "Short Links" -> "short links"
     *
     * @return string
     * @since 5.0.0
     */
    public function getPluralLowerDisplayName(): string
    {
        return strtolower($this->getPluralDisplayName());
    }
}
