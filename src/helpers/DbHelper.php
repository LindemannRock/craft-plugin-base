<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use yii\db\Expression;

/**
 * Database Helper
 *
 * Provides DB-agnostic SQL expressions for operations that differ
 * between MySQL and PostgreSQL.
 *
 * @author    LindemannRock
 * @package   Base
 * @since     5.15.0
 */
class DbHelper
{
    /**
     * Allowed pattern for SQL column names and expressions.
     *
     * Permits: alphanumeric, underscore, dot (table.column), backticks,
     * brackets (for aliases), and common SQL expression characters
     * (parentheses, spaces, commas, single quotes, colons, asterisks)
     * needed for composed expressions like CAST() or CASE WHEN.
     */
    private const SAFE_SQL_IDENTIFIER = '/^[a-zA-Z0-9_.*`\[\]()., \':]+$/';

    /**
     * Allowed pattern for simple column/path identifiers.
     *
     * Permits: alphanumeric, underscore, dot, hyphen, backticks, brackets.
     */
    private const SAFE_COLUMN_PATTERN = '/^[a-zA-Z0-9_.`\[\]-]+$/';

    /**
     * Returns a DB-agnostic expression to extract a text value from a JSON column.
     *
     * MySQL:      JSON_UNQUOTE(JSON_EXTRACT([[column]], '$.key'))
     * PostgreSQL: [[column]]->>'key'
     *
     * A bare column reference (`column`, `a.column`, `{{%table}}.column`) is
     * wrapped in Yii's [[...]] placeholder so it stays dialect-quoted —
     * PostgreSQL folds unquoted identifiers to lowercase, which breaks
     * camelCase columns. Already-bracketed references pass through unchanged.
     *
     * Pass an array for nested paths. Each segment is treated as a single key
     * (segments are NOT split on dots), so keys containing dots or other
     * special characters round-trip safely.
     *
     * @param string $column The JSON column name (e.g., 'metadata', 'a.metadata', or '{{%table}}.metadata')
     * @param string|string[] $path A single key, or an array of keys for nested extraction
     * @return string Raw SQL expression string (use inside select/where clauses)
     * @throws \InvalidArgumentException if column or any path segment contains unsafe characters
     */
    public static function jsonExtract(string $column, string|array $path): string
    {
        self::validateIdentifier(self::normalizeColumnReference($column), 'column');
        $column = self::bracketBareColumn($column);

        $segments = is_array($path) ? array_values($path) : [$path];
        if ($segments === []) {
            throw new \InvalidArgumentException('jsonExtract path must contain at least one segment.');
        }
        foreach ($segments as $segment) {
            self::validateIdentifier($segment, 'path');
        }

        if (Craft::$app->getDb()->getIsMysql()) {
            $jsonPath = '$';
            foreach ($segments as $segment) {
                $needsQuoting = (bool) preg_match('/[^a-zA-Z0-9_]/', $segment);
                $jsonPath .= $needsQuoting ? '."' . $segment . '"' : '.' . $segment;
            }
            return "JSON_UNQUOTE(JSON_EXTRACT($column, '$jsonPath'))";
        }

        // PostgreSQL: column->'a'->'b'->>'c' for nested, column->>'key' for single
        $last = array_pop($segments);
        $sql = $column;
        foreach ($segments as $segment) {
            $sql .= "->'$segment'";
        }
        return $sql . "->>'$last'";
    }

    /**
     * Returns a DB-agnostic Expression to extract a text value from a JSON column.
     *
     * Same as jsonExtract() but returns a yii\db\Expression for use in
     * query builder methods like ->select() and ->where().
     *
     * @param string $column The JSON column name (e.g., 'metadata', 'a.metadata', or '{{%table}}.metadata')
     * @param string|string[] $path A single key, or an array of keys for nested extraction
     * @param string|null $alias Optional column alias for SELECT clauses
     * @return Expression
     * @throws \InvalidArgumentException if column, path, or alias contains unsafe characters
     */
    public static function jsonExtractExpression(string $column, string|array $path, ?string $alias = null): Expression
    {
        $sql = self::jsonExtract($column, $path);

        if ($alias !== null) {
            self::validateIdentifier($alias, 'alias');
            $sql .= ' as ' . $alias;
        }

        return new Expression($sql);
    }

    /**
     * Returns a DB-agnostic expression that casts a value to text/string.
     *
     * MySQL:      CAST([[column]] AS CHAR)
     * PostgreSQL: ([[column]])::text
     *
     * A bare column argument is wrapped in [[...]] (see jsonExtract());
     * composed expressions and Expression instances pass through unchanged.
     *
     * Useful when an expression must be compared as text or composed with
     * text functions — for example, a COALESCE() that falls back from a
     * string column to an integer column needs the integer cast to text
     * for a stable return type across drivers.
     *
     * @param string|Expression $expression The SQL expression to cast
     * @return string Raw SQL expression string
     * @throws \InvalidArgumentException if expression contains unsafe characters
     * @since 5.25.0
     */
    public static function castToText(string|Expression $expression): string
    {
        $expressionSql = $expression instanceof Expression ? $expression->expression : $expression;
        if (!($expression instanceof Expression)) {
            self::validateExpression($expressionSql);
            $expressionSql = self::bracketBareColumn($expressionSql);
        }

        if (Craft::$app->getDb()->getIsMysql()) {
            return "CAST($expressionSql AS CHAR)";
        }

        return "($expressionSql)::text";
    }

    /**
     * Returns a DB-agnostic expression to concatenate grouped values.
     *
     * MySQL:      GROUP_CONCAT([[column]] SEPARATOR separator)
     * PostgreSQL: STRING_AGG(([[column]])::text, separator)
     *
     * A bare column argument is wrapped in [[...]] (see jsonExtract());
     * composed expressions and Expression instances pass through unchanged.
     *
     * @param string|Expression $expression The SQL expression to aggregate
     * @param string $separator The separator between values (default ',')
     * @return string Raw SQL expression string
     * @throws \InvalidArgumentException if expression contains unsafe characters
     */
    public static function groupConcat(string|Expression $expression, string $separator = ','): string
    {
        $expressionSql = $expression instanceof Expression ? $expression->expression : $expression;
        if (!($expression instanceof Expression)) {
            self::validateExpression($expressionSql);
            $expressionSql = self::bracketBareColumn($expressionSql);
        }

        $quotedSeparator = str_replace("'", "''", $separator);

        if (Craft::$app->getDb()->getIsMysql()) {
            return "GROUP_CONCAT($expressionSql SEPARATOR '$quotedSeparator')";
        }

        // PostgreSQL
        return "STRING_AGG(($expressionSql)::text, '$quotedSeparator')";
    }

    /**
     * Returns a reference to a target-table column for use inside an upsert's
     * update value expression.
     *
     * In PostgreSQL's ON CONFLICT DO UPDATE, a bare column reference is
     * ambiguous — the target row and the EXCLUDED pseudo-row both expose it —
     * and raises SQLSTATE 42702. Qualifying with the table name resolves it to
     * the existing row on both drivers ({{%table}} and [[column]] are expanded
     * per driver by Yii).
     *
     * ```php
     * $db->createCommand()->upsert('{{%mytable}}', $values, [
     *     'frequency' => new Expression(
     *         'GREATEST(' . DbHelper::existingColumn('mytable', 'frequency') . ', :incoming)',
     *         [':incoming' => $frequency],
     *     ),
     * ]);
     * ```
     *
     * @param string $table Table name without the {{%...}} wrapper (e.g. 'searchmanager_search_terms')
     * @param string $column Column name of the existing row to reference
     * @return string Raw SQL fragment: {{%table}}.[[column]]
     * @throws \InvalidArgumentException if table or column is not a plain identifier
     * @since 5.35.0
     */
    public static function existingColumn(string $table, string $column): string
    {
        foreach (['table' => $table, 'column' => $column] as $label => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value)) {
                throw new \InvalidArgumentException(
                    "Invalid $label for existingColumn(): '$value'. Only a plain identifier is allowed."
                );
            }
        }

        return '{{%' . $table . '}}.[[' . $column . ']]';
    }

    /**
     * Returns a portable 0/1 projection of a boolean column for use inside
     * aggregate functions.
     *
     * PostgreSQL has no MAX()/MIN() aggregate over boolean (SQLSTATE 42883
     * "function max(boolean) does not exist"); MySQL only allows it because
     * its booleans are tinyint(1). Wrapping the flag in a CASE makes the
     * aggregate integer-typed on both drivers, with unchanged semantics
     * (MAX(...) = 0 still means "no row in the group had the flag set"):
     *
     * ```php
     * 'MAX(' . DbHelper::boolToInt('isHit') . ') AS [[isHit]]'
     * // MAX(CASE WHEN [[isHit]] THEN 1 ELSE 0 END) AS [[isHit]]
     * ```
     *
     * @param string $column Boolean column (bare or alias-qualified; bracketed passes through)
     * @return string CASE WHEN [[column]] THEN 1 ELSE 0 END
     * @throws \InvalidArgumentException if column contains unsafe characters
     * @since 5.35.0
     */
    public static function boolToInt(string $column): string
    {
        self::validateIdentifier($column, 'column');

        return 'CASE WHEN ' . self::bracketBareColumn($column) . ' THEN 1 ELSE 0 END';
    }

    /**
     * Returns a portable ORDER BY fragment that sorts NULL values last on
     * both drivers, regardless of the main sort direction.
     *
     * MySQL sorts NULLs first for ASC and last for DESC; PostgreSQL defaults
     * to the exact opposite — so the same ORDER BY reorders rows between
     * engines. For optional columns, empty means "nothing set" and belongs at
     * the bottom whichever way the real values sort. MySQL lacks NULLS LAST
     * syntax, so the portable form is the boolean IS NULL prefix (0 for real
     * values, 1 for NULL, on both engines):
     *
     * ```php
     * $query->orderBy(new Expression(DbHelper::orderByNullsLast('dateExpired', 'DESC')));
     * // ([[dateExpired]] IS NULL) ASC, [[dateExpired]] DESC
     * ```
     *
     * Wrap the result in a yii\db\Expression — as a plain orderBy string it
     * would be re-parsed. A bare column argument is bracketed (see
     * jsonExtract()); pass an Expression for composed inputs (subqueries).
     *
     * @param string|Expression $column Column or expression to sort by
     * @param string $direction 'ASC' or 'DESC' for the non-NULL values (anything else falls back to ASC)
     * @return string Raw SQL fragment for use inside an Expression
     * @throws \InvalidArgumentException if a string column contains unsafe characters
     * @since 5.35.0
     */
    public static function orderByNullsLast(string|Expression $column, string $direction = 'ASC'): string
    {
        $columnSql = $column instanceof Expression ? $column->expression : $column;
        if (!($column instanceof Expression)) {
            self::validateExpression($columnSql);
            $columnSql = self::bracketBareColumn($columnSql);
        }

        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return "($columnSql IS NULL) ASC, $columnSql $dir";
    }

    /**
     * Wrap a bare column reference in Yii's [[...]] quoting placeholder.
     *
     * PostgreSQL folds unquoted identifiers to lowercase, so a camelCase
     * column embedded raw in generated SQL resolves to a non-existent column.
     * Bracketing makes the emitted SQL dialect-quoted on both drivers.
     * Anything that is not a bare (optionally alias-qualified) column —
     * already-bracketed references, composed expressions — passes through
     * unchanged.
     */
    private static function bracketBareColumn(string $value): string
    {
        // col or alias.col
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $value)) {
            return '[[' . $value . ']]';
        }

        // {{%table}}.col — keep the table token, bracket the column. Never nest
        // {{%...}} inside [[...]] (Yii's expansion order corrupts the parser).
        if (preg_match('/^(\{\{%[a-zA-Z0-9_]+\}\})\.([a-zA-Z_][a-zA-Z0-9_]*)$/', $value, $matches)) {
            return $matches[1] . '.[[' . $matches[2] . ']]';
        }

        return $value;
    }

    /**
     * Validate a column name or JSON path against the safe identifier pattern.
     *
     * @throws \InvalidArgumentException
     */
    private static function validateIdentifier(string $value, string $label): void
    {
        if (!preg_match(self::SAFE_COLUMN_PATTERN, $value)) {
            throw new \InvalidArgumentException(
                "Invalid $label for SQL expression: '$value'. Only alphanumeric characters, underscores, dots, hyphens, backticks, and brackets are allowed."
            );
        }
    }

    /**
     * Resolve Craft table-prefix syntax before validating column references.
     */
    private static function normalizeColumnReference(string $column): string
    {
        if (!str_contains($column, '{{%')) {
            return $column;
        }

        $normalized = preg_replace_callback(
            '/\{\{%[a-zA-Z0-9_]+\}\}/',
            static fn(array $matches): string => Craft::$app->getDb()->getSchema()->getRawTableName($matches[0]),
            $column
        );

        return $normalized ?? $column;
    }

    /**
     * Validate a SQL expression against the safe expression pattern.
     *
     * @throws \InvalidArgumentException
     */
    private static function validateExpression(string $value): void
    {
        if (!preg_match(self::SAFE_SQL_IDENTIFIER, $value)) {
            throw new \InvalidArgumentException(
                "Invalid expression for SQL: '$value'. Contains disallowed characters."
            );
        }
    }
}
