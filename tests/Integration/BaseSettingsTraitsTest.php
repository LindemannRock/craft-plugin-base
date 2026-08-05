<?php
/**
 * LindemannRock Plugin Base
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\base\tests\Integration;

use craft\base\Model;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\traits\DateFormatSettingsTrait;
use lindemannrock\base\traits\DateRangeSettingsTrait;
use lindemannrock\base\traits\ExportFormatSettingsTrait;
use lindemannrock\base\traits\ItemsPerPageSettingsTrait;
use lindemannrock\base\traits\LogLevelSettingsTrait;
use lindemannrock\base\traits\PluginNameSettingsTrait;

/**
 * Pins the public contracts of the six base-settings traits adopted by plugins
 * via the `_partials/cascade-base-overrides` umbrella (cascade traits) and the
 * `_partials/field-*` partials (shared-field traits).
 *
 * Each trait declares its properties + helper methods that plugins merge into
 * their own Settings model's `rules()` / `attributeLabels()`. The tests below
 * pin the property defaults, the rule shape (validator names + ranges), and
 * the label keys used by `Craft::t('lindemannrock-base', …)`. A regression in
 * any of these surfaces would silently break every adopting plugin.
 *
 * @since 5.25.0
 */
final class BaseSettingsTraitsTest extends IntegrationTestCase
{
    // ---------------------------------------------------------------------
    // DateFormatSettingsTrait
    // ---------------------------------------------------------------------

    public function testDateFormatSettingsTraitPropertiesDefaultToNullForCascade(): void
    {
        $settings = new class() extends Model {
            use DateFormatSettingsTrait;
        };

        // All five properties must be nullable and default to null — null is
        // the sentinel for "inherit from base config / hardcoded default" that
        // DateFormatHelper's cascade keys off of.
        self::assertNull($settings->timeFormat, 'timeFormat default must be null');
        self::assertNull($settings->monthFormat, 'monthFormat default must be null');
        self::assertNull($settings->dateOrder, 'dateOrder default must be null');
        self::assertNull($settings->dateSeparator, 'dateSeparator default must be null');
        self::assertNull($settings->showSeconds, 'showSeconds default must be null');
    }

    public function testDateFormatSettingsRulesReturnsExpectedRangesAndValidators(): void
    {
        $settings = new class() extends Model {
            use DateFormatSettingsTrait;
        };

        $rules = $settings->dateFormatSettingsRules();
        $byAttribute = self::indexRulesByFirstAttribute($rules);

        self::assertSame(['12', '24'], $byAttribute['timeFormat']['range'], "timeFormat range pins the 12/24 contract");
        self::assertSame(['numeric', 'short', 'long'], $byAttribute['monthFormat']['range']);
        self::assertSame(['dmy', 'mdy', 'ymd'], $byAttribute['dateOrder']['range']);
        self::assertSame(['/', '-', '.'], $byAttribute['dateSeparator']['range']);
        self::assertSame('boolean', $byAttribute['showSeconds']['validator']);

        // skipOnEmpty must be true on every rule — the partial submits '' for
        // "Use global default" and the controller's reflection coercion turns
        // that into null. The skipOnEmpty contract is what keeps validation
        // from rejecting null/'' values.
        foreach (['timeFormat', 'monthFormat', 'dateOrder', 'dateSeparator', 'showSeconds'] as $attribute) {
            self::assertTrue(
                $byAttribute[$attribute]['skipOnEmpty'] ?? false,
                "{$attribute} rule must have skipOnEmpty=true so the 'Use global default' empty submit doesn't fail validation",
            );
        }
    }

    public function testDateFormatSettingsLabelsReturnAllFiveAttributes(): void
    {
        $settings = new class() extends Model {
            use DateFormatSettingsTrait;
        };

        $labels = $settings->dateFormatSettingsLabels();
        self::assertSame(
            ['timeFormat', 'monthFormat', 'dateOrder', 'dateSeparator', 'showSeconds'],
            array_keys($labels),
            'all five trait properties must appear in the labels map — missing entries silently break translation in non-English contexts',
        );
        foreach ($labels as $value) {
            self::assertIsString($value);
            self::assertNotSame('', $value);
        }
    }

    // ---------------------------------------------------------------------
    // DateRangeSettingsTrait
    // ---------------------------------------------------------------------

    public function testDateRangeSettingsTraitDefaultsToNullForCascade(): void
    {
        $settings = new class() extends Model {
            use DateRangeSettingsTrait;
        };

        self::assertNull($settings->defaultDateRange, 'defaultDateRange default must be null');
    }

    public function testDateRangeSettingsRulesPinTheAllowedRanges(): void
    {
        $settings = new class() extends Model {
            use DateRangeSettingsTrait;
        };

        $rules = $settings->dateRangeSettingsRules();
        $byAttribute = self::indexRulesByFirstAttribute($rules);

        $expected = [
            'today', 'yesterday', 'thisWeek', 'lastWeek', 'last7days',
            'last14days', 'last30days', 'last90days', 'thisMonth',
            'lastMonth', 'thisQuarter', 'lastQuarter', 'thisYear',
            'lastYear', 'last12months', 'all',
        ];
        self::assertSame($expected, $byAttribute['defaultDateRange']['range']);
        self::assertTrue($byAttribute['defaultDateRange']['skipOnEmpty'] ?? false);
    }

    public function testDateRangeSettingsLabelHasOneEntry(): void
    {
        $settings = new class() extends Model {
            use DateRangeSettingsTrait;
        };

        self::assertSame(['defaultDateRange'], array_keys($settings->dateRangeSettingsLabel()));
    }

    // ---------------------------------------------------------------------
    // ExportFormatSettingsTrait
    // ---------------------------------------------------------------------

    public function testExportFormatSettingsTraitDefaultsToNullForCascade(): void
    {
        $settings = new class() extends Model {
            use ExportFormatSettingsTrait;
        };

        self::assertNull($settings->exportsCsv);
        self::assertNull($settings->exportsJson);
        self::assertNull($settings->exportsExcel);
    }

    public function testExportFormatSettingsRulesBundleAllThreeFlagsAsBooleans(): void
    {
        $settings = new class() extends Model {
            use ExportFormatSettingsTrait;
        };

        $rules = $settings->exportFormatSettingsRules();
        self::assertCount(1, $rules, 'expected a single bundled boolean rule covering all three flags');

        [$attributes, $validator] = $rules[0];
        self::assertSame(['exportsCsv', 'exportsJson', 'exportsExcel'], $attributes);
        self::assertSame('boolean', $validator);
        self::assertTrue($rules[0]['skipOnEmpty'] ?? false);
    }

    public function testExportFormatSettingsLabelsReturnAllThreeFormats(): void
    {
        $settings = new class() extends Model {
            use ExportFormatSettingsTrait;
        };

        self::assertSame(
            ['exportsCsv', 'exportsJson', 'exportsExcel'],
            array_keys($settings->exportFormatSettingsLabels()),
        );
    }

    // ---------------------------------------------------------------------
    // ItemsPerPageSettingsTrait
    // ---------------------------------------------------------------------

    public function testItemsPerPageSettingsTraitDefaultsToOneHundred(): void
    {
        $settings = new class() extends Model {
            use ItemsPerPageSettingsTrait;
        };

        // 100 is the standardized default the May 2026 rollout settled on
        // after auditing 14 plugins. Tests pin this so a regression to 50 (the
        // pre-rollout value used by ~6 plugins) is caught.
        self::assertSame(100, $settings->itemsPerPage);
    }

    public function testItemsPerPageSettingsRulesEnforceMinTenMaxFiveHundred(): void
    {
        $settings = new class() extends Model {
            use ItemsPerPageSettingsTrait;
        };

        $rules = $settings->itemsPerPageSettingsRules();

        // The trait emits TWO rules per attribute (integer bounds + default
        // value), so we can't use the first-attribute index helper here —
        // pull out the integer rule explicitly.
        $integerRules = array_filter($rules, static fn(array $rule): bool => ($rule[1] ?? null) === 'integer');
        self::assertCount(1, $integerRules, 'expected exactly one integer rule for itemsPerPage');
        $integerRule = array_values($integerRules)[0];

        // The min:10 contract is the bug fix from search-manager's bundled
        // `integer, min: 1` rule. Lower values are not meaningful for CP
        // pagination — pin this so future widening doesn't slip through.
        self::assertSame(['itemsPerPage'], $integerRule[0]);
        self::assertSame(10, $integerRule['min']);
        self::assertSame(500, $integerRule['max']);

        // The default-value rule keeps the property at 100 when validation
        // receives null (e.g. fresh DB rows on plugins that backfilled the
        // column without a notNull default).
        $defaultRules = array_filter($rules, static fn(array $rule): bool => ($rule[1] ?? null) === 'default');
        self::assertCount(1, $defaultRules, 'expected exactly one default-value rule for itemsPerPage');
        self::assertSame(100, array_values($defaultRules)[0]['value']);
    }

    // ---------------------------------------------------------------------
    // PluginNameSettingsTrait
    // ---------------------------------------------------------------------

    public function testPluginNameSettingsTraitDeclaresNoPropertyAdopterOwnsTheDefault(): void
    {
        // The trait deliberately does NOT declare $pluginName — each plugin's
        // default differs ("Search Manager", "Logging Library", etc.) and stays
        // on the plugin's own Settings class. Pin this so a future refactor
        // that tries to centralize the default is caught immediately.
        $traitProps = (new \ReflectionClass(PluginNameSettingsTrait::class))->getProperties();
        self::assertSame([], $traitProps, 'PluginNameSettingsTrait must not declare any properties');
    }

    public function testPluginNameSettingsRulesRequireValueCap255AndRejectMarkup(): void
    {
        // Adopter must already have a `public string $pluginName` property
        // for the trait's rules to validate against.
        $settings = new class() extends Model {
            use PluginNameSettingsTrait;

            public string $pluginName = 'Test Plugin';

            public function rules(): array
            {
                return $this->pluginNameSettingsRules();
            }
        };

        $rules = $settings->pluginNameSettingsRules();
        self::assertCount(4, $rules, 'expected trim filter + required + max-length + markup/control-character rule');

        $required = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'required'));
        $string = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'string'));
        $filter = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'filter'));
        $match = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'match'));

        self::assertSame(['pluginName'], $filter[0][0]);
        self::assertSame('trim', $filter[0]['filter']);
        self::assertSame(['pluginName'], $required[0][0]);
        self::assertSame(['pluginName'], $string[0][0]);
        self::assertSame(255, $string[0]['max']);
        self::assertSame(['pluginName'], $match[0][0]);

        $settings->pluginName = '  Safe Plugin Name  ';
        self::assertTrue($settings->validate());
        self::assertSame('Safe Plugin Name', $settings->pluginName);

        foreach (['<script>alert(1)</script>', "Unsafe\nName", "Unsafe\0Name"] as $unsafeName) {
            $settings->clearErrors();
            $settings->pluginName = $unsafeName;

            self::assertFalse($settings->validate(), "pluginName should reject {$unsafeName}");
            self::assertNotEmpty($settings->getErrors('pluginName'));
        }
    }

    public function testPluginNameSettingsLabelHasOneEntry(): void
    {
        $settings = new class() extends Model {
            use PluginNameSettingsTrait;

            public string $pluginName = 'Test Plugin';
        };

        self::assertSame(['pluginName'], array_keys($settings->pluginNameSettingsLabel()));
    }

    // ---------------------------------------------------------------------
    // LogLevelSettingsTrait
    // ---------------------------------------------------------------------

    public function testLogLevelSettingsTraitDefaultsToError(): void
    {
        $settings = new class() extends Model {
            use LogLevelSettingsTrait;
        };

        // 'error' is the universal default. Most plugins ship with this;
        // a future refactor that flips it (say, to 'warning') would silently
        // increase log volume across every adopter. Pin it.
        self::assertSame('error', $settings->logLevel);
    }

    public function testLogLevelSettingsRulesAllowKnownLevelsAndDelegateToValidator(): void
    {
        $settings = new class() extends Model {
            use LogLevelSettingsTrait;
        };

        $rules = $settings->logLevelSettingsRules();
        self::assertCount(2, $rules, 'expected in-range rule + validateLogLevel delegation');

        $inRule = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'in'));
        self::assertSame(['debug', 'info', 'warning', 'error'], $inRule[0]['range']);

        // The second rule delegates to `validateLogLevel` — which lives on
        // `SettingsConfigTrait` and handles the devMode-gated `debug` fallback.
        // The trait's rule list assumes adopters also `use SettingsConfigTrait`.
        $validatorRule = array_values(array_filter($rules, static fn(array $r): bool => ($r[1] ?? null) === 'validateLogLevel'));
        self::assertCount(1, $validatorRule, 'logLevel rules must include validateLogLevel delegation');
        self::assertSame(['logLevel'], $validatorRule[0][0]);
    }

    public function testLogLevelSettingsLabelHasOneEntry(): void
    {
        $settings = new class() extends Model {
            use LogLevelSettingsTrait;
        };

        self::assertSame(['logLevel'], array_keys($settings->logLevelSettingsLabel()));
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Yii rules come in array form `[[attributes], validator, ...options]`.
     * This collapses them into a flat `attribute => [validator, ...options]`
     * lookup keyed by the FIRST attribute in each rule's attribute list, so
     * tests can pull validator names + ranges by name instead of array index.
     *
     * @param array<int, array<int|string, mixed>> $rules
     * @return array<string, array<string, mixed>>
     */
    private static function indexRulesByFirstAttribute(array $rules): array
    {
        $byAttribute = [];
        foreach ($rules as $rule) {
            $firstAttribute = is_array($rule[0]) ? ($rule[0][0] ?? null) : $rule[0];
            if (!is_string($firstAttribute)) {
                continue;
            }
            $byAttribute[$firstAttribute] = [
                'validator' => $rule[1] ?? null,
            ] + array_filter($rule, static fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }
        return $byAttribute;
    }
}
