<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Result returned after ensuring ownership of a recurring queue row.
 *
 * @since 5.27.0
 */
final class RecurringQueueResult
{
    public const STATUS_CREATED = 'created';
    public const STATUS_EXISTING = 'existing';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_LOCK_MISSED = 'lock-missed';

    /**
     * @param string $status One of the STATUS_* constants
     * @param string|null $jobId Existing or newly queued job ID
     * @param int $duplicatesDeleted Number of duplicate pending rows removed
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $jobId = null,
        public readonly int $duplicatesDeleted = 0,
    ) {
    }

    public function wasCreated(): bool
    {
        return $this->status === self::STATUS_CREATED;
    }

    public function hasPending(): bool
    {
        return $this->jobId !== null;
    }

    public function missedLock(): bool
    {
        return $this->status === self::STATUS_LOCK_MISSED;
    }

    public function wasSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }
}
