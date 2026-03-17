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
        $devMode = \Craft::$app->getConfig()->getGeneral()->devMode;

        $this->sourcePath = __DIR__;
        $this->depends = [
            CpAsset::class,
        ];
        $this->css = [
            $devMode ? 'install-experience.css' : 'install-experience.min.css',
        ];
        $this->js = [
            $devMode ? 'install-experience.js' : 'install-experience.min.js',
        ];

        parent::init();
    }
}
