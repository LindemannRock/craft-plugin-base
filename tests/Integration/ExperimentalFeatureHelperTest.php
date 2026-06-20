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
use lindemannrock\base\helpers\ExperimentalFeatureHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use yii\web\NotFoundHttpException;

/**
 * Pins the contract for {@see ExperimentalFeatureHelper}.
 *
 * @since 5.28.0
 */
final class ExperimentalFeatureHelperTest extends IntegrationTestCase
{
    private const ENV_FLAG = 'LR_BASE_TEST_EXPERIMENTAL_FEATURE';

    protected function tearDown(): void
    {
        $this->setEnvFlag(null);
        parent::tearDown();
    }

    public function testFeatureIsDisabledUnlessEnvFlagIsLiteralTrue(): void
    {
        foreach ([null, '', 'false', '0', '1', 'yes', 'on', 'TRUE', ' true '] as $value) {
            $this->setEnvFlag($value);

            self::assertFalse(
                ExperimentalFeatureHelper::isEnabled(self::ENV_FLAG),
                sprintf('expected %s to be disabled', var_export($value, true)),
            );
        }

        $this->setEnvFlag('true');

        self::assertTrue(ExperimentalFeatureHelper::isEnabled(self::ENV_FLAG));
    }

    public function testDevModeIsAdditionalRequirementOnly(): void
    {
        $this->setEnvFlag(null);

        self::assertFalse(ExperimentalFeatureHelper::isEnabled(self::ENV_FLAG, requireDevMode: true));

        $this->setEnvFlag('true');

        self::assertSame(
            (bool)Craft::$app->getConfig()->getGeneral()->devMode,
            ExperimentalFeatureHelper::isEnabled(self::ENV_FLAG, requireDevMode: true),
        );
    }

    public function testRequireEnabledThrowsNotFoundWhenDisabled(): void
    {
        $this->setEnvFlag('false');

        $this->expectException(NotFoundHttpException::class);

        ExperimentalFeatureHelper::requireEnabled(self::ENV_FLAG);
    }

    public function testRequireEnabledAllowsLiteralTrue(): void
    {
        $this->setEnvFlag('true');

        ExperimentalFeatureHelper::requireEnabled(self::ENV_FLAG);

        self::addToAssertionCount(1);
    }

    private function setEnvFlag(?string $value): void
    {
        unset($_SERVER[self::ENV_FLAG], $_ENV[self::ENV_FLAG]);

        if ($value === null) {
            putenv(self::ENV_FLAG);
            return;
        }

        $_SERVER[self::ENV_FLAG] = $value;
        $_ENV[self::ENV_FLAG] = $value;
        putenv(self::ENV_FLAG . '=' . $value);
    }
}
