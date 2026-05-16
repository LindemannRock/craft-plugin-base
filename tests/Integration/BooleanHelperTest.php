<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\BooleanHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see BooleanHelper}.
 *
 * @since 5.25.0
 */
final class BooleanHelperTest extends IntegrationTestCase
{
    public function testNormalizeMapsRecognisedStringsCaseInsensitively(): void
    {
        foreach (['1', 'true', 'TRUE', 'True', 'on', 'ON', 'yes', 'YES', ' yes '] as $truthy) {
            self::assertTrue(
                BooleanHelper::normalize($truthy),
                "expected '$truthy' to normalize to true"
            );
        }

        foreach (['0', 'false', 'FALSE', 'False', 'off', 'OFF', 'no', 'NO', ' no '] as $falsy) {
            self::assertFalse(
                BooleanHelper::normalize($falsy, true),
                "expected '$falsy' to normalize to false even when default=true"
            );
        }

        // Real booleans and numerics are pass-through, not run through the
        // string allowlist.
        self::assertTrue(BooleanHelper::normalize(true));
        self::assertFalse(BooleanHelper::normalize(false));
        self::assertTrue(BooleanHelper::normalize(1));
        self::assertFalse(BooleanHelper::normalize(0));
    }

    public function testNormalizeFallsBackToDefaultExceptForEmptyStringWhichIsAlwaysTrue(): void
    {
        // null + unknown strings honour the supplied default.
        self::assertFalse(BooleanHelper::normalize(null), 'null defaults to false');
        self::assertTrue(BooleanHelper::normalize(null, true), 'null honours default=true');
        self::assertFalse(BooleanHelper::normalize('banana'), 'unknown strings default to false');
        self::assertTrue(BooleanHelper::normalize('banana', true), 'unknown strings honour default=true');

        // Empty string is the documented exception: returns true regardless of
        // default, so HTML attributes like `disabled=""` register as truthy.
        self::assertTrue(BooleanHelper::normalize(''), 'empty string is true even when default=false');
        self::assertTrue(BooleanHelper::normalize('', false));
    }

    public function testIsBooleanLikeContract(): void
    {
        // Booleans, null, and recognised string variants are boolean-like.
        self::assertTrue(BooleanHelper::isBooleanLike(true));
        self::assertTrue(BooleanHelper::isBooleanLike(false));
        self::assertTrue(BooleanHelper::isBooleanLike(null));
        self::assertTrue(BooleanHelper::isBooleanLike(0));
        self::assertTrue(BooleanHelper::isBooleanLike(1));
        self::assertTrue(BooleanHelper::isBooleanLike('0'));
        self::assertTrue(BooleanHelper::isBooleanLike('1'));
        self::assertTrue(BooleanHelper::isBooleanLike('true'));
        self::assertTrue(BooleanHelper::isBooleanLike('FALSE'));
        self::assertTrue(BooleanHelper::isBooleanLike(''), 'empty string is boolean-like (mirrors normalize)');

        // Arbitrary strings, non-bool ints, arrays, objects are not.
        self::assertFalse(BooleanHelper::isBooleanLike('banana'));
        self::assertFalse(BooleanHelper::isBooleanLike(2));
        self::assertFalse(BooleanHelper::isBooleanLike([]));
        self::assertFalse(BooleanHelper::isBooleanLike(new \stdClass()));
    }
}
