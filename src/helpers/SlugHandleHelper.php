<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use Craft;
use craft\db\Query;
use craft\helpers\StringHelper;

/**
 * Helpers for normalizing persisted slugs/handles and resolving DB collisions.
 *
 * This helper owns mechanics only. Callers still decide product policy: append,
 * reject, reuse an existing row, perform deterministic sync updates, or allow
 * config-file shadowing.
 *
 * @since 5.26.0
 */
class SlugHandleHelper
{
    /**
     * Normalize a value to a Craft-style handle.
     *
     * @param string|null $value Raw value, usually a posted handle or label.
     * @param string $fallback Fallback used when the normalized handle is empty.
     * @return string
     * @since 5.26.0
     */
    public static function normalizeHandle(?string $value, string $fallback = 'item'): string
    {
        $handle = StringHelper::toHandle(trim((string)$value));

        if ($handle !== '') {
            return $handle;
        }

        $fallbackHandle = StringHelper::toHandle($fallback);

        return $fallbackHandle !== '' ? $fallbackHandle : 'item';
    }

    /**
     * Normalize a value to a URL-style slug.
     *
     * Keeps lowercase letters, numbers, underscores, and hyphens. All other
     * character runs become a hyphen.
     *
     * @param string|null $value Raw value, usually a posted slug/code or label.
     * @param string $fallback Fallback used when the normalized slug is empty.
     * @return string
     * @since 5.26.0
     */
    public static function normalizeSlug(?string $value, string $fallback = 'item'): string
    {
        $slug = mb_strtolower(trim((string)$value));
        $slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';
        $slug = trim($slug, '-_');

        if ($slug !== '') {
            return $slug;
        }

        if ($fallback === '') {
            return '';
        }

        $fallbackSlug = self::normalizeSlug($fallback, '');

        return $fallbackSlug !== '' ? $fallbackSlug : 'item';
    }

    /**
     * Normalize a slash-preserving path slug.
     *
     * Each segment uses {@see normalizeSlug()}. Empty segments are removed, so
     * repeated slashes collapse. This is intended for docs/page paths, not
     * filesystem paths.
     *
     * @param string|null $value Raw path-like value.
     * @param string $fallback Fallback used when all segments normalize empty.
     * @return string
     * @since 5.26.0
     */
    public static function normalizePathSlug(?string $value, string $fallback = 'item'): string
    {
        $segments = [];
        foreach (explode('/', trim((string)$value, " \t\n\r\0\x0B/")) as $segment) {
            $normalized = self::normalizeSlug($segment, '');
            if ($normalized !== '') {
                $segments[] = $normalized;
            }
        }

        if ($segments !== []) {
            return implode('/', $segments);
        }

        if ($fallback === '') {
            return '';
        }

        return self::normalizePathSlug($fallback, 'item');
    }

    /**
     * Check whether a candidate value exists in a table/column.
     *
     * Supported options:
     * - excludeId: current row ID to ignore during edit flows
     * - idColumn: row ID column name, default `id`
     * - scope: equality conditions such as `['sourceId' => 1]`
     * - conditions: additional Yii query conditions
     *
     * @param string $table Yii table reference, e.g. `{{%my_table}}`.
     * @param string $column Column to check.
     * @param string $candidate Candidate value.
     * @param array<string, mixed> $options
     * @return bool
     * @since 5.26.0
     */
    public static function exists(string $table, string $column, string $candidate, array $options = []): bool
    {
        return self::buildExistenceQuery($table, $column, $candidate, $options)->exists();
    }

    /**
     * Return the next available candidate using `base`, `base-1`, `base-2`, ...
     *
     * Supported options are the same as {@see exists()}, plus:
     * - start: first suffix number, default `1`
     * - maxAttempts: maximum number of suffixed candidates to try, default `100`
     *
     * @param string $table Yii table reference, e.g. `{{%my_table}}`.
     * @param string $column Column to check.
     * @param string $base Already-normalized base value.
     * @param array<string, mixed> $options
     * @return string
     * @throws \RuntimeException when no unique value can be generated.
     * @since 5.26.0
     */
    public static function makeUnique(string $table, string $column, string $base, array $options = []): string
    {
        $base = trim($base);
        if ($base === '') {
            throw new \InvalidArgumentException('Base slug/handle cannot be empty.');
        }

        if (!self::exists($table, $column, $base, $options)) {
            return $base;
        }

        $start = max(1, (int)($options['start'] ?? 1));
        $maxAttempts = max(1, (int)($options['maxAttempts'] ?? 100));

        for ($suffix = $start; $suffix < $start + $maxAttempts; $suffix++) {
            $candidate = $base . '-' . $suffix;
            if (!self::exists($table, $column, $candidate, $options)) {
                return $candidate;
            }
        }

        throw new \RuntimeException(sprintf(
            "Could not generate a unique slug/handle for '%s' after %d attempts.",
            $base,
            $maxAttempts,
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function buildExistenceQuery(string $table, string $column, string $candidate, array $options): Query
    {
        self::assertSafeIdentifier($column, 'column');

        $query = (new Query())
            ->from($table)
            ->where([$column => $candidate]);

        $scope = $options['scope'] ?? [];
        if (is_array($scope) && $scope !== []) {
            foreach ($scope as $scopeColumn => $scopeValue) {
                self::assertSafeIdentifier((string)$scopeColumn, 'scope column');
                $query->andWhere([(string)$scopeColumn => $scopeValue]);
            }
        }

        $excludeId = $options['excludeId'] ?? null;
        if ($excludeId !== null && $excludeId !== '') {
            $idColumn = (string)($options['idColumn'] ?? 'id');
            self::assertSafeIdentifier($idColumn, 'id column');
            $query->andWhere(['not', [$idColumn => $excludeId]]);
        }

        $conditions = $options['conditions'] ?? [];
        if (is_array($conditions) && $conditions !== []) {
            $query->andWhere($conditions);
        }

        return $query;
    }

    private static function assertSafeIdentifier(string $identifier, string $label): void
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) === 1) {
            return;
        }

        Craft::warning("Unsafe {$label} passed to SlugHandleHelper: {$identifier}", __METHOD__);
        throw new \InvalidArgumentException("Unsafe {$label}: {$identifier}");
    }
}
