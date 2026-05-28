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
use lindemannrock\base\helpers\StoragePathHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the contract for {@see StoragePathHelper}.
 *
 * @since 5.26.0
 */
final class StoragePathHelperTest extends IntegrationTestCase
{
    private const ENV_NAME = 'LR_STORAGE_PATH_HELPER_TEST';

    protected function tearDown(): void
    {
        putenv(self::ENV_NAME);
        unset($_ENV[self::ENV_NAME], $_SERVER[self::ENV_NAME]);

        parent::tearDown();
    }

    public function testResolveParsesEnvVarToAlias(): void
    {
        $this->setEnvValue('@storage/example');

        self::assertSame(
            Craft::getAlias('@storage/example'),
            StoragePathHelper::resolve('$' . self::ENV_NAME),
        );
    }

    public function testResolveParsesEnvVarToAbsolutePath(): void
    {
        $path = Craft::getAlias('@storage/example');
        $this->setEnvValue($path);

        self::assertSame($path, StoragePathHelper::resolve('$' . self::ENV_NAME));
    }

    public function testResolveHandlesLiteralAlias(): void
    {
        self::assertSame(
            Craft::getAlias('@storage/example'),
            StoragePathHelper::resolve('@storage/example'),
        );
    }

    public function testValidatePathAcceptsAllowedAlias(): void
    {
        self::assertSame([], $this->validate('@storage/example'));
    }

    public function testValidatePathAcceptsEnvVarResolvingInsideAllowedAliasRoot(): void
    {
        $this->setEnvValue(Craft::getAlias('@storage/example'));

        self::assertSame([], $this->validate('$' . self::ENV_NAME));
    }

    public function testValidatePathRejectsEnvVarResolvingOutsideAllowedAliasRoot(): void
    {
        $this->setEnvValue('/tmp/lr-storage-path-helper');

        $errors = $this->validate('$' . self::ENV_NAME);

        self::assertNotEmpty($errors);
        self::assertStringContainsString('@storage', implode(' ', $errors));
    }

    public function testValidatePathRejectsWebrootWhenPreventWebrootIsEnabled(): void
    {
        $this->setEnvValue(Craft::getAlias('@webroot'));

        $errors = $this->validate('$' . self::ENV_NAME);

        self::assertNotEmpty($errors);
        self::assertStringContainsString('web-accessible', implode(' ', $errors));
    }

    public function testValidatePathCanAllowWebrootWhenPreventionIsDisabled(): void
    {
        self::assertSame([], $this->validate('@webroot/assets/icons', [
            'allowedAliases' => ['@root', '@storage', '@webroot'],
            'preventWebroot' => false,
        ]));
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, string>
     */
    private function validate(string $path, array $options = []): array
    {
        return StoragePathHelper::validatePath($path, array_merge([
            'allowedAliases' => ['@storage', '@root'],
            'requireAlias' => true,
            'preventWebroot' => true,
        ], $options));
    }

    private function setEnvValue(string $value): void
    {
        putenv(self::ENV_NAME . '=' . $value);
        $_ENV[self::ENV_NAME] = $value;
        $_SERVER[self::ENV_NAME] = $value;
    }
}
