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
use lindemannrock\base\console\RedisDiagnosticsRenderer;
use lindemannrock\base\services\RedisDatabaseDiagnostics;
use lindemannrock\base\services\RedisDatabaseDiagnosticsResult;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\redis\Cache;
use yii\redis\Connection;

/**
 * Reports point-in-time key counts for Craft's configured Redis-cache endpoint.
 *
 * @since 5.37.0
 */
class RedisController extends Controller
{
    private const MAXIMUM_DATABASE_COUNT = 64;

    /**
     * @var int|string First logical database to inspect.
     */
    public $from = 0;

    /**
     * @var int|string Last logical database to inspect.
     */
    public $to = 15;

    /**
     * @var string Output format: human or json.
     */
    public $format = 'human';

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'from',
            'to',
            'format',
        ]);
    }

    /**
     * Run bounded Redis database key-count diagnostics.
     */
    public function actionDatabases(): int
    {
        if (!in_array($this->format, ['human', 'json'], true)) {
            $this->stderr(Craft::t(
                'lindemannrock-base',
                'Output format must be human or json.',
            ) . "\n");

            return ExitCode::USAGE;
        }

        $range = self::normalizeRange($this->from, $this->to);
        if ($range === null) {
            return $this->finish(new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                null,
            ));
        }

        $cache = $this->cacheComponent();
        if (!$cache instanceof Cache) {
            return $this->finish(new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CACHE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                $range,
            ));
        }

        $source = $cache->redis;
        if (!$source instanceof Connection) {
            return $this->finish(new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CONNECTION,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                $range,
            ));
        }

        return $this->finish($this->inspect(
            $source,
            $range['from'],
            $range['to'],
        ));
    }

    protected function cacheComponent(): mixed
    {
        return Craft::$app->getCache();
    }

    protected function inspect(
        Connection $source,
        int $from,
        int $to,
    ): RedisDatabaseDiagnosticsResult {
        return (new RedisDatabaseDiagnostics())->inspect(
            $source,
            $from,
            $to,
        );
    }

    private function finish(RedisDatabaseDiagnosticsResult $result): int
    {
        $this->renderResult($result);

        return match ($result->outcome) {
            RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE => ExitCode::OK,
            RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST => ExitCode::USAGE,
            RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE => ExitCode::UNAVAILABLE,
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CACHE,
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_CONNECTION,
            RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE => ExitCode::CONFIG,
            RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED,
            RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED,
            RedisDatabaseDiagnosticsResult::OUTCOME_SELECT_FAILED,
            RedisDatabaseDiagnosticsResult::OUTCOME_DBSIZE_FAILED => ExitCode::PROTOCOL,
            default => ExitCode::PROTOCOL,
        };
    }

    private function renderResult(RedisDatabaseDiagnosticsResult $result): void
    {
        if ($this->format === 'json') {
            $this->stdout(RedisDiagnosticsRenderer::json($result));

            return;
        }

        $this->stdout(RedisDiagnosticsRenderer::human($result));
    }

    /**
     * @return array{
     *   from: int,
     *   to: int,
     *   databaseCount: int,
     *   maximumDatabaseCount: int,
     * }|null
     */
    private static function normalizeRange(mixed $from, mixed $to): ?array
    {
        $fromDatabase = self::normalizeDatabaseOption($from);
        $toDatabase = self::normalizeDatabaseOption($to);
        if (
            $fromDatabase === null
            || $toDatabase === null
            || $fromDatabase > $toDatabase
            || $toDatabase - $fromDatabase + 1 > self::MAXIMUM_DATABASE_COUNT
        ) {
            return null;
        }

        return [
            'from' => $fromDatabase,
            'to' => $toDatabase,
            'databaseCount' => $toDatabase - $fromDatabase + 1,
            'maximumDatabaseCount' => self::MAXIMUM_DATABASE_COUNT,
        ];
    }

    private static function normalizeDatabaseOption(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return null;
        }

        $normalizedDigits = ltrim($value, '0');
        $normalizedDigits = $normalizedDigits === '' ? '0' : $normalizedDigits;
        $max = (string)PHP_INT_MAX;
        if (
            strlen($normalizedDigits) > strlen($max)
            || (strlen($normalizedDigits) === strlen($max) && strcmp($normalizedDigits, $max) > 0)
        ) {
            return null;
        }

        return (int)$normalizedDigits;
    }
}
