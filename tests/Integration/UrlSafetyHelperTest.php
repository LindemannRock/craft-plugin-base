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

    public function testOptInExtraSchemesArePermitted(): void
    {
        $schemes = ['mailto', 'tel', 'whatsapp', 'slack', 'msteams'];

        // Opted-in schemes pass for both the bool check and the sanitizer.
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('mailto:x@y.com', $schemes));
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('tel:+15551234567', $schemes));
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('slack://channel', $schemes));
        self::assertSame('mailto:x@y.com', UrlSafetyHelper::sanitizeRedirectUrl('mailto:x@y.com', '/', $schemes));

        // Scheme matching is case-insensitive.
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('MAILTO:x@y.com', $schemes));

        // relative + http(s) still pass, //host still rejected, even with extra schemes.
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('/relative', $schemes));
        self::assertTrue(UrlSafetyHelper::isSafeRedirectUrl('https://example.com', $schemes));
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('//evil.com', $schemes));

        // A scheme that is not on the opt-in list stays rejected.
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('ftp://example.com', $schemes));
        // The default (no extra schemes) is unchanged — strict relative-or-http(s).
        self::assertFalse(UrlSafetyHelper::isSafeRedirectUrl('mailto:x@y.com'));
    }

    public function testHasDangerousSchemeFlagsExecutableSchemes(): void
    {
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('javascript:alert(1)'));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('vbscript:msgbox'));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('data:text/html,<script>'));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('file:///etc/passwd'));
        // Case-insensitive, and the `//comment` form that fools naive prefix checks.
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('JavaScript:alert(1)'));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('javascript://%0aalert(1)'));
    }

    public function testHasDangerousSchemeSeesThroughObfuscation(): void
    {
        // Leading whitespace, embedded control chars, and HTML entities are all
        // ignored by the browser when resolving the scheme.
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('  javascript:alert(1)'));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme("java\tscript:alert(1)"));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme("java\nscript:alert(1)"));
        self::assertTrue(UrlSafetyHelper::hasDangerousScheme('&#106;avascript:alert(1)'));
    }

    public function testHasDangerousSchemeAllowsSafeAndAppSchemes(): void
    {
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('https://example.com'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('http://example.com'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('/relative/path'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('mailto:x@y.com'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('tel:+15551234567'));
        // Custom app deep links must keep working.
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('myapp://open/profile'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('fb://profile/33138223345'));
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme(''));
        // A path that merely contains a dangerous word is not a dangerous scheme.
        self::assertFalse(UrlSafetyHelper::hasDangerousScheme('https://x.com/javascript:foo'));
    }
}
