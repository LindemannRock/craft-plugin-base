<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use lindemannrock\base\helpers\ConsoleHelpHelper;
use lindemannrock\base\testing\IntegrationTestCase;

/**
 * Pins the command-manifest rendering contract for {@see ConsoleHelpHelper}.
 *
 * @since 5.26.0
 */
final class ConsoleHelpHelperTest extends IntegrationTestCase
{
    public function testRenderOverviewListsCommonCommandsAndGroups(): void
    {
        $output = ConsoleHelpHelper::renderOverview(self::manifest());

        self::assertStringContainsString('Example Manager CLI', $output);
        self::assertStringContainsString('Common commands', $output);
        self::assertStringContainsString('example-manager/maintenance/clean-by-type --type=<all|site|forms>', $output);
        self::assertStringContainsString('Command groups', $output);
        self::assertStringContainsString('maintenance', $output);
        self::assertStringContainsString('Run focused help', $output);
        self::assertStringContainsString('ddev craft example-manager/help <group/action>', $output);
    }

    public function testRenderCommandShowsOptionsExamplesAndNativeHelp(): void
    {
        $output = ConsoleHelpHelper::renderCommand(self::manifest(), 'maintenance/clean-by-type');

        self::assertStringContainsString('example-manager/maintenance/clean-by-type --type=<all|site|forms>', $output);
        self::assertStringContainsString('--type', $output);
        self::assertStringContainsString('Required. all, site, or forms.', $output);
        self::assertStringContainsString('ddev craft example-manager/maintenance/clean-by-type --type=forms --provider=formie', $output);
        self::assertStringContainsString('Native Craft help', $output);
    }

    public function testRenderCommandSuggestsMatchingActionForWrongGroup(): void
    {
        $output = ConsoleHelpHelper::renderCommand(self::manifest(), 'translations/clean-by-type');

        self::assertStringContainsString("No help entry for 'translations/clean-by-type'.", $output);
        self::assertStringContainsString('Did you mean?', $output);
        self::assertStringContainsString('ddev craft example-manager/help maintenance/clean-by-type', $output);
    }

    public function testHasCommandAcceptsNullAndKnownCommands(): void
    {
        self::assertTrue(ConsoleHelpHelper::hasCommand(self::manifest(), null));
        self::assertTrue(ConsoleHelpHelper::hasCommand(self::manifest(), 'example-manager/maintenance/clean-by-type'));
        self::assertFalse(ConsoleHelpHelper::hasCommand(self::manifest(), 'translations/clean-by-type'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function manifest(): array
    {
        return [
            'title' => 'Example Manager',
            'pluginHandle' => 'example-manager',
            'commandPrefix' => 'ddev craft',
            'summary' => 'Short operator help for example commands.',
            'common' => [
                'maintenance/clean-by-type',
            ],
            'groups' => [
                [
                    'name' => 'maintenance',
                    'label' => 'Maintenance',
                    'description' => 'Clean and inspect data.',
                    'commands' => [
                        [
                            'path' => 'maintenance/clean-by-type',
                            'summary' => 'Clean unused rows by type.',
                            'description' => 'Clean unused rows by type.',
                            'usageOptions' => '--type=<all|site|forms> [--provider=<provider>]',
                            'options' => [
                                [
                                    'name' => '--type',
                                    'description' => 'all, site, or forms.',
                                    'required' => true,
                                ],
                                [
                                    'name' => '--provider',
                                    'description' => 'Only with --type=forms.',
                                ],
                            ],
                            'examples' => [
                                'example-manager/maintenance/clean-by-type --type=forms --provider=formie',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
