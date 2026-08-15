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
 * Immutable, semantic presentation for disposable cache storage.
 *
 * String properties are source-message keys in the lindemannrock-base
 * translation category. Rendering and translation remain template concerns.
 *
 * @since 5.38.0
 */
final readonly class DisposableCacheStoragePresentation
{
    /**
     * @param list<string> $explanationKeys
     * @param 'success'|'info'|'warning' $statusSeverity
     * @param 'success'|'info'|'warning'|'inactive' $utilitySeverity
     */
    public function __construct(
        public string $headingKey,
        public array $explanationKeys,
        public string $statusSeverity,
        public bool $filePathEligible,
        public string $utilityValueKey,
        public string $utilityDescriptionKey,
        public string $utilitySeverity,
    ) {
    }
}
