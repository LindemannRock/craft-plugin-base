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
use craft\cachecascade\CascadeCache;
use craft\web\View;
use lindemannrock\base\cache\CacheBackendStatus;
use lindemannrock\base\cache\DisposableCacheStorageDecision;
use lindemannrock\base\cache\DisposableCacheStoragePresentation;
use lindemannrock\base\cache\DisposableCacheStoragePresenter;
use lindemannrock\base\cache\DisposableCacheStorageResolver;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use ReflectionClass;
use ReflectionProperty;
use yii\base\Model;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\caching\DbCache;
use yii\caching\FileCache;
use yii\redis\Cache as RedisCache;

require_once dirname(__DIR__) . '/Fixtures/CascadeCache.php';

/**
 * @since 5.38.0
 */
final class DisposableCacheStorageTest extends IntegrationTestCase
{
    private CacheInterface $originalCache;
    private array $originalApplicationCacheDiagnostics;

    protected function setUp(): void
    {
        parent::setUp();
        $cache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $cache);
        $this->originalCache = $cache;
        $this->originalApplicationCacheDiagnostics = $this->applicationCacheDiagnosticsProperty()->getValue();
    }

    protected function tearDown(): void
    {
        Craft::$app->set('cache', $this->originalCache);
        $this->applicationCacheDiagnosticsProperty()->setValue(null, $this->originalApplicationCacheDiagnostics);
        parent::tearDown();
    }

    public function testDurableFileStorageNeverResolvesTheApplicationCache(): void
    {
        $lookups = 0;
        Craft::$app->set('cache', static function() use (&$lookups): never {
            $lookups++;
            throw new \LogicException('The application cache must not be resolved.');
        });

        $decision = $this->resolve('file', false);

        self::assertSame(0, $lookups);
        self::assertTrue($decision->usesFileCache());
        self::assertNull($decision->applicationCache);
        self::assertSame(DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED, $decision->persistenceConfidence);
        self::assertFalse($decision->fileStorageBypassed);
        self::assertTrue($decision->filePathEligible);
        self::assertSame(DisposableCacheStorageDecision::REASON_DURABLE_PLUGIN_FILE, $decision->reasonCode);
    }

    public function testEphemeralFileStorageAcceptsEverySuitableBackendBestEffort(): void
    {
        $redis = (new ReflectionClass(RedisCache::class))->newInstanceWithoutConstructor();
        self::assertInstanceOf(RedisCache::class, $redis);

        $cases = [
            [new CascadeCache(), CacheBackendStatus::BACKEND_MANAGED, DisposableCacheStorageDecision::PERSISTENCE_UNKNOWN],
            [$redis, CacheBackendStatus::BACKEND_REDIS, DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED],
            [new DbCache(), CacheBackendStatus::BACKEND_DATABASE, DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED],
            [new DisposableUnknownCache(), CacheBackendStatus::BACKEND_UNKNOWN, DisposableCacheStorageDecision::PERSISTENCE_UNKNOWN],
        ];

        foreach ($cases as [$cache, $backend, $persistence]) {
            self::assertInstanceOf(CacheInterface::class, $cache);
            Craft::$app->set('cache', $cache);
            $decision = $this->resolve('file', true);

            self::assertTrue($decision->usesApplicationCache());
            self::assertSame($cache, $decision->applicationCache);
            self::assertSame($backend, $decision->backendStatus->backend);
            self::assertSame($persistence, $decision->persistenceConfidence);
            self::assertTrue($decision->fileStorageBypassed);
            self::assertFalse($decision->filePathEligible);
            self::assertSame(
                DisposableCacheStorageDecision::REASON_EPHEMERAL_FILE_APPLICATION_CACHE,
                $decision->reasonCode,
            );
        }
    }

    public function testEphemeralFileStorageRejectsUnsuitableAndUnavailableBackends(): void
    {
        foreach ([new FileCache(), new ArrayCache()] as $cache) {
            Craft::$app->set('cache', $cache);
            $decision = $this->resolve('file', true);

            self::assertTrue($decision->isDisabled());
            self::assertSame($cache, $decision->applicationCache);
            self::assertSame(DisposableCacheStorageDecision::PERSISTENCE_UNSUITABLE, $decision->persistenceConfidence);
            self::assertTrue($decision->fileStorageBypassed);
            self::assertFalse($decision->filePathEligible);
            self::assertSame(
                DisposableCacheStorageDecision::REASON_EPHEMERAL_FILE_WITHOUT_APPLICATION_CACHE,
                $decision->reasonCode,
            );
        }

        Craft::$app->set('cache', static function(): never {
            throw new \RuntimeException('Injected application-cache failure.');
        });
        $unavailable = $this->resolve('file', true);
        self::assertTrue($unavailable->isDisabled());
        self::assertNull($unavailable->applicationCache);
        self::assertSame(CacheBackendStatus::BACKEND_UNAVAILABLE, $unavailable->backendStatus->backend);
        self::assertSame(
            DisposableCacheStorageDecision::REASON_EPHEMERAL_FILE_WITHOUT_APPLICATION_CACHE,
            $unavailable->reasonCode,
        );
    }

    public function testExplicitApplicationTokensUseSuitableBackendsAndCarryTheExactInstance(): void
    {
        foreach (['redis', 'craft'] as $token) {
            $cache = new DbCache();
            $lookups = 0;
            Craft::$app->set('cache', static function() use ($cache, &$lookups): CacheInterface {
                $lookups++;
                return $cache;
            });

            $decision = $this->resolve($token, true);

            self::assertSame(1, $lookups);
            self::assertTrue($decision->usesApplicationCache());
            self::assertSame($cache, $decision->applicationCache);
            self::assertSame(DisposableCacheStorageDecision::PERSISTENCE_CONFIRMED, $decision->persistenceConfidence);
            self::assertFalse($decision->fileStorageBypassed);
            self::assertSame(
                DisposableCacheStorageDecision::REASON_EXPLICIT_APPLICATION_CACHE,
                $decision->reasonCode,
            );
        }

        Craft::$app->set('cache', new FileCache());
        self::assertTrue($this->resolve('craft', false)->usesApplicationCache());
    }

    public function testExplicitApplicationTokensFailClosedForUnsuitableStorage(): void
    {
        Craft::$app->set('cache', new ArrayCache());
        $decision = $this->resolve('craft', false);

        self::assertTrue($decision->isDisabled());
        self::assertSame(DisposableCacheStorageDecision::PERSISTENCE_UNSUITABLE, $decision->persistenceConfidence);
        self::assertSame(
            DisposableCacheStorageDecision::REASON_APPLICATION_CACHE_UNSUITABLE,
            $decision->reasonCode,
        );
    }

    public function testUnsupportedTokensFailClosedWithoutResolvingStorage(): void
    {
        $lookups = 0;
        Craft::$app->set('cache', static function() use (&$lookups): never {
            $lookups++;
            throw new \LogicException('Unsupported tokens must not resolve storage.');
        });

        $decision = $this->resolve('unsupported', true);

        self::assertSame(0, $lookups);
        self::assertTrue($decision->isDisabled());
        self::assertSame(DisposableCacheStorageDecision::PERSISTENCE_UNSUITABLE, $decision->persistenceConfidence);
        self::assertFalse($decision->fileStorageBypassed);
        self::assertFalse($decision->filePathEligible);
        self::assertSame(
            DisposableCacheStorageDecision::REASON_UNKNOWN_CONFIGURED_TOKEN,
            $decision->reasonCode,
        );
    }

    public function testConsumerOwnedTokenPolicyIsHonored(): void
    {
        Craft::$app->set('cache', new DbCache());
        $resolver = new DisposableCacheStorageResolver();

        $file = $resolver->resolve('disk', __METHOD__, ['disk'], ['shared'], false);
        $application = $resolver->resolve('shared', __METHOD__, ['disk'], ['shared'], true);
        $unsupported = $resolver->resolve('craft', __METHOD__, ['disk'], ['shared'], true);

        self::assertTrue($file->usesFileCache());
        self::assertTrue($application->usesApplicationCache());
        self::assertTrue($unsupported->isDisabled());
    }

    public function testCascadeCacheIsClassifiedWithoutInspectingInternals(): void
    {
        $cache = new CascadeCache();
        Craft::$app->set('cache', $cache);

        $decision = $this->resolve('craft', true);

        self::assertSame(CacheBackendStatus::BACKEND_MANAGED, $decision->backendStatus->backend);
        self::assertSame($cache, $decision->applicationCache);
    }

    public function testPresenterMapsEveryBackendAndUnknownEphemeralFallbackKeepsBothFacts(): void
    {
        $presenter = new DisposableCacheStoragePresenter();
        $redis = (new ReflectionClass(RedisCache::class))->newInstanceWithoutConstructor();
        self::assertInstanceOf(RedisCache::class, $redis);

        $cases = [
            [new CascadeCache(), 'Using managed cache', 'Managed cache', 'success'],
            [$redis, 'Using Redis cache', 'Redis cache', 'success'],
            [new DbCache(), 'Using database cache', 'Database cache', 'success'],
            [new FileCache(), 'Using filesystem cache', 'Filesystem cache', 'success'],
        ];

        foreach ($cases as [$cache, $heading, $description, $severity]) {
            self::assertInstanceOf(CacheInterface::class, $cache);
            Craft::$app->set('cache', $cache);
            $presentation = $presenter->present($this->resolve('craft', false));
            self::assertSame($heading, $presentation->headingKey);
            self::assertSame([], $presentation->explanationKeys);
            self::assertSame('Active', $presentation->utilityValueKey);
            self::assertSame($description, $presentation->utilityDescriptionKey);
            self::assertSame($severity, $presentation->statusSeverity);
        }

        Craft::$app->set('cache', new DisposableUnknownCache());
        $unknownFallback = $presenter->present($this->resolve('file', true));
        self::assertSame('Using application cache', $unknownFallback->headingKey);
        self::assertSame([
            'This host has an ephemeral filesystem, so the application cache is used automatically.',
            'Cross-request persistence could not be confirmed.',
        ], $unknownFallback->explanationKeys);
        self::assertSame('Best effort', $unknownFallback->utilityValueKey);
        self::assertSame('Application cache', $unknownFallback->utilityDescriptionKey);
        self::assertSame('info', $unknownFallback->statusSeverity);
    }

    public function testPresenterMapsFileDisabledAndAllFamiliesDisabledStates(): void
    {
        $presenter = new DisposableCacheStoragePresenter();
        $file = $presenter->present($this->resolve('file', false));
        self::assertSame('Using file cache', $file->headingKey);
        self::assertTrue($file->filePathEligible);
        self::assertSame('Active', $file->utilityValueKey);
        self::assertSame('File cache', $file->utilityDescriptionKey);

        Craft::$app->set('cache', new ArrayCache());
        $disabled = $presenter->present($this->resolve('craft', false));
        self::assertSame('Caching disabled', $disabled->headingKey);
        self::assertSame(
            ['No suitable cross-request cache is available. Cache data is recomputed as needed.'],
            $disabled->explanationKeys,
        );
        self::assertSame('Disabled', $disabled->utilityValueKey);
        self::assertSame('Recomputed as needed', $disabled->utilityDescriptionKey);
        self::assertSame('warning', $disabled->utilitySeverity);

        $inactive = $presenter->present($this->resolve('craft', false), false);
        self::assertSame('Inactive', $inactive->utilityValueKey);
        self::assertSame('No cache families enabled', $inactive->utilityDescriptionKey);
        self::assertSame('inactive', $inactive->utilitySeverity);
    }

    public function testApplicationOptionTokenPreservesCompatibleTokens(): void
    {
        self::assertSame('redis', DisposableCacheStorageResolver::applicationOptionToken('redis'));
        self::assertSame('craft', DisposableCacheStorageResolver::applicationOptionToken('craft'));
        self::assertSame('craft', DisposableCacheStorageResolver::applicationOptionToken('file'));
        self::assertSame('shared', DisposableCacheStorageResolver::applicationOptionToken('disk', ['shared'], 'shared'));
    }

    public function testStatusComponentEscapesPathsAndCanRenderRepeatedlyWithoutIdsOrScripts(): void
    {
        $presentation = new DisposableCacheStoragePresentation(
            headingKey: 'Using file cache',
            explanationKeys: [],
            statusSeverity: 'success',
            filePathEligible: true,
            utilityValueKey: 'Active',
            utilityDescriptionKey: 'File cache',
            utilitySeverity: 'success',
        );
        $variables = [
            'presentation' => $presentation,
            'filePath' => '/runtime/<script>alert("unsafe")</script>/',
        ];

        $first = Craft::$app->getView()->renderTemplate(
            'lindemannrock-base/_components/cache-storage-status',
            $variables,
        );
        $second = Craft::$app->getView()->renderTemplate(
            'lindemannrock-base/_components/cache-storage-status',
            $variables,
        );

        foreach ([$first, $second] as $html) {
            self::assertStringContainsString('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;', $html);
            self::assertStringNotContainsString('<script>alert', $html);
            self::assertStringNotContainsString(' id=', $html);
            self::assertStringNotContainsString('<script', $html);
        }
    }

    public function testSharedFieldAndStatusTemplatesKeepRoutingOutOfPresentationMarkup(): void
    {
        $field = $this->readSource('src/templates/_partials/field-cache-storage.twig');
        $status = $this->readSource('src/templates/_components/cache-storage-status.twig');

        self::assertStringContainsString("label: 'Cache Storage Method'|t('lindemannrock-base')", $field);
        self::assertStringContainsString("{value: 'file', label: 'File cache'|t('lindemannrock-base')}", $field);
        self::assertStringContainsString("label: 'Application cache'|t('lindemannrock-base')", $field);
        self::assertStringContainsString('applicationOptionToken', $field);
        self::assertStringContainsString("select.value === 'file' ? 'file' : 'application'", $field);
        self::assertStringContainsString('settings.isOverriddenByConfig(settingProperty)', $field);
        self::assertStringContainsString('settings.getErrors(settingProperty)', $field);
        self::assertStringContainsString("presentation.headingKey|t('lindemannrock-base')", $status);
        self::assertStringContainsString("explanationKey|t('lindemannrock-base')", $status);
        self::assertStringContainsString('<code>{{ filePath }}</code>', $status);

        foreach ([$field, $status] as $template) {
            self::assertStringNotContainsString('CacheBackendStatus', $template);
            self::assertStringNotContainsString('yii\\redis\\Cache', $template);
            self::assertStringNotContainsString('CascadeCache', $template);
            self::assertStringNotContainsString('componentClass', $template);
        }
        self::assertStringNotContainsString('effectiveStorage', $status);
        self::assertStringNotContainsString('configuredStorageToken', $status);
    }

    public function testSharedFieldRendersBothChoicesAndPreservesOverrideAndErrorStates(): void
    {
        $settings = new DisposableCacheSettingsStub();
        $settings->addError('cacheStorageMethod', 'Injected storage error.');
        $presentation = new DisposableCacheStoragePresentation(
            headingKey: 'Using file cache',
            explanationKeys: [],
            statusSeverity: 'success',
            filePathEligible: true,
            utilityValueKey: 'Active',
            utilityDescriptionKey: 'File cache',
            utilitySeverity: 'success',
        );

        $html = Craft::$app->getView()->renderTemplate(
            'lindemannrock-base/_partials/field-cache-storage',
            [
                'settings' => $settings,
                'pluginHandle' => 'consumer-plugin',
                'configuredStorageToken' => 'redis',
                'filePresentation' => $presentation,
                'applicationPresentation' => $presentation,
                'applicationOptionToken' => 'redis',
                'filePath' => '/runtime/<unsafe>/',
            ],
            View::TEMPLATE_MODE_CP,
        );

        self::assertStringContainsString('Cache Storage Method', $html);
        self::assertStringContainsString('value="file"', $html);
        self::assertStringContainsString('value="redis" selected', $html);
        self::assertSame(2, substr_count($html, 'data-cache-storage-panel='));
        self::assertStringContainsString('data-cache-storage-panel="file" class="hidden"', $html);
        self::assertStringContainsString('config/consumer-plugin.php', $html);
        self::assertStringContainsString('Injected storage error.', $html);
        self::assertStringContainsString('/runtime/&lt;unsafe&gt;/', $html);
        self::assertStringNotContainsString('/runtime/<unsafe>/', $html);
    }

    public function testEveryPresentationMessageExistsInAllBaseCatalogues(): void
    {
        $keys = [
            'Cache Storage Method',
            'Choose where disposable cache data is stored. File caching automatically uses the application cache on ephemeral hosts.',
            'File cache',
            'Application cache',
            'Using managed cache',
            'Using Redis cache',
            'Using database cache',
            'Using filesystem cache',
            'Using file cache',
            'Using application cache',
            'Caching disabled',
            'This host has an ephemeral filesystem, so the application cache is used automatically.',
            'Cross-request persistence could not be confirmed.',
            'No suitable cross-request cache is available. Cache data is recomputed as needed.',
            'Active',
            'Managed cache',
            'Redis cache',
            'Database cache',
            'Filesystem cache',
            'Best effort',
            'Disabled',
            'Recomputed as needed',
            'Inactive',
            'No cache families enabled',
        ];

        foreach (['en', 'de', 'fr', 'nl', 'es', 'ar', 'it', 'pt', 'ja', 'sv', 'da', 'no'] as $locale) {
            $catalogue = require dirname(__DIR__, 2) . "/src/translations/{$locale}/lindemannrock-base.php";
            self::assertIsArray($catalogue);
            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $catalogue, "Missing {$locale} key: {$key}");
            }
        }
    }

    private function resolve(string $token, bool $ephemeral): DisposableCacheStorageDecision
    {
        return (new DisposableCacheStorageResolver())->resolve(
            $token,
            __METHOD__,
            ephemeralHost: $ephemeral,
        );
    }

    private function applicationCacheDiagnosticsProperty(): ReflectionProperty
    {
        return new ReflectionProperty(PluginHelper::class, 'applicationCacheDiagnostics');
    }

    private function readSource(string $relativePath): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
        self::assertIsString($source);

        return $source;
    }
}

/**
 * Unknown application-cache implementation with no persistence claim.
 *
 * @since 5.38.0
 */
final class DisposableUnknownCache extends Cache
{
    /** @var array<string, mixed> */
    private array $values = [];

    protected function getValue($key)
    {
        return $this->values[$key] ?? false;
    }

    protected function getValues($keys)
    {
        return array_map(fn(string $key): mixed => $this->getValue($key), $keys);
    }

    protected function setValue($key, $value, $duration)
    {
        $this->values[$key] = $value;

        return true;
    }

    protected function setValues($data, $duration)
    {
        foreach ($data as $key => $value) {
            $this->values[$key] = $value;
        }

        return [];
    }

    protected function addValue($key, $value, $duration)
    {
        if (array_key_exists($key, $this->values)) {
            return false;
        }
        $this->values[$key] = $value;

        return true;
    }

    protected function deleteValue($key)
    {
        unset($this->values[$key]);

        return true;
    }

    protected function flushValues()
    {
        $this->values = [];

        return true;
    }
}

/**
 * Settings fixture for the shared cache-storage field.
 *
 * @since 5.38.0
 */
final class DisposableCacheSettingsStub extends Model
{
    public string $cacheStorageMethod = 'redis';

    public function isOverriddenByConfig(string $attribute): bool
    {
        return $attribute === 'cacheStorageMethod';
    }
}
