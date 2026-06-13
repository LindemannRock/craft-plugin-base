<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration\fixtures\PluginWithIcon;

use craft\base\Plugin;

/**
 * Test fixture: a plugin whose directory contains an icon.svg, used to pin
 * {@see \lindemannrock\base\helpers\PluginHelper::getIconSvg()}.
 */
final class StubPluginWithIcon extends Plugin
{
}
