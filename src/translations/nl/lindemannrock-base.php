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
    'Custom Range' => 'Aangepast bereik',

    // Schedule options
    'Disabled' => 'Uitgeschakeld',
    'Every 6 Hours' => 'Elke 6 uur',
    'Every 12 Hours' => 'Elke 12 uur',
    'Daily' => 'Dagelijks',
    'Daily at 2:00 AM' => 'Dagelijks om 02:00',
    'Weekly' => 'Wekelijks',
    'Monthly' => 'Maandelijks',
    'Every 2 Months' => 'Elke 2 maanden',
    'Quarterly' => 'Per kwartaal',
    'Every 6 Months' => 'Elke 6 maanden',
    'Yearly' => 'Jaarlijks',

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

    // Date format settings (shared via _partials/cascade-date-format-settings.twig + _partials/cascade-base-overrides.twig)
    'Base Plugin Overrides' => 'Basis-plugin-overschrijvingen',
    'Settings marked "Globale standaardinstelling gebruiken" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply.' => 'Instellingen gemarkeerd als „Globale standaardinstelling gebruiken" worden overgenomen uit <code>config/lindemannrock-base.php</code>. Als dat bestand (of de specifieke sleutel) ontbreekt, gelden de vaste standaardwaarden.',
    'Time' => 'Tijd',
    'Date' => 'Datum',
    'Time Format' => 'Tijdnotatie',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'Bepaalt hoe tijden worden weergegeven in deze plugin (12-uurs met AM/PM of 24-uurs).',
    '24-hour (14:30)' => '24-uurs (14:30)',
    '12-hour (2:30 PM)' => '12-uurs (2:30 PM)',
    'Month Format' => 'Maandnotatie',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'Bepaalt hoe maanden worden weergegeven in datums: numeriek (01), kort (Jan) of lang (January).',
    'Numeric (01)' => 'Numeriek (01)',
    'Short (Jan)' => 'Kort (Jan)',
    'Long (January)' => 'Lang (January)',
    'Date Order' => 'Datumvolgorde',
    'Order of day, month, and year in date displays.' => 'Volgorde van dag, maand en jaar in datumweergaven.',
    'Day-Month-Year (31/01/2026)' => 'Dag-Maand-Jaar (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Maand-Dag-Jaar (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'Jaar-Maand-Dag (2026/01/31)',
    'Date Separator' => 'Datumscheidingsteken',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Teken tussen numerieke datumonderdelen. Geldt alleen wanneer de maandnotatie numeriek is.',
    'Slash (31/01/2026)' => 'Schuine streep (31/01/2026)',
    'Dash (31-01-2026)' => 'Koppelteken (31-01-2026)',
    'Dot (31.01.2026)' => 'Punt (31.01.2026)',
    'Show Seconds' => 'Seconden tonen',
    'Whether to include seconds in time displays by default.' => 'Bepaalt of seconden standaard worden opgenomen in tijdweergaven.',
    'No (14:30)' => 'Nee (14:30)',
    'Yes (14:30:25)' => 'Ja (14:30:25)',
    'Use global default' => 'Globale standaardinstelling gebruiken',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'Deze instelling wordt overschreven door de instelling <code>{setting}</code> in <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/field-items-per-page.twig)
    'Items Per Page' => 'Items per pagina',

    // Plugin name field (shared via _partials/field-plugin-name.twig)
    'Plugin Name' => 'Pluginnaam',
    'The name of the plugin as it appears in the Control Panel menu.' => 'De naam van de plugin zoals deze verschijnt in het bedieningspaneelmenu.',

    // Log level field (shared via _partials/field-log-level.twig)
    'Log Level' => 'Logboekniveau',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Kies welke soorten berichten worden gelogd. Het debugniveau vereist dat devMode is ingeschakeld.',
    'Error (Critical errors only)' => 'Fout (alleen kritieke fouten)',
    'Warning (Errors and warnings)' => 'Waarschuwing (fouten en waarschuwingen)',
    'Info (General information)' => 'Info (algemene informatie)',
    'Debug (Detailed debugging)' => 'Debug (gedetailleerde foutopsporing)',

    // Date range settings (shared via _partials/cascade-date-range-settings.twig)
    'Default Date Range' => 'Standaard datumbereik',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Standaard tijdvenster dat wordt toegepast op de analyse-, logboek- en dashboardpagina\'s van deze plugin.',

    // Export format settings (shared via _partials/cascade-export-format-settings.twig)
    'CSV Export' => 'CSV-export',
    'JSON Export' => 'JSON-export',
    'Excel Export' => 'Excel-export',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Bepaalt of de CSV-exportoptie verschijnt in de exportmenu\'s van deze plugin.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Bepaalt of de JSON-exportoptie verschijnt in de exportmenu\'s van deze plugin.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Bepaalt of de Excel-exportoptie verschijnt in de exportmenu\'s van deze plugin.',
    'Enabled' => 'Ingeschakeld',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Kopiëren',
    'Copied!' => 'Gekopieerd!',
    'Failed to copy to clipboard' => 'Kopiëren naar klembord mislukt',
];
