<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installed successfully',
    'Plugin installed' => 'Plugin installed',
    'Version' => 'Version',
    'Continue' => 'Continue',
    'Open plugin' => 'Open plugin',
    'Open settings' => 'Open settings',
    'Close dialog' => 'Close dialog',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Everything is wired up. You can start configuring the plugin right away.',

    // Validation messages
    'Value must be a whole number.' => 'Value must be a whole number.',
    'Value must be a number.' => 'Value must be a number.',
    'Value must be a string.' => 'Value must be a string.',
    'Plugin name cannot contain HTML or control characters.' => 'Plugin name cannot contain HTML or control characters.',
    'Value must be either true or false.' => 'Value must be either true or false.',
    'Value must be an array.' => 'Value must be an array.',

    // Date ranges
    'Today' => 'Today',
    'Yesterday' => 'Yesterday',
    'This week' => 'This week',
    'Last week' => 'Last week',
    'Last 7 days' => 'Last 7 days',
    'Last 14 days' => 'Last 14 days',
    'Last 30 days' => 'Last 30 days',
    'Last 90 days' => 'Last 90 days',
    'This month' => 'This month',
    'Last month' => 'Last month',
    'This quarter' => 'This quarter',
    'Last quarter' => 'Last quarter',
    'This year' => 'This year',
    'Last year' => 'Last year',
    'Last 12 months' => 'Last 12 months',
    'All time' => 'All time',
    'Custom Range' => 'Custom Range',

    // Schedule options
    'Disabled' => 'Disabled',
    'Every 15 Minutes' => 'Every 15 Minutes',
    'Every 30 Minutes' => 'Every 30 Minutes',
    'Hourly' => 'Hourly',
    'Every 2 Hours' => 'Every 2 Hours',
    'Every 3 Hours' => 'Every 3 Hours',
    'Every 4 Hours' => 'Every 4 Hours',
    'Every 6 Hours' => 'Every 6 Hours',
    'Every 12 Hours' => 'Every 12 Hours',
    'Daily' => 'Daily',
    'Daily at 2:00 AM' => 'Daily at 2:00 AM',
    'Weekly' => 'Weekly',
    'Every 2 Weeks' => 'Every 2 Weeks',
    'Monthly' => 'Monthly',
    'Every 2 Months' => 'Every 2 Months',
    'Quarterly' => 'Quarterly',
    'Every 6 Months' => 'Every 6 Months',
    'Yearly' => 'Yearly',

    // Export + editions
    'Nothing to export.' => 'Nothing to export.',
    'No sheets to export.' => 'No sheets to export.',
    'Failed to read generated CSV.' => 'Failed to read generated CSV.',
    'Failed to read generated Excel file.' => 'Failed to read generated Excel file.',
    'Failed to create temporary Excel file.' => 'Failed to create temporary Excel file.',
    'Failed to read generated ZIP file.' => 'Failed to read generated ZIP file.',
    'Failed to encode data as JSON: {error}' => 'Failed to encode data as JSON: {error}',
    'The PHP Zip extension is required to create ZIP exports.' => 'The PHP Zip extension is required to create ZIP exports.',
    'Failed to create temporary ZIP file.' => 'Failed to create temporary ZIP file.',
    'Failed to open temporary ZIP file.' => 'Failed to open temporary ZIP file.',
    '{feature} requires the {edition} edition.' => '{feature} requires the {edition} edition.',
    'This feature requires the {edition} edition.' => 'This feature requires the {edition} edition.',
    'Export' => 'Export',
    'Export as Excel' => 'Export as Excel',
    'Export as CSV' => 'Export as CSV',
    'Export as JSON' => 'Export as JSON',

    // Search + filters
    'Search' => 'Search',
    'Search...' => 'Search...',
    'Clear' => 'Clear',
    'Clear search' => 'Clear search',
    'All' => 'All',

    // Table view + pagination
    'No items found.' => 'No items found.',
    'View' => 'View',
    'Sort by' => 'Sort by',
    'Sort attribute' => 'Sort attribute',
    'Sort direction' => 'Sort direction',
    'Sort ascending' => 'Sort ascending',
    'Sort descending' => 'Sort descending',
    'Table Columns' => 'Table Columns',
    'Use defaults' => 'Use defaults',
    'Close' => 'Close',
    'New' => 'New',
    'Action' => 'Action',
    'Actions' => 'Actions',
    'Select all' => 'Select all',
    'Select' => 'Select',
    'Set status' => 'Set status',
    'Cannot modify config items' => 'Cannot modify config items',
    'Previous Page' => 'Previous Page',
    'Next Page' => 'Next Page',
    'no' => 'no',
    'of' => 'of',
    'Auto-refresh' => 'Auto-refresh',
    'Refreshing' => 'Refreshing',
    'Paused' => 'Paused',

    // Import + backups
    'Import from CSV' => 'Import from CSV',
    'CSV File' => 'CSV File',
    'CSV Delimiter' => 'CSV Delimiter',
    'Auto (detect)' => 'Auto (detect)',
    'Comma (,)' => 'Comma (,)',
    'Semicolon (;)' => 'Semicolon (;)',
    'Tab' => 'Tab',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Create Backup Before Import',
    'Upload & Map Columns' => 'Upload & Map Columns',
    'CSV Import' => 'CSV Import',
    'Alternate Import' => 'Alternate Import',
    'Import History' => 'Import History',
    'Recent CSV imports and their results.' => 'Recent CSV imports and their results.',
    'Created By' => 'Created By',
    'Filename' => 'Filename',
    'Size' => 'Size',
    'Imported' => 'Imported',
    'Failed' => 'Failed',
    'Clear history' => 'Clear history',
    'No import history yet.' => 'No import history yet.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Are you sure you want to clear all import logs? This action cannot be undone.',
    'Failed to clear history.' => 'Failed to clear history.',
    'Loading backup history...' => 'Loading backup history...',
    'No backups found.' => 'No backups found.',

    // Geo provider settings (shared via _partials/cascade-geo-settings.twig)
    'Geo Provider' => 'Geo Provider',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Select the geo IP lookup provider. HTTPS providers recommended for privacy.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP free, HTTPS paid)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/day free)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/month free)',
    'API Key' => 'API Key',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).',

    // Date format settings (shared via _partials/cascade-date-format-settings.twig + _partials/cascade-base-overrides.twig)
    'Base Settings Overrides' => 'Base Settings Overrides',
    'Settings marked "Use global default" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply. To customize globally, copy <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> to your project\'s <code>config/</code> directory.' => 'Settings marked "Use global default" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply. To customize globally, copy <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> to your project\'s <code>config/</code> directory.',
    'Time' => 'Time',
    'Date' => 'Date',
    'Time Format' => 'Time Format',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).',
    '24-hour (14:30)' => '24-hour (14:30)',
    '12-hour (2:30 PM)' => '12-hour (2:30 PM)',
    'Month Format' => 'Month Format',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'How months appear in dates: numeric (01), short (Jan), or long (January).',
    'Numeric (01)' => 'Numeric (01)',
    'Short (Jan)' => 'Short (Jan)',
    'Long (January)' => 'Long (January)',
    'Date Order' => 'Date Order',
    'Order of day, month, and year in date displays.' => 'Order of day, month, and year in date displays.',
    'Day-Month-Year (31/01/2026)' => 'Day-Month-Year (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Month-Day-Year (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'Year-Month-Day (2026/01/31)',
    'Date Separator' => 'Date Separator',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Character between numeric date parts. Only applies when month format is numeric.',
    'Slash (31/01/2026)' => 'Slash (31/01/2026)',
    'Dash (31-01-2026)' => 'Dash (31-01-2026)',
    'Dot (31.01.2026)' => 'Dot (31.01.2026)',
    'Show Seconds' => 'Show Seconds',
    'Whether to include seconds in time displays by default.' => 'Whether to include seconds in time displays by default.',
    'No (14:30)' => 'No (14:30)',
    'Yes (14:30:25)' => 'Yes (14:30:25)',
    'Use global default' => 'Use global default',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/field-items-per-page.twig)
    'Items Per Page' => 'Items Per Page',
    'Number of items to display per page in lists.' => 'Number of items to display per page in lists.',

    // Plugin name field (shared via _partials/field-plugin-name.twig)
    'Plugin Name' => 'Plugin Name',
    'The name of the plugin as it appears in the Control Panel menu.' => 'The name of the plugin as it appears in the Control Panel menu.',

    // Log level field (shared via _partials/field-log-level.twig)
    'Log Level' => 'Log Level',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Choose what types of messages to log. Debug level requires devMode to be enabled.',
    'Error (Critical errors only)' => 'Error (Critical errors only)',
    'Warning (Errors and warnings)' => 'Warning (Errors and warnings)',
    'Info (General information)' => 'Info (General information)',
    'Debug (Detailed debugging)' => 'Debug (Detailed debugging)',

    // Date range settings (shared via _partials/cascade-date-range-settings.twig)
    'Default Date Range' => 'Default Date Range',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Default time window applied to analytics, logs, and dashboard pages in this plugin.',

    // Analytics layout (shared via _layouts/cp-analytics.twig + _partials/analytics-panel.twig)
    'Analytics' => 'Analytics',
    'All Sites' => 'All Sites',
    'Loading' => 'Loading',

    // Export format settings (shared via _partials/cascade-export-format-settings.twig)
    'CSV Export' => 'CSV Export',
    'JSON Export' => 'JSON Export',
    'Excel Export' => 'Excel Export',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Whether the CSV export option appears in this plugin\'s export menus.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Whether the JSON export option appears in this plugin\'s export menus.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Whether the Excel export option appears in this plugin\'s export menus.',
    'Enabled' => 'Enabled',

    // Copy-to-clipboard controls (shared via _components/secret-reveal.twig, copy-input.twig, setup-task.twig + _partials/env-command-error.twig)
    'Copy' => 'Copy',
    'Copied!' => 'Copied!',
    'Failed to copy to clipboard' => 'Failed to copy to clipboard',

    // IP hash salt setup guidance (consumed by plugin setup templates + _partials/env-command-error.twig)
    'Configuration Required' => 'Configuration Required',
    'IP hash salt is missing.' => 'IP hash salt is missing.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'Analytics tracking requires a secure salt for privacy protection.',
    'Run one of these commands in your terminal:' => 'Run one of these commands in your terminal:',
    'Standard:' => 'Standard:',
    'DDEV:' => 'DDEV:',
    'This will automatically add {envVar} to your .env file.' => 'This will automatically add {envVar} to your .env file.',
    'Warning:' => 'Warning:',
    'Copy the same salt to staging and production environments.' => 'Copy the same salt to staging and production environments.',

    // Setup incomplete notice (shared via _components/setup-incomplete.twig)
    'Setup incomplete' => 'Setup incomplete',
    'Complete setup before using this plugin.' => 'Complete setup before using this plugin.',
    'Open setup' => 'Open setup',

    // Error summary (shared via _partials/error-summary.twig)
    'error' => 'error',
    'Found {count, number} {count, plural, =1{error} other{errors}}' => 'Found {count, number} {count, plural, =1{error} other{errors}}',

    // Utilities layout (shared via _layouts/cp-utilities.twig)
    'System Overview' => 'System Overview',
    'Quick Actions' => 'Quick Actions',

    // Storage volume validation
    'Selected volume not found.' => 'Selected volume not found.',
    'Selected volume must use a local filesystem.' => 'Selected volume must use a local filesystem.',
    'Local backup volumes cannot resolve inside @webroot.' => 'Local backup volumes cannot resolve inside @webroot.',
];
