<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use Craft;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\validators\StoragePathValidator;
use yii\base\DynamicModel;

/**
 * Pins the contract for {@see StoragePathValidator}.
 *
 * @since 5.26.0
 */
final class StoragePathValidatorTest extends IntegrationTestCase
{
    private const ENV_NAME = 'LR_STORAGE_PATH_VALIDATOR_TEST';

    protected function tearDown(): void
    {
        putenv(self::ENV_NAME);
        unset($_ENV[self::ENV_NAME], $_SERVER[self::ENV_NAME]);

        parent::tearDown();
    }

    public function testAllowedAliasPassesWhenAliasIsRequired(): void
    {
        $model = $this->validate('@storage/example');

        self::assertFalse($model->hasErrors('path'));
    }

    public function testEnvVarResolvingToAllowedAliasPassesWhenAliasIsRequired(): void
    {
        $this->setEnvValue('@storage/example');

        $model = $this->validate('$' . self::ENV_NAME);

        self::assertFalse($model->hasErrors('path'));
    }

    public function testEnvVarResolvingToAllowedAbsolutePathPassesWhenAliasIsRequired(): void
    {
        $this->setEnvValue(Craft::getAlias('@storage/example'));

        $model = $this->validate('$' . self::ENV_NAME);

        self::assertFalse($model->hasErrors('path'));
    }

    public function testAllowedAbsolutePathPassesWhenAliasIsRequired(): void
    {
        $model = $this->validate(Craft::getAlias('@storage/example'));

        self::assertFalse($model->hasErrors('path'));
    }

    public function testEnvVarResolvingOutsideAllowedAliasesFailsWhenAliasIsRequired(): void
    {
        $this->setEnvValue('/tmp/lr-storage-path-validator');

        $model = $this->validate('$' . self::ENV_NAME);

        self::assertTrue($model->hasErrors('path'));
        self::assertStringContainsString('@storage', (string)$model->getFirstError('path'));
    }

    public function testAbsolutePathOutsideAllowedAliasesFailsWhenAliasIsRequired(): void
    {
        $model = $this->validate('/tmp/lr-storage-path-validator');

        self::assertTrue($model->hasErrors('path'));
        self::assertStringContainsString('@storage', (string)$model->getFirstError('path'));
    }

    public function testEnvVarResolvingToWebrootFails(): void
    {
        $this->setEnvValue(Craft::getAlias('@webroot'));

        $model = $this->validate('$' . self::ENV_NAME);

        self::assertTrue($model->hasErrors('path'));
        self::assertStringContainsString('web-accessible', (string)$model->getFirstError('path'));
    }

    public function testWebrootCanBeAllowedWhenWebrootPreventionIsDisabled(): void
    {
        $model = $this->validate('@webroot/assets/icons', [
            'allowedAliases' => ['@root', '@storage', '@webroot'],
            'preventWebroot' => false,
        ]);

        self::assertFalse($model->hasErrors('path'));
    }

    public function testEnvVarsCanBeDisabledPerValidator(): void
    {
        $this->setEnvValue('@storage/example');

        $model = $this->validate('$' . self::ENV_NAME, ['allowEnvVars' => false]);

        self::assertTrue($model->hasErrors('path'));
        self::assertStringContainsString('Environment variables are not allowed', (string)$model->getFirstError('path'));
    }

    /**
     * @param array<string, mixed> $options
     */
    private function validate(string $path, array $options = []): DynamicModel
    {
        $model = DynamicModel::validateData(['path' => $path], [
            array_merge(
                [
                    ['path'],
                    StoragePathValidator::class,
                    'allowedAliases' => ['@storage', '@root'],
                    'requireAlias' => true,
                    'preventWebroot' => true,
                ],
                $options,
            ),
        ]);

        self::assertInstanceOf(DynamicModel::class, $model);

        return $model;
    }

    private function setEnvValue(string $value): void
    {
        putenv(self::ENV_NAME . '=' . $value);
        $_ENV[self::ENV_NAME] = $value;
        $_SERVER[self::ENV_NAME] = $value;
    }
}
