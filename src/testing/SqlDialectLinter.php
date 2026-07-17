<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\testing;

/**
 * Scans PHP source for raw SQL that is MySQL-safe but PostgreSQL-broken.
 *
 * MySQL treats unquoted identifiers case-insensitively; PostgreSQL folds them
 * to lowercase. A raw `SUM(resultsCount)` therefore queries a non-existent
 * column on PostgreSQL, and an unquoted `as avgTime` alias comes back as the
 * row key `avgtime`, silently breaking `$row['avgTime']` reads and hard-failing
 * any `orderBy(['avgTime' => ...])` that references the alias. Yii's `[[...]]`
 * placeholder quotes per-dialect, so the fix is bracketing — and this linter
 * lets a MySQL-only CI enforce it without a live PostgreSQL install.
 *
 * Only string literals are inspected (via token_get_all), so comments and
 * docblocks cannot false-positive. Wire it into a plugin's test suite as:
 *
 * ```php
 * $violations = SqlDialectLinter::scanDirectory(
 *     dirname(__DIR__, 2) . '/src',
 *     ['src/search/storage/MySqlStorage.php'], // MySQL-only dialect files
 * );
 * self::assertSame([], $violations);
 * ```
 *
 * @author    LindemannRock
 * @package   Base
 * @since     5.35.0
 */
final class SqlDialectLinter
{
    /**
     * Scan every .php file under a directory for PostgreSQL-unsafe SQL literals.
     *
     * @param string $directory Absolute path to the source directory to scan
     * @param string[] $excludeSuffixes Relative path suffixes to skip (e.g. MySQL-only dialect files)
     * @param string[] $booleanColumns Boolean column names of the plugin's tables — MAX()/MIN()
     * directly over one is flagged (PostgreSQL has no boolean max/min; this is a type error the
     * identifier rules can't see, so the linter needs the names)
     * @return string[] Human-readable violations ("path:line reason: literal"); empty when clean
     */
    public static function scanDirectory(string $directory, array $excludeSuffixes = [], array $booleanColumns = []): array
    {
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getPathname();
        }
        sort($files);

        foreach ($files as $path) {
            foreach ($excludeSuffixes as $suffix) {
                if (str_ends_with($path, $suffix)) {
                    continue 2;
                }
            }
            $violations = [...$violations, ...self::scanFile($path, $booleanColumns)];
        }

        return $violations;
    }

    /**
     * Scan a single PHP file for PostgreSQL-unsafe SQL literals.
     *
     * @param string $absolutePath Absolute path to the file
     * @param string[] $booleanColumns See scanDirectory()
     * @return string[] Human-readable violations; empty when clean
     */
    public static function scanFile(string $absolutePath, array $booleanColumns = []): array
    {
        $source = file_get_contents($absolutePath);
        if ($source === false) {
            return ["$absolutePath: unreadable"];
        }

        $violations = [];
        foreach (self::stringLiterals($source) as [$line, $literal]) {
            $reason = self::literalViolation($literal, $booleanColumns);
            if ($reason !== null) {
                $violations[] = "{$absolutePath}:{$line} {$reason}: {$literal}";
            }
        }

        return $violations;
    }

    /**
     * Why a single SQL-looking literal is PostgreSQL-unsafe, or null if clean.
     *
     * @param string[] $booleanColumns See scanDirectory()
     */
    private static function literalViolation(string $literal, array $booleanColumns = []): ?string
    {
        // Aggregate/CASE over an unbracketed camelCase column: PostgreSQL
        // folds it to lowercase and errors with "column ... does not exist".
        if (preg_match('/\b(?:SUM|MAX|MIN|AVG|COUNT)\(\s*(?:DISTINCT\s+)?[a-z][a-zA-Z0-9_]*[A-Z]/', $literal)
            || preg_match('/\bCASE\s+WHEN\s+[a-z][a-zA-Z0-9_]*[A-Z]/', $literal)
        ) {
            return 'unbracketed camelCase column in SQL';
        }

        // Unbracketed camelCase alias in an SQL-looking literal: the alias
        // folds to lowercase, breaking $row['camelCase'] reads and any
        // orderBy(['camelCase' => ...]) referencing it.
        if (preg_match('/\b(?:SELECT\s|SUM\(|COUNT\(|MAX\(|MIN\(|AVG\(|CASE\s+WHEN\s|GROUP\s+BY\s|ORDER\s+BY\s)/i', $literal)
            && preg_match('/\b[Aa][Ss]\s+(?!\[\[)[a-z][a-zA-Z0-9_]*[A-Z][a-zA-Z0-9_]*/', $literal)
        ) {
            return 'unbracketed camelCase alias in SQL';
        }

        // MAX()/MIN() directly over a known boolean column: PostgreSQL has no
        // boolean max/min (SQLSTATE 42883) — a type error bracketing can't fix.
        // Wrap the flag via DbHelper::boolToInt() instead. Needs the plugin's
        // boolean column names since types aren't visible in source.
        foreach ($booleanColumns as $column) {
            $quoted = preg_quote($column, '/');
            if (preg_match('/\b(?:MAX|MIN)\(\s*(?:\[\[)?(?:[a-zA-Z_][a-zA-Z0-9_]*\.)?' . $quoted . '(?:\]\])?\s*\)/', $literal)) {
                return "MAX/MIN over boolean column {$column} (no boolean max/min on PostgreSQL; use DbHelper::boolToInt())";
            }
        }

        // Bare camelCase identifier in a raw SQL statement literal (e.g. a
        // createCommand() string): SELECT indexHandle FROM ... folds to
        // indexhandle on PostgreSQL. Quoted SQL strings, bracketed/{{%...}}
        // references, and :params are stripped before matching.
        if (preg_match('/\b(?:SELECT|UNION|INSERT\s+INTO|DELETE\s+FROM|UPDATE)\b/', $literal)) {
            $stripped = preg_replace(
                ["/'[^']*'/", '/"[^"]*"/', '/\[\[[^\]]*\]\]/', '/\{\{%?[^}]*\}\}/', '/:\w+/'],
                ' ',
                $literal,
            ) ?? $literal;

            if (preg_match('/\b[a-z][a-zA-Z0-9_]*[A-Z][a-zA-Z0-9_]*\b/', $stripped, $matches)) {
                return "bare camelCase identifier '{$matches[0]}' in raw SQL";
            }
        }

        return null;
    }

    /**
     * @return array<int, array{int, string}> [line, literal content] pairs
     */
    private static function stringLiterals(string $source): array
    {
        $literals = [];
        foreach (token_get_all($source) as $token) {
            if (!is_array($token)) {
                continue;
            }

            [$id, $text, $line] = $token;
            if ($id === T_CONSTANT_ENCAPSED_STRING) {
                // Strip the surrounding quotes.
                $literals[] = [$line, substr($text, 1, -1)];
            } elseif ($id === T_ENCAPSED_AND_WHITESPACE) {
                // Static fragment of an interpolated double-quoted string.
                $literals[] = [$line, $text];
            }
        }

        return $literals;
    }
}
