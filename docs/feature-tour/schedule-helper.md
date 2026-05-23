# ScheduleHelper @since(5.25.0)

Preset scheduling for recurring queue jobs. Maps a schedule identifier (`'daily2am'`, `'weekly'`, etc.) to its next-run `DateTime` using fixed time slots, so successive runs don't drift. Returns delay-in-seconds for `Craft::$app->getQueue()->delay(...)`. Supplies labeled dropdown options for settings UIs and value allowlists for validation rules.

Use this for any plugin that needs a user-configurable recurring job (analytics cleanup, scheduled exports, usage rechecks, scheduled backups, etc.).

This helper is not a cron-expression evaluator. Plugins that need multiple named schedules, per-section schedule rows, arbitrary weekdays/month days, or custom cron expressions should keep a plugin-specific scheduler or use a future cron-specific base helper. `cache-manager` is the current example of that separate model.

## Schedule identifiers

| Value | Next-run rule |
|-------|---------------|
| `disabled` | No automatic schedule |
| `every15minutes` | Every 15 minutes |
| `every30minutes` | Every 30 minutes |
| `hourly` | Every hour |
| `every2hours` | 00:00, 02:00, 04:00, ... |
| `every3hours` | 00:00, 03:00, 06:00, ... |
| `every4hours` | 00:00, 04:00, 08:00, ... |
| `every6hours` | 00:00, 06:00, 12:00, 18:00 |
| `every12hours` | 00:00, 12:00 |
| `daily` | 00:00 |
| `daily2am` | 02:00 |
| `weekly` | Configured Craft week-start day at 00:00 |
| `every2weeks` | Same weekday + time as the starting point, +2 weeks |
| `monthly` | Same day-of-month + time as the starting point, +1 month |
| `every2months` | Same as monthly, +2 |
| `quarterly` | Same as monthly, +3 |
| `every6months` | Same as monthly, +6 |
| `yearly` | Same as monthly, +12 |

All times are in **Craft's app timezone** (`Craft::$app->getTimeZone()`), not PHP's `date.timezone`. The helper uses [`DateFormatHelper::now()`](date-format-helper.md) internally for "now".

`disabled` is the only base value for "do not schedule automatically". Do not add `manual` as a base schedule value. If a plugin currently stores `manual`, migrate or normalize it to `disabled` when adopting this helper; keep "manual/on-demand still works" in UI help text, not in the stored schedule value.

## Dropdown Options

Returns labeled options for select fields. Labels are translated via the base plugin's translation category (no duplication needed in your plugin's translation files).

Calling `getOptions()` without a value list returns every canonical option. User-facing plugin UIs should usually pass a curated list so they do not accidentally expose unsupported or poor-fit schedules.

```php
use lindemannrock\base\helpers\ScheduleHelper;

// Every canonical option as {value, label} objects (for Craft select fields)
$options = ScheduleHelper::getOptions();
// [['value' => 'disabled', 'label' => 'Disabled'], ['value' => 'every15minutes', 'label' => 'Every 15 Minutes'], ...]

// Every canonical option as an associative array (value => label)
$options = ScheduleHelper::getOptions('assoc');
// ['disabled' => 'Disabled', 'every15minutes' => 'Every 15 Minutes', ...]

// Curated UI options, preserving the requested order
$options = ScheduleHelper::getOptions(['hourly', 'daily', 'weekly', 'monthly']);

// Curated associative options
$options = ScheduleHelper::getOptions(['disabled', 'daily2am', 'weekly'], 'assoc');
```

Pass `$options` from the controller into the template — don't call helpers from Twig and don't hardcode the labels. Unknown curated values throw `InvalidArgumentException` so typos fail loudly.

## Validation Allowlist

```php
use lindemannrock\base\helpers\ScheduleHelper;

public function rules(): array
{
    return [
        // ...
        [['mySchedule'], 'in', 'range' => ScheduleHelper::getValidValues()],
    ];
}

ScheduleHelper::getValidValues();
// ['disabled', 'every15minutes', 'every30minutes', 'hourly', ...]

ScheduleHelper::getValidValues(['disabled', 'daily', 'weekly']);
// ['disabled', 'daily', 'weekly']
```

## Calculating the Next Run

Returns a `DateTime` in Craft's app timezone. Returns `null` when the schedule is `'disabled'` or unknown.

```php
$next = ScheduleHelper::calculateNext('daily2am');
// DateTime "2026-05-15 02:00 +03" (or whatever Craft's TZ is)

$next = ScheduleHelper::calculateNext('weekly');
// DateTime at next week-start day, 00:00, in Craft's TZ

$next = ScheduleHelper::calculateNext('disabled');
// null

$next = ScheduleHelper::calculateNext('every15minutes', new DateTime('2026-05-15 10:15:00'));
// DateTime "2026-05-15 10:30" (exact slots advance to the next slot)

// Pass an explicit "now" for testing or batch calculations
$next = ScheduleHelper::calculateNext('monthly', new DateTime('2026-01-31 12:00'));
// DateTime "2026-02-28 12:00" — end-of-month dates clamp safely (no overflow into March)
```

## Calculating Delay Seconds

Convenience wrapper for queue scheduling — returns the seconds from now until the next run. Returns `0` when the schedule is `'disabled'` or unknown (treat 0 as "do not enqueue").

```php
$delay = ScheduleHelper::calculateDelaySeconds('daily2am');
// 41946 (or however many seconds until next 2am Riyadh)

if ($delay > 0) {
    Craft::$app->getQueue()->delay($delay)->push(new MyJob([
        'reschedule' => true,
    ]));
}
```

## Typical Job + Bootstrap Pattern

The full pattern wires four pieces together: settings model, settings UI, the recurring job, and the plugin bootstrap. See the [Scheduler Migration Guide](../../../_docs/guides/scheduler-rollout-prompt.md) for the migration checklist. No shipped plugin is the canonical reference yet; the first migrated plugin should update the rollout tracker with observed verification notes.

### Settings model

```php
public const MY_JOB_SCHEDULES = ['disabled', 'daily2am', 'weekly', 'monthly'];

public bool $enableMyScheduledJob = true;
public string $myJobSchedule = 'daily2am';

protected static function booleanFields(): array
{
    return ['enableMyScheduledJob' /* ... */];
}

public function rules(): array
{
    return [
        [['enableMyScheduledJob'], 'boolean'],
        [['myJobSchedule'], 'in', 'range' => ScheduleHelper::getValidValues(self::MY_JOB_SCHEDULES)],
        // ...
    ];
}
```

### Settings UI (controller passes options into template)

```php
// SettingsController
return $this->renderTemplate('my-plugin/settings/scheduling', [
    'settings' => $settings,
    'scheduleOptions' => ScheduleHelper::getOptions(Settings::MY_JOB_SCHEDULES),
]);
```

```twig
{# settings template #}
{{ forms.lightswitchField({
    label: 'Enable Scheduled Job'|t('my-plugin'),
    name: 'settings[enableMyScheduledJob]',
    on: settings.enableMyScheduledJob,
    toggle: 'my-job-schedule-wrapper',
}) }}

<div id="my-job-schedule-wrapper" class="{{ not settings.enableMyScheduledJob ? 'hidden' }}">
    {{ forms.selectField({
        label: 'Schedule'|t('my-plugin'),
        name: 'settings[myJobSchedule]',
        value: settings.myJobSchedule,
        options: scheduleOptions,
    }) }}
</div>
```

### Job class

```php
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\ScheduleHelper;
use lindemannrock\base\traits\QueueTtrTrait;

class MyScheduledJob extends BaseJob implements RetryableJobInterface
{
    use QueueTtrTrait;

    public bool $reschedule = false;
    public ?string $nextRunTime = null;

    public function canRetry($attempt, $error): bool
    {
        return false;
    }

    public function init(): void
    {
        parent::init();

        if ($this->reschedule && !$this->nextRunTime) {
            $next = ScheduleHelper::calculateNext(
                MyPlugin::$plugin->getSettings()->myJobSchedule
            );
            if ($next !== null) {
                // calculateNext returns DateTime in Craft TZ — pass isUtc=false
                $this->nextRunTime = DateFormatHelper::formatCompactDatetime($next, false, false);
            }
        }
    }

    public function execute($queue): void
    {
        // ... do the work

        if ($this->reschedule) {
            $this->scheduleNextRun();
        }
    }

    public function getDescription(): ?string
    {
        $pluginName = MyPlugin::$plugin->getSettings()->getDisplayName();
        $description = Craft::t('my-plugin', '{pluginName}: My scheduled work', [
            'pluginName' => $pluginName,
        ]);

        if ($this->nextRunTime) {
            $description .= " ({$this->nextRunTime})";
        }

        return $description;
    }

    private function scheduleNextRun(): void
    {
        $settings = MyPlugin::$plugin->getSettings();

        if (!$settings->enableMyScheduledJob) {
            return;
        }

        $next = ScheduleHelper::calculateNext($settings->myJobSchedule);
        if ($next === null) {
            return;
        }

        $delay = $next->getTimestamp() - time();
        if ($delay <= 0) {
            return;
        }

        Craft::$app->getQueue()->delay($delay)->push(new self([
            'reschedule' => true,
            'nextRunTime' => DateFormatHelper::formatCompactDatetime($next, false, false),
        ]));
    }
}
```

### Plugin bootstrap

```php
public function init(): void
{
    parent::init();
    // ...

    $this->scheduleMyJob();
}

private function scheduleMyJob(): void
{
    $settings = $this->getSettings();

    if (!$settings->enableMyScheduledJob || $settings->myJobSchedule === 'disabled') {
        return;
    }

    // Plain queue check — no cache flag. This is what makes the job
    // recoverable from a manual "Release All Jobs" in the CP queue UI.
    $existingJob = (new \craft\db\Query())
        ->from('{{%queue}}')
        ->where(['like', 'job', 'myplugin'])
        ->andWhere(['like', 'job', 'MyScheduledJob'])
        ->exists();

    if ($existingJob) {
        return;
    }

    $next = ScheduleHelper::calculateNext($settings->myJobSchedule);
    if ($next === null) {
        return;
    }

    $delay = $next->getTimestamp() - time();
    if ($delay <= 0) {
        return;
    }

    Craft::$app->getQueue()->delay($delay)->push(new MyScheduledJob([
        'reschedule' => true,
        'nextRunTime' => DateFormatHelper::formatCompactDatetime($next, false, false),
    ]));
}
```

### Settings change hook (always cancel + re-push)

```php
// In SettingsController::actionSave, BEFORE the schedule-change handlers:
$plugin->setSettings([]);  // Reset cache so handlers see fresh values

if ($oldEnabled !== $settings->enableMyScheduledJob ||
    $oldSchedule !== $settings->myJobSchedule
) {
    $plugin->handleMyScheduleChange($settings);
}
```

```php
// On the plugin class:
public function handleMyScheduleChange(Settings $settings): void
{
    // Always cancel pending rows so the new schedule applies immediately
    Craft::$app->getDb()->createCommand()
        ->delete('{{%queue}}', [
            'and',
            ['like', 'job', 'myplugin'],
            ['like', 'job', 'MyScheduledJob'],
        ])
        ->execute();

    if (!$settings->enableMyScheduledJob || $settings->myJobSchedule === 'disabled') {
        return;
    }

    $delay = ScheduleHelper::calculateDelaySeconds($settings->myJobSchedule);
    if ($delay <= 0) {
        return;
    }

    Craft::$app->getQueue()->delay($delay)->push(new MyScheduledJob([
        'reschedule' => true,
    ]));
}
```

## Common Pitfalls

These cost real time during the first migration. The rollout guide covers them in detail.

| Pitfall | Symptom | Fix |
|---------|---------|-----|
| Using `new DateTime()` instead of `DateFormatHelper::now()` | Schedule math drifts when PHP TZ ≠ Craft TZ | Use `DateFormatHelper::now()` (already done inside the helper — only matters if you compute "now" yourself) |
| Using `date('M j, g:ia', time() + $delay)` for the display string | Display in wrong TZ; baked into serialized payload, so old jobs keep stale string until re-push | `DateFormatHelper::formatCompactDatetime($next, false, false)` |
| Cache-flag dedup in bootstrap | Manual "Release All Jobs" deletes the row but cache still says "scheduled" — job stays gone for hours | Plain `{{%queue}}` LIKE-check, no cache flag |
| Self-reschedule LIKE-checks the queue | False-matches the still-reserved row of the currently-executing job, kills the reschedule silently | Just push from inside `execute()`'s reschedule path; no dedup needed |
| `handleScheduleChange()` early-returns when a job exists | User changes schedule, but the previously-queued job still fires on the OLD delay | Always `delete()` then `push()` — never short-circuit |
| Settings cache reset AFTER the change handler | Job's `init()` reads `getSettings()` from stale cache, generates display string for the OLD schedule | Reset (`$plugin->setSettings([])`) BEFORE calling the handler |
| Settings field not in `_validationAttributesForSection()` | `saveToDatabase()` doesn't persist the new value; dropdown reverts on next visit | Add the field name to the section's allowlist in the controller |

## Next Steps

- [DateFormatHelper](date-format-helper.md) — TZ-aware "now" + display formatting (used internally by `ScheduleHelper`)
- [QueueTtrTrait](queue-ttr.md) — shared queue TTR for jobs
- [Scheduler Migration Guide](../../../_docs/guides/scheduler-rollout-prompt.md) — per-plugin rollout prompt
- [Scheduler Rollout Tracker](../../../_docs/rollouts/scheduler-pattern.md) — cross-plugin status
