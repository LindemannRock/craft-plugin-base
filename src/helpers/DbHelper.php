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
     * MySQL:      JSON_UNQUOTE(JSON_EXTRACT(column, '$.key'))
     * PostgreSQL: column->>'key'
     *
     * Pass an array for nested paths. Each segment is treated as a single key
     * (segments are NOT split on dots), so keys containing dots or other
     * special characters round-trip safely.
     *
     * @param string $column The JSON column name (e.g., 'metadata' or 'a.metadata')
     * @param string|string[] $path A single key, or an array of keys for nested extraction
     * @return string Raw SQL expression string (use inside select/where clauses)
     * @throws \InvalidArgumentException if column or any path segment contains unsafe characters
     * @since 5.15.0
     */
    public static function jsonExtract(string $column, string|array $path): string
    {
        self::validateIdentifier($column, 'column');

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
     * @param string $column The JSON column name (e.g., 'metadata' or 'a.metadata')
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
     * Returns a DB-agnostic expression to concatenate grouped values.
     *
     * MySQL:      GROUP_CONCAT(expression SEPARATOR separator)
     * PostgreSQL: STRING_AGG(expression::text, separator)
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
        }

        $quotedSeparator = str_replace("'", "''", $separator);

        if (Craft::$app->getDb()->getIsMysql()) {
            return "GROUP_CONCAT($expressionSql SEPARATOR '$quotedSeparator')";
        }

        // PostgreSQL
        return "STRING_AGG(($expressionSql)::text, '$quotedSeparator')";
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
