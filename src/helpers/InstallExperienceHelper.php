<?php
/**
 * LindemannRock Plugin Base for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

use Craft;
use craft\base\PluginInterface;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\View;
use lindemannrock\base\web\assets\install\InstallExperienceAsset;
use yii\base\Event;

/**
 * Registers a one-time CP welcome experience after plugin install.
 */
class InstallExperienceHelper
{
    private const SESSION_KEY_PREFIX = 'lr-install-experience:';
    private const PREVIEW_PARAM = 'lrInstallPreview';

    /**
     * Register the install experience for a plugin.
     *
     * @param PluginInterface $plugin
     * @param array $options
     */
    public static function register(PluginInterface $plugin, array $options = []): void
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) use ($plugin, $options) {
                if ($event->plugin !== $plugin) {
                    return;
                }

                $request = Craft::$app->getRequest();
                if (!$request->getIsCpRequest() || $request->getIsConsoleRequest()) {
                    return;
                }

                $payload = self::buildPayload($plugin, $options);
                Craft::$app->getSession()->set(self::sessionKey($plugin), $payload);

                Craft::$app->getResponse()
                    ->redirect(UrlHelper::cpUrl($payload['redirectUri']))
                    ->send();
            }
        );

        $request = Craft::$app->getRequest();
        if (!$request->getIsCpRequest() || $request->getIsConsoleRequest()) {
            return;
        }

        $payload = self::previewPayload($plugin, $options);

        if (!is_array($payload)) {
            $session = Craft::$app->getSession();
            $payload = $session->get(self::sessionKey($plugin));
            if (!is_array($payload)) {
                return;
            }

            $session->remove(self::sessionKey($plugin));
        }

        if (!is_array($payload)) {
            return;
        }

        $view = Craft::$app->getView();
        $view->registerAssetBundle(InstallExperienceAsset::class);
        $view->registerHtml(
            $view->renderTemplate('lindemannrock-base/_partials/install-experience', [
                'experience' => $payload,
            ]),
            View::POS_END,
            'lr-install-experience-' . ($payload['pluginHandle'] ?? $plugin->id)
        );

        $json = JsonHelper::htmlSafeJson($payload);
        $view->registerJs(
            "window.LrInstallExperience && window.LrInstallExperience.mount($json);",
            View::POS_END,
            'lr-install-experience-init-' . ($payload['pluginHandle'] ?? $plugin->id)
        );
    }

    /**
     * Build the install experience payload.
     *
     * @param PluginInterface $plugin
     * @param array $options
     * @return array
     */
    private static function buildPayload(PluginInterface $plugin, array $options): array
    {
        $pluginName = trim((string)$plugin->name) ?: self::labelFromHandle($plugin->id);
        $redirectUri = self::resolveRedirectUri($plugin, $options);
        $ctaUrl = (string)($options['ctaUrl'] ?? $redirectUri);

        $iconSvg = self::readPluginIconSvg($plugin);
        $iconColor = self::extractPrimaryHexColor($iconSvg);
        $sidebarColor = trim((string)($options['sidebarColor'] ?? ''));
        $accent = (string)($options['accent'] ?? '#0f766e');
        $resolvedSidebarColor = $sidebarColor !== '' ? $sidebarColor : $iconColor;
        $uiColor = trim((string)($options['uiColor'] ?? ''));

        return [
            'pluginHandle' => $plugin->id,
            'pluginName' => $pluginName,
            'pluginVersion' => PluginHelper::getPluginVersion($plugin),
            'redirectUri' => $redirectUri,
            'headline' => (string)($options['headline'] ?? ($pluginName . ' is installed')),
            'body' => (string)($options['body'] ?? 'Everything is wired up. You can start configuring the plugin right away.'),
            'eyebrow' => (string)($options['eyebrow'] ?? 'Installed successfully'),
            'ctaLabel' => (string)($options['ctaLabel'] ?? self::resolveCtaLabel($plugin)),
            'ctaUrl' => $ctaUrl,
            'secondaryLabel' => (string)($options['secondaryLabel'] ?? 'Close'),
            'accent' => $accent,
            'theme' => (string)($options['theme'] ?? 'classic'),
            'confettiPreset' => (string)($options['confettiPreset'] ?? 'surprise'),
            'iconSvg' => $iconSvg,
            'iconColor' => $iconColor,
            'sidebarColor' => $resolvedSidebarColor,
            'uiColor' => $uiColor !== '' ? $uiColor : ($resolvedSidebarColor ?: $accent),
        ];
    }

    /**
     * Resolve the best post-install redirect target.
     *
     * @param PluginInterface $plugin
     * @param array $options
     * @return string
     */
    private static function resolveRedirectUri(PluginInterface $plugin, array $options): string
    {
        $configured = trim((string)($options['redirectUri'] ?? ''));
        if ($configured !== '') {
            return $configured;
        }

        if (property_exists($plugin, 'hasCpSection') && $plugin->hasCpSection) {
            return $plugin->id;
        }

        if (property_exists($plugin, 'hasCpSettings') && $plugin->hasCpSettings) {
            return 'settings/plugins/' . $plugin->id;
        }

        return 'settings/plugins';
    }

    /**
     * Resolve the primary CTA label.
     *
     * @param PluginInterface $plugin
     * @return string
     */
    private static function resolveCtaLabel(PluginInterface $plugin): string
    {
        if (property_exists($plugin, 'hasCpSection') && $plugin->hasCpSection) {
            return 'Open plugin';
        }

        if (property_exists($plugin, 'hasCpSettings') && $plugin->hasCpSettings) {
            return 'Open settings';
        }

        return 'Continue';
    }

    /**
     * Build a session key for the plugin.
     *
     * @param PluginInterface $plugin
     * @return string
     */
    private static function sessionKey(PluginInterface $plugin): string
    {
        return self::SESSION_KEY_PREFIX . $plugin->id;
    }

    /**
     * Build a preview payload from the request when devMode is enabled.
     *
     * Example:
     * /admin/tailwind-manager?lrInstallPreview=tailwind-manager
     *
     * @param PluginInterface $plugin
     * @param array $options
     * @return array|null
     */
    private static function previewPayload(PluginInterface $plugin, array $options): ?array
    {
        if (!Craft::$app->getConfig()->getGeneral()->devMode) {
            return null;
        }

        $previewHandle = trim((string)Craft::$app->getRequest()->getQueryParam(self::PREVIEW_PARAM, ''));
        if ($previewHandle === '' || $previewHandle !== $plugin->id) {
            return null;
        }

        return self::buildPayload($plugin, $options);
    }

    /**
     * Convert a handle into a human readable label.
     *
     * @param string $handle
     * @return string
     */
    private static function labelFromHandle(string $handle): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $handle));
    }

    /**
     * Read the plugin's src/icon.svg if available.
     *
     * @param PluginInterface $plugin
     * @return string|null
     */
    private static function readPluginIconSvg(PluginInterface $plugin): ?string
    {
        try {
            $reflection = new \ReflectionClass($plugin);
            $pluginFile = $reflection->getFileName();
            if ($pluginFile === false) {
                return null;
            }

            $iconPath = dirname($pluginFile) . '/icon.svg';
            if (!is_file($iconPath) || !is_readable($iconPath)) {
                return null;
            }

            $svg = file_get_contents($iconPath);
            if (!is_string($svg) || trim($svg) === '') {
                return null;
            }

            return trim($svg);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Extract the first non-white/non-black hex color from an SVG string.
     *
     * @param string|null $svg
     * @return string|null
     */
    private static function extractPrimaryHexColor(?string $svg): ?string
    {
        if (!is_string($svg) || $svg === '') {
            return null;
        }

        if (!preg_match_all('/#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $svg, $matches)) {
            return null;
        }

        foreach ($matches[0] as $color) {
            $normalized = strtoupper($color);
            if (in_array($normalized, ['#FFF', '#FFFFFF', '#000', '#000000'], true)) {
                continue;
            }

            return $normalized;
        }

        return null;
    }
}
