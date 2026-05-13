# CP Table Index-Page Pattern

The architectural rules every LindemannRock CP index page that uses the [CP Table Layout](cp-table-layout.md) should follow. The layout itself only handles rendering; this guide covers the controller / template / JS contract around it.

## TL;DR

- **Controller owns** query-param parsing, allowlist validation, filtering, sorting, and pagination.
- **Template is presentational.** It receives an already-sliced collection plus the filter/sort state.
- **Row-action JS uses a delegated `document` click listener** narrowed by `a[data-action]` + an explicit action-name allowlist. `preventDefault()` only after the match.
- **Action endpoints called via `Craft.sendActionRequest` branch on `$request->getAcceptsJson()`** so the AJAX client doesn't pay for a redirect target render it will throw away.

## Why

**Twig is the wrong place for filter/sort/paginate.** Twig templates are presentational; pushing param parsing, allowlist validation, type coercion, search clamps, and sort branches into them buries security-relevant logic in template syntax, makes it untestable from PHPUnit, and tempts copy-paste drift across plugins. Identical pages diverge subtly: one clamps search to 64 chars, the next doesn't; one validates the sort allowlist, the next concatenates raw user input into SQL. Moving the orchestration to the controller puts that logic in code that already enforces permissions and where every plugin's audit trail of param validation lives.

**Twig filter/sort/paginate over a SQL-paginated source is also a footgun.** If the loaded items array is the current page, Twig `|filter` / `|sort` only operates on what was already loaded — the filter sees 50 of 5000 rows and returns wrong counts and broken sorts.

**Broad `[data-action]` JS selectors silently break Craft forms.** `document.querySelectorAll('[data-action]')` matches every element with that attribute, regardless of value. Pages that mix row-action anchors with a Craft-rendered `<form data-action="...">` (toolbar action) get their form submission blocked by `e.preventDefault()`. None of the current pages trip this, but the smell is one stray HTML element away from a silent regression. The fix is essentially the same shape as the SQL-injection fix: explicit allowlist.

## Architecture

```
                  ┌─────────────────────────┐
HTTP query ───►   │  Controller::actionIndex│   ◄── owns: param parsing,
                  │                         │       allowlist validation,
                  │  parses + validates →   │       filter / sort / paginate
                  │  filters + sorts →      │
                  │  paginates → renders    │
                  └────────────┬────────────┘
                               │ already-sliced collection
                               │ + filter/sort state
                               ▼
                  ┌─────────────────────────┐
                  │  index.twig             │   ◄── presentational only:
                  │                         │       extends cp-table layout,
                  │  builds tableConfig,    │       configures filters/columns,
                  │  renders blocks         │       renders row cells.
                  └─────────────────────────┘
                               │
                               │ rendered HTML
                               ▼
                  ┌─────────────────────────┐
                  │  {% block scripts %}    │   ◄── one delegated click listener,
                  │                         │       narrowed by a[data-action]
                  │  Craft.sendActionRequest│       + action-name allowlist
                  │  on match               │
                  └─────────────────────────┘
```

## Reference Implementations

Two shapes exist in `search-manager` today. **Mirror them verbatim — do not invent a third.**

| Pattern | Reference | When to use |
|---|---|---|
| **Controller-side, in-memory** | `search-manager/src/controllers/ApiKeysController.php::actionIndex` + `templates/api-keys/index.twig` | Small dataset that fits comfortably in memory (rough rule: < ~1,000 rows). Load everything, filter/sort/paginate in PHP. |
| **Controller-side, SQL-paginated** | `search-manager/src/controllers/PendingSyncsController.php::actionIndex` + `templates/pending-syncs/index.twig` + `services/sync/PendingSyncRepository::search()` | DB-backed table that could grow unbounded. Filter / sort / paginate at the SQL layer via a repository method. |

The **orchestration shape is identical**: same param parsing, same allowlists, same template contract. Only the filter mechanism differs (`array_filter` vs `WHERE` clauses).

## Controller Anatomy

The canonical `actionIndex`:

```php
public function actionIndex(): Response
{
    $this->requirePermission('myPlugin:manageThings');

    $request = Craft::$app->getRequest();

    // ---- Param parsing + allowlist validation -------------------------
    // Every parameter that controls filtering or sorting goes through an
    // explicit allowlist. Anything off-list snaps to the default — never
    // pass user input through to a query or template literal.

    $typeFilter = (string) $request->getQueryParam('type', 'all');
    $validTypes = ['all', Thing::TYPE_A, Thing::TYPE_B];
    if (!in_array($typeFilter, $validTypes, true)) {
        $typeFilter = 'all';
    }

    $statusFilter = (string) $request->getQueryParam('status', 'all');
    $validStatuses = ['all', 'enabled', 'disabled'];
    if (!in_array($statusFilter, $validStatuses, true)) {
        $statusFilter = 'all';
    }

    // 64-char defensive clamp on free-text search. Keeps a runaway payload
    // (URL of any length) from reaching the filter loop.
    $search = trim((string) $request->getQueryParam('search', ''));
    if (mb_strlen($search) > 64) {
        $search = mb_substr($search, 0, 64);
    }

    $validSortFields = ['name', 'status', 'type', 'createdAt'];
    $sort = (string) $request->getParam('sort', 'name');
    if (!in_array($sort, $validSortFields, true)) {
        $sort = 'name';
    }
    $dir = strtolower((string) $request->getParam('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    // ---- Load + filter ------------------------------------------------
    // In-memory variant: fetch everything, then array_filter.
    // SQL variant: hand the validated filters to a repository search().
    $things = Thing::findAll();

    if ($statusFilter === 'enabled') {
        $things = array_values(array_filter($things, fn(Thing $t): bool => $t->enabled));
    } elseif ($statusFilter === 'disabled') {
        $things = array_values(array_filter($things, fn(Thing $t): bool => !$t->enabled));
    }

    if ($search !== '') {
        $needle = mb_strtolower($search);
        $things = array_values(array_filter($things, fn(Thing $t): bool =>
            str_contains(mb_strtolower($t->name), $needle)
        ));
    }

    // ---- Sort + paginate ----------------------------------------------
    $things = $this->sortThings($things, $sort, $dir);

    // totalCount is computed *after* filtering so the pager reflects what
    // the user can actually see, not the underlying table size.
    $totalCount = count($things);
    $page = max(1, (int) $request->getParam('page', 1));
    $limit = max(1, (int) MyPlugin::$plugin->getSettings()->itemsPerPage);
    $offset = ($page - 1) * $limit;
    $things = array_slice($things, $offset, $limit);

    return $this->renderTemplate('my-plugin/things/index', [
        'things' => $things,
        'typeFilter' => $typeFilter,
        'statusFilter' => $statusFilter,
        'search' => $search,
        'sort' => $sort,
        'dir' => $dir,
        'page' => $page,
        'limit' => $limit,
        'totalCount' => $totalCount,
        // Pass permission checks computed once here, rather than re-checking
        // in every Twig branch — keeps the template DRY and the auth surface
        // visible from a single PHP file.
        'canEdit' => Craft::$app->getUser()->checkPermission('myPlugin:editThings'),
        'canDelete' => Craft::$app->getUser()->checkPermission('myPlugin:deleteThings'),
    ]);
}
```

### Sort helpers

Sorting is typically resource-specific. Keep these as **private methods on the controller** (do not extract a base helper — see "Helper extraction" below):

```php
/**
 * @param Thing[] $things
 * @return Thing[]
 */
private function sortThings(array $things, string $sort, string $dir): array
{
    $multiplier = $dir === 'desc' ? -1 : 1;

    usort($things, function (Thing $a, Thing $b) use ($sort, $multiplier): int {
        $cmp = match ($sort) {
            'status' => strcmp($a->getStatus(), $b->getStatus()),
            'type' => strcmp($a->type, $b->type),
            'createdAt' => $this->compareNullableDates($a->createdAt, $b->createdAt),
            default => strcasecmp($a->name, $b->name),
        };

        // Stable tie-break by name so equal primary keys don't shuffle
        // between requests — keeps pagination predictable.
        if ($cmp === 0 && $sort !== 'name') {
            $cmp = strcasecmp($a->name, $b->name);
        }

        return $cmp * $multiplier;
    });

    return $things;
}

/**
 * Null-aware datetime comparison. Null sorts AFTER non-null in ascending
 * order ("Never" / "—" feels like a high value at the bottom).
 */
private function compareNullableDates(?\DateTime $a, ?\DateTime $b): int
{
    if ($a === null && $b === null) {
        return 0;
    }
    if ($a === null) {
        return 1;
    }
    if ($b === null) {
        return -1;
    }
    return $a <=> $b;
}
```

### Param-parsing conventions

| Param | Convention |
|---|---|
| Filter values (`status`, `type`, etc.) | Read with default `'all'`, validate against an explicit allowlist that includes `'all'`, snap off-list values back to `'all'`. |
| Free-text `search` | `trim()` then clamp `mb_strlen` to 64 (or your plugin's documented cap). Defends the filter loop from absurd payloads. |
| `sort` | Validate against an explicit `$validSortFields` allowlist before passing to ORDER BY / `usort`. |
| `dir` | Lowercase compare against `'desc'`; everything else snaps to `'asc'`. |
| `page` | `max(1, (int) ...)` so negative/zero/garbage becomes page 1. |
| `limit` | `max(1, (int) plugin->getSettings()->itemsPerPage)` so the per-page count matches the rest of the plugin's CP. |

### When sort/dir plumbing can be omitted

If a table has **no `sortable: true` columns** AND the underlying data source already returns rows in a deterministic, useful order (e.g. `sortOrder ASC, name ASC` from a position-managed taxonomy), the controller can omit `$validSortFields` / `$sort` / `$dir` and the template can omit the `sort:` key on `tableConfig`. The other orchestration steps (param allowlists for filters, search clamp, pagination) still apply.

**When in doubt, ask before omitting.** A table with zero sortable columns is unusual enough to merit a check — "the agent silently dropped sort plumbing" and "the original template was missing sortable columns it should have had" look identical from the outside. The agent should not decide which case it is silently.

### JSON envelopes: `asJson` vs `asSuccess` / `asFailure`

The doc's examples use `$this->asJson(['success' => …, 'message' => …, 'error' => …])` because that's the search-manager idiom. Craft also ships `asSuccess()` / `asFailure()` helpers that produce the same envelope shape with less code. **Follow the plugin's own convention** — don't gratuitously rewrite an existing `asJson` call site to `asSuccess`, and don't gratuitously rewrite an existing `asSuccess` site to manual `asJson`. The contract with the JS client is the response shape, not which helper produced it.

### `newButton` is pre-gated, not key-gated

`newButton` follows the same rule as `checkboxes` and row actions: **pre-gate via the `canX` boolean passed from the controller; omit the `permission:` key.**

```twig
{# ✓ Canonical — pre-gated from controller, no permission: key. #}
newButton: canCreate ? {
    url: url('my-plugin/things/create'),
    label: 'New Thing'|t('my-plugin'),
} : null,

{# ✗ Redundant. The layout's permission: check is bypassed entirely
     when canCreate is false (newButton is null), so the key adds
     nothing. When canCreate is true, the user has the permission
     anyway, so the layout's check is a no-op too. #}
newButton: canCreate ? {
    url: url('my-plugin/things/create'),
    label: 'New Thing'|t('my-plugin'),
    permission: 'myPlugin:createThings',
} : null,
```

The layout still **honors** `permission:` if a caller passes it (back-compat with the layout-only idiom from before pre-gating became canonical), but a pre-gating caller should not. Two checks for the same condition is belt-and-suspenders that masks the convention.

## Template Anatomy

The template is **purely presentational** — it builds `tableConfig` and renders blocks. It does **not** parse query params, filter, sort, or paginate.

```twig
{% extends 'lindemannrock-base/_layouts/cp-table' %}

{# Filters arrive pre-validated from the controller. Don't re-validate or
   default them here — the controller has already snapped off-list values
   back to 'all'. #}

{% set tableConfig = {
    plugin: {
        handle: 'my-plugin',
        name: myHelper.fullName,
    },
    page: {
        title: 'Things'|t('my-plugin'),
        subnav: 'things',
        crumbs: [
            { label: myHelper.fullName, url: url('my-plugin') },
            { label: 'Things'|t('my-plugin'), url: url('my-plugin/things') },
        ],
    },
    filters: [
        {
            type: 'status',
            param: 'status',
            current: statusFilter,
            label: 'All'|t('my-plugin'),
            options: [
                {value: 'all', label: 'All'|t('my-plugin'), status: 'all'},
                {value: 'enabled', label: 'Enabled'|t('my-plugin'), colorKey: 'enabled'},
                {value: 'disabled', label: 'Disabled'|t('my-plugin'), colorKey: 'disabled'},
            ],
        },
    ],
    search: {
        placeholder: 'Search things…'|t('my-plugin'),
        value: search,
    },
    sort: {
        field: sort,
        direction: dir,
    },
    table: {
        columns: [
            {key: 'name', label: 'Name'|t('my-plugin'), sortable: true},
            {key: 'status', label: 'Status'|t('my-plugin'), sortable: true, hideable: true},
        ],
        items: things,
        emptyMessage: 'No things yet.'|t('my-plugin'),
    },
    pagination: {
        page: page,
        limit: limit,
        totalCount: totalCount,
        itemLabel: {singular: 'thing'|t('my-plugin'), plural: 'things'|t('my-plugin')},
    },
    checkboxes: canEdit or canDelete,
} %}

{% block tableRow %}
    <td>{{ item.name }}</td>
    <td>
        {% include 'lindemannrock-base/_components/badge' with {
            label: item.enabled ? 'Enabled'|t('my-plugin') : 'Disabled'|t('my-plugin'),
            value: item.enabled ? 'enabled' : 'disabled',
            colorSet: 'status',
        } only %}
    </td>
{% endblock %}
```

### Anti-patterns to avoid

```twig
{# ✗ Don't parse query params in Twig. #}
{% set statusFilter = craft.app.request.getParam('status', 'all') %}

{# ✗ Don't filter/sort/paginate in Twig. #}
{% set filteredThings = things|filter(t => t.enabled) %}
{% set sortedThings = filteredThings|sort((a, b) => a.name <=> b.name) %}
{% set paginatedThings = sortedThings|slice(offset, limit) %}

{# ✗ Don't compute totalCount from a Twig-filtered array — silently wrong
     against a SQL-paginated source. #}
{% set totalCount = things|filter(t => t.enabled)|length %}
```

## Row-Action JS Anatomy

The base `row-actions` component renders each menu item as `<a data-action="<jsAction>" data-id="..." ...>`. The canonical handler is a **single delegated listener on `document`**, narrowed by:

1. `e.target.closest('a[data-action]')` — only react to anchors inside the row-actions menu (not random buttons that happen to have `data-action`),
2. an **explicit action-name allowlist** — only this template's known actions,
3. `e.preventDefault()` **after** the match check — so unrelated `data-action` elements keep their default behaviour.

```twig
{% block scripts %}
<script>
(function() {
    if (typeof Craft === 'undefined') return;

    // ---------------------------------------------------------------------
    // Row actions: delegated click handler, scoped by anchor + action name.
    // Confirmation lives in this handler (single source of truth); do NOT
    // also set `confirm:` on the action item, or both layers may prompt.
    // ---------------------------------------------------------------------
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('a[data-action]');
        if (!trigger) return;

        const action = trigger.dataset.action;
        // Allowlist: own actions only. Anything else falls through to its
        // own handler / default behaviour.
        if (action !== 'edit-thing' && action !== 'delete-thing') return;

        const id = parseInt(trigger.dataset.id || '0', 10);
        if (!id) return;
        e.preventDefault();

        if (action === 'delete-thing') {
            if (!confirm({{ 'Delete this thing? This cannot be undone.'|t('my-plugin')|json_encode|raw }})) return;
            Craft.sendActionRequest('POST', 'my-plugin/things/delete', {
                data: {thingId: id},
            })
                .then(function() { window.location.reload(); })
                .catch(function() {
                    Craft.cp.displayError({{ 'Couldn’t delete thing.'|t('my-plugin')|json_encode|raw }});
                });
        }
    });

    // ---------------------------------------------------------------------
    // Bulk actions: mirror selection count into button labels, then dispatch
    // via Craft.sendActionRequest.
    // ---------------------------------------------------------------------
    document.addEventListener('lr:selectionChanged', function(e) {
        const ids = (e.detail && Array.isArray(e.detail.selectedIds))
            ? e.detail.selectedIds
            : [];
        document.querySelectorAll('.lr-bulk-count').forEach(function(el) {
            el.textContent = ids.length;
        });
    });

    function selectedIds() {
        return window.lrTableSelection ? window.lrTableSelection.getSelectedIds() : [];
    }

    document.getElementById('lr-bulk-delete-btn')?.addEventListener('click', function() {
        const ids = selectedIds();
        if (!ids.length) return;
        if (!confirm({{ 'Delete the selected things?'|t('my-plugin')|json_encode|raw }})) return;
        Craft.sendActionRequest('POST', 'my-plugin/things/bulk-delete', {data: {ids: ids}})
            .then(function() { window.location.reload(); })
            .catch(function() {
                Craft.cp.displayError({{ 'Couldn’t delete things.'|t('my-plugin')|json_encode|raw }});
            });
    });
})();
</script>
{% endblock %}
```

### Anti-patterns to avoid

```js
// ✗ Smell A: broad selector, unconditional preventDefault. If a Craft
// <form data-action="..."> ever lands on the page, its submit is silently
// blocked.
document.querySelectorAll('[data-action]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        // ...dispatch by dataset.action...
    });
});

// ✗ Smell B: narrow but attached once at script-load. New rows added via
// cp-table AJAX refresh won't have a handler.
document.querySelectorAll('[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', /* ... */);
});

// ✗ Hand-rolled form POST. Use Craft.sendActionRequest instead — no CSRF
// plumbing, consistent error UX via Craft.cp.displayError, less code.
function postAction(action, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    // ...build hidden inputs, body.appendChild, form.submit()...
}
```

## Paired Controller-Side JSON Branch

A redirect-returning controller action paired with `Craft.sendActionRequest` is functionally correct but wasteful: the AJAX client transparently follows the 302, the server renders the redirect target (often a full index page) just for the JS to throw the HTML away and call `window.location.reload()`. Two server renders per click.

When the row-action JS uses `Craft.sendActionRequest`, **branch the controller action on `$request->getAcceptsJson()`** and return `asJson(...)` on the AJAX path, keeping the redirect + `setNotice`/`setError` path for non-AJAX callers:

```php
public function actionDelete(?int $thingId = null): ?Response
{
    $this->requirePostRequest();
    $this->requirePermission('myPlugin:deleteThings');

    $request = Craft::$app->getRequest();
    $acceptsJson = $request->getAcceptsJson();

    $thingId ??= (int) $request->getBodyParam('thingId');
    // ...load, validate...

    if (!$thing->delete()) {
        $errorMessage = Craft::t('my-plugin', 'Couldn’t delete thing.');
        if ($acceptsJson) {
            return $this->asJson(['success' => false, 'error' => $errorMessage]);
        }
        Craft::$app->getSession()->setError($errorMessage);
        return $this->redirect('my-plugin/things');
    }

    $successMessage = Craft::t('my-plugin', 'Thing deleted.');
    if ($acceptsJson) {
        return $this->asJson(['success' => true, 'message' => $successMessage]);
    }
    Craft::$app->getSession()->setNotice($successMessage);
    return $this->redirect('my-plugin/things');
}
```

For bulk actions that are **AJAX-only by design** (no non-AJAX caller exists), `requireAcceptsJson()` at the top is fine — no redirect path needed.

## In-Memory vs SQL-Paginated

| | In-memory | SQL-paginated |
|---|---|---|
| **Reference** | `ApiKeysController` | `PendingSyncsController` + `PendingSyncRepository` |
| **Approx. ceiling** | < ~1,000 rows | Unbounded |
| **Filter** | `array_filter` | `WHERE` clause in repository |
| **Sort** | `usort` with `match` over allowlist | `ORDER BY` with allowlist-validated column name |
| **Paginate** | `array_slice($items, $offset, $limit)` after `count()` | `LIMIT $limit OFFSET $offset` + separate `COUNT(*)` query |
| **Total count** | `count($filtered)` after filter, before slice | Repository returns `['rows' => ..., 'total' => ...]` |

**Switch to SQL when in-memory hurts:** load times noticeable, memory pressure shows up, or the row count is operator-controlled and could realistically grow past a few thousand. Don't pre-optimise — `findAll()` + `array_slice` is fine for hundreds of rows.

**Sort-column allowlist enforcement for the SQL variant lives in the repository**, not the controller, so the SQL surface validates its own inputs. The controller still validates the param against `$validSortFields` so an off-list value doesn't even reach the repo.

## Migration Checklist

For each existing CP index page that doesn't follow this pattern:

**Before you start:** confirm `itemsPerPage` exists on the plugin's Settings model with integer validation (the established convention is `int $itemsPerPage = 100` with `[['itemsPerPage'], 'integer', 'min' => 1]` + `[['itemsPerPage'], 'integer', 'max' => 500]`). The canonical controller relies on `max(1, settings->itemsPerPage)` for pagination; add the property + validation rules if missing. Without it, `settings->itemsPerPage` would return null/0 and the `max(1, …)` guard would clamp every page to a single row.

1. **Read the current state.** Note what's in Twig today: which params are parsed, which filters, which sort branches, what page/limit defaults.
2. **Move param parsing + allowlist validation to `actionIndex`.** Each filter param gets its own `$validX` allowlist; off-list → default. `sort` gets `$validSortFields`. `dir` snaps to `'asc'`/`'desc'`. `page = max(1, …)`. `limit = max(1, settings->itemsPerPage)`.
3. **Move filter/sort/paginate to the controller.** Use `array_filter` + `usort` for in-memory; hand validated filters to a repository `search()` for SQL. Extract `sortX()` + `compareNullableDates()` as **private controller methods** if they reduce repetition within the file.
4. **Compute `totalCount` after filter, before slice.** Pass it through to the template.
5. **Strip the corresponding Twig logic.** No more `craft.app.request.getParam(...)` for filter/sort/page params. No more `|filter`/`|sort`/`|slice` chains. The template receives the already-sliced collection.
6. **Pass permission booleans (`canEdit`, `canDelete`, etc.) from the controller** to the template instead of calling `currentUser.can(...)` repeatedly in Twig.
7. **Rewrite row-action JS.** Replace `querySelectorAll('[data-action]').forEach(...)` with a single delegated `document.addEventListener('click', ...)` listener, narrowed by `closest('a[data-action]')` + explicit action-name allowlist. `preventDefault()` only after the match.
8. **Swap hand-rolled form submits for `Craft.sendActionRequest`** where applicable. Saves ~25 lines per file, no CSRF plumbing, consistent error UX.
9. **Add JSON branches to action endpoints called via `Craft.sendActionRequest`** if they currently only redirect. Use `$request->getAcceptsJson()` to branch. AJAX-only endpoints can `requireAcceptsJson()` at the top.
10. **Run `composer phpstan` + `composer fix-cs`** in the affected plugin. Both must be 0 errors.
11. **Verify in the browser.** Click every row action and every bulk action; confirm filter/sort/paginate / search input still works; confirm pagination still shows the filtered total count.

## Helper Extraction Decision

**No new base helpers, for now.**

The candidates considered — `sortX()`, `compareNullableDates()`, `parseBulkIds()`, the param-allowlist boilerplate — each look like duplication across plugins. They aren't, on inspection:

- **`sortX()` is resource-specific.** The `match` over allowlist fields names domain columns and the comparators are domain comparisons. Extracting a base helper would mean either (a) a generic "sort by Closure" wrapper that wraps `usort` with extra ceremony, or (b) a registry of column-name → comparator closures that's harder to read than the inline `match`. Neither is better than what's in `ApiKeysController` today.
- **`compareNullableDates()` is one screen of code.** Pure utility, no domain coupling, candidate for `plugins/base/src/helpers/DateFormatHelper`. Worth revisiting once two plugins land it as a private method.
- **`parseBulkIds()` is one screen of defensive code.** Same story as `compareNullableDates` — fine as a private method; promote to base if a second plugin needs it.
- **Param-allowlist boilerplate is ~20 lines per controller.** Extracting it would mean either a fluent `ParamValidator` API or an array-of-rules config. Both obscure what the controller is doing for a small line-count saving. Three similar lines is better than a premature abstraction (per `plugins/CLAUDE.md`).

**Out of scope but worth flagging.** A potentially better future direction: have `row-actions` emit a custom DOM event (e.g. `row-action:clicked` with `detail: {action, id, data}`). Plugin JS then listens for the event instead of wiring delegated selectors against the rendered DOM. That would obsolete the row-action JS pattern entirely. Touches base internals plus every plugin — out of scope for a per-page pattern doc; revisit when there's a budgeted base-side project.

## Cross-References

- [CP Table Layout](cp-table-layout.md) — the layout this guide complements.
- [Components](components.md) — `row-actions`, `badge`, `filter-*`, etc.
- [JavaScript API](../developers/javascript-api.md) — `lrTableSelection`, `lr:selectionChanged`, `lr:refresh`.
- Reference code (in-memory): `plugins/search-manager/src/controllers/ApiKeysController.php` + `templates/api-keys/index.twig`.
- Reference code (SQL-paginated): `plugins/search-manager/src/controllers/PendingSyncsController.php` + `templates/pending-syncs/index.twig` + `services/sync/PendingSyncRepository.php`.
