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
     * Validate a local storage path value without requiring a Yii model attribute.
     *
     * This is the raw validation API used by {@see \lindemannrock\base\validators\StoragePathValidator}
     * and by dynamic/nested settings that cannot attach the Yii validator directly.
     *
     * Supported options:
     * - translationCategory: translation category for returned errors
     * - allowedAliases: allowed alias roots when aliases are required
     * - preventWebroot: whether to block @web/@webroot and resolved webroot paths
     * - requireAlias: whether paths must use or resolve inside allowed alias roots
     * - allowEnvVars: whether leading environment variables are allowed
     *
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int, string> Translated validation errors
     * @since 5.26.0
     */
    public static function validatePath(string $path, array $options = []): array
    {
        $value = trim($path);
        if ($value === '') {
            return [];
        }

        $translationCategory = (string)($options['translationCategory'] ?? 'app');
        $allowedAliases = $options['allowedAliases'] ?? ['@storage', '@root'];
        $preventWebroot = (bool)($options['preventWebroot'] ?? true);
        $requireAlias = (bool)($options['requireAlias'] ?? false);
        $allowEnvVars = (bool)($options['allowEnvVars'] ?? true);

        if (!is_array($allowedAliases)) {
            $allowedAliases = ['@storage', '@root'];
        }
        $allowedAliases = array_values(array_map('strval', $allowedAliases));

        $syntaxError = self::getPathSyntaxError($value, $translationCategory, $preventWebroot);
        if ($syntaxError !== null) {
            return [$syntaxError];
        }

        $usesEnvVar = self::startsWithEnvVar($value);
        if ($usesEnvVar && !$allowEnvVars) {
            return [
                Craft::t($translationCategory, 'Environment variables are not allowed for this path.'),
            ];
        }

        try {
            $parsedValue = self::parseEnv($value);
        } catch (\Throwable $e) {
            return [
                Craft::t($translationCategory, 'Invalid path: {error}', ['error' => $e->getMessage()]),
            ];
        }

        if ($parsedValue === '') {
            return [
                Craft::t($translationCategory, 'Invalid path.'),
            ];
        }

        $syntaxError = self::getPathSyntaxError($parsedValue, $translationCategory, $preventWebroot);
        if ($syntaxError !== null) {
            return [$syntaxError];
        }

        if (str_starts_with($parsedValue, '@') && !self::startsWithAllowedAlias($parsedValue, $allowedAliases)) {
            return [self::getAllowedAliasError($translationCategory, $allowedAliases)];
        }

        try {
            $resolvedPath = self::resolveParsed($parsedValue);
        } catch (\Throwable $e) {
            return [
                Craft::t($translationCategory, 'Invalid path: {error}', ['error' => $e->getMessage()]),
            ];
        }

        if ($requireAlias) {
            if (str_starts_with($value, '@')) {
                if (!self::startsWithAllowedAlias($value, $allowedAliases)) {
                    return [self::getAllowedAliasError($translationCategory, $allowedAliases)];
                }
            } elseif (!self::isWithinAllowedAliasRoot((string)$resolvedPath, $allowedAliases)) {
                return [
                    Craft::t(
                        $translationCategory,
                        'Path must start with one of: {aliases}.',
                        ['aliases' => implode(', ', $allowedAliases)]
                    ),
                ];
            }
        }

        if (filter_var($resolvedPath, FILTER_VALIDATE_URL) !== false) {
            return [
                Craft::t($translationCategory, 'Path must resolve to a local filesystem path, not a URL.'),
            ];
        }

        if ($preventWebroot && self::isWithinWebroot((string)$resolvedPath)) {
            return [
                Craft::t($translationCategory, 'Path cannot be in a web-accessible directory (@webroot).'),
            ];
        }

        return [];
    }

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

    private static function getPathSyntaxError(string $value, string $translationCategory, bool $preventWebroot): ?string
    {
        if (preg_match('#(^|/)\.\.(/|$)#', $value) === 1) {
            return Craft::t($translationCategory, 'Path cannot contain parent directory traversal ("..").');
        }

        if ($preventWebroot && preg_match('/^@web(root)?(?:\/|$)/i', $value) === 1) {
            return Craft::t($translationCategory, 'Path cannot use @web or @webroot because those are web-accessible.');
        }

        return null;
    }

    private static function startsWithEnvVar(string $value): bool
    {
        return preg_match('/^\$(?:\{[A-Z0-9_]+\}|[A-Z0-9_]+)/i', $value) === 1;
    }

    /**
     * @param array<int, string> $allowedAliases
     */
    private static function startsWithAllowedAlias(string $value, array $allowedAliases): bool
    {
        foreach ($allowedAliases as $alias) {
            if (str_starts_with($value, $alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $allowedAliases
     */
    private static function isWithinAllowedAliasRoot(string $resolvedPath, array $allowedAliases): bool
    {
        $normalizedResolved = rtrim($resolvedPath, '/\\');

        foreach ($allowedAliases as $alias) {
            try {
                $resolvedAlias = Craft::getAlias($alias);
            } catch (\Throwable) {
                continue;
            }

            $normalizedAlias = rtrim((string)$resolvedAlias, '/\\');
            if (
                $normalizedResolved === $normalizedAlias
                || str_starts_with($normalizedResolved, $normalizedAlias . DIRECTORY_SEPARATOR)
                || str_starts_with($normalizedResolved, $normalizedAlias . '/')
            ) {
                return true;
            }
        }

        return false;
    }

    private static function isWithinWebroot(string $resolvedPath): bool
    {
        $webroot = Craft::getAlias('@webroot');
        $normalizedResolved = rtrim($resolvedPath, '/\\');
        $normalizedWebroot = rtrim((string)$webroot, '/\\');

        return $normalizedResolved === $normalizedWebroot
            || str_starts_with($normalizedResolved, $normalizedWebroot . DIRECTORY_SEPARATOR)
            || str_starts_with($normalizedResolved, $normalizedWebroot . '/');
    }

    /**
     * @param array<int, string> $allowedAliases
     */
    private static function getAllowedAliasError(string $translationCategory, array $allowedAliases): string
    {
        return Craft::t(
            $translationCategory,
            'Path must start with one of: {aliases}.',
            ['aliases' => implode(', ', $allowedAliases)]
        );
    }
}
