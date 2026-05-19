<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installeret',
    'Plugin installed' => 'Plugin installeret',
    'Version' => 'Version',
    'Continue' => 'Fortsæt',
    'Open plugin' => 'Åbn plugin',
    'Open settings' => 'Åbn indstillinger',
    'Close dialog' => 'Luk dialogboks',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Alt er klar. Du kan begynde at konfigurere pluginet med det samme.',

    // Date ranges
    'Today' => 'I dag',
    'Yesterday' => 'I går',
    'Last 7 days' => 'Seneste 7 dage',
    'Last 30 days' => 'Seneste 30 dage',
    'Last 90 days' => 'Seneste 90 dage',
    'This month' => 'Denne måned',
    'Last month' => 'Forrige måned',
    'This year' => 'Dette år',
    'Last year' => 'Forrige år',
    'All time' => 'Hele perioden',
    'Custom Range' => 'Brugerdefineret interval',

    // Schedule options
    'Disabled' => 'Deaktiveret',
    'Every 6 Hours' => 'Hver 6. time',
    'Every 12 Hours' => 'Hver 12. time',
    'Daily' => 'Dagligt',
    'Daily at 2:00 AM' => 'Dagligt kl. 02:00',
    'Weekly' => 'Ugentligt',
    'Monthly' => 'Månedligt',
    'Every 2 Months' => 'Hver 2. måned',
    'Quarterly' => 'Kvartalsvis',
    'Every 6 Months' => 'Hver 6. måned',
    'Yearly' => 'Årligt',

    // Export + editions
    'Nothing to export.' => 'Intet at eksportere.',
    '{feature} requires the {edition} edition.' => '{feature} kræver {edition}-udgaven.',
    'This feature requires the {edition} edition.' => 'Denne funktion kræver {edition}-udgaven.',
    'Export' => 'Export',
    'Export as Excel' => 'Eksporter som Excel',
    'Export as CSV' => 'Eksporter som CSV',
    'Export as JSON' => 'Eksporter som JSON',

    // Search + filters
    'Search' => 'Søg',
    'Search...' => 'Søg...',
    'Clear' => 'Ryd',
    'Clear search' => 'Ryd søgning',
    'All' => 'Alle',

    // Table view + pagination
    'No items found.' => 'Ingen elementer fundet.',
    'View' => 'Visning',
    'Sort by' => 'Sortér efter',
    'Sort attribute' => 'Sorteringsattribut',
    'Sort direction' => 'Sorteringsretning',
    'Sort ascending' => 'Stigende sortering',
    'Sort descending' => 'Faldende sortering',
    'Table Columns' => 'Tabelkolonner',
    'Use defaults' => 'Brug standardværdier',
    'Close' => 'Luk',
    'New' => 'Ny',
    'Action' => 'Handling',
    'Actions' => 'Handlinger',
    'Select all' => 'Vælg alle',
    'Select' => 'Vælg',
    'Cannot modify config items' => 'Konfigurationselementer kan ikke ændres',
    'Previous Page' => 'Forrige side',
    'Next Page' => 'Næste side',
    'no' => 'nej',
    'of' => 'af',
    'Auto-refresh' => 'Automatisk opdatering',

    // Import + backups
    'Import from CSV' => 'Importer fra CSV',
    'CSV File' => 'CSV-fil',
    'CSV Delimiter' => 'CSV-separator',
    'Auto (detect)' => 'Auto (registrer)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Semikolon (;)',
    'Tab' => 'Tabulator',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Opret sikkerhedskopi inden import',
    'Upload & Map Columns' => 'Upload og tilknyt kolonner',
    'CSV Import' => 'CSV-import',
    'Alternate Import' => 'Alternativ import',
    'Import History' => 'Importhistorik',
    'Clear history' => 'Ryd historik',
    'No import history yet.' => 'Ingen importhistorik endnu.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Er du sikker på, at du vil rydde alle importlogfiler? Denne handling kan ikke fortrydes.',
    'Failed to clear history.' => 'Kunne ikke rydde historik.',
    'Loading backup history...' => 'Indlæser sikkerhedskopieringshistorik...',
    'No backups found.' => 'Ingen sikkerhedskopier fundet.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Geo-udbyder',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Vælg geo-IP-opslagsudbyder. HTTPS-udbydere anbefales af hensyn til privatlivet.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betalt)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1.000/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50.000/måned gratis)',
    'API Key' => 'API-nøgle',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Valgfrit. Påkrævet for betalte niveauer (aktiverer HTTPS for ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Dette tilsidesættes af indstillingen <code>geoProvider</code> i <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Dette tilsidesættes af indstillingen <code>geoApiKey</code> i <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com gratisabonnementet bruger HTTP. IP-adresser overføres ukrypteret. Tilføj en API-nøgle for HTTPS (Pro-niveau) eller skift til ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP-gratisniveau (45 forespørgsler/min). Tilføj API-nøgle for HTTPS (Pro-niveau, $13/month). IP-adresser overføres ukrypteret uden API-nøgle.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS med 1.000 gratis forespørgsler/dag. API-nøgle valgfrit (øger hastighedsgrænserne).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS med 50.000 gratis forespørgsler/måned. API-nøgle valgfrit (øger hastighedsgrænserne).',

    // Date format settings (shared via _partials/date-format-settings.twig + _partials/base-overrides.twig)
    'Base Plugin Overrides' => 'Tilsidesættelser af basis-plugin',
    'Settings marked "Brug global standardindstilling" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply.' => 'Indstillinger markeret som „Brug global standardindstilling" arver fra <code>config/lindemannrock-base.php</code>. Hvis den fil (eller den specifikke nøgle) mangler, gælder de indbyggede standardværdier.',
    'Time' => 'Tid',
    'Date' => 'Dato',
    'Time Format' => 'Tidsformat',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'Angiver, hvordan klokkeslæt vises i dette plugin (12-timers med AM/PM eller 24-timers).',
    '24-hour (14:30)' => '24-timer (14:30)',
    '12-hour (2:30 PM)' => '12-timer (2:30 PM)',
    'Month Format' => 'Månedformat',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'Angiver, hvordan måneder vises i datoer: numerisk (01), forkortet (Jan) eller fuldt (January).',
    'Numeric (01)' => 'Numerisk (01)',
    'Short (Jan)' => 'Forkortet (Jan)',
    'Long (January)' => 'Fuldt (January)',
    'Date Order' => 'Datorækkefølge',
    'Order of day, month, and year in date displays.' => 'Rækkefølge for dag, måned og år i datovisninger.',
    'Day-Month-Year (31/01/2026)' => 'Dag-Måned-År (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Måned-Dag-År (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'År-Måned-Dag (2026/01/31)',
    'Date Separator' => 'Datoseparator',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Tegn mellem numeriske datodele. Gælder kun, når månedformatet er numerisk.',
    'Slash (31/01/2026)' => 'Skråstreg (31/01/2026)',
    'Dash (31-01-2026)' => 'Bindestreg (31-01-2026)',
    'Dot (31.01.2026)' => 'Punktum (31.01.2026)',
    'Show Seconds' => 'Vis sekunder',
    'Whether to include seconds in time displays by default.' => 'Angiver, om sekunder som standard skal inkluderes i tidsvisninger.',
    'No (14:30)' => 'Nej (14:30)',
    'Yes (14:30:25)' => 'Ja (14:30:25)',
    'Use global default' => 'Brug global standardindstilling',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'Dette tilsidesættes af indstillingen <code>{setting}</code> i <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/items-per-page-field.twig)
    'Items Per Page' => 'Elementer pr. side',

    // Date range settings (shared via _partials/date-range-settings.twig)
    'Default Date Range' => 'Standarddatointerval',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Standardtidsvindue der anvendes på analyse-, log- og dashboardsider i dette plugin.',

    // Export format settings (shared via _partials/export-format-settings.twig)
    'CSV Export' => 'CSV-eksport',
    'JSON Export' => 'JSON-eksport',
    'Excel Export' => 'Excel-eksport',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Bestemmer om CSV-eksportindstillingen vises i eksportmenuerne for dette plugin.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Bestemmer om JSON-eksportindstillingen vises i eksportmenuerne for dette plugin.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Bestemmer om Excel-eksportindstillingen vises i eksportmenuerne for dette plugin.',
    'Enabled' => 'Aktiveret',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Kopiér',
    'Copied!' => 'Kopieret!',
    'Failed to copy to clipboard' => 'Kopiering til udklipsholder mislykkedes',
];
