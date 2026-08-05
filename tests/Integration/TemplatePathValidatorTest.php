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
use craft\helpers\FileHelper;
use craft\web\View;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\validators\TemplatePathValidator;
use yii\base\DynamicModel;

/**
 * Pins the contract for {@see TemplatePathValidator}.
 *
 * @since 5.34.0
 */
final class TemplatePathValidatorTest extends IntegrationTestCase
{
    public function testExistingSiteTemplatePassesWhenCurrentViewModeIsCp(): void
    {
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();

        $template = $this->nextTestMarker('__base_template_validator_', 'template');
        $directory = Craft::$app->getPath()->getSiteTemplatesPath() . DIRECTORY_SEPARATOR . '__base_template_validator';
        FileHelper::createDirectory($directory);
        $this->trackTempPath($directory);

        file_put_contents($directory . DIRECTORY_SEPARATOR . $template . '.twig', 'Template validator fixture.');

        try {
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);

            $model = $this->validate('__base_template_validator/' . $template, true);
        } finally {
            $view->setTemplateMode($oldTemplateMode);
        }

        self::assertFalse($model->hasErrors('template'));
    }

    public function testMissingSiteTemplateFailsWhenExistenceCheckIsEnabled(): void
    {
        $model = $this->validate('__base_template_validator/missing-' . bin2hex(random_bytes(4)), true);

        self::assertTrue($model->hasErrors('template'));
        self::assertStringContainsString('does not exist', (string)$model->getFirstError('template'));
    }

    private function validate(string $template, bool $checkTemplateExists = false): DynamicModel
    {
        $model = DynamicModel::validateData(['template' => $template], [
            [
                ['template'],
                TemplatePathValidator::class,
                'translationCategory' => 'lindemannrock-base',
                'checkTemplateExists' => $checkTemplateExists,
            ],
        ]);

        self::assertInstanceOf(DynamicModel::class, $model);

        return $model;
    }
}
