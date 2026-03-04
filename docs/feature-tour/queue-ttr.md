# QueueTtrTrait

`QueueTtrTrait` provides a shared `getTtr()` implementation for queue jobs.

Default TTR is `1800` seconds (30 minutes), with per-job override support.

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

## Override Per Job

```php
protected function queueTtrSeconds(): int
{
    return 900; // 15 minutes
}
```

## Important

`getTtr()` is only used by `yii2-queue` when the job implements `RetryableJobInterface`.
If a job does not implement that interface, queue-level default TTR is used instead.
