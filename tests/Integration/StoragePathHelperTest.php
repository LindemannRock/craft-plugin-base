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

    private function setEnvValue(string $value): void
    {
        putenv(self::ENV_NAME . '=' . $value);
        $_ENV[self::ENV_NAME] = $value;
        $_SERVER[self::ENV_NAME] = $value;
    }
}
