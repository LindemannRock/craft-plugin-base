<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

use Craft;
use yii\caching\CacheInterface;

/**
 * Backend-neutral, plugin-owned cache with generation-based invalidation.
 *
 * @since 5.38.0
 */
final class ScopedCache
{
    private const SCHEMA_VERSION = 'v1';
    private const GENERATION_TTL = 315_360_000;
    private const VALUE_MARKER = 'lindemannrock-scoped-cache-v1';
    private const TOKEN_PATTERN = '/\A[a-f0-9]{32}\z/D';
    private const NAMESPACE_SEGMENT_PATTERN = '/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/D';

    private string $namespace;

    public function __construct(
        private CacheInterface $cache,
        string $pluginHandle,
        string $family,
    ) {
        if (
            preg_match(self::NAMESPACE_SEGMENT_PATTERN, $pluginHandle) !== 1
            || preg_match(self::NAMESPACE_SEGMENT_PATTERN, $family) !== 1
        ) {
            throw new \InvalidArgumentException('Cache plugin handles and families must be non-empty namespace segments.');
        }

        $applicationId = (string)Craft::$app->id;
        if ($applicationId === '') {
            throw new \RuntimeException('Craft application ID is unavailable.');
        }

        $this->namespace = sprintf(
            'lr-cache:%s:app:%s:plugin:%s:family:%s',
            self::SCHEMA_VERSION,
            hash('sha256', $applicationId),
            $pluginHandle,
            $family,
        );
    }

    public function get(string|array $itemIdentity, string|array|null $scopeIdentity = null): ScopedCacheResult
    {
        $key = $this->resolveItemKey($itemIdentity, $scopeIdentity);
        if ($key === null) {
            return ScopedCacheResult::failure();
        }

        try {
            $cached = $this->cache->get($key);
        } catch (\Throwable) {
            return ScopedCacheResult::failure();
        }

        if ($cached === false) {
            return ScopedCacheResult::miss();
        }

        if (
            !is_array($cached)
            || ($cached['marker'] ?? null) !== self::VALUE_MARKER
            || !array_key_exists('value', $cached)
        ) {
            return ScopedCacheResult::failure();
        }

        return ScopedCacheResult::hit($cached['value']);
    }

    public function set(
        string|array $itemIdentity,
        mixed $value,
        int $ttl,
        string|array|null $scopeIdentity = null,
    ): bool {
        if ($ttl <= 0) {
            return false;
        }

        $key = $this->resolveItemKey($itemIdentity, $scopeIdentity);
        if ($key === null) {
            return false;
        }

        try {
            return $this->cache->set($key, [
                'marker' => self::VALUE_MARKER,
                'value' => $value,
            ], $ttl) === true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function delete(string|array $itemIdentity, string|array|null $scopeIdentity = null): bool
    {
        $key = $this->resolveItemKey($itemIdentity, $scopeIdentity);
        if ($key === null) {
            return false;
        }

        try {
            return $this->cache->delete($key) === true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function invalidateFamily(): bool
    {
        return $this->replaceGeneration($this->globalGenerationKey());
    }

    public function invalidateScope(string|array $scopeIdentity): bool
    {
        return $this->replaceGeneration($this->scopeGenerationKey($scopeIdentity));
    }

    public function status(): CacheBackendStatus
    {
        return CacheBackendStatus::fromCache($this->cache);
    }

    private function resolveItemKey(string|array $itemIdentity, string|array|null $scopeIdentity): ?string
    {
        $globalGeneration = $this->resolveGeneration($this->globalGenerationKey());
        if ($globalGeneration === null) {
            return null;
        }

        $scopeGeneration = null;
        if ($scopeIdentity !== null) {
            $scopeGeneration = $this->resolveGeneration($this->scopeGenerationKey($scopeIdentity));
            if ($scopeGeneration === null) {
                return null;
            }
        }

        return sprintf(
            '%s:item:global:%s:scope:%s:identity:%s',
            $this->namespace,
            $globalGeneration,
            $scopeGeneration ?? 'none',
            $this->hashIdentity($itemIdentity),
        );
    }

    private function resolveGeneration(string $key): ?string
    {
        try {
            $generation = $this->cache->get($key);
        } catch (\Throwable) {
            return null;
        }

        if ($this->isGenerationToken($generation)) {
            return $generation;
        }
        if ($generation !== false) {
            return null;
        }

        $candidate = $this->newGenerationToken();
        try {
            $created = $this->cache->add($key, $candidate, self::GENERATION_TTL) === true;
        } catch (\Throwable) {
            $created = false;
        }

        if ($created) {
            return $candidate;
        }

        try {
            $winner = $this->cache->get($key);
        } catch (\Throwable) {
            return null;
        }

        return $this->isGenerationToken($winner) ? $winner : null;
    }

    private function replaceGeneration(string $key): bool
    {
        try {
            return $this->cache->set($key, $this->newGenerationToken(), self::GENERATION_TTL) === true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function globalGenerationKey(): string
    {
        return $this->namespace . ':generation:global';
    }

    private function scopeGenerationKey(string|array $scopeIdentity): string
    {
        return $this->namespace . ':generation:scope:' . $this->hashIdentity($scopeIdentity);
    }

    private function newGenerationToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function isGenerationToken(mixed $token): bool
    {
        return is_string($token) && preg_match(self::TOKEN_PATTERN, $token) === 1;
    }

    private function hashIdentity(string|array $identity): string
    {
        return hash('sha256', serialize($this->normalizeIdentity($identity)));
    }

    private function normalizeIdentity(mixed $identity): array
    {
        if (is_string($identity)) {
            return ['string', $identity];
        }
        if (is_int($identity)) {
            return ['integer', $identity];
        }
        if (is_float($identity)) {
            return ['float', $identity];
        }
        if (is_bool($identity)) {
            return ['boolean', $identity];
        }
        if ($identity === null) {
            return ['null'];
        }
        if (!is_array($identity)) {
            throw new \InvalidArgumentException('Cache identities may contain only arrays and scalar values.');
        }

        if (array_is_list($identity)) {
            return ['list', array_map(fn(mixed $value): array => $this->normalizeIdentity($value), $identity)];
        }

        $entries = [];
        foreach ($identity as $key => $value) {
            $normalizedKey = is_int($key) ? ['integer', $key] : ['string', $key];
            $entries[] = [$normalizedKey, $this->normalizeIdentity($value)];
        }
        usort(
            $entries,
            static fn(array $left, array $right): int => serialize($left[0]) <=> serialize($right[0]),
        );

        return ['map', $entries];
    }
}
