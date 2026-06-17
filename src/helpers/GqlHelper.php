<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\helpers\Gql;
use craft\models\GqlSchema;

/**
 * Helpers for plugin-owned GraphQL query and type plumbing.
 *
 * @since 5.27.0
 */
class GqlHelper extends Gql
{
    /**
     * Return whether the active schema can read a plugin-owned GraphQL scope.
     *
     * Pass the scope component without the action suffix. For a schema scope
     * stored as `redirectManager.all:read`, pass `redirectManager.all`.
     *
     * @param string $component
     * @param GqlSchema|null $schema
     * @return bool
     */
    public static function canQuery(string $component, ?GqlSchema $schema = null): bool
    {
        return self::canSchema($component, 'read', $schema);
    }

    /**
     * Resolve a GraphQL `site` / `siteId` argument pair into a concrete site ID.
     *
     * The site handle wins when both arguments are present, matching Craft's
     * common GraphQL argument style. Invalid handles or IDs return null rather
     * than silently falling back to another site.
     *
     * @param array<string, mixed> $arguments
     * @param int|null $fallbackSiteId
     * @return int|null
     */
    public static function resolveSiteId(array $arguments, ?int $fallbackSiteId = null): ?int
    {
        $siteHandle = $arguments['site'] ?? null;
        if (is_string($siteHandle) && trim($siteHandle) !== '') {
            return Craft::$app->getSites()->getSiteByHandle(trim($siteHandle))?->id;
        }

        $siteId = $arguments['siteId'] ?? null;
        if (is_numeric($siteId) && (int)$siteId > 0) {
            return Craft::$app->getSites()->getSiteById((int)$siteId)?->id;
        }

        return $fallbackSiteId;
    }

    /**
     * Resolve a site ID into its handle for virtual GraphQL `site` fields.
     *
     * @param int|null $siteId
     * @return string|null
     */
    public static function siteHandle(?int $siteId): ?string
    {
        if ($siteId === null || $siteId <= 0) {
            return null;
        }

        return Craft::$app->getSites()->getSiteById($siteId)?->handle;
    }

    /**
     * Convert empty strings to null while preserving other scalar falsey values.
     *
     * Useful in array-backed GraphQL type resolvers where empty strings should
     * serialize as `null`, but `0` and `false` are meaningful values.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function nullIfEmptyString(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
}
