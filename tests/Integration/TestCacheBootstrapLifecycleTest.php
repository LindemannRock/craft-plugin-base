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
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @since 5.37.0
 */
#[CoversNothing]
final class TestCacheBootstrapLifecycleTest extends IntegrationTestCase
{
    public function testSuccessfulFailureAndBackendReplacementProcessesRemoveOnlyOwnedCachePaths(): void
    {
        $sentinel = Craft::$app->getPath()->getTempPath()
            . DIRECTORY_SEPARATOR
            . 'base-cache-sentinel-' . bin2hex(random_bytes(6));
        file_put_contents($sentinel, 'unrelated');

        try {
            foreach ([['success', 0], ['failure', 37], ['replace-backend', 0]] as [$mode, $expectedStatus]) {
                $result = $this->runProbe($mode);
                self::assertSame($expectedStatus, $result['status'], $result['stderr']);
                self::assertNotSame('', $result['cachePath']);
                self::assertDirectoryDoesNotExist($result['cachePath']);
                self::assertFileExists($sentinel);
            }
        } finally {
            @unlink($sentinel);
        }
    }

    public function testConcurrentBootstrapProcessesUseDistinctPathsAndCleanBothOnExit(): void
    {
        $coordination = $this->createTrackedTempDirectory('__base_cache_processes_');
        $first = $this->startProbe('wait', $coordination . '/first.ready', $coordination . '/first.release');
        $second = $this->startProbe('wait', $coordination . '/second.ready', $coordination . '/second.release');

        try {
            $firstPath = $this->waitForReadyPath($coordination . '/first.ready');
            $secondPath = $this->waitForReadyPath($coordination . '/second.ready');
            self::assertNotSame($firstPath, $secondPath);
            self::assertDirectoryExists($firstPath);
            self::assertDirectoryExists($secondPath);

            file_put_contents($coordination . '/first.release', 'release');
            file_put_contents($coordination . '/second.release', 'release');
            self::assertSame(0, $this->closeProbe($first));
            self::assertSame(0, $this->closeProbe($second));
            self::assertDirectoryDoesNotExist($firstPath);
            self::assertDirectoryDoesNotExist($secondPath);
        } finally {
            $this->stopProbe($first);
            $this->stopProbe($second);
        }
    }

    /** @return array{status: int, cachePath: string, stderr: string} */
    private function runProbe(string $mode): array
    {
        $probe = $this->startProbe($mode);
        $stdout = stream_get_contents($probe['pipes'][1]);
        $stderr = stream_get_contents($probe['pipes'][2]);
        fclose($probe['pipes'][1]);
        fclose($probe['pipes'][2]);
        $status = proc_close($probe['process']);

        return [
            'status' => $status,
            'cachePath' => is_string($stdout) ? trim($stdout) : '',
            'stderr' => is_string($stderr) ? $stderr : '',
        ];
    }

    /**
     * @return array{process: resource, pipes: array<int, resource>}
     */
    private function startProbe(string $mode, string $readyPath = '', string $releasePath = ''): array
    {
        $environment = [];
        foreach ($_SERVER as $name => $value) {
            if (is_string($name) && is_string($value)) {
                $environment[$name] = $value;
            }
        }
        $environment['LINDEMANNROCK_BASE_TEST_CACHE_PATH'] = '';

        $pipes = [];
        $process = proc_open([
            PHP_BINARY,
            dirname(__DIR__) . '/Fixtures/TestCacheBootstrapProbe.php',
            dirname(Craft::$app->getPath()->getStoragePath()),
            $mode,
            $readyPath,
            $releasePath,
        ], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes, dirname(__DIR__, 2), $environment);
        if (!is_resource($process)) {
            self::fail('Unable to start the test-cache bootstrap probe.');
        }
        fclose($pipes[0]);

        return ['process' => $process, 'pipes' => $pipes];
    }

    private function waitForReadyPath(string $readyPath): string
    {
        for ($attempt = 0; $attempt < 250; $attempt++) {
            if (is_file($readyPath)) {
                $path = file_get_contents($readyPath);
                if (is_string($path) && $path !== '') {
                    return $path;
                }
            }
            usleep(20_000);
        }

        self::fail("Timed out waiting for cache probe: {$readyPath}");
    }

    /** @param array{process: resource, pipes: array<int, resource>} $probe */
    private function closeProbe(array &$probe): int
    {
        foreach ([1, 2] as $pipe) {
            if (isset($probe['pipes'][$pipe]) && is_resource($probe['pipes'][$pipe])) {
                fclose($probe['pipes'][$pipe]);
            }
        }
        $status = proc_close($probe['process']);
        unset($probe['process']);

        return $status;
    }

    /** @param array{process?: resource, pipes: array<int, resource>} $probe */
    private function stopProbe(array &$probe): void
    {
        if (!isset($probe['process']) || !is_resource($probe['process'])) {
            return;
        }
        $status = proc_get_status($probe['process']);
        if ($status['running']) {
            proc_terminate($probe['process']);
        }
        $this->closeProbe($probe);
    }
}
