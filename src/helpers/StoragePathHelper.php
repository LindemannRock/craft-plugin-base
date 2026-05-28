<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\helpers\App;

/**
 * Helper for resolving local storage path settings.
 *
 * @author LindemannRock
 * @since 5.26.0
 */
class StoragePathHelper
{
    /**
     * Resolve environment variables and Craft aliases in a storage path value.
     *
     * @param string $path
     * @return string
     */
    public static function resolve(string $path): string
    {
        return self::resolveParsed(self::parseEnv($path));
    }

    /**
     * Resolve environment variables while preserving aliases for later validation.
     *
     * @param string $path
     * @return string
     */
    public static function parseEnv(string $path): string
    {
        return (string)App::parseEnv($path);
    }

    /**
     * Resolve Craft aliases after syntax and alias allowlist validation.
     *
     * @param string $path
     * @return string
     */
    public static function resolveParsed(string $path): string
    {
        return (string)Craft::getAlias($path);
    }
}
