<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use craft\base\Model;
use craft\web\User;

/**
 * CP Navigation Helper
 *
 * Centralizes permission + settings checks for CP subnav and default routes.
 *
 * @author LindemannRock
 * @since 5.14.0
 */
class CpNavHelper
{
    /**
     * Build a CP subnav array from section definitions
     *
     * @param User $user Craft user component
     * @param Model|null $settings Plugin settings (DB-backed)
     * @param array $sections Section definitions
     * @return array
     */
    public static function buildSubnav(User $user, ?Model $settings, array $sections): array
    {
        $subnav = [];

        foreach ($sections as $section) {
            if (!self::isSectionAccessible($user, $settings, $section)) {
                continue;
            }

            $key = $section['key'] ?? null;
            $label = $section['label'] ?? null;
            $url = $section['url'] ?? ($section['route'] ?? null);

            if (!is_string($key) || $key === '' || !is_string($label) || $label === '' || !is_string($url) || $url === '') {
                continue;
            }

            $subnav[$key] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        return $subnav;
    }

    /**
     * Get the first accessible CP route based on section definitions
     *
     * @param User $user Craft user component
     * @param Model|null $settings Plugin settings (DB-backed)
     * @param array $sections Section definitions
     * @return string|null
     */
    public static function firstAccessibleRoute(User $user, ?Model $settings, array $sections): ?string
    {
        foreach ($sections as $section) {
            if (!self::isSectionAccessible($user, $settings, $section)) {
                continue;
            }

            $url = $section['url'] ?? ($section['route'] ?? null);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return null;
    }

    /**
     * Check if a section is accessible for the user/settings
     *
     * @param User $user
     * @param Model|null $settings
     * @param array $section
     * @return bool
     */
    private static function isSectionAccessible(User $user, ?Model $settings, array $section): bool
    {
        if (array_key_exists('enabled', $section) && $section['enabled'] === false) {
            return false;
        }

        $settingsFlag = $section['settingsFlag'] ?? null;
        if (is_string($settingsFlag) && $settingsFlag !== '') {
            if (!$settings || !property_exists($settings, $settingsFlag) || !$settings->$settingsFlag) {
                return false;
            }
        }

        $when = $section['when'] ?? null;
        if (is_callable($when)) {
            if (!$when($settings, $user)) {
                return false;
            }
        } elseif (is_bool($when) && !$when) {
            return false;
        }

        $permissionsAll = self::normalizePermissions($section['permissionsAll'] ?? []);
        if (!empty($permissionsAll)) {
            foreach ($permissionsAll as $permission) {
                if (!$user->checkPermission($permission)) {
                    return false;
                }
            }
        }

        $permissionsAny = self::normalizePermissions($section['permissionsAny'] ?? []);
        if (!empty($permissionsAny)) {
            $hasAny = false;
            foreach ($permissionsAny as $permission) {
                if ($user->checkPermission($permission)) {
                    $hasAny = true;
                    break;
                }
            }
            if (!$hasAny) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize permission definitions to a clean string array
     *
     * @param mixed $permissions
     * @return array
     */
    private static function normalizePermissions(mixed $permissions): array
    {
        if (is_string($permissions) && $permissions !== '') {
            return [$permissions];
        }

        if (!is_array($permissions)) {
            return [];
        }

        return array_values(array_filter($permissions, fn($permission) => is_string($permission) && $permission !== ''));
    }
}
