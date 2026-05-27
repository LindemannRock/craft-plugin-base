# Testing Utilities @since(5.25.0)

Shared scaffolding for PHPUnit integration tests against a live Craft install. Provides an abstract base test case, a Craft-console bootstrap function, and a `phpunit.xml.dist` template plugins copy once.

> [!TIP]
> This page is the API reference for the base testing layer. Copy the template, add a plugin bootstrap, and extend `IntegrationTestCase` for live Craft integration tests.

## What's in the namespace

`lindemannrock\base\testing\`

| Member | Kind | Purpose |
|--------|------|---------|
| `IntegrationTestCase` | abstract class | Base for all integration tests. Provides component swap/restore, generic DB helpers, marker cleanup, queue drain helper, and a `cleanupExternalState()` hook. |
| `StubConsoleRequest` | final class | Test double extending `craft\console\Request` that adds the web-only `getUserIP()` / `getUserAgent()` / `getReferrer()` accessors. Use when installing on `Craft::$app->set('request', …)` so mode-detection stays honest. |
| `StubWebRequest` | final class | Test double extending `yii\web\Request` with the same three accessors. Use when the service under test type-hints `yii\web\Request` (or `craft\web\Request`) as a method parameter. |
| `bootstrap()` | function | Initialises Craft as a console application from a test bootstrap file. |
| `phpunit.xml.dist.template` | template file | Copy-once `phpunit.xml.dist` shipping strict mode and a sensible default suite layout. |

Choose `StubWebRequest` when code accepts a web request argument, and choose `StubConsoleRequest` when code reads from `Craft::$app->request` inside the console-bootstrapped test harness.

## `IntegrationTestCase`

```php
abstract class IntegrationTestCase extends \PHPUnit\Framework\TestCase
```

`tearDown()` is overridden to call `cleanupExternalState()` (subclass hook), then restore swapped components, then `parent::tearDown()`. Subclasses that override `tearDown()` must call `parent::tearDown()` at the end of their cleanup, not the start, so plugin-specific DB cleanup runs against the real backend (not a stub).

### Method reference

| Method | Returns | Description |
|--------|---------|-------------|
| `cleanupExternalState()` | `void` | Override hook for non-DB cleanup (Redis, filesystem, external backends). Default is a no-op; subclasses do not need to call parent. |
| `swapPluginComponent(string $handle, string $componentId, object $stub)` | `void` | Swap a plugin's Yii service component for a test double. Original is auto-restored in `tearDown` (LIFO order). Throws `\RuntimeException` if the plugin isn't installed/enabled. |
| `countRows(string $table, array $where = [])` | `int` | Yii `Query::count()` wrapper. Pass tables in `{{%name}}` form. |
| `fetchRow(string $table, array $where)` | `?array` | Yii `Query::one()` wrapper. Returns `null` on no match. |
| `purgeRowsByMarker(string $table, string $column, string $prefix)` | `void` | `DELETE FROM $table WHERE $column LIKE '$prefix%'`. The canonical cleanup pattern for marker-tagged test rows. |
| `drainQueueJob(BaseJob $job, callable $isDone, int $maxIterations = 50)` | `void` | Run `$job->execute(Craft::$app->queue)` in a loop until `$isDone()` returns true. Capped to surface hangs as failures. |

### `swapPluginComponent()`

```php
protected function swapPluginComponent(
    string $pluginHandle,
    string $componentId,
    object $stub,
): void
```

Resolves the target plugin via `Craft::$app->plugins->getPlugin($pluginHandle)` and swaps the component slot using Yii's ServiceLocator API. The original component is captured **before** the swap so lazy-resolved components are restored to the same instance the rest of the system was using.

Multiple swaps of the same `(handle, componentId)` pair are safe — restoration walks the snapshot stack in reverse (LIFO). A swap of a slot that had no prior value calls `$plugin->clear($componentId)` on restoration rather than re-setting it.

```php
$stub = new StubBackend();
$this->swapPluginComponent('search-manager', 'backend', $stub);
// stub is in effect for the rest of the test, then auto-restored
```

### `cleanupExternalState()`

```php
protected function cleanupExternalState(): void
```

Default is a no-op. Override in subclasses that need to clean up non-DB state between tests. Called from `tearDown` **before** component restoration, so test stubs installed via `swapPluginComponent()` are still active and can be consulted by cleanup logic.

Subclasses do **not** need to call `parent::cleanupExternalState()` — the base is intentionally empty.

```php
protected function cleanupExternalState(): void
{
    // Redis cache keys
    Craft::$app->cache->delete(self::CACHE_KEY);

    // Filesystem artefacts
    @unlink($this->tempFile);

    // External search backend
    if ($this->algoliaIndex !== null) {
        $this->algoliaIndex->clearObjects();
    }
}
```

### `purgeRowsByMarker()`

```php
protected function purgeRowsByMarker(
    string $table,
    string $column,
    string $prefix,
): void
```

Deletes rows whose `$column` value starts with `$prefix`. Backed by a Yii LIKE condition with special-character escaping disabled — the literal `%` appended internally acts as the SQL wildcard.

> [!WARNING]
> Pick a marker prefix that doesn't contain SQL LIKE metacharacters (`%`, `_`). The prefix itself is passed through unescaped, so a `%` inside it will widen the match.

Per-plugin marker conventions in use:

- `__sm_test_…` — search-manager (general)
- `__sm_dedup_test__` — search-manager API key tests

### `drainQueueJob()`

```php
protected function drainQueueJob(
    \craft\queue\BaseJob $job,
    callable $isDone,
    int $maxIterations = 50,
): void
```

Generalises the BatchSyncJob "drain until empty" pattern. Works for any Craft queueable job whose single execution makes incremental progress against a queue or buffer.

`$isDone` is evaluated **before** each iteration — a zero-iteration drain is fine when the buffer is already empty. The iteration cap calls `self::fail()` to surface infinite loops as a test failure rather than a hang.

```php
$job = new BatchSyncJob();
$this->drainQueueJob(
    $job,
    fn(): bool => $this->countPendingRows() === 0,
);
```

## `bootstrap()`

```php
namespace lindemannrock\base\testing;

function bootstrap(?string $projectRoot = null): void
```

Initialises Craft as a console application so PHPUnit tests can use `Craft::$app`, plugin services, and the live DB.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$projectRoot` | `?string` | `null` (auto-detect) | Absolute path to the Craft project root — the directory holding both `bootstrap.php` and `vendor/`. |

### Auto-detection

When `$projectRoot` is null, walks up from `__DIR__` (the location of `bootstrap.php` inside base) looking for the first directory that contains **both** a `bootstrap.php` file **and** a `vendor/` directory. That pair uniquely identifies a Craft project root in both supported layouts:

- **In-tree workspace:** `plugins/base/src/testing/bootstrap.php` → walks to the workspace repo root
- **Vendor-installed:** `vendor/lindemannrock/craft-plugin-base/src/testing/bootstrap.php` → walks to the consumer project root

If auto-detection reaches the filesystem root without a match, the function exits with a stderr message asking for an explicit `$projectRoot`.

### Errors

| Condition | Behaviour |
|-----------|-----------|
| `$projectRoot/bootstrap.php` missing | Writes a clear message to STDERR, calls `exit(1)`. |
| `$projectRoot/vendor/craftcms/cms/bootstrap/console.php` missing | Writes a clear message to STDERR, calls `exit(1)`. |
| Auto-detection fails | Writes a clear message to STDERR, calls `exit(1)`. |

All three error paths exit rather than throw — `bootstrap()` runs before any test framework is wired up, so a thrown exception would produce a less actionable error.

### Calling pattern

From each plugin's `tests/bootstrap.php`:

```php
<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/vendor/lindemannrock/craft-plugin-base/src/testing/bootstrap.php';

\lindemannrock\base\testing\bootstrap();
```

CI runners with non-standard layouts can pass an explicit path:

```php
\lindemannrock\base\testing\bootstrap('/var/lib/ci-runner/workspace');
```

## Request stubs

The integration harness boots Craft in **console** mode, but services under test often read user IP / user-agent / referrer via methods that only exist on `yii\web\Request`. Base ships two stub variants so each test picks the right shape for how it installs the stub.

### Decision matrix — which one to use

| Test pattern | Use | Why |
|---|---|---|
| Pass directly as a method argument when the signature type-hints `yii\web\Request` (or `craft\web\Request`) | `StubWebRequest` | Must satisfy the parameter type. The stub is just passed around — never installed on the app — so Craft's mode-detection is untouched. |
| Install on the service locator via `Craft::$app->set('request', …)` so a service that reads `Craft::$app->request` sees the stub | `StubConsoleRequest` | Installing a web request on a console-bootstrapped Craft would make `getIsConsoleRequest()` lie. Extending the real console request keeps mode-detection honest. |

If both patterns apply in one test, use both stubs. They're independent.

### `StubConsoleRequest`

```php
namespace lindemannrock\base\testing;

final class StubConsoleRequest extends \craft\console\Request
```

Extends Craft's console request and adds:

- `getUserIP(): ?string`
- `getUserAgent(): ?string`
- `getReferrer(): ?string`

Use this when the code under test reads off `Craft::$app->request` and the request must remain a console request in Craft's eyes.

**Installation:**

```php
protected function setUp(): void
{
    parent::setUp();
    $this->savedRequest = Craft::$app->getRequest();
    Craft::$app->set('request', new StubConsoleRequest(userIp: '192.168.1.42'));
}

protected function tearDown(): void
{
    Craft::$app->set('request', $this->savedRequest);
    parent::tearDown();
}
```

> [!NOTE]
> {@see IntegrationTestCase::swapPluginComponent()} does NOT apply to the request — the request is a Craft-level component (`Craft::$app->set('request', ...)`), not a plugin component. Save and restore it manually in `setUp` / `tearDown` as shown above.

### `StubWebRequest`

```php
namespace lindemannrock\base\testing;

final class StubWebRequest extends \yii\web\Request
```

Extends `yii\web\Request` and adds the same three accessors:

- `getUserIP(): ?string`
- `getUserAgent(): ?string`
- `getReferrer(): ?string`

Use this when the **method under test** type-hints `yii\web\Request` (or `craft\web\Request`) as a parameter — the stub satisfies the type, and because it's only passed around (never installed on `Craft::$app`), it doesn't fool Craft's mode-detection.

**Usage:**

```php
$this->analytics->trackClick($link, new StubWebRequest(userIp: '203.0.113.42'));
```

> [!WARNING]
> Do NOT install `StubWebRequest` on `Craft::$app->set('request', …)`. A web request installed on a console-bootstrapped Craft would make `getIsConsoleRequest()` return false even though the harness is in console mode. Use `StubConsoleRequest` for installation, `StubWebRequest` for passing as a method argument.

### Constructor (both stubs)

Both stubs share an identical constructor signature:

```php
public function __construct(
    public string $userIp = '203.0.113.42',
    public string $userAgent = 'Mozilla/5.0 (Test) LindemannRockStub/1.0',
    public ?string $referrer = 'https://example.com/some/page',
    array $config = [],
)
```

Defaults are reasonable for "give me a working request" — override any property via constructor named args.

### Common assertions enabled by these stubs

- **Deterministic IP hashing.** Pass a fixed `userIp` and assert the analytics row stores the expected SHA-256.
- **/24 anonymisation.** Pass two IPs in the same /24 (e.g. `192.168.1.42` and `192.168.1.99`) and assert they collapse to the same hash.
- **Privacy contract.** Assert that the raw `userIp` value never appears in the stored JSON metadata blob.
- **Missing-salt path.** Combined with a settings override, prove the row lands with `ip=null` instead of crashing.

## `phpunit.xml.dist.template`

Located at `plugins/base/src/testing/phpunit.xml.dist.template`. Copy verbatim to your plugin's root as `phpunit.xml.dist`:

```bash
cp ../base/src/testing/phpunit.xml.dist.template phpunit.xml.dist
```

The template ships with:

- `bootstrap="tests/bootstrap.php"`
- `colors="true"`
- Strict mode (`failOnRisky="true"`, `failOnWarning="true"`, `beStrictAboutOutputDuringTests="true"`)
- `cacheDirectory=".phpunit.cache"` (add this directory to `.gitignore`)
- One `integration` suite pointing at `tests/Integration`
- `<source>` covering `src/`

Adjust only if your plugin has non-standard layout.

## Related

- [Bootstrapping](../developers/bootstrapping.md) — how to initialize the base module
- `phpunit.xml.dist.template` — copy-once suite setup for plugin integration tests
