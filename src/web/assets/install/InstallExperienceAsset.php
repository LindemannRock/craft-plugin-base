<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\web\assets\install;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Asset bundle for the one-time plugin install experience.
 */
class InstallExperienceAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@lindemannrock/base/web/assets/install/dist';
        $this->depends = [
            CpAsset::class,
        ];
        $this->css = [
            'css/install-experience.css',
        ];
        $this->js = [
            'js/install-experience.js',
        ];

        parent::init();
    }
}
