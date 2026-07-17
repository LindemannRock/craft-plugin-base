# DbHelper @since(5.15.0)

DB-agnostic SQL expressions for operations that differ between MySQL and PostgreSQL. Use these instead of writing MySQL-specific SQL like `JSON_EXTRACT` or `GROUP_CONCAT`.

> [!NOTE]
> Column-taking helpers wrap a bare column reference in Yii's `[[...]]` quoting placeholder @since(5.35.0). PostgreSQL folds unquoted identifiers to lowercase, so an unquoted camelCase column like `sessionId` would resolve to a non-existent `sessionid` there — bracketing keeps the emitted SQL dialect-quoted on both drivers. Already-bracketed references and composed expressions pass through unchanged.

## JSON Extraction

Extract a text value from a JSON column. Generates the correct SQL for both MySQL and PostgreSQL.

### jsonExtract()

Returns a raw SQL string for use in query conditions or raw SQL.

```php
use lindemannrock\base\helpers\DbHelper;

$sql = DbHelper::jsonExtract('metadata', 'source');
// MySQL:      JSON_UNQUOTE(JSON_EXTRACT([[metadata]], '$.source'))
// PostgreSQL: [[metadata]]->>'source'
```

Works with table aliases and special characters in keys:

```php
// With table alias
DbHelper::jsonExtract('a.metadata', 'clickType');
// MySQL: JSON_UNQUOTE(JSON_EXTRACT([[a.metadata]], '$.clickType'))

// With Craft table-prefix syntax — the {{%...}} token is preserved for Yii
// to resolve and quote; only the column part is bracketed
DbHelper::jsonExtract('{{%formie_submissions}}.content', $fieldUid);
// MySQL: JSON_UNQUOTE(JSON_EXTRACT({{%formie_submissions}}.[[content]], '$....'))

// Keys with hyphens get quoted for MySQL
DbHelper::jsonExtract('metadata', 'utm-source');
// MySQL: JSON_UNQUOTE(JSON_EXTRACT([[metadata]], '$."utm-source"'))
```

When building Yii-quoted column references, do not nest Craft table-prefix syntax inside `[[...]]`. Yii's expansion order can corrupt the column-reference parser. Prefer either a query-builder alias such as `s.content` / `[[s.content]]`, or pass `{{%table}}.column` directly to `DbHelper::jsonExtract()`.

Pass an array for nested paths @since(5.23.0). Each segment is treated as a single key — segments are NOT split on dots, so keys containing dots or hyphens round-trip safely:

```php
// Nested extraction (e.g. Formie submission content keyed by field UID)
DbHelper::jsonExtract('content', ['cc27f796-8ab2-46d8-92e3-885d504037e0', 'date']);
// MySQL:      JSON_UNQUOTE(JSON_EXTRACT(content, '$."cc27f796-8ab2-46d8-92e3-885d504037e0".date'))
// PostgreSQL: content->'cc27f796-8ab2-46d8-92e3-885d504037e0'->>'date'
```

### jsonExtractExpression()

Returns a `yii\db\Expression` for use in query builder methods.

```php
// In a SELECT clause with alias
$query->select([
    'source' => DbHelper::jsonExtractExpression('metadata', 'source'),
    'COUNT(*) as count',
]);

// Or with explicit alias
$expr = DbHelper::jsonExtractExpression('metadata', 'source', 'sourceType');
$query->addSelect($expr);

// In a WHERE clause
$query->andWhere([
    DbHelper::jsonExtract('metadata', 'status') => 'active',
]);

// In a GROUP BY
$source = DbHelper::jsonExtract('metadata', 'source');
$query->groupBy(new Expression($source));
```

## Group Concatenation

Aggregate grouped values into a single string. Generates `GROUP_CONCAT` for MySQL and `STRING_AGG` for PostgreSQL.

```php
$sql = DbHelper::groupConcat('tag');
// MySQL:      GROUP_CONCAT([[tag]] SEPARATOR ',')
// PostgreSQL: STRING_AGG(([[tag]])::text, ',')

// Custom separator
$sql = DbHelper::groupConcat('category', ' | ');
// MySQL:      GROUP_CONCAT([[category]] SEPARATOR ' | ')
// PostgreSQL: STRING_AGG(([[category]])::text, ' | ')
```

Usage in a query:

```php
$query->select([
    'userId',
    'tags' => new Expression(DbHelper::groupConcat('tag')),
])
->groupBy('userId');
```

## Text Casting @since(5.25.0)

Cast a value to text/string for safe composition with text functions or COALESCE across mixed-type columns.

### castToText()

```php
$sql = DbHelper::castToText('id');
// MySQL:      CAST([[id]] AS CHAR)
// PostgreSQL: ([[id]])::text
```

Useful when a `COALESCE()` must fall back between a text column and a non-text column — the non-text side needs an explicit cast or PostgreSQL will reject the mixed-type expression. Common pattern: dedup a fan-out by composite identity (note the `[[sessionId]]` bracketing — hand-written camelCase columns in raw SQL must be bracketed too, or PostgreSQL folds them to lowercase):

```php
// "One row per search action" — sessionId when set, row id otherwise
$identity = "COALESCE([[sessionId]], " . DbHelper::castToText('id') . ")";
$query->select(["COUNT(DISTINCT $identity) as actions"]);
// MySQL:      COUNT(DISTINCT COALESCE(`sessionId`, CAST(`id` AS CHAR)))
// PostgreSQL: COUNT(DISTINCT COALESCE("sessionId", ("id")::text))
```

Accepts an `Expression` for composed inputs:

```php
$inner = new Expression('IFNULL(id, 0)');
$sql = DbHelper::castToText($inner);
```

## Upsert Existing-Row References @since(5.35.0)

### existingColumn()

Builds a reference to a target-table column for use inside an upsert's update value expression.

PostgreSQL's `ON CONFLICT DO UPDATE` treats a bare column reference as ambiguous — the existing target row and the incoming `EXCLUDED` pseudo-row both expose it — and fails with `SQLSTATE 42702: column reference is ambiguous`. MySQL's upsert SQL never surfaces this, so the bug stays latent until the first conflicting write on a PostgreSQL install. Qualifying the reference with the table name resolves it to the existing row on both drivers:

```php
$db->createCommand()->upsert('{{%myplugin_terms}}', $values, [
    // Keep the larger of the existing and incoming frequency
    'frequency' => new Expression(
        'GREATEST(' . DbHelper::existingColumn('myplugin_terms', 'frequency') . ', :incoming)',
        [':incoming' => $frequency],
    ),
])->execute();
// DbHelper::existingColumn('myplugin_terms', 'frequency')
// returns: {{%myplugin_terms}}.[[frequency]]
```

Use it for **every** `new Expression(...)` update value that reads a column of the table being upserted (`GREATEST(...)`, `CASE WHEN [[status]] = ... THEN [[attemptCount]] ...`, counters). Plain scalar update values don't need it. The `EXCLUDED` (incoming) row can't be referenced portably across drivers — pass incoming values as `:params` instead.

## Next Steps

- [DateFormatHelper](date-format-helper.md) — DB-agnostic timezone SQL expressions
- [API Reference](../developers/api-reference.md) — full PHP API reference
