<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\helpers;

use DirectoryIterator;

/**
 * Helpers for bounded plugin cache cleanup.
 *
 * @since 5.31.0
 */
final class CacheHelper
{
    /**
     * Clear cache entries tracked in a plugin-owned Redis set.
     *
     * The set is scanned with Redis SSCAN so large cache sets are processed in
     * batches instead of being materialized into PHP memory at once.
     */
    public static function clearTrackedRedisKeys(string $pluginHandle, string $keyType, int $batchSize = 500): int
    {
        $cache = PluginHelper::getRedisCacheOrLog($pluginHandle);
        if ($cache === null) {
            return 0;
        }

        $redis = $cache->redis;
        $setKey = PluginHelper::getCacheKeySet($pluginHandle, $keyType);
        $batchSize = max(1, $batchSize);
        $cursor = '0';
        $deleted = 0;

        do {
            $result = $redis->executeCommand('SSCAN', [$setKey, $cursor, 'COUNT', $batchSize]);
            if (!is_array($result) || count($result) < 2) {
                break;
            }

            $cursor = (string)($result[0] ?? '0');
            $keys = $result[1] ?? [];
            if (!is_array($keys)) {
                continue;
            }

            foreach ($keys as $key) {
                if (!is_string($key) || $key === '') {
                    continue;
                }

                if ($cache->delete($key)) {
                    $deleted++;
                }
            }
        } while ($cursor !== '0');

        $redis->executeCommand('DEL', [$setKey]);

        return $deleted;
    }

    /**
     * Delete local cache files from a directory.
     */
    public static function clearCacheFiles(string $directory, string $suffix = '.cache'): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $deleted = 0;
        foreach (new DirectoryIterator($directory) as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), $suffix)) {
                continue;
            }

            if (@unlink($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Count local cache files in a directory.
     */
    public static function countCacheFiles(string $directory, string $suffix = '.cache'): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $count = 0;
        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $suffix)) {
                $count++;
            }
        }

        return $count;
    }
}
