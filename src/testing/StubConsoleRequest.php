<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\testing;

use craft\console\Request as CraftConsoleRequest;

/**
 * Test double for Craft's console `Request` that exposes the web-only
 * `getUserIP()` / `getUserAgent()` / `getReferrer()` accessors so analytics /
 * tracking services that reach for `Craft::$app->request->getUserIP()` can run
 * under the console-bootstrapped integration test harness.
 *
 * `getUserIP()` lives on `yii\web\Request`, not `yii\console\Request`, so an
 * unmodified console request throws `UnknownMethodException` the moment any
 * code calls those methods. Extending the real Craft console request (rather
 * than the Yii web request) keeps Craft's mode-detection paths happy — the
 * harness still looks like a console request to `getIsConsoleRequest()` /
 * `getIsWebRequest()` and other plumbing — while filling in the three
 * accessors the tracking code actually needs.
 *
 * Installation pattern (in a test's `setUp`):
 *
 *     Craft::$app->set('request', new StubConsoleRequest(userIp: '203.0.113.42'));
 *
 * Or pass the stub directly to a service that accepts a Request parameter:
 *
 *     $this->analytics->trackClick($link, new StubConsoleRequest());
 *
 * Restoration is the caller's responsibility — `Craft::$app->set('request',
 * $original)` in `tearDown`. `IntegrationTestCase::swapPluginComponent()` does
 * not apply here because the request is a Craft-level component, not a plugin
 * component.
 *
 * @since 5.26.0
 */
final class StubConsoleRequest extends CraftConsoleRequest
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public string $userIp = '203.0.113.42',
        public string $userAgent = 'Mozilla/5.0 (Test) LindemannRockStub/1.0',
        public ?string $referrer = 'https://example.com/some/page',
        array $config = [],
    ) {
        parent::__construct($config);
    }

    public function getUserIP(): ?string
    {
        return $this->userIp;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }
}
