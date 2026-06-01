<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\SafeSegmentHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see SafeSegmentHelper}.
 *
 * @since 5.26.0
 */
final class SafeSegmentHelperTest extends IntegrationTestCase
{
    public function testFilenamePartRemovesPathAndHeaderUnsafeCharacters(): void
    {
        self::assertSame(
            'my-report-june-2026',
            SafeSegmentHelper::filenamePart(" My Report: June/2026\"\r\n "),
        );
    }

    public function testFilenamePartCanPreserveDotsWhenRequested(): void
    {
        self::assertSame(
            'report-v2.csv',
            SafeSegmentHelper::filenamePart(' Report v2.csv ', 'file', ['allowDots' => true]),
        );
        self::assertSame(
            'report-v2-csv',
            SafeSegmentHelper::filenamePart(' Report v2.csv '),
        );
    }

    public function testFilenamePartFallbackAndMaxLength(): void
    {
        self::assertSame('download', SafeSegmentHelper::filenamePart(' /// ', 'Download'));
        self::assertSame('abc', SafeSegmentHelper::filenamePart('abcdef', 'file', ['maxLength' => 3]));
    }

    public function testFilenamePartCanPreserveCase(): void
    {
        self::assertSame(
            'My-Report',
            SafeSegmentHelper::filenamePart('My Report', 'file', ['lowercase' => false]),
        );
    }

    public function testTokenKeyNormalizesToLowercaseDashKey(): void
    {
        self::assertSame('primary-color', SafeSegmentHelper::tokenKey(' Primary Color! '));
        self::assertSame('color', SafeSegmentHelper::tokenKey(' !!! ', 'Color'));
    }

    public function testTokenKeyTruncatesAndTrimsTrailingDash(): void
    {
        self::assertSame('primary', SafeSegmentHelper::tokenKey('Primary Color', 'token', 7));
        self::assertSame('primary-color', SafeSegmentHelper::tokenKey('Primary Color', 'token', 0));
    }
}
