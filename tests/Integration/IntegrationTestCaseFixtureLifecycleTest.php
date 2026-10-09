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
use craft\base\Plugin;
use craft\console\Application;
use craft\console\User as UserSession;
use craft\elements\User;
use craft\helpers\FileHelper;
use craft\services\Elements;
use craft\services\Plugins;
use craft\services\UserPermissions;
use lindemannrock\base\testing\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since 5.26.0
 */
#[CoversClass(IntegrationTestCase::class)]
final class IntegrationTestCaseFixtureLifecycleTest extends IntegrationTestCase
{
    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function teardownFailureProvider(): iterable
    {
        yield 'successful cleanup restores a guest' => ['none', false];
        yield 'external exception restores a guest' => ['external', false];
        yield 'external exception restores an identity' => ['external', true];
        yield 'external error is not swallowed' => ['external-error', false];
        yield 'identity restoration failure allows later cleanup' => ['identity', true];
        yield 'user cleanup failure allows later cleanup' => ['users', false];
        yield 'element cleanup failure allows later cleanup' => ['elements', false];
        yield 'component restoration failure still resets markers' => ['components', false];
        yield 'multiple failures retain the first error' => ['external-and-elements', false];
    }

    #[DataProvider('teardownFailureProvider')]
    public function testTeardownAttemptsRemainingPhasesAndPreservesTheFirstFailure(string $scenario, bool $signedIn): void
    {
        $originalApp = Craft::$app;
        $originalIdentity = $signedIn ? $this->createStub(User::class) : null;
        $testIdentity = $this->createStub(User::class);
        $identity = $originalIdentity;
        $events = [];
        $failure = $scenario === 'external-error' ? new \Error('External cleanup failed') : new \RuntimeException('Cleanup failed');
        $laterFailure = new \RuntimeException('Element cleanup also failed');
        $plugin = new Plugin('lifecycle-probe');
        $originalComponent = new \stdClass();
        $firstStub = new \stdClass();
        $lastStub = new \stdClass();
        $plugin->set('service', $originalComponent);

        $state = new class() {
            public bool $inTeardown = false;
        };
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getPlugin')->with('lifecycle-probe')->willReturnCallback(
            static function() use ($state, $scenario, $failure, $plugin): Plugin {
                if ($state->inTeardown && $scenario === 'components') {
                    throw $failure;
                }
                return $plugin;
            },
        );
        $userSession = $this->createMock(UserSession::class);
        $userSession->method('getIdentity')->willReturnCallback(static function() use (&$identity): ?User {
            return $identity;
        });
        $userSession->method('setIdentity')->willReturnCallback(
            static function(?User $next) use (&$identity, &$events, $originalIdentity, $scenario, $failure): void {
                if ($next === $originalIdentity) {
                    $events[] = 'identity';
                    if ($scenario === 'identity') {
                        throw $failure;
                    }
                }
                $identity = $next;
            },
        );

        // Faults are injected at Craft service boundaries, without creating
        // database rows or changing the live application's components.
        $element = $this->createStub(User::class);
        $elements = $this->createMock(Elements::class);
        $elements->method('saveElement')->willReturnCallback(static function(User $saved): bool {
            $saved->id = 42;
            return true;
        });
        $elements->method('getElementById')->with(42, null, null, ['status' => null])->willReturn($element);
        $elements->method('deleteElement')->with($element, true)->willReturnCallback(
            static function() use (&$events, $scenario, $failure, $laterFailure): bool {
                $events[] = 'elements';
                if ($scenario === 'elements') {
                    throw $failure;
                }
                if ($scenario === 'external-and-elements') {
                    throw $laterFailure;
                }
                return true;
            },
        );
        $permissions = $this->createMock(UserPermissions::class);
        if ($scenario === 'users') {
            $permissions->method('saveUserPermissions')->with(42, [])->willReturnCallback(
                static function() use (&$events, $failure): never {
                    $events[] = 'users';
                    throw $failure;
                },
            );
        }

        $app = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPlugins', 'getUser', 'getElements', 'getUserPermissions'])
            ->getMock();
        $app->method('getPlugins')->willReturn($plugins);
        $app->method('getUser')->willReturn($userSession);
        $app->method('getElements')->willReturn($elements);
        $app->method('getUserPermissions')->willReturn($permissions);
        $app->set('config', $originalApp->getConfig());

        $case = new class('fixture-teardown') extends IntegrationTestCase {
            public ?\Closure $cleanup = null;

            public function seed(User $identity, User $element, bool $trackUser): string
            {
                $this->actingAs($identity);
                $this->saveTestElement($element);
                if ($trackUser) {
                    $this->trackUserForCleanup((int) $element->id);
                }
                return $this->createTrackedTempDirectory('base-teardown-');
            }

            public function swap(string $id, object $stub): void
            {
                $this->swapPluginComponent('lifecycle-probe', $id, $stub);
            }

            public function marker(): string
            {
                return $this->nextTestMarker('base-teardown-', 'marker');
            }

            public function runBaseTearDown(): void
            {
                parent::tearDown();
            }

            protected function cleanupExternalState(): void
            {
                ($this->cleanup)();
            }
        };

        $dir = null;
        try {
            Craft::$app = $app;
            $dir = $case->seed($testIdentity, $element, $scenario === 'users');
            file_put_contents($dir . '/fixture.txt', 'fixture');
            $case->swap('service', $firstStub);
            $case->swap('service', $lastStub);
            $case->swap('temporary', new \stdClass());
            $case->cleanup = static function() use (&$events, $plugin, $lastStub, $scenario, $failure): void {
                $events[] = 'external';
                self::assertSame($lastStub, $plugin->get('service'), 'Cleanup must still see the installed stub.');
                if (str_starts_with($scenario, 'external')) {
                    throw $failure;
                }
            };

            $caught = null;
            $state->inTeardown = true;
            try {
                $case->runBaseTearDown();
            } catch (\Throwable $e) {
                $caught = $e;
            }

            self::assertSame($scenario === 'none' ? null : $failure, $caught);
            self::assertSame($scenario === 'users' ? ['external', 'identity', 'users', 'elements'] : ['external', 'identity', 'elements'], $events);
            self::assertSame($scenario === 'identity' ? $testIdentity : $originalIdentity, $identity);
            self::assertDirectoryDoesNotExist($dir);
            self::assertSame($scenario === 'components' ? $lastStub : $originalComponent, $plugin->get('service'));
            self::assertSame($scenario === 'components', $plugin->has('temporary'));
            self::assertMatchesRegularExpression('/^base-teardown-marker_1_[a-f0-9]{8}$/', $case->marker());
            if ($scenario === 'none') {
                $case->cleanup = static function(): void {
                };
                $case->runBaseTearDown();
                self::assertSame($originalComponent, $plugin->get('service'));
                self::assertSame($originalIdentity, $identity);
                self::assertFalse($plugin->has('temporary'));
            }
        } finally {
            Craft::$app = $originalApp;
            // Cleanup is independent of the behavior under test, including
            // when an assertion fails against an unfixed implementation.
            if ($dir !== null && is_dir($dir)) {
                FileHelper::removeDirectory($dir);
            }
        }
    }

    public function testNextTestMarkerUsesPrefixKindCounterAndRandomSuffix(): void
    {
        $first = $this->nextTestMarker('__base_test_', 'row');
        $second = $this->nextTestMarker('__base_test_', 'row');

        self::assertMatchesRegularExpression('/^__base_test_row_1_[a-f0-9]{8}$/', $first);
        self::assertMatchesRegularExpression('/^__base_test_row_2_[a-f0-9]{8}$/', $second);
        self::assertNotSame($first, $second);
    }

    public function testTrackedTempDirectoryIsRemovedDuringTearDown(): void
    {
        $case = new class('fixture-cleanup') extends IntegrationTestCase {
            public string $dir;

            public function seedTempDir(): string
            {
                $this->dir = $this->createTrackedTempDirectory('__base_lifecycle_');
                file_put_contents($this->dir . DIRECTORY_SEPARATOR . 'fixture.txt', 'fixture');

                return $this->dir;
            }

            public function runBaseTearDown(): void
            {
                $this->tearDown();
            }
        };

        $dir = $case->seedTempDir();
        self::assertDirectoryExists($dir);
        self::assertFileExists($dir . DIRECTORY_SEPARATOR . 'fixture.txt');

        $case->runBaseTearDown();

        self::assertDirectoryDoesNotExist($dir);
    }

    public function testTestUserPermissionsAndIdentityAreRestoredDuringTearDown(): void
    {
        $originalIdentity = Craft::$app->getUser()->getIdentity();

        $case = new class('user-auth') extends IntegrationTestCase {
            public function seedUser(): User
            {
                return $this->createTestUser('__base_auth_');
            }

            /**
             * @param list<string> $permissions
             */
            public function grant(User $user, array $permissions): void
            {
                $this->grantPermissions($user, $permissions);
            }

            public function become(User $user): void
            {
                $this->actingAs($user);
            }

            public function runBaseTearDown(): void
            {
                $this->tearDown();
            }
        };

        $user = $case->seedUser();
        $case->grant($user, ['accessCp']);
        $case->become($user);

        self::assertFalse(Craft::$app->getUser()->getIsAdmin());
        self::assertTrue(Craft::$app->getUser()->checkPermission('accessCp'));
        self::assertFalse(Craft::$app->getUser()->checkPermission('administrateUsers'));

        $userId = (int) $user->id;
        $case->runBaseTearDown();

        $restoredIdentity = Craft::$app->getUser()->getIdentity();
        $originalIdentityId = $originalIdentity instanceof User ? $originalIdentity->id : null;
        $restoredIdentityId = $restoredIdentity instanceof User ? $restoredIdentity->id : null;

        self::assertSame($originalIdentityId, $restoredIdentityId);
        self::assertNull(User::find()->id($userId)->status(null)->one());
    }
}
