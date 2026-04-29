<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installationen lyckades',
    'Plugin installed' => 'Plugin installerat',
    'Version' => 'Version',
    'Continue' => 'Fortsätt',
    'Open plugin' => 'Öppna plugin',
    'Open settings' => 'Öppna inställningar',
    'Close dialog' => 'Stäng dialogruta',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Allt är klart. Du kan börja konfigurera pluginet direkt.',

    // Date ranges
    'Today' => 'Idag',
    'Yesterday' => 'Igår',
    'Last 7 days' => 'Senaste 7 dagarna',
    'Last 30 days' => 'Senaste 30 dagarna',
    'Last 90 days' => 'Senaste 90 dagarna',
    'This month' => 'Den här månaden',
    'Last month' => 'Förra månaden',
    'This year' => 'Det här året',
    'Last year' => 'Förra året',
    'All time' => 'Hela perioden',

    // Export + editions
    'Nothing to export.' => 'Inget att exportera.',
    '{feature} requires the {edition} edition.' => '{feature} kräver {edition}-utgåvan.',
    'This feature requires the {edition} edition.' => 'Den här funktionen kräver {edition}-utgåvan.',
    'Export' => 'Export',
    'Export as Excel' => 'Exportera som Excel',
    'Export as CSV' => 'Exportera som CSV',
    'Export as JSON' => 'Exportera som JSON',

    // Search + filters
    'Search' => 'Sök',
    'Search...' => 'Sök...',
    'Clear' => 'Rensa',
    'Clear search' => 'Rensa sökning',
    'All' => 'Alla',

    // Table view + pagination
    'No items found.' => 'Inga objekt hittades.',
    'View' => 'Vy',
    'Sort by' => 'Sortera efter',
    'Sort attribute' => 'Sorteringsattribut',
    'Sort direction' => 'Sorteringsriktning',
    'Sort ascending' => 'Stigande sortering',
    'Sort descending' => 'Fallande sortering',
    'Table Columns' => 'Tabellkolumner',
    'Use defaults' => 'Använd standardvärden',
    'Close' => 'Stäng',
    'New' => 'Ny',
    'Action' => 'Åtgärd',
    'Actions' => 'Åtgärder',
    'Select all' => 'Välj alla',
    'Select' => 'Välj',
    'Cannot modify config items' => 'Konfigurationsobjekt kan inte ändras',
    'Previous Page' => 'Föregående sida',
    'Next Page' => 'Nästa sida',
    'no' => 'nej',
    'of' => 'av',
    'Auto-refresh' => 'Automatisk uppdatering',

    // Import + backups
    'Import from CSV' => 'Importera från CSV',
    'CSV File' => 'CSV-fil',
    'CSV Delimiter' => 'CSV-avgränsare',
    'Auto (detect)' => 'Auto (identifiera)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Semikolon (;)',
    'Tab' => 'Tabb',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Skapa säkerhetskopia innan import',
    'Upload & Map Columns' => 'Ladda upp och mappa kolumner',
    'CSV Import' => 'CSV-import',
    'Alternate Import' => 'Alternativ import',
    'Import History' => 'Importhistorik',
    'Clear history' => 'Rensa historik',
    'No import history yet.' => 'Ingen importhistorik ännu.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Är du säker på att du vill rensa alla importloggar? Den här åtgärden kan inte ångras.',
    'Failed to clear history.' => 'Det gick inte att rensa historiken.',
    'Loading backup history...' => 'Laddar säkerhetskopieringshistorik...',
    'No backups found.' => 'Inga säkerhetskopior hittades.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Geo-leverantör',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Välj leverantör för geo-IP-uppslag. HTTPS-leverantörer rekommenderas av integritetsskäl.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betald)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1 000/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50 000/månad gratis)',
    'API Key' => 'API-nyckel',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Valfritt. Krävs för betalda nivåer (aktiverar HTTPS för ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Det här åsidosätts av inställningen <code>geoProvider</code> i <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Det här åsidosätts av inställningen <code>geoApiKey</code> i <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com:s gratisplan använder HTTP. IP-adresser överförs okrypterade. Lägg till en API-nyckel för HTTPS (Pro-nivå) eller byt till ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP gratisplan (45 förfrågningar/min). Lägg till API-nyckel för HTTPS (Pro-nivå, $13/month). IP-adresser överförs okrypterade utan API-nyckel.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS med 1 000 gratis förfrågningar/dag. API-nyckel är valfri (ökar hastighetsgränser).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS med 50 000 gratis förfrågningar/månad. API-nyckel är valfri (ökar hastighetsgränser).',
];
