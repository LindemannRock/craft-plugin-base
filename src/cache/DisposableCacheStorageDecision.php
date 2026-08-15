<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

use yii\caching\CacheInterface;

/**
 * Immutable effective-storage decision for disposable plugin cache data.
 *
 * @since 5.38.0
 */
final readonly class DisposableCacheStorageDecision
{
    public const EFFECTIVE_APPLICATION = 'application';
    public const EFFECTIVE_FILE = 'file';
    public const EFFECTIVE_DISABLED = 'disabled';

    public const PERSISTENCE_CONFIRMED = 'confirmed';
    public const PERSISTENCE_UNKNOWN = 'unknown';
    public const PERSISTENCE_UNSUITABLE = 'unsuitable';

    public const REASON_EXPLICIT_APPLICATION_CACHE = 'explicit-application-cache';
    public const REASON_DURABLE_PLUGIN_FILE = 'durable-plugin-file';
    public const REASON_EPHEMERAL_FILE_APPLICATION_CACHE = 'ephemeral-file-application-cache';
    public const REASON_EPHEMERAL_FILE_WITHOUT_APPLICATION_CACHE = 'ephemeral-file-without-application-cache';
    public const REASON_APPLICATION_CACHE_UNSUITABLE = 'application-cache-unsuitable';
    public const REASON_UNKNOWN_CONFIGURED_TOKEN = 'unknown-configured-token';

    public function __construct(
        public string $configuredStorageToken,
        public string $effectiveStorage,
        public bool $ephemeralHost,
        public CacheBackendStatus $backendStatus,
        public ?CacheInterface $applicationCache,
        public string $persistenceConfidence,
        public bool $fileStorageBypassed,
        public bool $filePathEligible,
        public string $reasonCode,
    ) {
    }

    public function usesApplicationCache(): bool
    {
        return $this->effectiveStorage === self::EFFECTIVE_APPLICATION;
    }

    public function usesFileCache(): bool
    {
        return $this->effectiveStorage === self::EFFECTIVE_FILE;
    }

    public function isDisabled(): bool
    {
        return $this->effectiveStorage === self::EFFECTIVE_DISABLED;
    }
}
