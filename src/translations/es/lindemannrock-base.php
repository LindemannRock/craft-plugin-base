<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Instalado correctamente',
    'Plugin installed' => 'Plugin instalado',
    'Version' => 'Versión',
    'Continue' => 'Continuar',
    'Open plugin' => 'Abrir plugin',
    'Open settings' => 'Abrir configuración',
    'Close dialog' => 'Cerrar diálogo',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Todo está listo. Puede comenzar a configurar el plugin de inmediato.',

    // Date ranges
    'Today' => 'Hoy',
    'Yesterday' => 'Ayer',
    'Last 7 days' => 'Últimos 7 días',
    'Last 30 days' => 'Últimos 30 días',
    'Last 90 days' => 'Últimos 90 días',
    'This month' => 'Este mes',
    'Last month' => 'El mes pasado',
    'This year' => 'Este año',
    'Last year' => 'El año pasado',
    'All time' => 'Todo el tiempo',
    'Custom Range' => 'Rango personalizado',

    // Schedule options
    'Disabled' => 'Desactivado',
    'Every 6 Hours' => 'Cada 6 horas',
    'Every 12 Hours' => 'Cada 12 horas',
    'Daily' => 'Diario',
    'Daily at 2:00 AM' => 'Diariamente a las 02:00',
    'Weekly' => 'Semanal',
    'Monthly' => 'Mensual',
    'Every 2 Months' => 'Cada 2 meses',
    'Quarterly' => 'Trimestral',
    'Every 6 Months' => 'Cada 6 meses',
    'Yearly' => 'Anual',

    // Export + editions
    'Nothing to export.' => 'Nada que exportar.',
    '{feature} requires the {edition} edition.' => '{feature} requiere la edición {edition}.',
    'This feature requires the {edition} edition.' => 'Esta función requiere la edición {edition}.',
    'Export' => 'Export',
    'Export as Excel' => 'Exportar como Excel',
    'Export as CSV' => 'Exportar como CSV',
    'Export as JSON' => 'Exportar como JSON',

    // Search + filters
    'Search' => 'Buscar',
    'Search...' => 'Buscar...',
    'Clear' => 'Borrar',
    'Clear search' => 'Borrar búsqueda',
    'All' => 'Todos',

    // Table view + pagination
    'No items found.' => 'No se encontraron elementos.',
    'View' => 'Vista',
    'Sort by' => 'Ordenar por',
    'Sort attribute' => 'Atributo de ordenación',
    'Sort direction' => 'Dirección de ordenación',
    'Sort ascending' => 'Orden ascendente',
    'Sort descending' => 'Orden descendente',
    'Table Columns' => 'Columnas de la tabla',
    'Use defaults' => 'Usar valores predeterminados',
    'Close' => 'Cerrar',
    'New' => 'Nuevo',
    'Action' => 'Acción',
    'Actions' => 'Acciones',
    'Select all' => 'Seleccionar todo',
    'Select' => 'Seleccionar',
    'Cannot modify config items' => 'No se pueden modificar los elementos de configuración',
    'Previous Page' => 'Página anterior',
    'Next Page' => 'Página siguiente',
    'no' => 'no',
    'of' => 'de',
    'Auto-refresh' => 'Actualización automática',

    // Import + backups
    'Import from CSV' => 'Importar desde CSV',
    'CSV File' => 'Archivo CSV',
    'CSV Delimiter' => 'Delimitador CSV',
    'Auto (detect)' => 'Auto (detectar)',
    'Comma (,)' => 'Coma (,)',
    'Semicolon (;)' => 'Punto y coma (;)',
    'Tab' => 'Tabulador',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Crear copia de seguridad antes de importar',
    'Upload & Map Columns' => 'Cargar y asignar columnas',
    'CSV Import' => 'Importación CSV',
    'Alternate Import' => 'Importación alternativa',
    'Import History' => 'Historial de importaciones',
    'Clear history' => 'Borrar historial',
    'No import history yet.' => 'Aún no hay historial de importaciones.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => '¿Está seguro de que desea borrar todos los registros de importación? Esta acción no se puede deshacer.',
    'Failed to clear history.' => 'Error al borrar el historial.',
    'Loading backup history...' => 'Cargando historial de copias de seguridad...',
    'No backups found.' => 'No se encontraron copias de seguridad.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'Proveedor de geolocalización',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Seleccione el proveedor de búsqueda de IP geográfica. Se recomiendan proveedores HTTPS por privacidad.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratuito, HTTPS de pago)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/día gratuito)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/mes gratuito)',
    'API Key' => 'Clave API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Opcional. Necesaria para los planes de pago (activa HTTPS para ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Esto está siendo sobreescrito por la configuración <code>geoProvider</code> en <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Esto está siendo sobreescrito por la configuración <code>geoApiKey</code> en <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'El plan gratuito de ip-api.com usa HTTP. Las direcciones IP se transmitirán sin cifrar. Añada una clave API para HTTPS (plan Pro) o cambie a ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: plan gratuito HTTP (45 solicitudes/min). Añada una clave API para HTTPS (plan Pro, $13/month). Las direcciones IP se transmiten sin cifrar sin clave API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS con 1.000 solicitudes gratuitas/día. Clave API opcional (aumenta los límites de velocidad).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS con 50.000 solicitudes gratuitas/mes. Clave API opcional (aumenta los límites de velocidad).',
];
