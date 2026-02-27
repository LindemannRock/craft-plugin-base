<?php

namespace lindemannrock\base\validators;

use Craft;
use craft\helpers\App;
use yii\validators\Validator;

/**
 * Validates local storage path values used in plugin settings.
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

    public function validateAttribute($model, $attribute): void
    {
        $value = trim((string)($model->$attribute ?? ''));
        if ($value === '') {
            return;
        }

        if (preg_match('#(^|/)\.\.(/|$)#', $value) === 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Path cannot contain parent directory traversal ("..").')
            );
            return;
        }

        if (preg_match('/^@web(root)?(?:\/|$)/i', $value) === 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Path cannot use @web or @webroot because those are web-accessible.')
            );
            return;
        }

        if ($this->requireAlias && !str_starts_with($value, '@')) {
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

        if (str_starts_with($value, '@')) {
            $hasValidAlias = false;
            foreach ($this->allowedAliases as $alias) {
                if (str_starts_with($value, $alias)) {
                    $hasValidAlias = true;
                    break;
                }
            }

            if (!$hasValidAlias) {
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

        try {
            $resolvedPath = App::parseEnv($value);
            $resolvedPath = Craft::getAlias($resolvedPath);
        } catch (\Throwable $e) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Invalid path: {error}', ['error' => $e->getMessage()])
            );
            return;
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
}
