# QueueTtrTrait

`QueueTtrTrait` provides a shared `getTtr()` implementation for queue jobs, so every long-running job in the suite reserves a sensible amount of time instead of each plugin hand-rolling its own value.

**TTR** ("time to reserve") is how long the queue runner waits for a job to finish before it assumes the worker died and releases the job back to the queue. Set it too low and a still-running rebuild gets picked up a second time; set it too high and a genuinely stuck job blocks the queue for that long. Reach for this trait on any job that can run for more than a few seconds — indexing, rebuilds, exports, bulk imports.

The default is `1800` seconds (30 minutes) — tuned for long rebuild/indexing workloads. Override per job when you know it runs shorter or longer.

## Usage

```php
use craft\queue\BaseJob;
use lindemannrock\base\traits\QueueTtrTrait;
use yii\queue\RetryableJobInterface;

class MyLongJob extends BaseJob implements RetryableJobInterface
{
    use QueueTtrTrait;

    public function canRetry($attempt, $error): bool
    {
        return false;
    }
}
```

## Override per job

Override `queueTtrSeconds()` to set a value that matches the job's real worst-case runtime — long enough that a healthy run always finishes inside it, with some headroom:

```php
protected function queueTtrSeconds(): int
{
    return 900; // 15 minutes
}
```

A non-positive return is treated as invalid and falls back to the `1800`-second default.

## Important: the job must be retryable

`getTtr()` only takes effect when the job implements `yii\queue\RetryableJobInterface` — `getTtr()` is one of that interface's two methods (alongside `canRetry()`). That's why the [Usage](#usage) example implements it.

If a job does **not** implement `RetryableJobInterface`, `yii2-queue` ignores this trait entirely and uses the **queue component's own default TTR** (configured on the queue, often much shorter than 30 minutes). So a long job that forgets the interface can be released and re-run mid-flight even though it `use`s this trait — always implement `RetryableJobInterface` when you adopt `QueueTtrTrait`.
