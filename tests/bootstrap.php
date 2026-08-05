<?php

/**
 * PHPUnit bootstrap for the lindemannrock-base plugin's own tests.
 *
 * Base hosts the shared testing scaffolding consumer plugins delegate to.
 * Its own tests cannot delegate through the vendor path
 * (`vendor/lindemannrock/craft-plugin-base/...`) — that would be circular —
 * so we require the in-tree `src/testing/bootstrap.php` directly. The
 * vendor path is in fact a Composer path-repo symlink to this same file
 * in the workspace, but routing through the source tree keeps the
 * dependency arrow pointing the right way for any reader.
 *
 * @since 5.25.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/testing/bootstrap.php';

$projectRoot = $_SERVER['CRAFT_TEST_PROJECT_ROOT'] ?? null;
\lindemannrock\base\testing\bootstrap(is_string($projectRoot) && $projectRoot !== '' ? $projectRoot : null);
