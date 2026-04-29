<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Erfolgreich installiert',
    'Plugin installed' => 'Plugin installiert',
    'Version' => 'Version',
    'Continue' => 'Weiter',
    'Open plugin' => 'Plugin öffnen',
    'Open settings' => 'Einstellungen öffnen',
    'Close dialog' => 'Dialog schließen',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Alles ist eingerichtet. Sie können das Plugin sofort konfigurieren.',

    // Date ranges
    'Today' => 'Heute',
    'Yesterday' => 'Gestern',
    'Last 7 days' => 'Letzte 7 Tage',
    'Last 30 days' => 'Letzte 30 Tage',
    'Last 90 days' => 'Letzte 90 Tage',
    'This month' => 'Diesen Monat',
    'Last month' => 'Letzten Monat',
    'This year' => 'Dieses Jahr',
    'Last year' => 'Letztes Jahr',
    'All time' => 'Gesamte Zeit',

    // Export + editions
    'Nothing to export.' => 'Nichts zu exportieren.',
    '{feature} requires the {edition} edition.' => '{feature} erfordert die {edition}-Edition.',
    'This feature requires the {edition} edition.' => 'Diese Funktion erfordert die {edition}-Edition.',
    'Export' => 'Export',
    'Export as Excel' => 'Als Excel exportieren',
    'Export as CSV' => 'Als CSV exportieren',
    'Export as JSON' => 'Als JSON exportieren',

    // Search + filters
    'Search' => 'Suchen',
    'Search...' => 'Suchen...',
    'Clear' => 'Leeren',
    'Clear search' => 'Suche leeren',
    'All' => 'Alle',

    // Table view + pagination
    'No items found.' => 'Keine Einträge gefunden.',
    'View' => 'Ansicht',
    'Sort by' => 'Sortieren nach',
    'Sort attribute' => 'Sortierattribut',
    'Sort direction' => 'Sortierrichtung',
    'Sort ascending' => 'Aufsteigend sortieren',
    'Sort descending' => 'Absteigend sortieren',
    'Table Columns' => 'Tabellenspalten',
    'Use defaults' => 'Standard verwenden',
    'Close' => 'Schließen',
    'New' => 'Neu',
    'Action' => 'Aktion',
    'Actions' => 'Aktionen',
    'Select all' => 'Alle auswählen',
    'Select' => 'Auswählen',
    'Cannot modify config items' => 'Konfigurationselemente können nicht geändert werden',
    'Previous Page' => 'Vorherige Seite',
    'Next Page' => 'Nächste Seite',
    'no' => 'nein',
    'of' => 'von',
    'Auto-refresh' => 'Automatische Aktualisierung',

    // Import + backups
    'Import from CSV' => 'Aus CSV importieren',
    'CSV File' => 'CSV-Datei',
    'CSV Delimiter' => 'CSV-Trennzeichen',
    'Auto (detect)' => 'Auto (erkennen)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Semikolon (;)',
    'Tab' => 'Tabulator',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Sicherung vor dem Import erstellen',
    'Upload & Map Columns' => 'Hochladen & Spalten zuordnen',
    'CSV Import' => 'CSV-Import',
    'Alternate Import' => 'Alternativer Import',
    'Import History' => 'Importverlauf',
    'Clear history' => 'Verlauf leeren',
    'No import history yet.' => 'Noch kein Importverlauf vorhanden.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Sind Sie sicher, dass Sie alle Importprotokolle löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
    'Failed to clear history.' => 'Verlauf konnte nicht geleert werden.',
    'Loading backup history...' => 'Sicherungsverlauf wird geladen...',
    'No backups found.' => 'Keine Sicherungen gefunden.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Geo-Anbieter',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Geo-IP-Suchanbieter auswählen. HTTPS-Anbieter werden aus Datenschutzgründen empfohlen.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP kostenlos, HTTPS kostenpflichtig)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/Tag kostenlos)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/Monat kostenlos)',
    'API Key' => 'API-Schlüssel',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Optional. Erforderlich für kostenpflichtige Tarife (aktiviert HTTPS für ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Diese Einstellung wird durch <code>geoProvider</code> in <code>config/{handle}.php</code> überschrieben.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Diese Einstellung wird durch <code>geoApiKey</code> in <code>config/{handle}.php</code> überschrieben.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com Free-Tarif verwendet HTTP. IP-Adressen werden unverschlüsselt übertragen. Fügen Sie einen API-Schlüssel für HTTPS (Pro-Tarif) hinzu oder wechseln Sie zu ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP Free-Tarif (45 Anfragen/Min). API-Schlüssel für HTTPS hinzufügen (Pro-Tarif, 13 $/Monat). IP-Adressen werden ohne API-Schlüssel unverschlüsselt übertragen.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS mit 1.000 kostenlosen Anfragen/Tag. API-Schlüssel optional (erhöht Ratenlimits).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS mit 50.000 kostenlosen Anfragen/Monat. API-Schlüssel optional (erhöht Ratenlimits).',
];
