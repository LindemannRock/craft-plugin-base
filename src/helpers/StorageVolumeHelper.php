<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\base\LocalFsInterface;
use craft\models\Volume;

/**
 * Helper for validating and displaying asset volumes used as plugin storage.
 *
 * @since 5.26.0
 */
class StorageVolumeHelper
{
    /**
     * Validate an optional volume UID for plugin-managed storage.
     *
     * @param string|null $volumeUid
     * @param array<string, mixed> $options
     * @return array<int, string> Translated validation errors
     */
    public static function validateVolume(?string $volumeUid, array $options = []): array
    {
        $volumeUid = trim((string)$volumeUid);
        if ($volumeUid === '') {
            return [];
        }

        $translationCategory = (string)($options['translationCategory'] ?? 'lindemannrock-base');
        $preventLocalWebroot = (bool)($options['preventLocalWebroot'] ?? true);
        $requireLocal = (bool)($options['requireLocal'] ?? false);

        $volume = Craft::$app->getVolumes()->getVolumeByUid($volumeUid);
        if (!$volume instanceof Volume) {
            return [
                Craft::t($translationCategory, 'Selected volume not found.'),
            ];
        }

        $fs = $volume->getFs();
        if ($requireLocal && !$fs instanceof LocalFsInterface) {
            return [
                Craft::t($translationCategory, 'Selected volume must use a local filesystem.'),
            ];
        }

        if ($preventLocalWebroot && $fs instanceof LocalFsInterface && self::isLocalVolumeInsideWebroot($volume)) {
            return [
                Craft::t($translationCategory, 'Local backup volumes cannot resolve inside @webroot.'),
            ];
        }

        return [];
    }

    /**
     * Return a human-readable volume storage label for CP display.
     */
    public static function displayPath(?string $volumeUid, string $subpath): ?string
    {
        $volumeUid = trim((string)$volumeUid);
        if ($volumeUid === '') {
            return null;
        }

        $volume = Craft::$app->getVolumes()->getVolumeByUid($volumeUid);
        if (!$volume instanceof Volume) {
            return null;
        }

        return 'Volume: ' . $volume->name . '/' . trim($subpath, '/');
    }

    /**
     * Return the resolved root path for a local volume, or null for remote/non-local volumes.
     */
    public static function localRootPath(?string $volumeUid): ?string
    {
        $volumeUid = trim((string)$volumeUid);
        if ($volumeUid === '') {
            return null;
        }

        $volume = Craft::$app->getVolumes()->getVolumeByUid($volumeUid);
        if (!$volume instanceof Volume) {
            return null;
        }

        $fs = $volume->getFs();
        if (!$fs instanceof LocalFsInterface) {
            return null;
        }

        $rootPath = trim($fs->getRootPath());
        return $rootPath !== '' ? $rootPath : null;
    }

    /**
     * Return whether a local volume resolves inside Craft's public webroot.
     */
    public static function isLocalVolumeInsideWebroot(Volume $volume): bool
    {
        $fs = $volume->getFs();
        if (!$fs instanceof LocalFsInterface) {
            return false;
        }

        $rootPath = trim($fs->getRootPath());
        if ($rootPath === '') {
            return false;
        }

        try {
            $webroot = Craft::getAlias('@webroot');
        } catch (\Throwable) {
            return false;
        }

        $normalizedRoot = self::normalizePath($rootPath);
        $normalizedWebroot = self::normalizePath((string)$webroot);

        return $normalizedRoot === $normalizedWebroot
            || str_starts_with($normalizedRoot, $normalizedWebroot . '/');
    }

    private static function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
