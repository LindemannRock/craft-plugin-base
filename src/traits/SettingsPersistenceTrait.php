<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\base\traits;

use Craft;
use craft\db\Query;
use craft\helpers\Db;

/**
 * Settings Persistence Trait
 *
 * Provides database persistence methods for Settings models.
 * Handles loading from and saving to a plugin's settings table with automatic type conversion.
 *
 * Requirements:
 * - Using class must implement tableName() method
 * - Database table must exist with id=1 row (created by Install migration)
 * - Table should have dateCreated, dateUpdated, uid columns
 *
 * Usage:
 * ```php
 * class Settings extends Model
 * {
 *     use SettingsPersistenceTrait;
 *
 *     protected static function tableName(): string { return 'myplugin_settings'; }
 *     protected static function booleanFields(): array { return ['enabled']; }
 *     protected static function integerFields(): array { return ['limit']; }
 *     protected static function jsonFields(): array { return ['patterns']; }
 * }
 *
 * // Load
 * $settings = Settings::loadFromDatabase();
 *
 * // Save
 * $settings->enabled = true;
 * $settings->saveToDatabase();
 * ```
 *
 * @author LindemannRock
 * @since 5.0.0
 */
trait SettingsPersistenceTrait
{
    /**
     * Get the database table name (without prefix)
     *
     * Example: 'redirectmanager_settings' (not '{{%redirectmanager_settings}}')
     *
     * @return string
     */
    abstract protected static function tableName(): string;

    /**
     * Get boolean field names for type conversion
     *
     * These fields will be cast to bool when loading from database.
     * Database stores booleans as 0/1, this converts to true/false.
     *
     * @return array
     */
    protected static function booleanFields(): array
    {
        return [];
    }

    /**
     * Get integer field names for type conversion
     *
     * These fields will be cast to int when loading from database.
     * Ensures fields are actual integers, not strings.
     *
     * @return array
     */
    protected static function integerFields(): array
    {
        return [];
    }

    /**
     * Get JSON array field names for encoding/decoding
     *
     * These fields will be JSON encoded when saving and decoded when loading.
     * Use for complex settings like arrays of patterns, custom configurations, etc.
     *
     * @return array
     */
    protected static function jsonFields(): array
    {
        return [];
    }

    /**
     * Get fields that should NOT be saved to database
     *
     * Useful for fields that come from .env or config file only.
     * These fields will be excluded from saveToDatabase().
     *
     * @return array
     */
    protected static function excludeFromSave(): array
    {
        return [];
    }

    /**
     * Load settings from database
     *
     * Loads the settings row from the plugin's settings table (always id=1).
     * Gracefully handles missing tables during installation.
     *
     * @param static|null $settings Optional existing settings instance to populate
     * @return static Settings instance with values from database (or defaults)
     */
    public static function loadFromDatabase(?self $settings = null): self
    {
        if ($settings === null) {
            $settings = new static();
        }

        $db = Craft::$app->getDb();
        $tableName = '{{%' . static::tableName() . '}}';

        // Check if table exists (prevents errors during installation)
        try {
            $tableSchema = $db->getSchema()->getTableSchema($tableName);
            if ($tableSchema === null) {
                // Table doesn't exist yet, return default settings
                return $settings;
            }
        } catch (\Exception $e) {
            return $settings;
        }

        // Load from database
        try {
            $row = (new Query())
                ->from($tableName)
                ->where(['id' => 1])
                ->one();
        } catch (\Exception $e) {
            Craft::error('Failed to load settings from database: ' . $e->getMessage(), __METHOD__);
            return $settings;
        }

        if ($row) {
            // Remove system fields that aren't model attributes
            unset($row['id'], $row['dateCreated'], $row['dateUpdated'], $row['uid']);

            // Convert boolean fields (DB stores as 0/1)
            foreach (static::booleanFields() as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (bool) $row[$field];
                }
            }

            // Convert integer fields (ensure actual int, not string)
            foreach (static::integerFields() as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (int) $row[$field];
                }
            }

            // Decode JSON fields
            foreach (static::jsonFields() as $field) {
                if (isset($row[$field])) {
                    $row[$field] = !empty($row[$field])
                        ? json_decode($row[$field], true)
                        : [];
                }
            }

            // Apply database values to settings
            $settings->setAttributes($row, false);
        }

        return $settings;
    }

    /**
     * Save settings to database
     *
     * Saves the current settings to the plugin's settings table (always updates id=1).
     * Validates before saving. Excludes config-overridden fields.
     *
     * @return bool True on success, false on failure
     */
    public function saveToDatabase(): bool
    {
        // Validate settings first
        if (!$this->validate()) {
            Craft::error('Settings validation failed: ' . json_encode($this->getErrors()), __METHOD__);
            return false;
        }

        $db = Craft::$app->getDb();
        $tableName = '{{%' . static::tableName() . '}}';
        $attributes = $this->getAttributes();

        // Remove excluded fields (e.g., env-only fields)
        foreach (static::excludeFromSave() as $field) {
            unset($attributes[$field]);
        }

        // Remove fields overridden by config file
        if (method_exists($this, 'isOverriddenByConfig')) {
            foreach (array_keys($attributes) as $attribute) {
                if ($this->isOverriddenByConfig($attribute)) {
                    unset($attributes[$attribute]);
                }
            }
        }

        // Encode JSON fields
        foreach (static::jsonFields() as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = json_encode($attributes[$field]);
            }
        }

        // Update timestamp
        $attributes['dateUpdated'] = Db::prepareDateForDb(new \DateTime());

        // Always UPDATE (never INSERT) - Install migration creates id=1 row
        try {
            $db->createCommand()
                ->update($tableName, $attributes, ['id' => 1])
                ->execute();

            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save settings to database: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
