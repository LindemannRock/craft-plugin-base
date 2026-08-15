<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

use craft\helpers\App;
use lindemannrock\base\helpers\PluginHelper;

/**
 * Resolves consumer-owned disposable-cache tokens for the current host.
 *
 * @since 5.38.0
 */
final class DisposableCacheStorageResolver
{
    /**
     * @param list<string> $fileTokens
     * @param list<string> $applicationTokens
     */
    public function resolve(
        string $configuredStorageToken,
        string $diagnosticContext,
        array $fileTokens = ['file'],
        array $applicationTokens = ['redis', 'craft'],
        ?bool $ephemeralHost = null,
    ): DisposableCacheStorageDecision {
        $ephemeralHost ??= App::isEphemeral();
        $isFileToken = in_array($configuredStorageToken, $fileTokens, true);

        if ($isFileToken && !$ephemeralHost) {
            return new DisposableCacheStorageDecision(
                configuredStorageToken: $configuredStorageToken,
                effectiveStorage: DisposableCacheStorageDecision::EFFECTIVE_FILE,
                ephemeralHost: false,
                backendStatus: CacheBackendStatus::fromCache(null),
                applicationCache: null,
                persistenceConfidence: DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED,
                fileStorageBypassed: false,
                filePathEligible: true,
                reasonCode: DisposableCacheStorageDecision::REASON_DURABLE_PLUGIN_FILE,
            );
        }

        $isApplicationToken = in_array($configuredStorageToken, $applicationTokens, true);
        if (!$isFileToken && !$isApplicationToken) {
            return new DisposableCacheStorageDecision(
                configuredStorageToken: $configuredStorageToken,
                effectiveStorage: DisposableCacheStorageDecision::EFFECTIVE_DISABLED,
                ephemeralHost: $ephemeralHost,
                backendStatus: CacheBackendStatus::fromCache(null),
                applicationCache: null,
                persistenceConfidence: DisposableCacheStorageDecision::PERSISTENCE_UNSUITABLE,
                fileStorageBypassed: false,
                filePathEligible: false,
                reasonCode: DisposableCacheStorageDecision::REASON_UNKNOWN_CONFIGURED_TOKEN,
            );
        }

        // Resolve exactly once and carry that accepted instance in the decision.
        $applicationCache = PluginHelper::getApplicationCacheOrLog($diagnosticContext);
        $backendStatus = CacheBackendStatus::fromCache($applicationCache);
        $fileStorageBypassed = $isFileToken;

        if ($backendStatus->supportsCrossRequest($ephemeralHost)) {
            return new DisposableCacheStorageDecision(
                configuredStorageToken: $configuredStorageToken,
                effectiveStorage: DisposableCacheStorageDecision::EFFECTIVE_APPLICATION,
                ephemeralHost: $ephemeralHost,
                backendStatus: $backendStatus,
                applicationCache: $applicationCache,
                persistenceConfidence: $backendStatus->crossRequestPersistent === true
                    ? DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED
                    : DisposableCacheStorageDecision::PERSISTENCE_UNKNOWN,
                fileStorageBypassed: $fileStorageBypassed,
                filePathEligible: false,
                reasonCode: $fileStorageBypassed
                    ? DisposableCacheStorageDecision::REASON_EPHEMERAL_FILE_APPLICATION_CACHE
                    : DisposableCacheStorageDecision::REASON_EXPLICIT_APPLICATION_CACHE,
            );
        }

        return new DisposableCacheStorageDecision(
            configuredStorageToken: $configuredStorageToken,
            effectiveStorage: DisposableCacheStorageDecision::EFFECTIVE_DISABLED,
            ephemeralHost: $ephemeralHost,
            backendStatus: $backendStatus,
            applicationCache: $applicationCache,
            persistenceConfidence: DisposableCacheStorageDecision::PERSISTENCE_UNSUITABLE,
            fileStorageBypassed: $fileStorageBypassed,
            filePathEligible: false,
            reasonCode: $fileStorageBypassed
                ? DisposableCacheStorageDecision::REASON_EPHEMERAL_FILE_WITHOUT_APPLICATION_CACHE
                : DisposableCacheStorageDecision::REASON_APPLICATION_CACHE_UNSUITABLE,
        );
    }

    /**
     * Preserve an existing compatible application token, otherwise prefer craft.
     *
     * @param list<string> $applicationTokens
     */
    public static function applicationOptionToken(
        string $configuredStorageToken,
        array $applicationTokens = ['redis', 'craft'],
        string $preferredApplicationToken = 'craft',
    ): string {
        return in_array($configuredStorageToken, $applicationTokens, true)
            ? $configuredStorageToken
            : $preferredApplicationToken;
    }
}
