<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installert',
    'Plugin installed' => 'Plugin installert',
    'Version' => 'Versjon',
    'Continue' => 'Fortsett',
    'Open plugin' => 'Åpne plugin',
    'Open settings' => 'Åpne innstillinger',
    'Close dialog' => 'Lukk dialogboks',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Alt er klart. Du kan begynne å konfigurere pluginet med en gang.',

    // Date ranges
    'Today' => 'I dag',
    'Yesterday' => 'I går',
    'Last 7 days' => 'Siste 7 dager',
    'Last 30 days' => 'Siste 30 dager',
    'Last 90 days' => 'Siste 90 dager',
    'This month' => 'Denne måneden',
    'Last month' => 'Forrige måned',
    'This year' => 'Dette året',
    'Last year' => 'Forrige år',
    'All time' => 'Hele perioden',
    'Custom Range' => 'Egendefinert område',

    // Export + editions
    'Nothing to export.' => 'Ingenting å eksportere.',
    '{feature} requires the {edition} edition.' => '{feature} krever {edition}-utgaven.',
    'This feature requires the {edition} edition.' => 'Denne funksjonen krever {edition}-utgaven.',
    'Export' => 'Export',
    'Export as Excel' => 'Eksporter som Excel',
    'Export as CSV' => 'Eksporter som CSV',
    'Export as JSON' => 'Eksporter som JSON',

    // Search + filters
    'Search' => 'Søk',
    'Search...' => 'Søk...',
    'Clear' => 'Tøm',
    'Clear search' => 'Tøm søk',
    'All' => 'Alle',

    // Table view + pagination
    'No items found.' => 'Ingen elementer funnet.',
    'View' => 'Vis',
    'Sort by' => 'Sorter etter',
    'Sort attribute' => 'Sorteringsattributt',
    'Sort direction' => 'Sorteringsretning',
    'Sort ascending' => 'Stigende sortering',
    'Sort descending' => 'Synkende sortering',
    'Table Columns' => 'Tabellkolonner',
    'Use defaults' => 'Bruk standardverdier',
    'Close' => 'Lukk',
    'New' => 'Ny',
    'Action' => 'Handling',
    'Actions' => 'Handlinger',
    'Select all' => 'Velg alle',
    'Select' => 'Velg',
    'Cannot modify config items' => 'Kan ikke endre konfigurasjonselementer',
    'Previous Page' => 'Forrige side',
    'Next Page' => 'Neste side',
    'no' => 'nei',
    'of' => 'av',
    'Auto-refresh' => 'Automatisk oppdatering',

    // Import + backups
    'Import from CSV' => 'Importer fra CSV',
    'CSV File' => 'CSV-fil',
    'CSV Delimiter' => 'CSV-skilletegn',
    'Auto (detect)' => 'Auto (oppdag)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Semikolon (;)',
    'Tab' => 'Tabulator',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Opprett sikkerhetskopi før import',
    'Upload & Map Columns' => 'Last opp og tilordne kolonner',
    'CSV Import' => 'CSV-import',
    'Alternate Import' => 'Alternativ import',
    'Import History' => 'Importhistorikk',
    'Clear history' => 'Tøm historikk',
    'No import history yet.' => 'Ingen importhistorikk ennå.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Er du sikker på at du vil tømme alle importlogger? Denne handlingen kan ikke angres.',
    'Failed to clear history.' => 'Kunne ikke tømme historikk.',
    'Loading backup history...' => 'Laster inn sikkerhetskopieringshistorikk...',
    'No backups found.' => 'Ingen sikkerhetskopier funnet.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Geo-leverandør',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Velg geo IP-oppslagsleverandør. HTTPS-leverandører anbefales for personvern.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betalt)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/måned gratis)',
    'API Key' => 'API-nøkkel',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Valgfri. Kreves for betalte planer (aktiverer HTTPS for ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Denne innstillingen overstyres av <code>geoProvider</code> i <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Denne innstillingen overstyres av <code>geoApiKey</code> i <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com gratis plan bruker HTTP. IP-adresser overføres ukryptert. Legg til en API-nøkkel for HTTPS (Pro-plan) eller bytt til ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP gratis plan (45 forespørsler/min). Legg til API-nøkkel for HTTPS (Pro-plan, $13/måned). IP-adresser overføres ukryptert uten API-nøkkel.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS med 1 000 gratis forespørsler/dag. API-nøkkel valgfri (øker hastighetsgrenser).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS med 50 000 gratis forespørsler/måned. API-nøkkel valgfri (øker hastighetsgrenser).',
];
