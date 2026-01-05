<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
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
     *
     * Examples:
     * - "Redirect Manager" -> "Redirect"
     * - "Short Links" -> "Short Link"
     * - "Search Manager" -> "Search"
     * - "Icons" -> "Icon"
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name and trim whitespace
        $name = trim(str_replace([' Manager', ' manager'], '', $this->pluginName));

        // Singularize by removing trailing 's' if present
        // But only if the word is more than 2 characters (don't change "As" to "A")
        // And not if it ends in 'ss' (e.g., "Class" should stay "Class")
        if (strlen($name) > 2 && str_ends_with($name, 's') && !str_ends_with($name, 'ss')) {
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
     */
    public function getPluralDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name and trim whitespace
        $name = trim(str_replace([' Manager', ' manager'], '', $this->pluginName));

        // Add 's' if not already ending in 's'
        if (!str_ends_with($name, 's')) {
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
     */
    public function getPluralLowerDisplayName(): string
    {
        return strtolower($this->getPluralDisplayName());
    }
}
