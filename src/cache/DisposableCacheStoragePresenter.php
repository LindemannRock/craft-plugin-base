<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\cache;

/**
 * Maps disposable-cache decisions to translatable semantic presentation data.
 *
 * @since 5.38.0
 */
final class DisposableCacheStoragePresenter
{
    public function present(
        DisposableCacheStorageDecision $decision,
        bool $hasEnabledFamilies = true,
    ): DisposableCacheStoragePresentation {
        if ($decision->isDisabled()) {
            $presentation = new DisposableCacheStoragePresentation(
                headingKey: 'Caching disabled',
                explanationKeys: ['No suitable cross-request cache is available. Cache data is recomputed as needed.'],
                statusSeverity: 'warning',
                filePathEligible: false,
                utilityValueKey: 'Disabled',
                utilityDescriptionKey: 'Recomputed as needed',
                utilitySeverity: 'warning',
            );
        } elseif ($decision->usesFileCache()) {
            $presentation = new DisposableCacheStoragePresentation(
                headingKey: 'Using file cache',
                explanationKeys: [],
                statusSeverity: 'success',
                filePathEligible: $decision->filePathEligible,
                utilityValueKey: 'Active',
                utilityDescriptionKey: 'File cache',
                utilitySeverity: 'success',
            );
        } else {
            $presentation = $this->presentApplicationCache($decision);
        }

        if ($hasEnabledFamilies) {
            return $presentation;
        }

        return new DisposableCacheStoragePresentation(
            headingKey: $presentation->headingKey,
            explanationKeys: $presentation->explanationKeys,
            statusSeverity: $presentation->statusSeverity,
            filePathEligible: $presentation->filePathEligible,
            utilityValueKey: 'Inactive',
            utilityDescriptionKey: 'No cache families enabled',
            utilitySeverity: 'inactive',
        );
    }

    private function presentApplicationCache(
        DisposableCacheStorageDecision $decision,
    ): DisposableCacheStoragePresentation {
        [$headingKey, $utilityDescriptionKey] = match ($decision->backendStatus->backend) {
            CacheBackendStatus::BACKEND_MANAGED => ['Using managed cache', 'Managed cache'],
            CacheBackendStatus::BACKEND_REDIS => ['Using Redis cache', 'Redis cache'],
            CacheBackendStatus::BACKEND_DATABASE => ['Using database cache', 'Database cache'],
            CacheBackendStatus::BACKEND_FILESYSTEM => ['Using filesystem cache', 'Filesystem cache'],
            default => ['Using application cache', 'Application cache'],
        };

        $explanationKeys = [];
        if ($decision->fileStorageBypassed) {
            $explanationKeys[] = 'This host has an ephemeral filesystem, so the application cache is used automatically.';
        }
        if ($decision->persistenceConfidence === DisposableCacheStorageDecision::PERSISTENCE_UNKNOWN
            && $decision->backendStatus->backend === CacheBackendStatus::BACKEND_UNKNOWN) {
            $explanationKeys[] = 'Cross-request persistence could not be confirmed.';
        }

        $bestEffort = $decision->backendStatus->backend === CacheBackendStatus::BACKEND_UNKNOWN;

        return new DisposableCacheStoragePresentation(
            headingKey: $headingKey,
            explanationKeys: $explanationKeys,
            statusSeverity: $bestEffort ? 'info' : 'success',
            filePathEligible: false,
            utilityValueKey: $bestEffort ? 'Best effort' : 'Active',
            utilityDescriptionKey: $utilityDescriptionKey,
            utilitySeverity: $bestEffort ? 'info' : 'success',
        );
    }
}
