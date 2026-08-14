<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

/**
 * Immutable result of a scoped cache read.
 *
 * @since 5.38.0
 */
final readonly class ScopedCacheResult
{
    public const HIT = 'hit';
    public const MISS = 'miss';
    public const FAILURE = 'failure';

    private function __construct(
        public string $state,
        public mixed $value = null,
    ) {
    }

    public static function hit(mixed $value): self
    {
        return new self(self::HIT, $value);
    }

    public static function miss(): self
    {
        return new self(self::MISS);
    }

    public static function failure(): self
    {
        return new self(self::FAILURE);
    }

    public function isHit(): bool
    {
        return $this->state === self::HIT;
    }

    public function isMiss(): bool
    {
        return $this->state === self::MISS;
    }

    public function isFailure(): bool
    {
        return $this->state === self::FAILURE;
    }
}
