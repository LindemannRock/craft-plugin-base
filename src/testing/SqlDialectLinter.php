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
     * @return string[] Human-readable violations ("path:line reason: literal"); empty when clean
     */
    public static function scanDirectory(string $directory, array $excludeSuffixes = []): array
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
            $violations = [...$violations, ...self::scanFile($path)];
        }

        return $violations;
    }

    /**
     * Scan a single PHP file for PostgreSQL-unsafe SQL literals.
     *
     * @param string $absolutePath Absolute path to the file
     * @return string[] Human-readable violations; empty when clean
     */
    public static function scanFile(string $absolutePath): array
    {
        $source = file_get_contents($absolutePath);
        if ($source === false) {
            return ["$absolutePath: unreadable"];
        }

        $violations = [];
        foreach (self::stringLiterals($source) as [$line, $literal]) {
            $reason = self::literalViolation($literal);
            if ($reason !== null) {
                $violations[] = "{$absolutePath}:{$line} {$reason}: {$literal}";
            }
        }

        return $violations;
    }

    /**
     * Why a single SQL-looking literal is PostgreSQL-unsafe, or null if clean.
     */
    private static function literalViolation(string $literal): ?string
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
