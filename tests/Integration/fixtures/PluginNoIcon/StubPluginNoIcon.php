<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration\fixtures\PluginNoIcon;

use craft\base\Plugin;

/**
 * Test fixture: a plugin whose directory contains no icon.svg, used to pin the
 * null branch of {@see \lindemannrock\base\helpers\PluginHelper::getIconSvg()}.
 */
final class StubPluginNoIcon extends Plugin
{
}
