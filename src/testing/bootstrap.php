<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\testing;

/**
 * Initialise Craft as a console application for integration tests.
 *
 * Replaces the dirname-walking boilerplate previously duplicated in each
 * plugin's `tests/bootstrap.php`. Plugins call this from their own bootstrap
 * file:
 *
 *     require_once dirname(__DIR__) . '/vendor/lindemannrock/craft-plugin-base/src/testing/bootstrap.php';
 *     \lindemannrock\base\testing\bootstrap();
 *
 * @param ?string $projectRoot Path to the Craft project root — the directory
 *   that holds `bootstrap.php` and `vendor/`. When null, walks up from this
 *   file's directory until it finds a viable candidate. Pass an explicit path
 *   when CI runners or unusual layouts make auto-detection fail.
 *
 * @since 5.25.0
 */
function bootstrap(?string $projectRoot = null): void
{
    $projectRoot ??= findProjectRoot();

    $projectBootstrap = $projectRoot . '/bootstrap.php';
    if (!file_exists($projectBootstrap)) {
        fwrite(STDERR, "Project bootstrap.php not found at {$projectBootstrap}\n");
        fwrite(STDERR, "Pass an explicit \$projectRoot to bootstrap() or run tests from inside the workspace.\n");
        exit(1);
    }

    require_once $projectBootstrap;

    $craftConsole = $projectRoot . '/vendor/craftcms/cms/bootstrap/console.php';
    if (!file_exists($craftConsole)) {
        fwrite(STDERR, "Craft console bootstrap not found at {$craftConsole}\n");
        exit(1);
    }

    // Loading Craft's console.php both initialises Craft::$app and returns the
    // Application instance. Tests drive the app directly via Craft::$app — no
    // run() call needed.
    require $craftConsole;
}

/**
 * Walk up from this file's directory looking for a directory that contains
 * both `bootstrap.php` and `vendor/`. That pair uniquely identifies a Craft
 * project root in both layouts the workspace uses:
 *
 *   - In-tree:  plugins/base/src/testing/bootstrap.php  →  walks to repo root
 *   - Vendor:   vendor/lindemannrock/craft-plugin-base/src/testing/bootstrap.php
 *               →  walks to consumer project root
 *
 * Bails out with a clear stderr message if it reaches the filesystem root
 * without finding a candidate. Callers can always pass $projectRoot explicitly.
 */
function findProjectRoot(): string
{
    $dir = __DIR__;
    while (true) {
        if (file_exists($dir . '/bootstrap.php') && is_dir($dir . '/vendor')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            fwrite(STDERR, "Could not auto-detect Craft project root from " . __DIR__ . ".\n");
            fwrite(STDERR, "Pass an explicit \$projectRoot to \\lindemannrock\\base\\testing\\bootstrap().\n");
            exit(1);
        }
        $dir = $parent;
    }
}
