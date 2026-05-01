<?php

namespace lindemannrock\base\validators;

use Craft;
use yii\validators\Validator;

/**
 * Validates URL route prefix formatting (no leading/trailing slash, no empty segments).
 *
 * @since 5.18.0
 */
class RoutePrefixValidator extends Validator
{
    public string $translationCategory = 'app';

    public function validateAttribute($model, $attribute): void
    {
        $prefix = (string) ($model->$attribute ?? '');
        if ($prefix === '') {
            return;
        }

        if ($prefix !== trim($prefix, '/')) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Do not start or end the prefix with "/".')
            );
            return;
        }

        if (str_contains($prefix, '//')) {
            $model->addError(
                $attribute,
                Craft::t($this->translationCategory, 'Prefix cannot contain empty path segments ("//"). Use single slashes only.')
            );
        }
    }
}
