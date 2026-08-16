<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\queue;

use craft\queue\BaseJob;
use craft\queue\Queue;
use yii\queue\JobInterface;

/**
 * Carries a consumer job across bounded queue-delay handoffs.
 *
 * @since 5.38.0
 */
final class DeferredQueueJob extends BaseJob
{
    public JobInterface $job;

    public int $targetTimestamp;

    /**
     * @var list<string>
     */
    public array $identityTokens;

    public string $mutexName;

    public int $mutexTimeout = 5;

    public ?int $priority = null;

    public ?int $ttr = null;

    public string $chainId;

    public function execute($queue): void
    {
        if (!$queue instanceof Queue) {
            throw new \RuntimeException('Portable deferred queue jobs require Craft\'s database queue.');
        }

        PortableQueueScheduler::continue($this, $queue);
    }

    protected function defaultDescription(): ?string
    {
        return $this->job instanceof BaseJob ? $this->job->getDescription() : null;
    }
}
