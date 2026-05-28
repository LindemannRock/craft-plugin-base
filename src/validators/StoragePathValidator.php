<?php

namespace lindemannrock\base\validators;

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
        foreach (StoragePathHelper::validatePath((string)($model->$attribute ?? ''), [
            'translationCategory' => $this->translationCategory,
            'allowedAliases' => $this->allowedAliases,
            'preventWebroot' => $this->preventWebroot,
            'requireAlias' => $this->requireAlias,
            'allowEnvVars' => $this->allowEnvVars,
        ]) as $error) {
            $model->addError($attribute, $error);
        }
    }
}
