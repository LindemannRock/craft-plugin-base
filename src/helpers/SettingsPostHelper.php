<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\base\Model;
use lindemannrock\base\models\SettingsPostResult;

/**
 * Safely applies raw Control Panel settings POST values to typed settings models.
 *
 * @since 5.26.0
 */
class SettingsPostHelper
{
    /**
     * @param array<string, mixed> $postedValues
     * @param array<int, string> $allowedAttributes
     * @param callable(string): bool|null $isOverridden
     * @param array<string, callable(mixed, string, Model): mixed> $adapters
     */
    public static function apply(
        Model $model,
        array $postedValues,
        array $allowedAttributes,
        ?callable $isOverridden = null,
        array $adapters = [],
    ): SettingsPostResult {
        $allowedLookup = array_fill_keys($allowedAttributes, true);
        $attributesToValidate = [];
        $assignedAttributes = [];
        $ignoredAttributes = [];
        $hadErrors = false;

        foreach ($allowedAttributes as $attribute) {
            if ($isOverridden !== null && $isOverridden($attribute)) {
                continue;
            }

            $attributesToValidate[] = $attribute;
        }

        foreach ($postedValues as $attribute => $value) {
            if (!is_string($attribute)) {
                $ignoredAttributes[] = (string)$attribute;
                continue;
            }

            if (
                !isset($allowedLookup[$attribute]) ||
                ($isOverridden !== null && $isOverridden($attribute)) ||
                !property_exists($model, $attribute)
            ) {
                $ignoredAttributes[] = $attribute;
                continue;
            }

            $hasAdapter = isset($adapters[$attribute]);
            if ($hasAdapter) {
                $value = $adapters[$attribute]($value, $attribute, $model);
            }

            $normalized = self::normalizeForAttribute($model, $attribute, $value, $hasAdapter);
            if ($normalized === null) {
                $ignoredAttributes[] = $attribute;
                continue;
            }

            if (!$normalized['valid']) {
                $model->addError($attribute, $normalized['message']);
                $hadErrors = true;
                continue;
            }

            self::assign($model, $attribute, $normalized['value']);
            $assignedAttributes[] = $attribute;
        }

        return new SettingsPostResult(
            attributesToValidate: array_values(array_unique($attributesToValidate)),
            assignedAttributes: array_values(array_unique($assignedAttributes)),
            ignoredAttributes: array_values(array_unique($ignoredAttributes)),
            hasErrors: $hadErrors,
        );
    }

    /**
     * @return array{valid: bool, value?: mixed, message?: string}|null
     */
    private static function normalizeForAttribute(Model $model, string $attribute, mixed $value, bool $hasAdapter): ?array
    {
        $property = new \ReflectionProperty($model, $attribute);
        if (!$property->isPublic()) {
            return $hasAdapter
                ? ['valid' => true, 'value' => $value]
                : null;
        }

        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            return $hasAdapter
                ? ['valid' => true, 'value' => $value]
                : null;
        }

        $typeName = $type->getName();
        if ($value === '' && $type->allowsNull()) {
            return ['valid' => true, 'value' => null];
        }

        return match ($typeName) {
            'int' => self::normalizeInteger($value),
            'float' => self::normalizeFloat($value),
            'bool' => self::normalizeBoolean($value),
            'string' => self::normalizeString($value, $type->allowsNull()),
            'array' => self::normalizeArray($value),
            default => $hasAdapter ? ['valid' => true, 'value' => $value] : null,
        };
    }

    /**
     * @return array{valid: bool, value?: int, message?: string}
     */
    private static function normalizeInteger(mixed $value): array
    {
        if (is_int($value)) {
            return ['valid' => true, 'value' => $value];
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return [
                'valid' => false,
                'message' => Craft::t('lindemannrock-base', 'Value must be a whole number.'),
            ];
        }

        return ['valid' => true, 'value' => (int)$value];
    }

    /**
     * @return array{valid: bool, value?: float, message?: string}
     */
    private static function normalizeFloat(mixed $value): array
    {
        if (is_float($value) || is_int($value)) {
            return ['valid' => true, 'value' => (float)$value];
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return [
                'valid' => false,
                'message' => Craft::t('lindemannrock-base', 'Value must be a number.'),
            ];
        }

        return ['valid' => true, 'value' => (float)$value];
    }

    /**
     * @return array{valid: bool, value?: bool, message?: string}
     */
    private static function normalizeBoolean(mixed $value): array
    {
        if (is_bool($value)) {
            return ['valid' => true, 'value' => $value];
        }

        if (is_int($value) || is_float($value)) {
            if ($value == 0 || $value == 1) {
                return ['valid' => true, 'value' => (bool)$value];
            }
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return ['valid' => true, 'value' => true];
            }
            if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
                return ['valid' => true, 'value' => false];
            }
        }

        return [
            'valid' => false,
            'message' => Craft::t('lindemannrock-base', 'Value must be either true or false.'),
        ];
    }

    /**
     * @return array{valid: bool, value?: string|null}
     */
    private static function normalizeString(mixed $value, bool $allowsNull): ?array
    {
        if ($value === null && $allowsNull) {
            return ['valid' => true, 'value' => null];
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return ['valid' => true, 'value' => (string)$value];
        }

        return null;
    }

    /**
     * @return array{valid: bool, value?: array<mixed>, message?: string}
     */
    private static function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return ['valid' => true, 'value' => $value];
        }

        return [
            'valid' => false,
            'message' => Craft::t('lindemannrock-base', 'Value must be an array.'),
        ];
    }

    private static function assign(Model $model, string $attribute, mixed $value): void
    {
        $setterMethod = 'set' . ucfirst($attribute);
        if (method_exists($model, $setterMethod)) {
            $model->$setterMethod($value);
            return;
        }

        $model->$attribute = $value;
    }
}
