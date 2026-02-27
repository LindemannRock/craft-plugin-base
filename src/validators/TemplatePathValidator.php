<?php

namespace lindemannrock\base\validators;

use Craft;
use craft\helpers\App;
use yii\validators\Validator;

/**
 * Validates template path values (relative path, no traversal/absolute/url formats).
 */
class TemplatePathValidator extends Validator
{
    public string $translationCategory = 'app';
    public bool $checkTemplateExists = false;

    public function validateAttribute($model, $attribute): void
    {
        $rawValue = trim((string) ($model->$attribute ?? ''));
        if ($rawValue === '') {
            return;
        }

        $pathToValidate = $rawValue;

        // Allow environment variable prefix, e.g. $FOO/path or ${FOO}/path
        if (preg_match('/^\$(\{[A-Z0-9_]+\}|[A-Z0-9_]+)/i', $pathToValidate, $m) === 1) {
            $pathToValidate = ltrim(substr($pathToValidate, strlen($m[0])), '/');
        }

        if ($pathToValidate === '') {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path must include a relative template path after the environment variable.')
            );
            return;
        }

        if (str_contains($pathToValidate, '\\')) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path must use forward slashes only.')
            );
            return;
        }

        if (str_starts_with($pathToValidate, '/')
            || str_contains($pathToValidate, '://')
            || preg_match('/^[A-Za-z]:\//', $pathToValidate) === 1
        ) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path must be relative to your templates folder (not absolute URL/path).')
            );
            return;
        }

        if (str_contains($pathToValidate, '//')) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path cannot contain empty segments ("//").')
            );
            return;
        }

        if (preg_match('#(^|/)\.\.(/|$)#', $pathToValidate) === 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path cannot contain parent directory traversal ("..").')
            );
            return;
        }

        if (preg_match('#^[A-Za-z0-9_.\-/]+$#', $pathToValidate) !== 1) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Template path contains invalid characters.')
            );
            return;
        }

        if ($this->checkTemplateExists) {
            $templatePath = trim((string) App::parseEnv($rawValue));

            // If env vars are still unresolved, skip existence check.
            if ($templatePath !== '' && !str_contains($templatePath, '$') && !str_contains($templatePath, '{')) {
                if (!Craft::$app->getView()->doesTemplateExist($templatePath)) {
                    $model->addError(
                        $attribute,
                        Craft::t($this->translationCategory, 'Template "{path}" does not exist in your templates folder.', ['path' => $templatePath])
                    );
                }
            }
        }
    }
}
