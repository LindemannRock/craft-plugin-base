<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\services;

use lindemannrock\base\helpers\YiiRedisConnectionHelper;
use Throwable;
use yii\db\Exception as YiiDbException;
use yii\redis\Connection;

/**
 * Runs bounded, read-only Redis database key-count diagnostics.
 *
 * @since 5.37.0
 */
class RedisDatabaseDiagnostics
{
    private const CLUSTER_DISABLED_EXCEPTION = "Redis error: ERR This instance has cluster support disabled\nRedis command was: CLUSTER INFO";
    private const MAXIMUM_DATABASE_COUNT = 64;

    /**
     * Inspect a bounded logical-database range on an independently owned
     * connection.
     */
    public function inspect(
        Connection $source,
        int $fromDatabase = 0,
        int $toDatabase = 15,
    ): RedisDatabaseDiagnosticsResult {
        if (!self::isValidRange($fromDatabase, $toDatabase)) {
            return new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_INVALID_REQUEST,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                null,
            );
        }

        $normalizedSourceDatabase = self::normalizeSourceDatabase($source->database);
        if (!$normalizedSourceDatabase['supported']) {
            return new RedisDatabaseDiagnosticsResult(
                RedisDatabaseDiagnosticsResult::OUTCOME_UNSUPPORTED_SOURCE_DATABASE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                self::range($fromDatabase, $toDatabase),
            );
        }

        try {
            $connection = $this->createDiagnosticConnection($source);
        } catch (Throwable) {
            return $this->result(
                RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_NOT_ATTEMPTED,
                false,
                false,
                $fromDatabase,
                $toDatabase,
                [],
                $normalizedSourceDatabase['value'],
            );
        }

        try {
            if ($connection->executeCommand('PING') !== true) {
                throw new \RuntimeException('Unrecognized PING response.');
            }
        } catch (Throwable) {
            $this->close($connection);

            return $this->result(
                RedisDatabaseDiagnosticsResult::OUTCOME_DIAGNOSTIC_CONNECTION_UNAVAILABLE,
                RedisDatabaseDiagnosticsResult::TOPOLOGY_NOT_CHECKED,
                RedisDatabaseDiagnosticsResult::PING_FAILED,
                false,
                false,
                $fromDatabase,
                $toDatabase,
                [],
                $normalizedSourceDatabase['value'],
            );
        }

        try {
            $topology = $this->detectTopology($connection);
            if ($topology === RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER) {
                return $this->result(
                    RedisDatabaseDiagnosticsResult::OUTCOME_CLUSTER_DETECTED,
                    $topology,
                    RedisDatabaseDiagnosticsResult::PING_OK,
                    false,
                    false,
                    $fromDatabase,
                    $toDatabase,
                    [],
                    $normalizedSourceDatabase['value'],
                );
            }

            if ($topology !== RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE) {
                return $this->result(
                    RedisDatabaseDiagnosticsResult::OUTCOME_TOPOLOGY_UNDETERMINED,
                    RedisDatabaseDiagnosticsResult::TOPOLOGY_UNKNOWN,
                    RedisDatabaseDiagnosticsResult::PING_OK,
                    false,
                    false,
                    $fromDatabase,
                    $toDatabase,
                    [],
                    $normalizedSourceDatabase['value'],
                );
            }

            return $this->enumerateDatabases(
                $connection,
                $fromDatabase,
                $toDatabase,
                $normalizedSourceDatabase['value'],
            );
        } finally {
            $this->close($connection);
        }
    }

    /**
     * Create the independently owned, non-persistent diagnostic connection.
     */
    protected function createDiagnosticConnection(Connection $source): Connection
    {
        return YiiRedisConnectionHelper::createIndependentConnection(
            $source,
            null,
            0,
        );
    }

    /**
     * @return array{supported: bool, value: ?int}
     */
    private static function normalizeSourceDatabase(mixed $value): array
    {
        if ($value === null) {
            return [
                'supported' => true,
                'value' => null,
            ];
        }

        if (is_int($value)) {
            return [
                'supported' => $value >= 0,
                'value' => $value >= 0 ? $value : null,
            ];
        }

        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return [
                'supported' => false,
                'value' => null,
            ];
        }

        $normalizedDigits = ltrim($value, '0');
        $normalizedDigits = $normalizedDigits === '' ? '0' : $normalizedDigits;
        $max = (string)PHP_INT_MAX;
        if (
            strlen($normalizedDigits) > strlen($max)
            || (strlen($normalizedDigits) === strlen($max) && strcmp($normalizedDigits, $max) > 0)
        ) {
            return [
                'supported' => false,
                'value' => null,
            ];
        }

        return [
            'supported' => true,
            'value' => (int)$normalizedDigits,
        ];
    }

    private static function isValidRange(int $from, int $to): bool
    {
        return $from >= 0
            && $from <= $to
            && $to - $from + 1 <= self::MAXIMUM_DATABASE_COUNT;
    }

    private function detectTopology(Connection $connection): string
    {
        try {
            $info = $connection->executeCommand('CLUSTER', ['INFO']);
        } catch (Throwable $exception) {
            return $exception instanceof YiiDbException
                && $exception->getMessage() === self::CLUSTER_DISABLED_EXCEPTION
                    ? RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE
                    : RedisDatabaseDiagnosticsResult::TOPOLOGY_UNKNOWN;
        }

        return is_string($info)
            && preg_match('/^cluster_state:(?:ok|fail)\r?$/mi', $info) === 1
                ? RedisDatabaseDiagnosticsResult::TOPOLOGY_CLUSTER
                : RedisDatabaseDiagnosticsResult::TOPOLOGY_UNKNOWN;
    }

    private function enumerateDatabases(
        Connection $connection,
        int $from,
        int $to,
        ?int $sourceConfiguredDatabase,
    ): RedisDatabaseDiagnosticsResult {
        $databases = [];

        for ($database = $from; $database <= $to; $database++) {
            try {
                $connection->executeCommand('SELECT', [$database]);
            } catch (Throwable) {
                return $this->result(
                    RedisDatabaseDiagnosticsResult::OUTCOME_SELECT_FAILED,
                    RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
                    RedisDatabaseDiagnosticsResult::PING_OK,
                    true,
                    false,
                    $from,
                    $to,
                    $databases,
                    $sourceConfiguredDatabase,
                );
            }

            try {
                $keyCount = self::normalizeKeyCount(
                    $connection->executeCommand('DBSIZE'),
                );
            } catch (Throwable) {
                $keyCount = [
                    'supported' => false,
                    'value' => null,
                ];
            }

            if (!$keyCount['supported'] || $keyCount['value'] === null) {
                return $this->result(
                    RedisDatabaseDiagnosticsResult::OUTCOME_DBSIZE_FAILED,
                    RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
                    RedisDatabaseDiagnosticsResult::PING_OK,
                    true,
                    false,
                    $from,
                    $to,
                    $databases,
                    $sourceConfiguredDatabase,
                );
            }

            $databases[] = [
                'database' => $database,
                'keyCount' => $keyCount['value'],
            ];
        }

        return $this->result(
            RedisDatabaseDiagnosticsResult::OUTCOME_COMPLETE,
            RedisDatabaseDiagnosticsResult::TOPOLOGY_STANDALONE,
            RedisDatabaseDiagnosticsResult::PING_OK,
            true,
            true,
            $from,
            $to,
            $databases,
            $sourceConfiguredDatabase,
        );
    }

    /**
     * @param list<array{database: int, keyCount: int}> $databases
     */
    private function result(
        string $outcome,
        string $topology,
        string $ping,
        bool $enumerationAvailable,
        bool $enumerationComplete,
        int $from,
        int $to,
        array $databases,
        ?int $sourceConfiguredDatabase,
    ): RedisDatabaseDiagnosticsResult {
        return new RedisDatabaseDiagnosticsResult(
            $outcome,
            $topology,
            $ping,
            $enumerationAvailable,
            $enumerationComplete,
            self::range($from, $to),
            $databases,
            true,
            $sourceConfiguredDatabase,
        );
    }

    /**
     * @return array{
     *   from: int,
     *   to: int,
     *   databaseCount: int,
     *   maximumDatabaseCount: int,
     * }
     */
    private static function range(int $from, int $to): array
    {
        return [
            'from' => $from,
            'to' => $to,
            'databaseCount' => $to - $from + 1,
            'maximumDatabaseCount' => self::MAXIMUM_DATABASE_COUNT,
        ];
    }

    /**
     * @return array{supported: bool, value: ?int}
     */
    private static function normalizeKeyCount(mixed $value): array
    {
        if (is_int($value)) {
            return [
                'supported' => $value >= 0,
                'value' => $value >= 0 ? $value : null,
            ];
        }

        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return [
                'supported' => false,
                'value' => null,
            ];
        }

        $normalizedDigits = ltrim($value, '0');
        $normalizedDigits = $normalizedDigits === '' ? '0' : $normalizedDigits;
        $max = (string)PHP_INT_MAX;
        if (
            strlen($normalizedDigits) > strlen($max)
            || (strlen($normalizedDigits) === strlen($max) && strcmp($normalizedDigits, $max) > 0)
        ) {
            return [
                'supported' => false,
                'value' => null,
            ];
        }

        return [
            'supported' => true,
            'value' => (int)$normalizedDigits,
        ];
    }

    private function close(?Connection $connection): void
    {
        if ($connection === null) {
            return;
        }

        try {
            $connection->close();
        } catch (Throwable) {
            // Best-effort cleanup of the independently owned connection.
        }
    }
}
