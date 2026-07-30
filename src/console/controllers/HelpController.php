<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\console\controllers;

use Craft;

/**
 * Console help for Base commands.
 *
 * @since 5.37.0
 */
class HelpController extends AbstractHelpController
{
    /**
     * @inheritdoc
     */
    protected function helpManifest(): array
    {
        return [
            'title' => Craft::t(
                'lindemannrock-base',
                'LindemannRock Base',
            ),
            'pluginHandle' => 'lindemannrock-base',
            'commandPrefixes' => [
                'php craft',
                'ddev craft',
            ],
            'summary' => Craft::t(
                'lindemannrock-base',
                'Use these commands for bounded, read-only diagnostics on shared Base infrastructure.',
            ),
            'common' => [
                'redis/databases',
            ],
            'groups' => [
                [
                    'name' => 'redis',
                    'label' => Craft::t(
                        'lindemannrock-base',
                        'Redis diagnostics',
                    ),
                    'description' => Craft::t(
                        'lindemannrock-base',
                        'Inspect Craft Redis-cache connectivity and point-in-time logical database key counts.',
                    ),
                    'commands' => [
                        [
                            'path' => 'redis/databases',
                            'summary' => Craft::t(
                                'lindemannrock-base',
                                'Show bounded point-in-time Redis database key counts.',
                            ),
                            'description' => Craft::t(
                                'lindemannrock-base',
                                'Inspect only Craft\'s configured Redis-cache endpoint using an independently owned, non-persistent connection.',
                            ),
                            'usageOptions' => '[--from=<database>] [--to=<database>] [--format=<human|json>]',
                            'options' => [
                                [
                                    'name' => '--from',
                                    'description' => Craft::t(
                                        'lindemannrock-base',
                                        'First logical database in the inclusive range. Default: 0.',
                                    ),
                                ],
                                [
                                    'name' => '--to',
                                    'description' => Craft::t(
                                        'lindemannrock-base',
                                        'Last logical database in the inclusive range. Default: 15; at most 64 databases.',
                                    ),
                                ],
                                [
                                    'name' => '--format',
                                    'description' => Craft::t(
                                        'lindemannrock-base',
                                        'Output format: human or json. Default: human.',
                                    ),
                                ],
                            ],
                            'examples' => [
                                'lindemannrock-base/redis/databases',
                                'lindemannrock-base/redis/databases --from=0 --to=3',
                                'lindemannrock-base/redis/databases --format=json',
                            ],
                            'notes' => [
                                Craft::t(
                                    'lindemannrock-base',
                                    'The command is read-only and never uses KEYS, SCAN, or mutating Redis commands.',
                                ),
                                Craft::t(
                                    'lindemannrock-base',
                                    'Logical databases are inspected only after the endpoint is positively identified as standalone.',
                                ),
                                Craft::t(
                                    'lindemannrock-base',
                                    'Other plugin Redis endpoints are outside this command\'s visibility.',
                                ),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
