<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\console;

use Craft;
use lindemannrock\base\services\RedisDatabaseDiagnosticsResult;
use yii\helpers\Json;

/**
 * Renders Redis diagnostics for human and machine consumers.
 *
 * @internal
 * @since 5.37.0
 */
final class RedisDiagnosticsRenderer
{
    public static function json(RedisDatabaseDiagnosticsResult $result): string
    {
        return Json::encode(
            $result->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ) . "\n";
    }

    public static function human(RedisDatabaseDiagnosticsResult $result): string
    {
        $lines = [
            Craft::t('lindemannrock-base', 'Redis connection: Craft cache'),
            Craft::t('lindemannrock-base', 'Topology: {topology}', [
                'topology' => self::topologyLabel($result->topology),
            ]),
        ];

        if ($result->hasSourceConfiguredDatabase) {
            $lines[] = $result->sourceConfiguredDatabase === null
                ? Craft::t(
                    'lindemannrock-base',
                    'Source configured database: default (no automatic SELECT)',
                )
                : Craft::t(
                    'lindemannrock-base',
                    'Source configured database: {database}',
                    ['database' => $result->sourceConfiguredDatabase],
                );
        }

        $lines[] = Craft::t(
            'lindemannrock-base',
            'Automatic database selection: none',
        );
        $lines[] = Craft::t(
            'lindemannrock-base',
            'SELECT issued on open: no',
        );
        $lines[] = Craft::t('lindemannrock-base', 'Database enumeration: {availability}', [
            'availability' => self::availabilityLabel($result->enumerationAvailable),
        ]);
        $lines[] = Craft::t('lindemannrock-base', 'Enumeration completion: {completion}', [
            'completion' => self::completionLabel($result),
        ]);
        if ($result->requestedRange !== null) {
            $lines[] = Craft::t(
                'lindemannrock-base',
                'Diagnostic range: DB {from} to DB {to}',
                [
                    'from' => $result->requestedRange['from'],
                    'to' => $result->requestedRange['to'],
                ],
            );
        }

        $reason = self::reasonMessage($result);
        if ($reason !== null) {
            $lines[] = '';
            $lines[] = $reason;
        }

        if ($result->databases !== []) {
            $lines[] = '';
            $lines[] = Craft::t(
                'lindemannrock-base',
                'Point-in-time Redis database key counts:',
            );
            foreach ($result->databases as $database) {
                $count = Craft::$app->getFormatter()->asInteger($database['keyCount']);
                $key = $database['keyCount'] === 1
                    ? 'DB {database}: {count} key'
                    : 'DB {database}: {count} keys';
                $lines[] = Craft::t('lindemannrock-base', $key, [
                    'database' => $database['database'],
                    'count' => $count,
                ]);
            }
        }

        $lines[] = '';
        $lines[] = Craft::t(
            'lindemannrock-base',
            'This is a point-in-time key-count diagnostic for Craft\'s configured Redis-cache endpoint.',
        );
        $lines[] = Craft::t(
            'lindemannrock-base',
            'Other plugin Redis endpoints are not inspected.',
        );
        $lines[] = Craft::t(
            'lindemannrock-base',
            'Database ownership must be confirmed with the hosting provider.',
        );

        return implode("\n", $lines) . "\n";
    }

    private static function topologyLabel(string $topology): string
    {
        return Craft::t('lindemannrock-base', match ($topology) {
            RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED => 'Not checked',
            RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE => 'Standalone',
            RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER => 'Cluster',
            default => 'Unknown',
        });
    }

    private static function availabilityLabel(bool $available): string
    {
        return Craft::t(
            'lindemannrock-base',
            $available ? 'Available' : 'Unavailable',
        );
    }

    private static function completionLabel(RedisDatabaseDiagnosticsResult $result): string
    {
        if ($result->enumerationComplete) {
            return Craft::t('lindemannrock-base', 'Complete');
        }

        return Craft::t(
            'lindemannrock-base',
            $result->databases !== [] ? 'Partial' : 'Not started',
        );
    }

    private static function reasonMessage(RedisDatabaseDiagnosticsResult $result): ?string
    {
        return match ($result->outcome) {
            RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE => null,
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CACHE => Craft::t(
                'lindemannrock-base',
                'Craft\'s configured cache component is not a Yii Redis cache.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CONNECTION => Craft::t(
                'lindemannrock-base',
                'Craft\'s Redis cache does not use a supported Yii Redis connection.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE => Craft::t(
                'lindemannrock-base',
                'Craft\'s Redis cache has an unsupported source database configuration.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE => Craft::t(
                'lindemannrock-base',
                'The independent Redis diagnostic connection could not be opened.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED => Craft::t(
                'lindemannrock-base',
                'Logical database enumeration was not run because the endpoint uses Redis Cluster.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED => Craft::t(
                'lindemannrock-base',
                'Logical database enumeration was not run because the endpoint was not positively identified as standalone.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_SELECT_FAILED => Craft::t(
                'lindemannrock-base',
                'Logical database enumeration could not continue because SELECT is unsupported.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_DBSIZE_FAILED => Craft::t(
                'lindemannrock-base',
                'A database key count could not be read; the reported enumeration is partial.',
            ),
            RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST => Craft::t(
                'lindemannrock-base',
                'The database range must use non-negative whole numbers, run from lower to higher, and contain no more than 64 databases.',
            ),
            default => null,
        };
    }
}
