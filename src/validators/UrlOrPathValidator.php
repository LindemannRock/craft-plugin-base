<?php
/**
 * @copyright Copyright (c) LindemannRock
 */

namespace lindemannrock\base\validators;

use Craft;
use yii\validators\Validator;

/**
 * Validates values that can be either an absolute HTTP(S) URL or a site-relative path.
 */
class UrlOrPathValidator extends Validator
{
    public string $translationCategory = 'app';

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute): void
    {
        $value = trim((string)($model->$attribute ?? ''));

        if ($value === '') {
            return;
        }

        // Allow environment variable values like $BASE_URL or $BASE_URL/path.
        if (preg_match('/^\$[A-Z0-9_]+(?:\/.*)?$/i', $value) === 1) {
            return;
        }

        if (preg_match('/^https?:\/\//i', $value) === 1) {
            if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return;
            }

            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'URL is invalid. Use a full URL like https://example.com/path or a relative path like /.')
            );
            return;
        }

        if (str_starts_with($value, '/')) {
            if (str_contains($value, '\\')) {
                $model->addError(
                    $attribute,
                    Craft::t($this->translationCategory, 'Path must use forward slashes only.')
                );
                return;
            }

            if (preg_match('#(^|/)\.\.(/|$)#', $value) === 1) {
                $model->addError(
                    $attribute,
                    Craft::t($this->translationCategory, 'Path cannot contain parent directory traversal ("..").')
                );
                return;
            }

            if (str_contains($value, '//')) {
                $model->addError(
                    $attribute,
                    Craft::t($this->translationCategory, 'Path cannot contain empty segments ("//").')
                );
                return;
            }

            return;
        }

        $model->addError(
            $attribute,
            Craft::t($this->translationCategory, 'Use a relative path starting with "/" or an absolute URL starting with http:// or https://.')
        );
    }
}
