<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\web\assets\analytics;

use craft\web\AssetBundle;

/**
 * Analytics Asset Bundle
 *
 * Provides Chart.js and analytics helper functions for CP analytics pages.
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
        $this->sourcePath = '@lindemannrock/base/web/assets/analytics/dist';

        // Chart.js library + our helpers
        $this->js = [
            'js/chart.umd.min.js',
            'js/analytics.js',
        ];

        parent::init();
    }
}
