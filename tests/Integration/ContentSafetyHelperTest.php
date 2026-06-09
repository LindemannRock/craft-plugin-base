<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\ContentSafetyHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see ContentSafetyHelper}.
 *
 * @since 5.27.0
 */
final class ContentSafetyHelperTest extends IntegrationTestCase
{
    public function testFlagsDangerousTags(): void
    {
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<script>alert(1)</script>'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<script src="x.js">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<svg onload="x">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<iframe src="evil">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<object data="x">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<embed src="x">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<form action="x">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<meta http-equiv="refresh">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<base href="evil">'));
    }

    public function testFlagsScriptProtocolsAndHandlers(): void
    {
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<a href="javascript:steal()">click</a>'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('vbscript:msgbox(1)'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('data:text/html,<x>'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<img src=x onerror="alert(1)">'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('<div onclick="x">'));
    }

    public function testMatchesMarkupAnywhereInTheValue(): void
    {
        // Unlike a URL scheme check, the dangerous markup can sit mid-string.
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('Summer sale <script>alert(1)</script> ends soon'));
    }

    public function testSeesThroughEntityEncoding(): void
    {
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('&lt;script&gt;alert(1)&lt;/script&gt;'));
        self::assertTrue(ContentSafetyHelper::containsMaliciousMarkup('&#106;avascript:alert(1)'));
    }

    public function testAllowsPlainTextWithBenignAngleBrackets(): void
    {
        // The precise denylist must NOT trip on a lone `<` — strip_tags would
        // mangle these, which is exactly why this helper rejects rather than strips.
        self::assertFalse(ContentSafetyHelper::containsMaliciousMarkup('price < $5 today'));
        self::assertFalse(ContentSafetyHelper::containsMaliciousMarkup('Comparison: a < b and c > d'));
        self::assertFalse(ContentSafetyHelper::containsMaliciousMarkup('Hello <b>bold</b> and <p>paragraph</p>'));
        self::assertFalse(ContentSafetyHelper::containsMaliciousMarkup('Summer Sale 2026'));
        self::assertFalse(ContentSafetyHelper::containsMaliciousMarkup(''));
    }

    public function testPopulatesThreatLabels(): void
    {
        ContentSafetyHelper::containsMaliciousMarkup('<script>x</script>', $threats);
        self::assertContains('Script tag', $threats);

        ContentSafetyHelper::containsMaliciousMarkup('&lt;script&gt;', $encodedThreats);
        self::assertContains('Script tag (encoded)', $encodedThreats);

        ContentSafetyHelper::containsMaliciousMarkup('Just plain text', $noThreats);
        self::assertSame([], $noThreats);
    }
}
