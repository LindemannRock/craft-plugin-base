<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installato con successo',
    'Plugin installed' => 'Plugin installato',
    'Version' => 'Versione',
    'Continue' => 'Continua',
    'Open plugin' => 'Apri plugin',
    'Open settings' => 'Apri impostazioni',
    'Close dialog' => 'Chiudi finestra',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Tutto è pronto. Puoi iniziare a configurare il plugin immediatamente.',

    // Date ranges
    'Today' => 'Oggi',
    'Yesterday' => 'Ieri',
    'Last 7 days' => 'Ultimi 7 giorni',
    'Last 30 days' => 'Ultimi 30 giorni',
    'Last 90 days' => 'Ultimi 90 giorni',
    'This month' => 'Questo mese',
    'Last month' => 'Il mese scorso',
    'This year' => 'Quest\'anno',
    'Last year' => 'L\'anno scorso',
    'All time' => 'Tutto il periodo',
    'Custom Range' => 'Intervallo personalizzato',

    // Export + editions
    'Nothing to export.' => 'Niente da esportare.',
    '{feature} requires the {edition} edition.' => '{feature} richiede l\'edizione {edition}.',
    'This feature requires the {edition} edition.' => 'Questa funzione richiede l\'edizione {edition}.',
    'Export' => 'Export',
    'Export as Excel' => 'Esporta come Excel',
    'Export as CSV' => 'Esporta come CSV',
    'Export as JSON' => 'Esporta come JSON',

    // Search + filters
    'Search' => 'Cerca',
    'Search...' => 'Cerca...',
    'Clear' => 'Cancella',
    'Clear search' => 'Cancella ricerca',
    'All' => 'Tutti',

    // Table view + pagination
    'No items found.' => 'Nessun elemento trovato.',
    'View' => 'Vista',
    'Sort by' => 'Ordina per',
    'Sort attribute' => 'Attributo di ordinamento',
    'Sort direction' => 'Direzione di ordinamento',
    'Sort ascending' => 'Ordine crescente',
    'Sort descending' => 'Ordine decrescente',
    'Table Columns' => 'Colonne della tabella',
    'Use defaults' => 'Usa valori predefiniti',
    'Close' => 'Chiudi',
    'New' => 'Nuovo',
    'Action' => 'Azione',
    'Actions' => 'Azioni',
    'Select all' => 'Seleziona tutto',
    'Select' => 'Seleziona',
    'Cannot modify config items' => 'Impossibile modificare gli elementi di configurazione',
    'Previous Page' => 'Pagina precedente',
    'Next Page' => 'Pagina successiva',
    'no' => 'no',
    'of' => 'di',
    'Auto-refresh' => 'Aggiornamento automatico',

    // Import + backups
    'Import from CSV' => 'Importa da CSV',
    'CSV File' => 'File CSV',
    'CSV Delimiter' => 'Delimitatore CSV',
    'Auto (detect)' => 'Auto (rileva)',
    'Comma (,)' => 'Virgola (,)',
    'Semicolon (;)' => 'Punto e virgola (;)',
    'Tab' => 'Tab',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Crea backup prima dell\'importazione',
    'Upload & Map Columns' => 'Carica e mappa le colonne',
    'CSV Import' => 'Importazione CSV',
    'Alternate Import' => 'Importazione alternativa',
    'Import History' => 'Cronologia importazioni',
    'Clear history' => 'Cancella cronologia',
    'No import history yet.' => 'Nessuna cronologia di importazione ancora.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Sei sicuro di voler cancellare tutti i log di importazione? Questa azione non può essere annullata.',
    'Failed to clear history.' => 'Impossibile cancellare la cronologia.',
    'Loading backup history...' => 'Caricamento della cronologia dei backup...',
    'No backups found.' => 'Nessun backup trovato.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Provider geo',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Selezionare il provider di geolocalizzazione IP. Si raccomandano provider HTTPS per la privacy.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratuito, HTTPS a pagamento)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1.000/giorno gratuiti)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50.000/mese gratuiti)',
    'API Key' => 'API Key',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Facoltativa. Obbligatoria per i piani a pagamento (abilita HTTPS per ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Questa impostazione è sovrascritta dall\'opzione <code>geoProvider</code> in <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Questa impostazione è sovrascritta dall\'opzione <code>geoApiKey</code> in <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'Il piano gratuito di ip-api.com utilizza HTTP. Gli indirizzi IP saranno trasmessi senza cifratura. Aggiungere una API Key per HTTPS (piano Pro) o passare a ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: piano gratuito HTTP (45 richieste/min). Aggiungere una API Key per HTTPS (piano Pro, $13/month). Gli indirizzi IP sono trasmessi senza cifratura senza API Key.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS con 1.000 richieste gratuite/giorno. API Key facoltativa (aumenta i limiti di utilizzo).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS con 50.000 richieste gratuite/mese. API Key facoltativa (aumenta i limiti di utilizzo).',
];
