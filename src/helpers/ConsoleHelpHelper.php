<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Renders concise, operator-friendly console command help from a manifest.
 *
 * Craft/Yii already provide exact-command `--help`; this helper provides the
 * missing plugin-level catalog that tells users which exact command to run.
 *
 * @since 5.26.0
 */
class ConsoleHelpHelper
{
    /**
     * Render the top-level command catalog.
     *
     * @param array<string, mixed> $manifest
     */
    public static function renderOverview(array $manifest): string
    {
        $lines = [];
        $title = (string)($manifest['title'] ?? self::titleFromHandle((string)($manifest['pluginHandle'] ?? 'Plugin')));
        $handle = (string)($manifest['pluginHandle'] ?? '');
        $prefix = (string)($manifest['commandPrefix'] ?? 'php craft');

        $lines[] = $title . ' CLI';
        $lines[] = str_repeat('=', strlen($title . ' CLI'));
        $lines[] = '';

        if (!empty($manifest['summary'])) {
            $lines[] = self::wrap((string)$manifest['summary']);
            $lines[] = '';
        }

        $common = $manifest['common'] ?? [];
        if (is_array($common) && $common !== []) {
            $lines[] = 'Common commands';
            foreach ($common as $command) {
                if (!is_string($command)) {
                    continue;
                }
                $entry = self::findCommand($manifest, $command);
                if ($entry === null) {
                    continue;
                }
                $lines[] = self::formatCommandLine($entry, $handle);
            }
            $lines[] = '';
        }

        $groups = $manifest['groups'] ?? [];
        if (is_array($groups) && $groups !== []) {
            $lines[] = 'Command groups';
            foreach ($groups as $group) {
                if (!is_array($group)) {
                    continue;
                }
                $name = (string)($group['name'] ?? '');
                $description = (string)($group['description'] ?? '');
                if ($name === '') {
                    continue;
                }
                $lines[] = '  ' . str_pad($name, 14) . $description;
            }
            $lines[] = '';

            foreach ($groups as $group) {
                if (!is_array($group)) {
                    continue;
                }
                $commands = $group['commands'] ?? [];
                if (!is_array($commands) || $commands === []) {
                    continue;
                }
                $lines[] = (string)($group['label'] ?? self::titleFromHandle((string)($group['name'] ?? 'Commands')));
                foreach ($commands as $entry) {
                    if (is_array($entry)) {
                        $lines[] = self::formatCommandLine($entry, $handle);
                    }
                }
                $lines[] = '';
            }
        }

        $lines[] = 'Run focused help';
        $lines[] = "  {$prefix} {$handle}/help <group/action>";
        $lines[] = "  {$prefix} help {$handle}/<group>/<action>";

        return rtrim(implode("\n", $lines)) . "\n";
    }

    /**
     * Render help for one command path.
     *
     * @param array<string, mixed> $manifest
     */
    public static function renderCommand(array $manifest, string $command): string
    {
        $handle = (string)($manifest['pluginHandle'] ?? '');
        $prefix = (string)($manifest['commandPrefix'] ?? 'php craft');
        $normalized = self::normalizeCommand($command, $handle);
        $entry = self::findCommand($manifest, $normalized);

        if ($entry === null) {
            return self::renderUnknownCommand($manifest, $normalized);
        }

        $usage = self::commandUsage($entry, $handle);
        $lines = [
            $usage,
            str_repeat('=', strlen($usage)),
            '',
        ];

        if (!empty($entry['description'])) {
            $lines[] = self::wrap((string)$entry['description']);
            $lines[] = '';
        }

        $options = $entry['options'] ?? [];
        if (is_array($options) && $options !== []) {
            $lines[] = 'Options';
            foreach ($options as $option) {
                if (!is_array($option)) {
                    continue;
                }
                $name = (string)($option['name'] ?? '');
                $description = (string)($option['description'] ?? '');
                if ($name === '') {
                    continue;
                }
                $required = !empty($option['required']) ? 'Required. ' : '';
                $lines[] = '  ' . str_pad($name, 14) . self::wrap($required . $description, 64, 16);
            }
            $lines[] = '';
        }

        $examples = $entry['examples'] ?? [];
        if (is_array($examples) && $examples !== []) {
            $lines[] = 'Examples';
            foreach ($examples as $example) {
                if (is_string($example)) {
                    $lines[] = "  {$prefix} {$example}";
                }
            }
            $lines[] = '';
        }

        $notes = $entry['notes'] ?? [];
        if (is_array($notes) && $notes !== []) {
            $lines[] = 'Notes';
            foreach ($notes as $note) {
                if (is_string($note)) {
                    $lines[] = '  - ' . self::wrap($note, 68, 4);
                }
            }
            $lines[] = '';
        }

        $lines[] = 'Native Craft help';
        $nativePath = self::commandPath($entry, $handle);
        $lines[] = "  {$prefix} help {$nativePath}";
        $lines[] = "  {$prefix} {$nativePath} --help";

        return rtrim(implode("\n", $lines)) . "\n";
    }

    /**
     * Check whether the manifest contains a command.
     *
     * @param array<string, mixed> $manifest
     */
    public static function hasCommand(array $manifest, ?string $command): bool
    {
        if ($command === null) {
            return true;
        }

        return self::findCommand($manifest, $command) !== null;
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>|null
     */
    private static function findCommand(array $manifest, string $command): ?array
    {
        $handle = (string)($manifest['pluginHandle'] ?? '');
        $normalized = self::normalizeCommand($command, $handle);
        $groups = $manifest['groups'] ?? [];

        if (!is_array($groups)) {
            return null;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $commands = $group['commands'] ?? [];
            if (!is_array($commands)) {
                continue;
            }
            foreach ($commands as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $path = (string)($entry['path'] ?? '');
                if (self::normalizeCommand($path, $handle) === $normalized) {
                    return $entry;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private static function renderUnknownCommand(array $manifest, string $command): string
    {
        $handle = (string)($manifest['pluginHandle'] ?? '');
        $prefix = (string)($manifest['commandPrefix'] ?? 'php craft');
        $suggestion = self::suggestCommand($manifest, $command);

        $lines = [
            "No help entry for '{$command}'.",
            '',
        ];

        if ($suggestion !== null) {
            $lines[] = 'Did you mean?';
            $lines[] = "  {$prefix} {$handle}/help {$suggestion}";
            $lines[] = "  {$prefix} {$handle}/{$suggestion}";
            $lines[] = '';
        }

        $lines[] = 'Show all commands';
        $lines[] = "  {$prefix} {$handle}/help";

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private static function suggestCommand(array $manifest, string $command): ?string
    {
        $commands = self::commandPaths($manifest);
        if ($commands === []) {
            return null;
        }

        $lastSegment = basename(str_replace('\\', '/', $command));
        foreach ($commands as $candidate) {
            if (basename(str_replace('\\', '/', $candidate)) === $lastSegment) {
                return $candidate;
            }
        }

        $best = null;
        $bestScore = PHP_INT_MAX;
        foreach ($commands as $candidate) {
            $score = levenshtein($command, $candidate);
            if ($score < $bestScore) {
                $best = $candidate;
                $bestScore = $score;
            }
        }

        return $bestScore <= 8 ? $best : null;
    }

    /**
     * @param array<string, mixed> $manifest
     * @return string[]
     */
    private static function commandPaths(array $manifest): array
    {
        $paths = [];
        $groups = $manifest['groups'] ?? [];
        if (!is_array($groups)) {
            return $paths;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $commands = $group['commands'] ?? [];
            if (!is_array($commands)) {
                continue;
            }
            foreach ($commands as $entry) {
                if (is_array($entry) && !empty($entry['path']) && is_string($entry['path'])) {
                    $paths[] = self::normalizeCommand($entry['path'], (string)($manifest['pluginHandle'] ?? ''));
                }
            }
        }

        return $paths;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private static function formatCommandLine(array $entry, string $handle): string
    {
        $usage = self::commandUsage($entry, $handle);
        $summary = (string)($entry['summary'] ?? $entry['description'] ?? '');

        if (strlen($usage) > 68) {
            return '  ' . $usage . "\n" . '      ' . $summary;
        }

        return '  ' . str_pad($usage, 72) . $summary;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private static function commandUsage(array $entry, string $handle): string
    {
        $path = self::normalizeCommand((string)($entry['path'] ?? ''), $handle);
        $arguments = (string)($entry['arguments'] ?? '');
        $options = (string)($entry['usageOptions'] ?? '');
        $suffix = trim($arguments . ' ' . $options);

        return trim($handle . '/' . $path . ($suffix !== '' ? ' ' . $suffix : ''));
    }

    /**
     * @param array<string, mixed> $entry
     */
    private static function commandPath(array $entry, string $handle): string
    {
        $path = self::normalizeCommand((string)($entry['path'] ?? ''), $handle);

        return trim($handle . '/' . $path);
    }

    private static function normalizeCommand(string $command, string $handle): string
    {
        $command = trim($command);
        $command = trim($command, '/');

        if ($handle !== '' && str_starts_with($command, $handle . '/')) {
            $command = substr($command, strlen($handle) + 1);
        }

        return $command;
    }

    private static function titleFromHandle(string $handle): string
    {
        return ucwords(str_replace('-', ' ', $handle));
    }

    private static function wrap(string $text, int $width = 78, int $indent = 0): string
    {
        $padding = str_repeat(' ', $indent);
        return wordwrap($text, $width, "\n" . $padding);
    }
}
