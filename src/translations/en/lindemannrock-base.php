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

    // Date ranges
    'Today' => 'Today',
    'Yesterday' => 'Yesterday',
    'Last 7 days' => 'Last 7 days',
    'Last 30 days' => 'Last 30 days',
    'Last 90 days' => 'Last 90 days',
    'This month' => 'This month',
    'Last month' => 'Last month',
    'This year' => 'This year',
    'Last year' => 'Last year',
    'All time' => 'All time',
    'Custom Range' => 'Custom Range',

    // Schedule options
    'Disabled' => 'Disabled',
    'Every 6 Hours' => 'Every 6 Hours',
    'Every 12 Hours' => 'Every 12 Hours',
    'Daily' => 'Daily',
    'Daily at 2:00 AM' => 'Daily at 2:00 AM',
    'Weekly' => 'Weekly',
    'Monthly' => 'Monthly',
    'Every 2 Months' => 'Every 2 Months',
    'Quarterly' => 'Quarterly',
    'Every 6 Months' => 'Every 6 Months',
    'Yearly' => 'Yearly',

    // Export + editions
    'Nothing to export.' => 'Nothing to export.',
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
    'Cannot modify config items' => 'Cannot modify config items',
    'Previous Page' => 'Previous Page',
    'Next Page' => 'Next Page',
    'no' => 'no',
    'of' => 'of',
    'Auto-refresh' => 'Auto-refresh',

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
    'Clear history' => 'Clear history',
    'No import history yet.' => 'No import history yet.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Are you sure you want to clear all import logs? This action cannot be undone.',
    'Failed to clear history.' => 'Failed to clear history.',
    'Loading backup history...' => 'Loading backup history...',
    'No backups found.' => 'No backups found.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
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

    // Date format settings (shared via _partials/date-format-settings.twig)
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

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Copy',
    'Copied!' => 'Copied!',
    'Failed to copy to clipboard' => 'Failed to copy to clipboard',
];
