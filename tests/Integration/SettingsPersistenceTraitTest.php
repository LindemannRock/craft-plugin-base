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
use craft\db\Connection;
use craft\db\Query;
use craft\base\Model;
use craft\helpers\Db;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\traits\SettingsPersistenceTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\db\Exception as DbException;

/**
 * Pins the scoped-save contract for {@see SettingsPersistenceTrait}.
 *
 * @since 5.26.0
 */
final class SettingsPersistenceTraitTest extends IntegrationTestCase
{
    private const TABLE = '{{%base_settings_persistence_trait_test}}';
    private const SENTINELS = [
        'exception-message' => '__BASE_B1_EXCEPTION_MESSAGE__',
        'interpolated-sql' => '__BASE_B1_INTERPOLATED_SQL__',
        'bound-value' => '__BASE_B1_BOUND_VALUE__',
        'error-detail' => '__BASE_B1_ERROR_INFO_DETAIL__',
        'previous-message' => '__BASE_B1_PREVIOUS_EXCEPTION__',
        'plaintext-credential' => '__BASE_B1_PASSWORD_PLAINTEXT__',
        'credential-hash' => '__BASE_B1_HASH_8F72D7E8__',
        'ciphertext' => '__BASE_B1_CIPHERTEXT_Q2lwaGVy__',
        'json-settings' => '__BASE_B1_JSON_SETTINGS_VALUE__',
    ];

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

    public function testLoadFailureUsesFallbackWithoutLoggingPersistedValues(): void
    {
        $settings = new SettingsPersistenceTraitTestModel();
        $exception = $this->sentinelException('HY000', 1045);
        $logOffset = count(\Yii::getLogger()->messages);

        $loaded = $this->withFailingDatabase(
            $exception,
            static fn() => SettingsPersistenceTraitTestModel::loadFromDatabase($settings),
        );

        self::assertSame($settings, $loaded);
        self::assertSame('default-a', $loaded->textA);

        $logged = $this->logTextSince($logOffset);
        $this->assertNoSentinelsLogged($logged);
        self::assertStringContainsString('load', $logged);
        self::assertStringContainsString(SettingsPersistenceTraitTestModel::class, $logged);
        self::assertStringContainsString(DbException::class, $logged);
        self::assertStringContainsString('HY000', $logged);
        self::assertStringContainsString('1045', $logged);
    }

    public function testSaveFailureReturnsFalseWithoutLoggingPersistedValues(): void
    {
        $settings = new SettingsPersistenceTraitTestModel();
        $settings->textA = 'unsaved-a';
        $exception = $this->sentinelException('23505', '7');
        $logOffset = count(\Yii::getLogger()->messages);

        $saved = $this->withFailingDatabase(
            $exception,
            static fn() => $settings->saveToDatabase(),
        );

        self::assertFalse($saved);
        self::assertSame('old-a', $this->row()['textA']);
        self::assertSame([], $settings->getErrors());

        $logged = $this->logTextSince($logOffset);
        $this->assertNoSentinelsLogged($logged);
        self::assertStringContainsString('save', $logged);
        self::assertStringContainsString(SettingsPersistenceTraitTestModel::class, $logged);
        self::assertStringContainsString(DbException::class, $logged);
        self::assertStringContainsString('23505', $logged);
        self::assertStringContainsString('7', $logged);
        self::assertStringNotContainsString('success', strtolower($logged));
    }

    /**
     * @param array<int, mixed>|string $errorInfo
     */
    #[DataProvider('databaseFailureMetadataProvider')]
    public function testDatabaseFailureMetadataIsStrictlyAllowlisted(
        array|string $errorInfo,
        ?string $expectedSqlState,
        ?string $expectedDriverCode,
    ): void {
        $exception = $this->sentinelException('HY000', 1045);
        $exception->errorInfo = $errorInfo;
        $logOffset = count(\Yii::getLogger()->messages);

        $loaded = $this->withFailingDatabase(
            $exception,
            static fn() => SettingsPersistenceTraitTestModel::loadFromDatabase(),
        );

        self::assertSame('default-a', $loaded->textA);

        $logged = $this->logTextSince($logOffset);
        $this->assertNoSentinelsLogged($logged);
        self::assertStringContainsString('operation=load', $logged);
        self::assertStringContainsString('resource=' . SettingsPersistenceTraitTestModel::class, $logged);
        self::assertStringContainsString('table=base_settings_persistence_trait_test', $logged);
        self::assertStringContainsString('exception=' . DbException::class, $logged);

        if ($expectedSqlState === null) {
            self::assertStringNotContainsString('sqlState=', $logged);
        } else {
            self::assertStringContainsString('sqlState=' . $expectedSqlState, $logged);
        }

        if ($expectedDriverCode === null) {
            self::assertStringNotContainsString('driverCode=', $logged);
        } else {
            self::assertStringContainsString('driverCode=' . $expectedDriverCode, $logged);
        }
    }

    public function testNonDatabaseFailureLogsOnlySafeOperationalMetadata(): void
    {
        $exception = new \RuntimeException(
            self::SENTINELS['exception-message'],
            0,
            new \RuntimeException(self::SENTINELS['previous-message']),
        );
        $logOffset = count(\Yii::getLogger()->messages);

        $saved = $this->withFailingDatabase(
            $exception,
            static fn() => (new SettingsPersistenceTraitTestModel())->saveToDatabase(),
        );

        self::assertFalse($saved);

        $logged = $this->logTextSince($logOffset);
        $this->assertNoSentinelsLogged($logged);
        self::assertStringContainsString('operation=save', $logged);
        self::assertStringContainsString('exception=' . \RuntimeException::class, $logged);
        self::assertStringNotContainsString('sqlState=', $logged);
        self::assertStringNotContainsString('driverCode=', $logged);
    }

    public function testSuccessfulLoadAndSaveControlsRemainOperational(): void
    {
        $logOffset = count(\Yii::getLogger()->messages);

        $settings = SettingsPersistenceTraitTestModel::loadFromDatabase();

        self::assertSame('old-a', $settings->textA);
        self::assertSame('old-b', $settings->textB);
        self::assertSame(1, $settings->countA);
        self::assertFalse($settings->enabled);
        self::assertSame(['old' => true], $settings->payload);

        $settings->textA = 'saved-control';
        self::assertTrue($settings->saveToDatabase(['textA']));
        self::assertSame('saved-control', $this->row()['textA']);

        $logged = $this->logTextSince($logOffset);
        self::assertStringNotContainsString('Settings database operation failed', $logged);
    }

    /**
     * @return iterable<string, array{array<int, mixed>|string, ?string, ?string}>
     */
    public static function databaseFailureMetadataProvider(): iterable
    {
        yield 'MySQL-shaped safe SQLSTATE and integer driver code' => [
            ['HY000', 1045, self::SENTINELS['error-detail']],
            'HY000',
            '1045',
        ];
        yield 'PostgreSQL-shaped safe SQLSTATE and numeric-string driver code' => [
            ['23505', '7', self::SENTINELS['error-detail']],
            '23505',
            '7',
        ];
        yield 'missing errorInfo' => [
            [],
            null,
            null,
        ];
        yield 'malformed errorInfo container' => [
            '__BASE_B1_MALFORMED_ERROR_INFO__',
            null,
            null,
        ];
        yield 'unsafe SQLSTATE and nonnumeric driver code' => [
            [
                '__BASE_B1_UNSAFE_SQLSTATE__',
                '__BASE_B1_NONNUMERIC_DRIVER__',
                self::SENTINELS['error-detail'],
            ],
            null,
            null,
        ];
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

    private function sentinelException(string $sqlState, int|string $driverCode): DbException
    {
        $message = implode(' ', [
            self::SENTINELS['exception-message'],
            'SQL=' . self::SENTINELS['interpolated-sql'],
            'bound=' . self::SENTINELS['bound-value'],
            'password=' . self::SENTINELS['plaintext-credential'],
            'hash=' . self::SENTINELS['credential-hash'],
            'ciphertext=' . self::SENTINELS['ciphertext'],
            'settings={"value":"' . self::SENTINELS['json-settings'] . '"}',
        ]);

        return new DbException(
            $message,
            [$sqlState, $driverCode, self::SENTINELS['error-detail']],
            $sqlState,
            new \RuntimeException(self::SENTINELS['previous-message']),
        );
    }

    private function withFailingDatabase(\Exception $exception, callable $callback): mixed
    {
        $database = Craft::$app->getDb();
        Craft::$app->set('db', new FailingSettingsConnection($database, $exception));

        try {
            return $callback();
        } finally {
            Craft::$app->set('db', $database);
        }
    }

    private function logTextSince(int $offset): string
    {
        return serialize(array_slice(\Yii::getLogger()->messages, $offset));
    }

    private function assertNoSentinelsLogged(string $logged): void
    {
        foreach (self::SENTINELS as $name => $sentinel) {
            self::assertStringNotContainsString($sentinel, $logged, "Logged $name sentinel.");
        }

        self::assertStringNotContainsString('__BASE_B1_MALFORMED_ERROR_INFO__', $logged);
        self::assertStringNotContainsString('__BASE_B1_UNSAFE_SQLSTATE__', $logged);
        self::assertStringNotContainsString('__BASE_B1_NONNUMERIC_DRIVER__', $logged);
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

final class FailingSettingsConnection extends Connection
{
    public function __construct(
        private readonly Connection $database,
        private readonly \Exception $exception,
    ) {
    }

    public function getSchema()
    {
        return $this->database->getSchema();
    }

    public function createCommand($sql = null, $params = [])
    {
        throw $this->exception;
    }
}
