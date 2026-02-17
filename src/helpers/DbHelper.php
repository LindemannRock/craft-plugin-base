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
     * @param string $column The JSON column name (e.g., 'metadata' or 'a.metadata')
     * @param string $path The JSON key to extract (e.g., 'source', 'clickType')
     * @return string Raw SQL expression string (use inside select/where clauses)
     * @throws \InvalidArgumentException if column or path contains unsafe characters
     * @since 5.15.0
     */
    public static function jsonExtract(string $column, string $path): string
    {
        self::validateIdentifier($column, 'column');
        self::validateIdentifier($path, 'path');

        // If path contains special characters (hyphens, dots), quote it for MySQL
        $needsQuoting = (bool) preg_match('/[^a-zA-Z0-9_]/', $path);

        if (Craft::$app->getDb()->getIsMysql()) {
            $jsonPath = $needsQuoting ? "$.\"$path\"" : "$.$path";
            return "JSON_UNQUOTE(JSON_EXTRACT($column, '$jsonPath'))";
        }

        // PostgreSQL
        return "$column->>'$path'";
    }

    /**
     * Returns a DB-agnostic Expression to extract a text value from a JSON column.
     *
     * Same as jsonExtract() but returns a yii\db\Expression for use in
     * query builder methods like ->select() and ->where().
     *
     * @param string $column The JSON column name (e.g., 'metadata' or 'a.metadata')
     * @param string $path The JSON key to extract (e.g., 'source', 'clickType')
     * @param string|null $alias Optional column alias for SELECT clauses
     * @return Expression
     * @throws \InvalidArgumentException if column, path, or alias contains unsafe characters
     * @since 5.15.0
     */
    public static function jsonExtractExpression(string $column, string $path, ?string $alias = null): Expression
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
     * @param string $expression The SQL expression to aggregate
     * @param string $separator The separator between values (default ',')
     * @return string Raw SQL expression string
     * @throws \InvalidArgumentException if expression contains unsafe characters
     * @since 5.16.0
     */
    public static function groupConcat(string $expression, string $separator = ','): string
    {
        self::validateExpression($expression);
        $quotedSeparator = str_replace("'", "''", $separator);

        if (Craft::$app->getDb()->getIsMysql()) {
            return "GROUP_CONCAT($expression SEPARATOR '$quotedSeparator')";
        }

        // PostgreSQL
        return "STRING_AGG(($expression)::text, '$quotedSeparator')";
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
