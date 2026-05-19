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

    // Date format settings (shared via _partials/date-format-settings.twig + _partials/base-overrides.twig)
    'Base Plugin Overrides' => 'Anulaciones del plugin base',
    'Settings marked "Usar el valor predeterminado global" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply.' => 'Los ajustes marcados como «Usar el valor predeterminado global» se heredan de <code>config/lindemannrock-base.php</code>. Si ese archivo (o la clave específica) no existe, se aplican los valores predeterminados codificados.',
    'Time' => 'Hora',
    'Date' => 'Fecha',
    'Time Format' => 'Formato de hora',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'Define cómo se muestran las horas en este plugin (12 horas con AM/PM o 24 horas).',
    '24-hour (14:30)' => '24 horas (14:30)',
    '12-hour (2:30 PM)' => '12 horas (2:30 PM)',
    'Month Format' => 'Formato de mes',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'Define cómo aparecen los meses en las fechas: numérico (01), abreviado (Jan) o completo (January).',
    'Numeric (01)' => 'Numérico (01)',
    'Short (Jan)' => 'Abreviado (Jan)',
    'Long (January)' => 'Completo (January)',
    'Date Order' => 'Orden de fecha',
    'Order of day, month, and year in date displays.' => 'Orden del día, mes y año en las visualizaciones de fecha.',
    'Day-Month-Year (31/01/2026)' => 'Día-Mes-Año (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Mes-Día-Año (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'Año-Mes-Día (2026/01/31)',
    'Date Separator' => 'Separador de fecha',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Carácter entre las partes numéricas de la fecha. Solo se aplica cuando el formato de mes es numérico.',
    'Slash (31/01/2026)' => 'Barra (31/01/2026)',
    'Dash (31-01-2026)' => 'Guion (31-01-2026)',
    'Dot (31.01.2026)' => 'Punto (31.01.2026)',
    'Show Seconds' => 'Mostrar segundos',
    'Whether to include seconds in time displays by default.' => 'Determina si los segundos se incluyen de forma predeterminada en las visualizaciones de hora.',
    'No (14:30)' => 'No (14:30)',
    'Yes (14:30:25)' => 'Sí (14:30:25)',
    'Use global default' => 'Usar el valor predeterminado global',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'Esto está siendo sobreescrito por la configuración <code>{setting}</code> en <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/items-per-page-field.twig)
    'Items Per Page' => 'Elementos por página',

    // Date range settings (shared via _partials/date-range-settings.twig)
    'Default Date Range' => 'Rango de fechas predeterminado',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Ventana de tiempo predeterminada aplicada a las páginas de análisis, registros y panel de este plugin.',

    // Export format settings (shared via _partials/export-format-settings.twig)
    'CSV Export' => 'Exportación CSV',
    'JSON Export' => 'Exportación JSON',
    'Excel Export' => 'Exportación Excel',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Determina si la opción de exportación CSV aparece en los menús de exportación de este plugin.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Determina si la opción de exportación JSON aparece en los menús de exportación de este plugin.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Determina si la opción de exportación Excel aparece en los menús de exportación de este plugin.',
    'Enabled' => 'Activado',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Copiar',
    'Copied!' => '¡Copiado!',
    'Failed to copy to clipboard' => 'Error al copiar al portapapeles',
];
