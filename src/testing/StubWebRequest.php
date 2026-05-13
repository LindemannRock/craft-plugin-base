<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\testing;

use yii\web\Request as YiiWebRequest;

/**
 * Test double for `yii\web\Request` that returns deterministic IP / user-agent /
 * referrer values fixed at construction.
 *
 * Use this stub when the **method under test** type-hints `yii\web\Request` (or
 * `craft\web\Request`) as a parameter, and the test needs to pass a request in
 * directly. Example: shortlink-manager's
 * `AnalyticsService::trackClick(ShortLink $link, yii\web\Request $request, …)`.
 *
 * For services that read the request off `Craft::$app->request` instead (i.e.
 * the request is fetched from the service locator rather than passed in),
 * use {@see StubConsoleRequest} and install it via
 * `Craft::$app->set('request', …)`. That stub extends Craft's **console**
 * request so `getIsConsoleRequest()` stays truthful under the integration
 * harness — installing a web request on a console-bootstrapped Craft would
 * lie to mode-detection callers.
 *
 * Decision matrix:
 *
 * | Test pattern                                | Use                  |
 * |---------------------------------------------|----------------------|
 * | Pass as `yii\web\Request` method argument   | `StubWebRequest`     |
 * | Install on `Craft::$app->request`           | `StubConsoleRequest` |
 *
 * @since 5.26.0
 */
final class StubWebRequest extends YiiWebRequest
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
