<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\services;

/**
 * Stable result contract for Redis database diagnostics.
 *
 * @since 5.37.0
 */
final readonly class RedisDatabaseDiagnosticsResult
{
    public const OUTCOME_COMPLETE = 'complete';
    public const OUTCOME_INVALID_REQUEST = 'invalid-request';
    public const OUTCOME_UNSUPPORTED_CACHE = 'unsupported-cache';
    public const OUTCOME_UNSUPPORTED_CONNECTION = 'unsupported-connection';
    public const OUTCOME_UNSUPPORTED_SOURCE_DATABASE = 'unsupported-source-database';
    public const OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE = 'diagnostic-connection-unavailable';
    public const OUTCOME_CLUSTER_DETECTED = 'cluster-detected';
    public const OUTCOME_TOPOLOGY_UNDETERMINED = 'topology-undetermined';
    public const OUTCOME_SELECT_FAILED = 'select-failed';
    public const OUTCOME_DBSIZE_FAILED = 'dbsize-failed';

    public const TOPOLOGY_NOT_CHECKED = 'not-checked';
    public const TOPOLOGY_STANDALONE = 'standalone';
    public const TOPOLOGY_CLUSTER = 'cluster';
    public const TOPOLOGY_UNKNOWN = 'unknown';

    public const PING_NOT_ATTEMPTED = 'not-attempted';
    public const PING_OK = 'ok';
    public const PING_FAILED = 'failed';

    /**
     * @param array{
     *   from: int,
     *   to: int,
     *   databaseCount: int,
     *   maximumDatabaseCount: int,
     * }|null $requestedRange
     * @param list<array{database: int, keyCount: int}> $databases
     */
    public function __construct(
        public string $outcome,
        public string $topology,
        public string $ping,
        public bool $enumerationAvailable,
        public bool $enumerationComplete,
        public ?array $requestedRange,
        public array $databases = [],
        public bool $hasSourceConfiguredDatabase = false,
        public ?int $sourceConfiguredDatabase = null,
    ) {
    }

    /**
     * Return the stable, untranslated JSON payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'schemaVersion' => 1,
            'scope' => 'craft-redis-cache-endpoint',
            'outcome' => $this->outcome,
            'topology' => $this->topology,
        ];

        if ($this->hasSourceConfiguredDatabase) {
            $payload['sourceConfiguredDatabase'] = $this->sourceConfiguredDatabase;
        }

        $payload['automaticDatabaseSelection'] = null;
        $payload['selectIssuedOnOpen'] = false;
        $payload['ping'] = $this->ping;
        $payload['enumerationAvailable'] = $this->enumerationAvailable;
        $payload['enumerationComplete'] = $this->enumerationComplete;
        $payload['requestedRange'] = $this->requestedRange;
        $payload['databases'] = $this->databases;

        return $payload;
    }
}
