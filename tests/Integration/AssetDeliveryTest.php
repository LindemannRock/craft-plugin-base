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
use craft\web\AssetBundle;
use craft\web\AssetManager;
use craft\web\assets\cp\CpAsset;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\web\assets\analytics\AnalyticsAsset;
use lindemannrock\base\web\assets\components\ComponentsAsset;
use lindemannrock\base\web\assets\install\InstallExperienceAsset;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Covers static asset delivery for shared components and control-panel features.
 *
 * @since 5.38.0
 */
final class AssetDeliveryTest extends IntegrationTestCase
{
    public function testInstalledAliasResolvesToPackageAssets(): void
    {
        $packageRoot = dirname(__DIR__, 2);
        $sourceRoot = realpath($packageRoot . '/src');
        $aliasRoot = realpath(Craft::getAlias('@lindemannrock/base'));

        self::assertIsString($sourceRoot);
        self::assertSame($sourceRoot, $aliasRoot);
        self::assertDirectoryExists($aliasRoot . '/web/assets/components/dist');
        self::assertDirectoryExists($aliasRoot . '/web/assets/analytics/dist');
        self::assertDirectoryExists($aliasRoot . '/web/assets/install/dist');
    }

    /**
     * @param class-string<AssetBundle> $bundleClass
     * @param list<string> $js
     * @param list<string> $css
     * @param list<class-string<AssetBundle>> $depends
     */
    #[DataProvider('assetBundleDefinitions')]
    public function testBundlesResolveAliasWithoutModuleDatabaseOrPublicationState(
        string $bundleClass,
        string $sourceSuffix,
        array $js,
        array $css,
        array $depends,
    ): void {
        $originalAlias = Craft::getAlias('@lindemannrock/base');
        $originalAssetManager = Craft::$app->getAssetManager();
        $originalDb = Craft::$app->getDb();
        $originalModule = Craft::$app->getModule('lindemannrock-base');
        $aliasRoot = $this->createTrackedTempDirectory('base-asset-package-');
        $offlineDb = new Connection([
            'dsn' => 'unsupported:base-asset-test',
        ]);
        $offlineAssetManager = new class([ 'basePath' => $aliasRoot, 'baseUrl' => '/unavailable-runtime-assets', ]) extends AssetManager {
            /** @var list<string> */
            public array $publicationPaths = [];

            /**
             * @inheritdoc
             */
            public function publish($path, $options = []): array
            {
                $this->publicationPaths[] = Craft::getAlias($path);

                throw new \RuntimeException('Runtime asset publication is unavailable.');
            }
        };

        try {
            Craft::setAlias('@lindemannrock/base', $aliasRoot);
            Craft::$app->set('assetManager', $offlineAssetManager);
            Craft::$app->set('db', $offlineDb);
            Craft::$app->setModule('lindemannrock-base', null);

            $bundle = new $bundleClass();

            self::assertNull(Craft::$app->getModule('lindemannrock-base'));
            self::assertFalse($offlineDb->getIsActive());
            self::assertSame([], $offlineAssetManager->publicationPaths);
            self::assertSame($aliasRoot . $sourceSuffix, $bundle->sourcePath);
            self::assertSame($js, $bundle->js);
            self::assertSame($css, $bundle->css);
            self::assertSame($depends, $bundle->depends);
            self::assertSame([], $bundle->jsOptions);
            self::assertSame([], $bundle->cssOptions);
        } finally {
            Craft::$app->setModule('lindemannrock-base', $originalModule);
            Craft::$app->set('db', $originalDb);
            Craft::$app->set('assetManager', $originalAssetManager);
            Craft::setAlias('@lindemannrock/base', $originalAlias);
        }
    }

    public function testPrepublishedUrlsRenderOnceInBundleOrder(): void
    {
        $originalAssetManager = Craft::$app->getAssetManager();
        $view = Craft::$app->getView();
        $sourceRoot = Craft::getAlias('@lindemannrock/base');
        $componentsUrl = 'https://cdn.example.test/base/components';
        $analyticsUrl = 'https://cdn.example.test/base/analytics';
        $installUrl = 'https://cdn.example.test/base/install';
        $assetManager = new class([ 'basePath' => $sourceRoot, 'baseUrl' => '/unavailable-runtime-assets', 'appendTimestamp' => false, 'bundles' => [ ComponentsAsset::class => [ 'basePath' => $sourceRoot . '/web/assets/components/dist', 'baseUrl' => $componentsUrl, ], AnalyticsAsset::class => [ 'basePath' => $sourceRoot . '/web/assets/analytics/dist', 'baseUrl' => $analyticsUrl, ], InstallExperienceAsset::class => [ 'basePath' => $sourceRoot . '/web/assets/install/dist', 'baseUrl' => $installUrl, ], CpAsset::class => [ 'class' => \yii\web\AssetBundle::class, 'basePath' => $sourceRoot, 'baseUrl' => 'https://cdn.example.test/craft', ], ], ]) extends AssetManager {
            /** @var list<string> */
            public array $publicationPaths = [];

            /**
             * @inheritdoc
             */
            public function publish($path, $options = []): array
            {
                $this->publicationPaths[] = Craft::getAlias($path);

                throw new \RuntimeException('Runtime asset publication is unavailable.');
            }
        };

        try {
            Craft::$app->set('assetManager', $assetManager);
            $view->clear();

            foreach ([ComponentsAsset::class, AnalyticsAsset::class, InstallExperienceAsset::class] as $bundleClass) {
                $view->registerAssetBundle($bundleClass);
                $view->registerAssetBundle($bundleClass);
            }

            $approvedBundles = array_values(array_intersect(
                array_keys($view->assetBundles),
                [ComponentsAsset::class, AnalyticsAsset::class, InstallExperienceAsset::class],
            ));

            self::assertSame([], $assetManager->publicationPaths);
            self::assertSame(
                [ComponentsAsset::class, AnalyticsAsset::class, InstallExperienceAsset::class],
                $approvedBundles,
            );
            self::assertSame($componentsUrl, $view->assetBundles[ComponentsAsset::class]->baseUrl);
            self::assertSame($analyticsUrl, $view->assetBundles[AnalyticsAsset::class]->baseUrl);
            self::assertSame($installUrl, $view->assetBundles[InstallExperienceAsset::class]->baseUrl);

            $headHtml = $view->getHeadHtml(false);
            $bodyHtml = $view->getBodyHtml(false);
            $styleCount = preg_match_all('/<link[^>]+href="([^"]+)"[^>]*>/', $headHtml, $styleMatches);
            $scriptCount = preg_match_all('/<script[^>]+src="([^"]+)"[^>]*><\/script>/', $bodyHtml, $scriptMatches);
            $styleUrls = array_map(static fn(string $url): string => html_entity_decode($url), $styleMatches[1]);
            $scriptUrls = array_map(static fn(string $url): string => html_entity_decode($url), $scriptMatches[1]);

            self::assertSame(2, $styleCount, $headHtml);
            self::assertSame([
                $componentsUrl . '/css/components.css',
                $installUrl . '/css/install-experience.css',
            ], $styleUrls, $headHtml);
            self::assertSame(4, $scriptCount, $bodyHtml);
            self::assertSame([
                $componentsUrl . '/js/components.js',
                $analyticsUrl . '/js/chart.umd.min.js',
                $analyticsUrl . '/js/analytics.js',
                $installUrl . '/js/install-experience.js',
            ], $scriptUrls, $bodyHtml);
        } finally {
            $view->clear();
            Craft::$app->set('assetManager', $originalAssetManager);
        }
    }

    public function testCustomerArchiveIncludesEveryBundleAsset(): void
    {
        $expected = [
            'src/web/assets/components/dist/css/components.css',
            'src/web/assets/components/dist/js/components.js',
            'src/web/assets/analytics/dist/js/chart.umd.min.js',
            'src/web/assets/analytics/dist/js/analytics.js',
            'src/web/assets/install/dist/css/install-experience.css',
            'src/web/assets/install/dist/js/install-experience.js',
        ];
        $packageRoot = dirname(__DIR__, 2);
        $archivePath = $this->createTrackedTempDirectory('base-asset-archive-') . '/package.tar';

        foreach ($expected as $path) {
            self::assertFileExists($packageRoot . '/' . $path, $path);
        }

        $this->runProcess([
            'git',
            '-c',
            'safe.directory=' . $packageRoot,
            'archive',
            '--worktree-attributes',
            '--output=' . $archivePath,
            'HEAD',
        ], $packageRoot);
        $members = array_filter(explode("\n", $this->runProcess(['tar', '-tf', $archivePath], $packageRoot)));

        foreach ($expected as $path) {
            self::assertContains($path, $members, $path);
        }
    }

    /**
     * @return iterable<string, array{class-string<AssetBundle>, string, list<string>, list<string>, list<class-string<AssetBundle>>}>
     */
    public static function assetBundleDefinitions(): iterable
    {
        yield 'components' => [
            ComponentsAsset::class,
            '/web/assets/components/dist',
            ['js/components.js'],
            ['css/components.css'],
            [],
        ];
        yield 'analytics' => [
            AnalyticsAsset::class,
            '/web/assets/analytics/dist',
            ['js/chart.umd.min.js', 'js/analytics.js'],
            [],
            [],
        ];
        yield 'install experience' => [
            InstallExperienceAsset::class,
            '/web/assets/install/dist',
            ['js/install-experience.js'],
            ['css/install-experience.css'],
            [CpAsset::class],
        ];
    }

    /**
     * @param list<string> $command
     */
    private function runProcess(array $command, string $workingDirectory): string
    {
        $pipes = [];
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $workingDirectory,
        );
        self::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        self::assertIsString($output);
        self::assertIsString($error);
        self::assertSame(0, proc_close($process), $error);

        return $output;
    }
}
