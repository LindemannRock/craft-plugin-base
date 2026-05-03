<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\web\assets\components;

use craft\web\AssetBundle;

/**
 * Components Asset Bundle
 *
 * Provides styles for all base plugin components (cards, etc.).
 *
 * Usage in templates:
 * {% do view.registerAssetBundle('lindemannrock\\base\\web\\assets\\components\\ComponentsAsset') %}
 *
 * @author LindemannRock
 * @since 5.14.0
 */
class ComponentsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->css = [
            'css/components.css',
        ];
        $this->js = [
            'js/components.js',
        ];

        parent::init();
    }
}
