<?php
/**
 * Test-compatible stand-in used when the optional cascade package is absent.
 *
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace craft\cachecascade;

use yii\caching\ArrayCache;

if (!class_exists(CascadeCache::class)) {
    /**
     * @since 5.38.0
     */
    class CascadeCache extends ArrayCache
    {
        public function hiddenPrimary(): never
        {
            throw new \LogicException('The hidden primary must not be inspected.');
        }
    }
}
