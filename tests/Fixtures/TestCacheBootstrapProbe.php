<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

use yii\caching\ArrayCache;
use yii\caching\FileCache;

[$script, $projectRoot, $mode, $readyPath, $releasePath] = array_pad($argv, 5, '');
require_once dirname(__DIR__, 2) . '/src/testing/bootstrap.php';

\lindemannrock\base\testing\bootstrap($projectRoot);
$cache = Craft::$app->getCache();
if (!$cache instanceof FileCache) {
    fwrite(STDERR, "Bootstrap did not install a FileCache backend.\n");
    exit(2);
}

$cachePath = $cache->cachePath;
if (!is_string($cachePath) || $cachePath === '') {
    fwrite(STDERR, "Bootstrap FileCache has no owned path.\n");
    exit(2);
}
if (file_put_contents($cachePath . DIRECTORY_SEPARATOR . 'owned-cache.bin', 'owned') === false) {
    fwrite(STDERR, "Unable to create the owned cache probe.\n");
    exit(2);
}

fwrite(STDOUT, $cachePath . PHP_EOL);
fflush(STDOUT);

if ($mode === 'replace-backend') {
    Craft::$app->set('cache', new ArrayCache());
}
if ($mode === 'failure') {
    exit(37);
}
if ($mode === 'wait') {
    if (file_put_contents($readyPath, $cachePath) === false) {
        exit(3);
    }
    while (!is_file($releasePath)) {
        usleep(20_000);
    }
}
