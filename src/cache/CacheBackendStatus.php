<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

use yii\caching\ArrayCache;
use yii\caching\CacheInterface;
use yii\caching\DbCache;
use yii\caching\FileCache;
use yii\redis\Cache as RedisCache;

/**
 * Immutable capabilities of Craft's exposed application-cache component.
 *
 * A null persistence or ephemeral-suitability value means the capability is
 * unknown. The top-level component is classified without inspecting wrapped
 * or hidden cache layers.
 *
 * @since 5.38.0
 */
final readonly class CacheBackendStatus
{
    public const BACKEND_REDIS = 'redis';
    public const BACKEND_MANAGED = 'managed';
    public const BACKEND_DATABASE = 'database';
    public const BACKEND_FILESYSTEM = 'filesystem';
    public const BACKEND_MEMORY = 'memory';
    public const BACKEND_UNKNOWN = 'unknown';
    public const BACKEND_UNAVAILABLE = 'unavailable';

    private const CASCADE_CACHE_CLASS = 'craft\\cachecascade\\CascadeCache';

    private function __construct(
        public string $backend,
        public bool $available,
        public ?bool $crossRequestPersistent,
        public ?bool $ephemeralSuitable,
        public ?string $componentClass,
    ) {
    }

    public static function fromCache(?CacheInterface $cache): self
    {
        if ($cache === null) {
            return new self(self::BACKEND_UNAVAILABLE, false, false, false, null);
        }

        $componentClass = $cache::class;

        if ($cache instanceof RedisCache) {
            return new self(self::BACKEND_REDIS, true, true, true, $componentClass);
        }

        if (is_a($cache, self::CASCADE_CACHE_CLASS)) {
            return new self(self::BACKEND_MANAGED, true, null, true, $componentClass);
        }

        if ($cache instanceof DbCache) {
            return new self(self::BACKEND_DATABASE, true, true, true, $componentClass);
        }

        if ($cache instanceof FileCache) {
            return new self(self::BACKEND_FILESYSTEM, true, true, false, $componentClass);
        }

        if ($cache instanceof ArrayCache) {
            return new self(self::BACKEND_MEMORY, true, false, false, $componentClass);
        }

        return new self(self::BACKEND_UNKNOWN, true, null, null, $componentClass);
    }

    /**
     * Whether the exposed component can be used best-effort for data that must
     * be reusable across requests.
     */
    public function supportsCrossRequest(bool $ephemeral): bool
    {
        if (!$this->available || $this->crossRequestPersistent === false) {
            return false;
        }

        return !$ephemeral || $this->ephemeralSuitable !== false;
    }
}
