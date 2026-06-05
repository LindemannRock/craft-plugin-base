<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use craft\base\Model;
use lindemannrock\base\helpers\SettingsPostHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the v1 contract for {@see SettingsPostHelper}.
 *
 * @since 5.26.0
 */
final class SettingsPostHelperTest extends IntegrationTestCase
{
    public function testApplyNormalizesSupportedPropertyTypes(): void
    {
        $settings = new SettingsPostHelperTestModel();

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: [
                'integerValue' => '42',
                'nullableIntegerValue' => '',
                'floatValue' => '1.25',
                'booleanValue' => 'on',
                'nullableBooleanValue' => '',
                'stringValue' => 123,
                'nullableStringValue' => '',
                'arrayValue' => ['a' => 'b'],
            ],
            allowedAttributes: [
                'integerValue',
                'nullableIntegerValue',
                'floatValue',
                'booleanValue',
                'nullableBooleanValue',
                'stringValue',
                'nullableStringValue',
                'arrayValue',
            ],
        );

        self::assertSame(42, $settings->integerValue);
        self::assertNull($settings->nullableIntegerValue);
        self::assertSame(1.25, $settings->floatValue);
        self::assertTrue($settings->booleanValue);
        self::assertNull($settings->nullableBooleanValue);
        self::assertSame('123', $settings->stringValue);
        self::assertNull($settings->nullableStringValue);
        self::assertSame(['a' => 'b'], $settings->arrayValue);
        self::assertFalse($result->hasErrors);
        self::assertSame([
            'integerValue',
            'nullableIntegerValue',
            'floatValue',
            'booleanValue',
            'nullableBooleanValue',
            'stringValue',
            'nullableStringValue',
            'arrayValue',
        ], $result->assignedAttributes);
    }

    public function testApplyTreatsPostedEmptyStringAsFalseForNonNullableBooleans(): void
    {
        $settings = new SettingsPostHelperTestModel();
        $settings->booleanValue = true;

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: [
                'booleanValue' => '',
            ],
            allowedAttributes: [
                'booleanValue',
            ],
        );

        self::assertFalse($settings->booleanValue);
        self::assertFalse($result->hasErrors);
        self::assertSame(['booleanValue'], $result->assignedAttributes);
    }

    public function testApplyIgnoresUnknownOffSectionConfigOverriddenAndUnsupportedProperties(): void
    {
        $settings = new SettingsPostHelperTestModel();

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: [
                'integerValue' => '42',
                'offSectionValue' => 'changed',
                'configLockedValue' => 'changed',
                'unknownValue' => 'changed',
                'untypedValue' => 'changed',
                'objectValue' => new \DateTimeImmutable(),
            ],
            allowedAttributes: ['integerValue', 'configLockedValue', 'untypedValue', 'objectValue'],
            isOverridden: static fn(string $attribute): bool => $attribute === 'configLockedValue',
        );

        self::assertSame(42, $settings->integerValue);
        self::assertSame('locked', $settings->configLockedValue);
        self::assertSame('initial', $settings->untypedValue);
        self::assertNull($settings->objectValue);
        self::assertSame(['integerValue', 'untypedValue', 'objectValue'], $result->attributesToValidate);
        self::assertSame(['integerValue'], $result->assignedAttributes);
        self::assertSame(['offSectionValue', 'configLockedValue', 'unknownValue', 'untypedValue', 'objectValue'], $result->ignoredAttributes);
    }

    public function testApplyAddsErrorsForInvalidTypedValues(): void
    {
        $settings = new SettingsPostHelperTestModel();

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: [
                'integerValue' => 'abc',
                'floatValue' => 'abc',
                'booleanValue' => 'maybe',
                'arrayValue' => 'not-array',
            ],
            allowedAttributes: ['integerValue', 'floatValue', 'booleanValue', 'arrayValue'],
        );

        self::assertTrue($result->hasErrors);
        self::assertSame([], $result->assignedAttributes);
        self::assertSame(['Value must be a whole number.'], $settings->getErrors('integerValue'));
        self::assertSame(['Value must be a number.'], $settings->getErrors('floatValue'));
        self::assertSame(['Value must be either true or false.'], $settings->getErrors('booleanValue'));
        self::assertSame(['Value must be an array.'], $settings->getErrors('arrayValue'));
    }

    public function testApplyUsesAdaptersBeforeNormalizationAndSettersForAssignment(): void
    {
        $settings = new SettingsPostHelperTestModel();

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: [
                'integerValue' => ['42'],
                'arrayValue' => '{"enabled":true}',
                'objectValue' => '2026-06-05',
            ],
            allowedAttributes: ['integerValue', 'arrayValue', 'objectValue'],
            adapters: [
                'integerValue' => static fn(mixed $value): mixed => is_array($value) ? ($value[0] ?? null) : $value,
                'arrayValue' => static fn(mixed $value): array => is_string($value)
                    ? (json_decode($value, true) ?: [])
                    : (is_array($value) ? $value : []),
                'objectValue' => static fn(mixed $value): \DateTimeImmutable => new \DateTimeImmutable((string)$value),
            ],
        );

        self::assertSame(42, $settings->integerValue);
        self::assertSame(['enabled' => true], $settings->arrayValue);
        self::assertInstanceOf(\DateTimeImmutable::class, $settings->objectValue);
        self::assertSame(['integerValue', 'arrayValue', 'objectValue'], $result->assignedAttributes);
        self::assertFalse($settings->setterWasBypassed);
    }
}

final class SettingsPostHelperTestModel extends Model
{
    public int $integerValue = 0;

    public ?int $nullableIntegerValue = 1;

    public float $floatValue = 0.0;

    public bool $booleanValue = false;

    public ?bool $nullableBooleanValue = null;

    public string $stringValue = '';

    public ?string $nullableStringValue = 'initial';

    /**
     * @var array<mixed>
     */
    public array $arrayValue = [];

    public string $configLockedValue = 'locked';

    public \DateTimeImmutable|null $objectValue = null;

    public mixed $untypedValue = 'initial';

    public bool $setterWasBypassed = true;

    public function setObjectValue(\DateTimeImmutable $value): void
    {
        $this->setterWasBypassed = false;
        $this->objectValue = $value;
    }
}
