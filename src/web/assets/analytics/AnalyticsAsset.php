<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\web\assets\analytics;

use Craft;
use craft\web\AssetBundle;

/**
 * Analytics Asset Bundle
 *
 * Provides Chart.js and analytics helper functions for CP analytics pages.
 * Uses minified versions in production mode.
 *
 * @author LindemannRock
 * @since 5.8.0
 */
class AnalyticsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__;

        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;

        // Chart.js library + our helpers
        $this->js = [
            $devMode ? 'chart.umd.js' : 'chart.umd.min.js',
            $devMode ? 'analytics.js' : 'analytics.min.js',
        ];

        parent::init();
    }
}
