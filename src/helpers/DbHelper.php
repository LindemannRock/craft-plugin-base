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
     * Returns a DB-agnostic expression to extract a text value from a JSON column.
     *
     * MySQL:      JSON_UNQUOTE(JSON_EXTRACT(column, '$.key'))
     * PostgreSQL: column->>'key'
     *
     * @param string $column The JSON column name (e.g., 'metadata' or 'a.metadata')
     * @param string $path The JSON key to extract (e.g., 'source', 'clickType')
     * @return string Raw SQL expression string (use inside select/where clauses)
     * @since 5.15.0
     */
    public static function jsonExtract(string $column, string $path): string
    {
        if (Craft::$app->getDb()->getIsMysql()) {
            return "JSON_UNQUOTE(JSON_EXTRACT($column, '$.$path'))";
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
     * @since 5.15.0
     */
    public static function jsonExtractExpression(string $column, string $path, ?string $alias = null): Expression
    {
        $sql = self::jsonExtract($column, $path);

        if ($alias !== null) {
            $sql .= ' as ' . $alias;
        }

        return new Expression($sql);
    }
}
