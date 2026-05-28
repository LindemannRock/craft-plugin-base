<?php

namespace lindemannrock\base\validators;

use Craft;
use lindemannrock\base\helpers\StoragePathHelper;
use yii\validators\Validator;

/**
 * Validates local storage path values used in plugin settings.
 *
 * @since 5.18.0
 */
class StoragePathValidator extends Validator
{
    public string $translationCategory = 'app';

    /**
     * @var array<int, string> Allowed alias prefixes when a path starts with "@"
     */
    public array $allowedAliases = ['@storage', '@root'];

    /**
     * @var bool Whether to block webroot/web-accessible storage paths
     */
    public bool $preventWebroot = true;
    public bool $requireAlias = false;
    public bool $allowEnvVars = true;

    public function validateAttribute($model, $attribute): void
    {
        $value = trim((string)($model->$attribute ?? ''));
        if ($value === '') {
            return;
        }

        if (!$this->validatePathSyntax($model, $attribute, $value)) {
            return;
        }

        $usesEnvVar = $this->startsWithEnvVar($value);
        if ($usesEnvVar && !$this->allowEnvVars) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Environment variables are not allowed for this path.')
            );
            return;
        }

        try {
            $parsedValue = StoragePathHelper::parseEnv($value);
        } catch (\Throwable $e) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Invalid path: {error}', ['error' => $e->getMessage()])
            );
            return;
        }

        if ($parsedValue === '') {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Invalid path.')
            );
            return;
        }

        if (!$this->validatePathSyntax($model, $attribute, $parsedValue)) {
            return;
        }

        if (str_starts_with($parsedValue, '@') && !$this->startsWithAllowedAlias($parsedValue)) {
            $this->addAllowedAliasError($model, $attribute);
            return;
        }

        try {
            $resolvedPath = StoragePathHelper::resolveParsed($parsedValue);
        } catch (\Throwable $e) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Invalid path: {error}', ['error' => $e->getMessage()])
            );
            return;
        }

        if ($this->requireAlias) {
            if (str_starts_with($value, '@')) {
                if (!$this->startsWithAllowedAlias($value)) {
                    $this->addAllowedAliasError($model, $attribute);
                    return;
                }
            } elseif ($usesEnvVar) {
                if (!$this->isWithinAllowedAliasRoot((string)$resolvedPath)) {
                    $this->addAllowedAliasError($model, $attribute);
                    return;
                }
            } elseif ($this->isWithinAllowedAliasRoot((string)$resolvedPath)) {
                return;
            } else {
                $model->addError(
                    $attribute,
                    Craft::t(
                        $this->translationCategory,
                        'Path must start with one of: {aliases}.',
                        ['aliases' => implode(', ', $this->allowedAliases)]
                    )
                );
                return;
            }
        }

        if (filter_var($resolvedPath, FILTER_VALIDATE_URL) !== false) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Path must resolve to a local filesystem path, not a URL.')
            );
            return;
        }

        if ($this->preventWebroot) {
            $webroot = Craft::getAlias('@webroot');
            $normalizedResolved = rtrim((string)$resolvedPath, '/\\');
            $normalizedWebroot = rtrim((string)$webroot, '/\\');

            if (
                $normalizedResolved === $normalizedWebroot ||
                str_starts_with($normalizedResolved, $normalizedWebroot . DIRECTORY_SEPARATOR) ||
                str_starts_with($normalizedResolved, $normalizedWebroot . '/')
            ) {
                $model->addError(
                    $attribute,
                    Craft::t($this->translationCategory, 'Path cannot be in a web-accessible directory (@webroot).')
                );
            }
        }
    }

    private function validatePathSyntax($model, string $attribute, string $value): bool
    {
        if (preg_match('#(^|/)\.\.(/|$)#', $value) === 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Path cannot contain parent directory traversal ("..").')
            );
            return false;
        }

        if (preg_match('/^@web(root)?(?:\/|$)/i', $value) === 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Path cannot use @web or @webroot because those are web-accessible.')
            );
            return false;
        }

        return true;
    }

    private function startsWithEnvVar(string $value): bool
    {
        return preg_match('/^\$(?:\{[A-Z0-9_]+\}|[A-Z0-9_]+)/i', $value) === 1;
    }

    private function startsWithAllowedAlias(string $value): bool
    {
        foreach ($this->allowedAliases as $alias) {
            if (str_starts_with($value, $alias)) {
                return true;
            }
        }

        return false;
    }

    private function isWithinAllowedAliasRoot(string $resolvedPath): bool
    {
        $normalizedResolved = rtrim($resolvedPath, '/\\');

        foreach ($this->allowedAliases as $alias) {
            try {
                $resolvedAlias = Craft::getAlias($alias);
            } catch (\Throwable) {
                continue;
            }

            $normalizedAlias = rtrim((string)$resolvedAlias, '/\\');
            if (
                $normalizedResolved === $normalizedAlias
                || str_starts_with($normalizedResolved, $normalizedAlias . DIRECTORY_SEPARATOR)
                || str_starts_with($normalizedResolved, $normalizedAlias . '/')
            ) {
                return true;
            }
        }

        return false;
    }

    private function addAllowedAliasError($model, string $attribute): void
    {
        $model->addError(
            $attribute,
            Craft::t(
                $this->translationCategory,
                'Path must start with one of: {aliases}.',
                ['aliases' => implode(', ', $this->allowedAliases)]
            )
        );
    }
}
