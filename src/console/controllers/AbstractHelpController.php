<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\console\controllers;

use craft\console\Controller;
use lindemannrock\base\helpers\ConsoleHelpHelper;
use yii\console\ExitCode;

/**
 * Base controller for plugin-level CLI help.
 *
 * Extend this in a plugin's console namespace and return a command manifest
 * from {@see helpManifest()}. The resulting command gives operators a stable
 * discovery entry point such as `php craft my-plugin/help`.
 *
 * @since 5.26.0
 */
abstract class AbstractHelpController extends Controller
{
    /**
     * @var string Controller default action ID.
     */
    public $defaultAction = 'index';

    /**
     * Display plugin CLI help.
     *
     * Pass a command path such as `maintenance/clean-by-type` for focused help.
     */
    public function actionIndex(?string $command = null): int
    {
        $manifest = $this->helpManifest();

        $output = $command === null
            ? ConsoleHelpHelper::renderOverview($manifest)
            : ConsoleHelpHelper::renderCommand($manifest, $command);

        $this->stdout($output);

        return ExitCode::OK;
    }

    /**
     * Return the plugin command manifest consumed by {@see ConsoleHelpHelper}.
     *
     * @return array<string, mixed>
     */
    abstract protected function helpManifest(): array;
}
