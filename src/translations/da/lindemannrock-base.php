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
];
