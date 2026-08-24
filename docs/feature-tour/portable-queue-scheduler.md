# Portable queue scheduler @since(5.38.0)

Schedule Craft database-queue jobs for their real target time without exceeding an SQS proxy's 900-second delay limit. `PortableQueueScheduler` keeps native database-queue delays intact and inserts bounded handoff jobs only when the configured proxy requires them.

Use it for delayed recurring or one-off work that may run behind Craft's database queue with an SQS proxy. It does not replace domain ownership, cancellation, or recurring-job deduplication.

## Schedule a relative delay

```php
use lindemannrock\base\queue\PortableQueueScheduler;

$jobId = PortableQueueScheduler::push(
    job: new RefreshStatisticsJob(['siteId' => 2]),
    delay: 21_600,
    identityTokens: ['myplugin', RefreshStatisticsJob::class, 'site:2'],
    mutexName: 'my-plugin:refresh-statistics:2',
    priority: 1024,
    ttr: 900,
);
```

`delay` must be zero or greater. `identityTokens` must be a non-empty list of non-empty serialized-payload tokens that identify every row in the schedule. The mutex name must also be non-empty, and its timeout cannot be negative.

## Preserve an absolute target

Use `pushAt()` when schedule calculations already produced a Unix timestamp:

```php
PortableQueueScheduler::pushAt(
    job: new RefreshStatisticsJob(['siteId' => 2]),
    targetTimestamp: $nextRun->getTimestamp(),
    identityTokens: ['myplugin', RefreshStatisticsJob::class, 'site:2'],
    mutexName: 'my-plugin:refresh-statistics:2',
);
```

Each deferred handoff carries the original target timestamp. It recomputes the remaining delay when it runs, so multiple hops do not accumulate drift or dispatch the consumer early. A late handoff queues the consumer immediately with a zero delay.

## Driver behavior

| Queue shape | Scheduler behavior |
|---|---|
| Craft database queue without SQS proxy | Queue the consumer once with the full delay. |
| SQS proxy, remaining delay up to and including 900 seconds | Queue the consumer directly. |
| SQS proxy, remaining delay above 900 seconds | Queue a `DeferredQueueJob` for 900 seconds, then continue toward the same absolute target. |

The scheduler requires `craft\queue\Queue`. Passing another Yii queue implementation throws a runtime exception. Driver selection is capability-based: no Craft Cloud package or environment-name detection is used.

## Recurring ownership and cancellation

`RecurringQueueHelper::ensurePending()` uses the portable scheduler automatically. Its identity tokens allow the helper to recognize both the final consumer row and Base-owned handoffs.

When a schedule changes or is disabled, call `RecurringQueueHelper::deletePending()` with the same identity and mutex inputs. It removes pending consumer rows plus reserved or failed Base handoffs so an in-flight or manually retried handoff cannot restore a cancelled schedule.

For execute-time self-rescheduling, call `PortableQueueScheduler::push()` directly. Do not call `ensurePending()` from the running job: the current reserved row may still match the recurring identity.

## API reference

| Method | Returns | Purpose |
|---|---|---|
| `push(JobInterface $job, int $delay, array $identityTokens, string $mutexName, int $mutexTimeout = 5, ?int $priority = null, ?int $ttr = null, ?Queue $queue = null)` | `?string` | Schedule relative to the current timestamp. |
| `pushAt(JobInterface $job, int $targetTimestamp, array $identityTokens, string $mutexName, int $mutexTimeout = 5, ?int $priority = null, ?int $ttr = null, ?Queue $queue = null)` | `?string` | Schedule for an absolute Unix timestamp. |

`DeferredQueueJob` is the serialized handoff carrier. Consumers should not instantiate it or call `PortableQueueScheduler::continue()`; those are queue-runtime boundaries owned by Base.

Queue/proxy failures propagate to the caller. Craft's database row remains available for normal inspection and retry behavior rather than being silently discarded.

## Gotchas

- Keep identity tokens stable and specific enough to distinguish recurring and manual rows.
- Use the same mutex name and timeout for scheduling and cancellation.
- Preserve the consumer job's priority and TTR when those values matter; Base carries both through every handoff.
- Cancelling a schedule is a database-row operation. It does not retract an already-sent SQS message, but a missing/reserved handoff row prevents that message from creating a successor.

## Next steps

- [RecurringQueueHelper](recurring-queue-helper.md) — own exactly one recurring schedule and cancel it safely
- [ScheduleHelper](schedule-helper.md) — calculate the next wall-clock occurrence
- [QueueTtrTrait](queue-ttr.md) — provide retryable-job TTR values
