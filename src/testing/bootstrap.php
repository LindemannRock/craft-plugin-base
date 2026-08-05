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

    configureTestCache();
}

/**
 * Replace the project cache component with an isolated file cache for tests.
 *
 * Plugin integration tests run inside the consumer project's Craft app, so
 * config/app.php and .env may point Craft's global cache at Redis, Memcached,
 * or another shared backend. The default test harness should be deterministic;
 * tests that need a specific backend can still install it explicitly.
 *
 * @since 5.34.0
 */
function configureTestCache(): void
{
    $configuredPath = \craft\helpers\App::env('LINDEMANNROCK_BASE_TEST_CACHE_PATH');
    if (is_string($configuredPath) && $configuredPath !== '') {
        $cachePath = \craft\helpers\FileHelper::normalizePath($configuredPath);
        $basename = basename($cachePath);
        if (preg_match('/^lindemannrock-base-phpunit-cache[.-][A-Za-z0-9]+$/', $basename) !== 1) {
            throw new \RuntimeException("Refusing unsafe test cache path: {$cachePath}");
        }
    } else {
        $cachePath = \Craft::$app->getPath()->getTempPath()
            . DIRECTORY_SEPARATOR
            . 'lindemannrock-base-phpunit-cache-'
            . bin2hex(random_bytes(12));
    }

    if (!\craft\helpers\FileHelper::createDirectory($cachePath)) {
        throw new \RuntimeException("Unable to create the owned test cache directory: {$cachePath}");
    }

    register_shutdown_function(static function() use ($cachePath): void {
        if (!is_dir($cachePath)) {
            return;
        }

        try {
            \craft\helpers\FileHelper::removeDirectory($cachePath);
        } catch (\Throwable $exception) {
            fwrite(STDERR, "Unable to remove the owned test cache directory {$cachePath}: {$exception->getMessage()}\n");
        }
    });

    $config = \craft\helpers\App::cacheConfig();
    $config['cachePath'] = $cachePath;

    \Craft::$app->set('cache', $config);
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
