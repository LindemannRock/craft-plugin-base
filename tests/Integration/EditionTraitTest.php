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
use craft\base\Plugin;
use craft\web\View;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\traits\EditionTrait;
use yii\web\ForbiddenHttpException;

/**
 * Pins the controller gate and shared upgrade-prompt contracts for {@see EditionTrait}.
 *
 * @since 5.36.0
 */
final class EditionTraitTest extends IntegrationTestCase
{
    public function testConsoleRequestUsesExceptionGateBelowRequiredEdition(): void
    {
        $plugin = $this->createPlugin(EditionTraitTestPlugin::EDITION_STANDARD);

        $this->expectException(ForbiddenHttpException::class);

        $plugin->requireEditionOrPrompt(EditionTraitTestPlugin::EDITION_PRO, 'Analytics');
    }

    public function testRequiredEditionPassesWithoutResponse(): void
    {
        $plugin = $this->createPlugin(EditionTraitTestPlugin::EDITION_PRO);

        self::assertNull($plugin->requireEditionOrPrompt(EditionTraitTestPlugin::EDITION_PRO, 'Analytics'));
    }

    public function testUpgradePromptDerivesPluginMetadataAndRendersConsumerPitch(): void
    {
        $plugin = $this->createPlugin(EditionTraitTestPlugin::EDITION_STANDARD);
        $html = Craft::$app->getView()->renderTemplate(
            'lindemannrock-base/_partials/edition-upgrade-prompt',
            [
                'plugin' => $plugin,
                'edition' => EditionTraitTestPlugin::EDITION_PRO,
                'featureName' => 'Analytics',
                'pitch' => 'Edition Test Pro adds advanced tools.',
            ],
            View::TEMPLATE_MODE_CP,
        );

        self::assertStringContainsString('Analytics requires Edition Test Pro', $html);
        self::assertStringContainsString('Edition Test Pro adds advanced tools.', $html);
        self::assertStringContainsString('plugin-store/edition-test', $html);
        self::assertStringContainsString('View Edition Test Pro in the Plugin Store', $html);
    }

    private function createPlugin(string $edition): EditionTraitTestPlugin
    {
        return new EditionTraitTestPlugin('edition-test', null, [
            'name' => 'Edition Test',
            'edition' => $edition,
        ]);
    }
}

/**
 * Multi-edition plugin fixture for {@see EditionTraitTest}.
 *
 * @since 5.36.0
 */
final class EditionTraitTestPlugin extends Plugin
{
    use EditionTrait;

    /** @inheritdoc */
    public static function editions(): array
    {
        return [self::EDITION_STANDARD, self::EDITION_PRO];
    }
}
