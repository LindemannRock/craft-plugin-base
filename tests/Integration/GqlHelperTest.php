<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use Craft;
use lindemannrock\base\helpers\GqlHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see GqlHelper}.
 *
 * @since 5.27.0
 */
final class GqlHelperTest extends IntegrationTestCase
{
    public function testResolveSiteIdFallsBackWhenNoSiteArgumentsArePresent(): void
    {
        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        self::assertSame($siteId, GqlHelper::resolveSiteId([], $siteId));
    }

    public function testResolveSiteIdUsesSiteHandleBeforeSiteId(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();

        self::assertSame($site->id, GqlHelper::resolveSiteId([
            'site' => $site->handle,
            'siteId' => 2147483000,
        ]));
    }

    public function testResolveSiteIdValidatesNumericSiteId(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();

        self::assertSame($site->id, GqlHelper::resolveSiteId(['siteId' => (string)$site->id]));
        self::assertNull(GqlHelper::resolveSiteId(['siteId' => 2147483000]));
        self::assertNull(GqlHelper::resolveSiteId(['siteId' => 'not-a-number']));
    }

    public function testResolveSiteIdDoesNotFallbackForInvalidExplicitSite(): void
    {
        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        self::assertNull(GqlHelper::resolveSiteId(['site' => '__missing_site__'], $siteId));
    }

    public function testSiteHandleReturnsHandleForKnownSiteOnly(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();

        self::assertSame($site->handle, GqlHelper::siteHandle($site->id));
        self::assertNull(GqlHelper::siteHandle(null));
        self::assertNull(GqlHelper::siteHandle(0));
        self::assertNull(GqlHelper::siteHandle(2147483000));
    }

    public function testNullIfEmptyStringPreservesOtherFalseyValues(): void
    {
        self::assertNull(GqlHelper::nullIfEmptyString(''));
        self::assertSame('0', GqlHelper::nullIfEmptyString('0'));
        self::assertSame(0, GqlHelper::nullIfEmptyString(0));
        self::assertFalse(GqlHelper::nullIfEmptyString(false));
        self::assertSame([], GqlHelper::nullIfEmptyString([]));
    }

    public function testCanQueryReturnsFalseWhenNoActiveSchemaProvidesScope(): void
    {
        self::assertFalse(GqlHelper::canQuery('__missing_scope__'));
    }
}
