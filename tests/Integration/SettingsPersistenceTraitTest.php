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
use craft\db\Query;
use craft\base\Model;
use craft\helpers\Db;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\traits\SettingsPersistenceTrait;

/**
 * Pins the scoped-save contract for {@see SettingsPersistenceTrait}.
 *
 * @since 5.26.0
 */
final class SettingsPersistenceTraitTest extends IntegrationTestCase
{
    private const TABLE = '{{%base_settings_persistence_trait_test}}';

    protected function setUp(): void
    {
        parent::setUp();
        $this->dropTable();
        Craft::$app->getDb()->createCommand()->createTable(self::TABLE, [
            'id' => 'pk',
            'textA' => 'varchar(255) NOT NULL',
            'textB' => 'varchar(255) NOT NULL',
            'countA' => 'integer NOT NULL DEFAULT 0',
            'enabled' => 'boolean NOT NULL DEFAULT 0',
            'payload' => 'text NULL',
            'excludedField' => 'varchar(255) NOT NULL',
            'overriddenField' => 'varchar(255) NOT NULL',
            'dateCreated' => 'datetime NOT NULL',
            'dateUpdated' => 'datetime NOT NULL',
            'uid' => 'varchar(36) NOT NULL',
        ])->execute();

        $now = Db::prepareDateForDb(new \DateTimeImmutable('2026-06-05 00:00:00'));
        Craft::$app->getDb()->createCommand()->insert(self::TABLE, [
            'id' => 1,
            'textA' => 'old-a',
            'textB' => 'old-b',
            'countA' => 1,
            'enabled' => false,
            'payload' => json_encode(['old' => true]),
            'excludedField' => 'old-excluded',
            'overriddenField' => 'old-overridden',
            'dateCreated' => $now,
            'dateUpdated' => $now,
            'uid' => 'test-uid',
        ])->execute();
    }

    protected function tearDown(): void
    {
        $this->dropTable();
        parent::tearDown();
    }

    public function testScopedSavePersistsOnlyProvidedAttributes(): void
    {
        $settings = new SettingsPersistenceTraitTestModel();
        $settings->textA = 'new-a';
        $settings->textB = 'new-b';
        $settings->countA = 42;
        $settings->enabled = true;
        $settings->payload = ['new' => true];
        $settings->excludedField = 'new-excluded';
        $settings->overriddenField = 'new-overridden';

        self::assertTrue($settings->saveToDatabase([
            'textA',
            'countA',
            'payload',
            'excludedField',
            'overriddenField',
        ]));

        $row = $this->row();
        self::assertSame('new-a', $row['textA']);
        self::assertSame('old-b', $row['textB']);
        self::assertSame(42, (int)$row['countA']);
        self::assertFalse((bool)$row['enabled']);
        self::assertSame(['new' => true], json_decode((string)$row['payload'], true));
        self::assertSame('old-excluded', $row['excludedField']);
        self::assertSame('old-overridden', $row['overriddenField']);
    }

    public function testScopedSaveDoesNotValidateOrPersistOutOfScopeInvalidAttributes(): void
    {
        $settings = new SettingsPersistenceTraitTestModel();
        $settings->textA = 'new-a';
        $settings->textB = str_repeat('x', 300);

        self::assertTrue($settings->saveToDatabase(['textA']));

        $row = $this->row();
        self::assertSame('new-a', $row['textA']);
        self::assertSame('old-b', $row['textB']);
    }

    public function testFullSaveKeepsExistingPersistenceBehavior(): void
    {
        $settings = new SettingsPersistenceTraitTestModel();
        $settings->textA = 'new-a';
        $settings->textB = 'new-b';
        $settings->countA = 42;
        $settings->enabled = true;
        $settings->payload = ['new' => true];
        $settings->excludedField = 'new-excluded';
        $settings->overriddenField = 'new-overridden';

        self::assertTrue($settings->saveToDatabase());

        $row = $this->row();
        self::assertSame('new-a', $row['textA']);
        self::assertSame('new-b', $row['textB']);
        self::assertSame(42, (int)$row['countA']);
        self::assertTrue((bool)$row['enabled']);
        self::assertSame(['new' => true], json_decode((string)$row['payload'], true));
        self::assertSame('old-excluded', $row['excludedField']);
        self::assertSame('old-overridden', $row['overriddenField']);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(): array
    {
        return (array)(new Query())
            ->from(self::TABLE)
            ->where(['id' => 1])
            ->one();
    }

    private function dropTable(): void
    {
        Craft::$app->getDb()->createCommand()->dropTableIfExists(self::TABLE)->execute();
    }
}

final class SettingsPersistenceTraitTestModel extends Model
{
    use SettingsPersistenceTrait;

    public string $textA = 'default-a';
    public string $textB = 'default-b';
    public int $countA = 0;
    public bool $enabled = false;
    /** @var array<string, bool> */
    public array $payload = [];
    public string $excludedField = 'default-excluded';
    public string $overriddenField = 'default-overridden';

    protected static function tableName(): string
    {
        return 'base_settings_persistence_trait_test';
    }

    protected static function jsonFields(): array
    {
        return ['payload'];
    }

    protected static function excludeFromSave(): array
    {
        return ['excludedField'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function defineRules(): array
    {
        return [
            [['textA', 'textB', 'excludedField', 'overriddenField'], 'string', 'max' => 255],
            [['countA'], 'integer'],
            [['enabled'], 'boolean'],
            [['payload'], 'safe'],
        ];
    }

    public function isOverriddenByConfig(string $attribute): bool
    {
        return $attribute === 'overriddenField';
    }
}
