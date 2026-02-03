<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\web\assets\components;

use Craft;
use craft\web\AssetBundle;

/**
 * Components Asset Bundle
 *
 * Provides styles for all base plugin components (cards, etc.).
 * Uses minified version in production mode.
 *
 * Usage in templates:
 * {% do view.registerAssetBundle('lindemannrock\\base\\web\\assets\\components\\ComponentsAsset') %}
 *
 * @author LindemannRock
 * @since 5.5.0
 */
class ComponentsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__;

        $devMode = Craft::$app->getConfig()->getGeneral()->devMode;

        $this->css = [
            $devMode ? 'components.css' : 'components.min.css',
        ];

        parent::init();
    }
}
