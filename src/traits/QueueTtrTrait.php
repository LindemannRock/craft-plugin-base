<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\traits;

/**
 * Queue TTR Trait
 *
 * Provides a shared default TTR (time to reserve) implementation for queue jobs.
 * Default is 30 minutes to match long-running rebuild/indexing workloads.
 *
 * Usage:
 * ```php
 * class MyJob extends BaseJob
 * {
 *     use QueueTtrTrait;
 *
 *     // Optional: customize per job
 *     protected function queueTtrSeconds(): int
 *     {
 *         return 900; // 15 minutes
 *     }
 * }
 * ```
 *
 * @author LindemannRock
 * @since 5.17.0
 */
trait QueueTtrTrait
{
    /**
     * Job TTR (time to reserve), in seconds.
     *
     * Override in the using class if needed.
     */
    protected function queueTtrSeconds(): int
    {
        return 1800;
    }

    /**
     * @inheritdoc
     */
    public function getTtr(): int
    {
        $ttr = $this->queueTtrSeconds();
        return $ttr > 0 ? $ttr : 1800;
    }
}
