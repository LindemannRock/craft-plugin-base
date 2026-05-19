<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Installé avec succès',
    'Plugin installed' => 'Plugin installé',
    'Version' => 'Version',
    'Continue' => 'Continuer',
    'Open plugin' => 'Ouvrir le plugin',
    'Open settings' => 'Ouvrir les paramètres',
    'Close dialog' => 'Fermer la boîte de dialogue',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Tout est en place. Vous pouvez commencer à configurer le plugin immédiatement.',

    // Date ranges
    'Today' => 'Aujourd\'hui',
    'Yesterday' => 'Hier',
    'Last 7 days' => '7 derniers jours',
    'Last 30 days' => '30 derniers jours',
    'Last 90 days' => '90 derniers jours',
    'This month' => 'Ce mois-ci',
    'Last month' => 'Le mois dernier',
    'This year' => 'Cette année',
    'Last year' => 'L\'année dernière',
    'All time' => 'Toute la période',
    'Custom Range' => 'Plage personnalisée',

    // Schedule options
    'Disabled' => 'Désactivé',
    'Every 6 Hours' => 'Toutes les 6 heures',
    'Every 12 Hours' => 'Toutes les 12 heures',
    'Daily' => 'Quotidien',
    'Daily at 2:00 AM' => 'Tous les jours à 02:00',
    'Weekly' => 'Hebdomadaire',
    'Monthly' => 'Mensuel',
    'Every 2 Months' => 'Tous les 2 mois',
    'Quarterly' => 'Trimestriel',
    'Every 6 Months' => 'Tous les 6 mois',
    'Yearly' => 'Annuel',

    // Export + editions
    'Nothing to export.' => 'Rien à exporter.',
    '{feature} requires the {edition} edition.' => '{feature} nécessite l\'édition {edition}.',
    'This feature requires the {edition} edition.' => 'Cette fonctionnalité nécessite l\'édition {edition}.',
    'Export' => 'Export',
    'Export as Excel' => 'Exporter en Excel',
    'Export as CSV' => 'Exporter en CSV',
    'Export as JSON' => 'Exporter en JSON',

    // Search + filters
    'Search' => 'Rechercher',
    'Search...' => 'Rechercher...',
    'Clear' => 'Effacer',
    'Clear search' => 'Effacer la recherche',
    'All' => 'Tous',

    // Table view + pagination
    'No items found.' => 'Aucun élément trouvé.',
    'View' => 'Vue',
    'Sort by' => 'Trier par',
    'Sort attribute' => 'Attribut de tri',
    'Sort direction' => 'Sens du tri',
    'Sort ascending' => 'Tri croissant',
    'Sort descending' => 'Tri décroissant',
    'Table Columns' => 'Colonnes du tableau',
    'Use defaults' => 'Utiliser les valeurs par défaut',
    'Close' => 'Fermer',
    'New' => 'Nouveau',
    'Action' => 'Action',
    'Actions' => 'Actions',
    'Select all' => 'Tout sélectionner',
    'Select' => 'Sélectionner',
    'Cannot modify config items' => 'Impossible de modifier les éléments de configuration',
    'Previous Page' => 'Page précédente',
    'Next Page' => 'Page suivante',
    'no' => 'non',
    'of' => 'sur',
    'Auto-refresh' => 'Actualisation automatique',

    // Import + backups
    'Import from CSV' => 'Importer depuis CSV',
    'CSV File' => 'Fichier CSV',
    'CSV Delimiter' => 'Délimiteur CSV',
    'Auto (detect)' => 'Auto (détecter)',
    'Comma (,)' => 'Virgule (,)',
    'Semicolon (;)' => 'Point-virgule (;)',
    'Tab' => 'Tabulation',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Créer une sauvegarde avant l\'import',
    'Upload & Map Columns' => 'Téléverser et mapper les colonnes',
    'CSV Import' => 'Import CSV',
    'Alternate Import' => 'Import alternatif',
    'Import History' => 'Historique des imports',
    'Clear history' => 'Effacer l\'historique',
    'No import history yet.' => 'Aucun historique d\'import pour l\'instant.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Êtes-vous sûr de vouloir supprimer tous les journaux d\'import ? Cette action est irréversible.',
    'Failed to clear history.' => 'Échec de la suppression de l\'historique.',
    'Loading backup history...' => 'Chargement de l\'historique des sauvegardes...',
    'No backups found.' => 'Aucune sauvegarde trouvée.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Fournisseur géo',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Sélectionnez le fournisseur de géolocalisation IP. Les fournisseurs HTTPS sont recommandés pour la confidentialité.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratuit, HTTPS payant)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/jour gratuit)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/mois gratuit)',
    'API Key' => 'Clé API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Facultatif. Requis pour les offres payantes (active HTTPS pour ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Ce paramètre est remplacé par le paramètre <code>geoProvider</code> dans <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Ce paramètre est remplacé par le paramètre <code>geoApiKey</code> dans <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'L\'offre gratuite d\'ip-api.com utilise HTTP. Les adresses IP seront transmises non chiffrées. Ajoutez une clé API pour HTTPS (offre Pro) ou passez à ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com : offre gratuite HTTP (45 requêtes/min). Ajoutez une clé API pour HTTPS (offre Pro, 13 $/mois). Les adresses IP sont transmises non chiffrées sans clé API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co : HTTPS avec 1 000 requêtes gratuites/jour. Clé API facultative (augmente les limites de débit).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io : HTTPS avec 50 000 requêtes gratuites/mois. Clé API facultative (augmente les limites de débit).',

    // Date format settings (shared via _partials/cascade-date-format-settings.twig + _partials/cascade-base-overrides.twig)
    'Base Plugin Overrides' => 'Remplacements du plugin de base',
    'Settings marked "Use global default" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply. To customize globally, copy <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> to your project\'s <code>config/</code> directory.' => 'Les paramètres marqués « Utiliser le paramètre global par défaut » héritent de <code>config/lindemannrock-base.php</code>. Si ce fichier (ou la clé spécifique) est absent, les valeurs par défaut codées en dur s\'appliquent. Pour personnaliser globalement, copiez <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> dans le répertoire <code>config/</code> de votre projet.',
    'Time' => 'Heure',
    'Date' => 'Date',
    'Time Format' => 'Format d\'heure',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'Définit comment les heures s\'affichent dans ce plugin (format 12 heures avec AM/PM ou 24 heures).',
    '24-hour (14:30)' => '24 heures (14:30)',
    '12-hour (2:30 PM)' => '12 heures (2:30 PM)',
    'Month Format' => 'Format du mois',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'Définit comment les mois apparaissent dans les dates : numérique (01), abrégé (Jan) ou complet (January).',
    'Numeric (01)' => 'Numérique (01)',
    'Short (Jan)' => 'Abrégé (Jan)',
    'Long (January)' => 'Complet (January)',
    'Date Order' => 'Ordre de la date',
    'Order of day, month, and year in date displays.' => 'Ordre du jour, du mois et de l\'année dans les affichages de dates.',
    'Day-Month-Year (31/01/2026)' => 'Jour-Mois-Année (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Mois-Jour-Année (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'Année-Mois-Jour (2026/01/31)',
    'Date Separator' => 'Séparateur de date',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Caractère entre les parties numériques de la date. S\'applique uniquement lorsque le format du mois est numérique.',
    'Slash (31/01/2026)' => 'Barre oblique (31/01/2026)',
    'Dash (31-01-2026)' => 'Tiret (31-01-2026)',
    'Dot (31.01.2026)' => 'Point (31.01.2026)',
    'Show Seconds' => 'Afficher les secondes',
    'Whether to include seconds in time displays by default.' => 'Détermine si les secondes sont incluses par défaut dans les affichages d\'heure.',
    'No (14:30)' => 'Non (14:30)',
    'Yes (14:30:25)' => 'Oui (14:30:25)',
    'Use global default' => 'Utiliser le paramètre global par défaut',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'Ce paramètre est remplacé par le paramètre <code>{setting}</code> dans <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/field-items-per-page.twig)
    'Items Per Page' => 'Éléments par page',
    'Number of items to display per page in lists.' => 'Nombre d\'éléments à afficher par page dans les listes.',

    // Plugin name field (shared via _partials/field-plugin-name.twig)
    'Plugin Name' => 'Nom du plugin',
    'The name of the plugin as it appears in the Control Panel menu.' => 'Le nom du plugin tel qu\'il apparaît dans le menu du panneau de contrôle.',

    // Log level field (shared via _partials/field-log-level.twig)
    'Log Level' => 'Niveau de journalisation',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Choisissez les types de messages à journaliser. Le niveau Debug nécessite que devMode soit activé.',
    'Error (Critical errors only)' => 'Erreur (erreurs critiques uniquement)',
    'Warning (Errors and warnings)' => 'Avertissement (erreurs et avertissements)',
    'Info (General information)' => 'Info (informations générales)',
    'Debug (Detailed debugging)' => 'Debug (débogage détaillé)',

    // Date range settings (shared via _partials/cascade-date-range-settings.twig)
    'Default Date Range' => 'Plage de dates par défaut',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Fenêtre temporelle par défaut appliquée aux pages d\'analytique, de journaux et de tableau de bord de ce plugin.',

    // Export format settings (shared via _partials/cascade-export-format-settings.twig)
    'CSV Export' => 'Export CSV',
    'JSON Export' => 'Export JSON',
    'Excel Export' => 'Export Excel',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Détermine si l\'option d\'export CSV apparaît dans les menus d\'export de ce plugin.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Détermine si l\'option d\'export JSON apparaît dans les menus d\'export de ce plugin.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Détermine si l\'option d\'export Excel apparaît dans les menus d\'export de ce plugin.',
    'Enabled' => 'Activé',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Copier',
    'Copied!' => 'Copié !',
    'Failed to copy to clipboard' => 'Échec de la copie dans le presse-papiers',
];
