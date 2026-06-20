<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use Craft;
use yii\web\NotFoundHttpException;

/**
 * Gates experimental/internal-only plugin features behind explicit env flags.
 *
 * This helper is for features that exist in source but should not have a public
 * surface yet. The env flag is always required; dev mode can only be an
 * additional requirement, never a replacement for the flag.
 *
 * @since 5.28.0
 */
class ExperimentalFeatureHelper
{
    /**
     * Return whether an experimental feature is enabled.
     *
     * Only the literal env value `true` enables the feature. Values such as
     * `1`, `yes`, an empty string, or missing env vars are intentionally off.
     */
    public static function isEnabled(string $envFlag, bool $requireDevMode = false): bool
    {
        if (self::rawEnv($envFlag) !== 'true') {
            return false;
        }

        if (!$requireDevMode) {
            return true;
        }

        return (bool)Craft::$app->getConfig()->getGeneral()->devMode;
    }

    /**
     * Require an experimental feature to be enabled for the current request.
     *
     * Use at controller/action/service entry points so hidden UI is not the
     * only protection. A 404 keeps disabled internal features from advertising
     * their existence through direct URLs.
     *
     * @throws NotFoundHttpException
     */
    public static function requireEnabled(string $envFlag, bool $requireDevMode = false): void
    {
        if (!self::isEnabled($envFlag, $requireDevMode)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Read the raw env value without Craft/Yii boolean normalization.
     */
    private static function rawEnv(string $envFlag): ?string
    {
        $value = $_SERVER[$envFlag] ?? $_ENV[$envFlag] ?? getenv($envFlag);

        return is_string($value) ? $value : null;
    }
}
