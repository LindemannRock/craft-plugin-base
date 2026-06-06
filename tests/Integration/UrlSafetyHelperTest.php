<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\UrlSafetyHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see UrlSafetyHelper}.
 *
 * @since 5.26.0
 */
final class UrlSafetyHelperTest extends IntegrationTestCase
{
    public function testAllowsRelativeAndHttpUrls(): void
    {
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('/'));
        self::assertSame('/foo/bar', UrlSafetyHelper::sanitizeRedirectUrl('/foo/bar'));
        self::assertSame('https://example.com/x', UrlSafetyHelper::sanitizeRedirectUrl('https://example.com/x'));
        self::assertSame('http://example.com', UrlSafetyHelper::sanitizeRedirectUrl('http://example.com'));
        // Scheme matching is case-insensitive.
        self::assertSame('HTTPS://example.com', UrlSafetyHelper::sanitizeRedirectUrl('HTTPS://example.com'));
    }

    public function testRejectsExecutableAndUnknownSchemes(): void
    {
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('javascript:alert(1)'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('data:text/html,<script>'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('vbscript:msgbox'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('ftp://example.com'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('not a url'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl(''));
    }

    public function testRejectsProtocolRelativeUrls(): void
    {
        // `//host` resolves to an external origin in the browser.
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('//evil.com'));
        self::assertSame('/', UrlSafetyHelper::sanitizeRedirectUrl('//evil.com/phishing'));
        self::assertSame('/404', UrlSafetyHelper::sanitizeRedirectUrl('//evil.com', '/404'));
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('//evil.com'));
    }

    public function testHonorsCustomFallback(): void
    {
        self::assertSame('/404', UrlSafetyHelper::sanitizeRedirectUrl('javascript:alert(1)', '/404'));
        self::assertSame('/safe', UrlSafetyHelper::sanitizeRedirectUrl('   ', '/safe'));
    }

    public function testTrimsBeforeEvaluating(): void
    {
        self::assertSame('https://example.com', UrlSafetyHelper::sanitizeRedirectUrl('  https://example.com  '));
        self::assertSame('/path', UrlSafetyHelper::sanitizeRedirectUrl("\t/path\n"));
    }

    public function testIsSafeRedirectUrl(): void
    {
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('/relative'));
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('https://example.com'));
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('javascript:alert(1)'));
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('mailto:x@y.com'));
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl(''));
    }
}
