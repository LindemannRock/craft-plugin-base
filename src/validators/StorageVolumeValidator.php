<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\validators;

use lindemannrock\base\helpers\StorageVolumeHelper;
use yii\validators\Validator;

/**
 * Validates optional asset volume UIDs used for plugin-managed storage.
 *
 * @since 5.26.0
 */
class StorageVolumeValidator extends Validator
{
    public string $translationCategory = 'lindemannrock-base';
    public bool $preventLocalWebroot = true;
    public bool $requireLocal = false;

    public function validateAttribute($model, $attribute): void
    {
        foreach (StorageVolumeHelper::validateVolume((string)($model->$attribute ?? ''), [
            'translationCategory' => $this->translationCategory,
            'preventLocalWebroot' => $this->preventLocalWebroot,
            'requireLocal' => $this->requireLocal,
        ]) as $error) {
            $model->addError($attribute, $error);
        }
    }
}
