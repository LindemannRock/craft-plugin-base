<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Succesvol geïnstalleerd',
    'Plugin installed' => 'Plugin geïnstalleerd',
    'Version' => 'Versie',
    'Continue' => 'Doorgaan',
    'Open plugin' => 'Plugin openen',
    'Open settings' => 'Instellingen openen',
    'Close dialog' => 'Dialoogvenster sluiten',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Alles is ingesteld. U kunt de plugin direct configureren.',

    // Date ranges
    'Today' => 'Vandaag',
    'Yesterday' => 'Gisteren',
    'Last 7 days' => 'Afgelopen 7 dagen',
    'Last 30 days' => 'Afgelopen 30 dagen',
    'Last 90 days' => 'Afgelopen 90 dagen',
    'This month' => 'Deze maand',
    'Last month' => 'Vorige maand',
    'This year' => 'Dit jaar',
    'Last year' => 'Vorig jaar',
    'All time' => 'Alle tijd',

    // Export + editions
    'Nothing to export.' => 'Niets te exporteren.',
    '{feature} requires the {edition} edition.' => '{feature} vereist de {edition}-editie.',
    'This feature requires the {edition} edition.' => 'Deze functie vereist de {edition}-editie.',
    'Export' => 'Export',
    'Export as Excel' => 'Exporteren als Excel',
    'Export as CSV' => 'Exporteren als CSV',
    'Export as JSON' => 'Exporteren als JSON',

    // Search + filters
    'Search' => 'Zoeken',
    'Search...' => 'Zoeken...',
    'Clear' => 'Wissen',
    'Clear search' => 'Zoekopdracht wissen',
    'All' => 'Alle',

    // Table view + pagination
    'No items found.' => 'Geen items gevonden.',
    'View' => 'Weergave',
    'Sort by' => 'Sorteren op',
    'Sort attribute' => 'Sorteerattribuut',
    'Sort direction' => 'Sorteerrichting',
    'Sort ascending' => 'Oplopend sorteren',
    'Sort descending' => 'Aflopend sorteren',
    'Table Columns' => 'Tabelkolommen',
    'Use defaults' => 'Standaardwaarden gebruiken',
    'Close' => 'Sluiten',
    'New' => 'Nieuw',
    'Action' => 'Actie',
    'Actions' => 'Acties',
    'Select all' => 'Alles selecteren',
    'Select' => 'Selecteren',
    'Cannot modify config items' => 'Configuratie-items kunnen niet worden gewijzigd',
    'Previous Page' => 'Vorige pagina',
    'Next Page' => 'Volgende pagina',
    'no' => 'nee',
    'of' => 'van',
    'Auto-refresh' => 'Automatisch vernieuwen',

    // Import + backups
    'Import from CSV' => 'Importeren uit CSV',
    'CSV File' => 'CSV-bestand',
    'CSV Delimiter' => 'CSV-scheidingsteken',
    'Auto (detect)' => 'Auto (detecteren)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Puntkomma (;)',
    'Tab' => 'Tab',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Back-up maken vóór import',
    'Upload & Map Columns' => 'Uploaden & kolommen koppelen',
    'CSV Import' => 'CSV-import',
    'Alternate Import' => 'Alternatieve import',
    'Import History' => 'Importgeschiedenis',
    'Clear history' => 'Geschiedenis wissen',
    'No import history yet.' => 'Nog geen importgeschiedenis.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Weet u zeker dat u alle importlogboeken wilt wissen? Deze actie kan niet ongedaan worden gemaakt.',
    'Failed to clear history.' => 'Geschiedenis wissen mislukt.',
    'Loading backup history...' => 'Back-upgeschiedenis laden...',
    'No backups found.' => 'Geen back-ups gevonden.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Geo-aanbieder',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Selecteer de geo-IP-opzoekprovider. HTTPS-providers aanbevolen voor privacy.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betaald)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/maand gratis)',
    'API Key' => 'API-sleutel',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Optioneel. Vereist voor betaalde abonnementen (schakelt HTTPS in voor ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Deze instelling wordt overschreven door de instelling <code>geoProvider</code> in <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Deze instelling wordt overschreven door de instelling <code>geoApiKey</code> in <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'Het gratis abonnement van ip-api.com gebruikt HTTP. IP-adressen worden onversleuteld verzonden. Voeg een API-sleutel toe voor HTTPS (Pro-abonnement) of schakel over naar ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP gratis abonnement (45 verzoeken/min). API-sleutel toevoegen voor HTTPS (Pro-abonnement, $13/maand). IP-adressen worden onversleuteld verzonden zonder API-sleutel.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS met 1.000 gratis verzoeken/dag. API-sleutel optioneel (verhoogt limieten).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS met 50.000 gratis verzoeken/maand. API-sleutel optioneel (verhoogt limieten).',
];
