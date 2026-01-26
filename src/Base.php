<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * Common utilities and building blocks for LindemannRock Craft CMS plugins
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use lindemannrock\base\twigextensions\ColorExtension;
use lindemannrock\base\twigextensions\DateTimeExtension;
use lindemannrock\base\twigextensions\ExportExtension;
use lindemannrock\base\twigextensions\PluginExtension;
use yii\base\Event;
use yii\base\Module;

/**
 * Base Module
 *
 * Provides common utilities for LindemannRock plugins:
 * - Settings traits (displayName, persistence, config)
 * - Plugin helpers (bootstrap, config override)
 * - DateTime helper (centralized date/time formatting)
 * - Twig extensions (plugin name helpers, datetime filters)
 * - Shared templates (plugin-credit, etc.)
 *
 * @author LindemannRock
 * @since 5.0.0
 */
class Base extends Module
{
    /**
     * @var bool Whether the module has been registered
     */
    private static bool $registered = false;

    /**
     * Register the base module with Craft
     *
     * This method is idempotent - calling it multiple times has no effect.
     * Should be called by consuming plugins in their init() method via PluginHelper::bootstrap()
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        $moduleId = 'lindemannrock-base';

        if (!Craft::$app->hasModule($moduleId)) {
            Craft::$app->setModule($moduleId, [
                'class' => self::class,
            ]);

            // Initialize the module
            Craft::$app->getModule($moduleId);
        }

        // Register template root for shared templates
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['lindemannrock-base'] = __DIR__ . '/templates';
            }
        );

        // Register Twig extensions
        // Note: registerTwigExtension() queues extensions - no need to check if Twig exists
        Craft::$app->getView()->registerTwigExtension(new DateTimeExtension());
        Craft::$app->getView()->registerTwigExtension(new ColorExtension());
        Craft::$app->getView()->registerTwigExtension(new ExportExtension());
        Craft::$app->getView()->registerTwigExtension(new PluginExtension());

        self::$registered = true;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Set alias for the base module
        Craft::setAlias('@lindemannrock/base', __DIR__);
    }
}
