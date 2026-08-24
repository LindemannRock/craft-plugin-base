# RecurringQueueHelper @since(5.27.0)

Deployment-safe ownership for recurring Craft queue jobs.

Use this helper when a plugin maintains a long-lived recurring queue row: analytics cleanup, scheduled backups, scheduled exports, sync jobs, cache warmers, or any job that reschedules itself after it runs.

Do not use it for one-off jobs started by a user action, import/export action, element save event, or batch continuation. Those jobs need their own domain-specific debounce rules, not a recurring scheduler.

## Why This Exists

Recurring jobs often need a bootstrap path so a site can recover after someone clears the queue. The unsafe version is:

```php
if (!$existingJob) {
    Craft::$app->getQueue()->delay($delay)->push($job);
}
```

That check-then-push sequence is not atomic. During deploys, cache warmup, or concurrent web requests, multiple PHP processes can all pass the empty check before one has inserted the row.

`RecurringQueueHelper::ensurePending()` wraps the check/push path in Craft's mutex, re-checks inside the lock, collapses duplicate pending rows, and then queues at most one replacement.

Since Base 5.38, the replacement is scheduled through [PortableQueueScheduler](portable-queue-scheduler.md). Native database queues retain the full delay; an SQS proxy receives bounded handoffs that preserve the absolute target time.

## Ensure One Pending Row

```php
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\RecurringQueueHelper;
use lindemannrock\base\helpers\ScheduleHelper;

private function scheduleMyJob(): void
{
    $settings = $this->getSettings();

    if (!$settings->enableMyScheduledJob || $settings->myJobSchedule === 'disabled') {
        return;
    }

    $next = ScheduleHelper::calculateNext($settings->myJobSchedule);
    if ($next === null) {
        return;
    }

    $delay = $next->getTimestamp() - DateFormatHelper::now()->getTimestamp();

    $result = RecurringQueueHelper::ensurePending(
        pluginToken: 'myplugin',
        jobClass: MyScheduledJob::class,
        delay: $delay,
        jobFactory: fn() => new MyScheduledJob([
            'reschedule' => true,
            'nextRunTime' => DateFormatHelper::formatCompactDatetimeFromSettings(
                $next,
                $settings,
                null,
                false,
                pluginHandle: 'my-plugin',
            ),
        ]),
    );

    if ($result->wasCreated()) {
        // Optional: record that this request created the pending row.
    }
}
```

`pluginToken` is a stable string found in the serialized queue payload. For LindemannRock plugins this is usually the namespace handle without punctuation, such as `searchmanager`, `redirectmanager`, or `formieratingfield`.

The queue description timestamp is serialized when Craft queues the row. If date/time display settings change later, existing delayed rows keep their old label until they run or are requeued. New rows use the current effective settings. Queue labels stay compact: `numeric` months render numerically, while `short` and `long` month settings both render as short month names.

## Result Object

`ensurePending()` returns a `RecurringQueueResult` so callers can distinguish a newly queued row from an existing healthy row:

| Property / method | Description |
|-------------------|-------------|
| `$result->status` | One of `created`, `existing`, `skipped`, or `lock-missed`. |
| `$result->jobId` | Existing or newly queued job ID, or `null` when no pending row was ensured. |
| `$result->duplicatesDeleted` | Number of duplicate pending rows collapsed after keeping the earliest row. |
| `$result->wasCreated()` | `true` only when this call pushed a new queue row. |
| `$result->hasPending()` | `true` when the result has an existing or newly queued pending row. |
| `$result->missedLock()` | `true` when bootstrap could not acquire the recurring ownership mutex. |
| `$result->wasSkipped()` | `true` when a nonpositive delay skipped scheduling. |

Use `wasCreated()` for INFO-level operational logs. Routine bootstrap calls that find an existing row should usually stay quiet.

## Extra Identity Tokens

Some job classes have both recurring and manual rows. Add serialized-payload tokens to distinguish the recurring row:

```php
RecurringQueueHelper::ensurePending(
    pluginToken: 'formieratingfield',
    jobClass: GenerateCacheJob::class,
    delay: $delay,
    jobFactory: fn() => new GenerateCacheJob([
        'reschedule' => true,
        'scheduledMaster' => true,
    ]),
    extraLikeTokens: ['"scheduledMaster";b:1'],
);
```

If old queue rows predate a marker property, handle that migration deliberately before relying only on the new marker.

## Settings Changes

When the user changes or disables a schedule, delete matching pending rows and then enqueue one replacement when still enabled:

```php
public function handleMyScheduleChange(Settings $settings): void
{
    RecurringQueueHelper::deletePending(
        pluginToken: 'myplugin',
        jobClass: MyScheduledJob::class,
    );

    if (!$settings->enableMyScheduledJob || $settings->myJobSchedule === 'disabled') {
        return;
    }

    $this->scheduleMyJob();
}
```

Never early-return from a settings-change handler just because a pending row exists. The old row has the old schedule.

## Self-reschedule path

The running job should still queue the next occurrence directly from `execute()` after successful work. Use `PortableQueueScheduler`, not `ensurePending()`: the current reserved row can still exist and should not block the next run.

```php
use lindemannrock\base\queue\PortableQueueScheduler;

private function scheduleNextRun(): void
{
    $settings = MyPlugin::$plugin->getSettings();
    $next = ScheduleHelper::calculateNext($settings->myJobSchedule);

    if ($next === null) {
        return;
    }

    $delay = $next->getTimestamp() - DateFormatHelper::now()->getTimestamp();
    if ($delay <= 0) {
        return;
    }

    PortableQueueScheduler::push(
        job: new self([
            'reschedule' => true,
            'nextRunTime' => DateFormatHelper::formatCompactDatetimeFromSettings(
                $next,
                $settings,
                null,
                false,
                pluginHandle: 'my-plugin',
            ),
        ]),
        delay: $delay,
        identityTokens: ['myplugin', self::class],
        mutexName: self::SCHEDULE_MUTEX,
    );
}
```

Use the same explicit mutex name in `ensurePending()`, `deletePending()`, and the execute-time scheduler call so schedule creation and cancellation share one lifecycle lock.

## API

| Method | Purpose |
|---|---|
| `ensurePending(string $pluginToken, string $jobClass, int $delay, callable $jobFactory, array $extraLikeTokens = [], ?string $mutexName = null, int $mutexTimeout = 5)` | Atomically ensure one pending recurring row exists. Returns `RecurringQueueResult` with status, job ID, and duplicate-collapse metadata. |
| `deletePending(string $pluginToken, string $jobClass, array $extraLikeTokens = [], ?string $mutexName = null, int $mutexTimeout = 5)` | Under the schedule mutex, delete matching consumer rows plus reserved or failed Base handoffs. Throws when the cancellation lock cannot be acquired. |
| `hasPending(string $pluginToken, string $jobClass, array $extraLikeTokens = [])` | Check whether a pending row exists for a recurring job identity. |

## Pitfalls

| Pitfall | Symptom | Fix |
|---|---|---|
| Plain check-then-push during plugin bootstrap | Duplicate delayed rows after deploy or concurrent warmup | Use `ensurePending()` |
| Cache-flag dedup | Manual "Release All Jobs" deletes the row but cache still says scheduled | Query the queue table through `ensurePending()` |
| Calling `ensurePending()` from the running job's self-reschedule path | Current reserved row can block the next occurrence | Call `PortableQueueScheduler::push()` directly from self-reschedule |
| Matching only a new marker property | Legacy rows without the marker are ignored and duplicated | Collapse or migrate legacy rows first |
