<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'Instalado com sucesso',
    'Plugin installed' => 'Plugin instalado',
    'Version' => 'Versão',
    'Continue' => 'Continuar',
    'Open plugin' => 'Abrir plugin',
    'Open settings' => 'Abrir configurações',
    'Close dialog' => 'Fechar diálogo',
    'Everything is wired up. You can start configuring the plugin right away.' => 'Tudo está pronto. Você pode começar a configurar o plugin imediatamente.',

    // Date ranges
    'Today' => 'Hoje',
    'Yesterday' => 'Ontem',
    'This week' => 'Esta semana',
    'Last week' => 'Semana passada',
    'Last 7 days' => 'Últimos 7 dias',
    'Last 14 days' => 'Últimos 14 dias',
    'Last 30 days' => 'Últimos 30 dias',
    'Last 90 days' => 'Últimos 90 dias',
    'This month' => 'Este mês',
    'Last month' => 'Mês passado',
    'This quarter' => 'Este trimestre',
    'Last quarter' => 'Trimestre passado',
    'This year' => 'Este ano',
    'Last year' => 'Ano passado',
    'Last 12 months' => 'Últimos 12 meses',
    'All time' => 'Todo o período',
    'Custom Range' => 'Intervalo personalizado',

    // Schedule options
    'Disabled' => 'Desativado',
    'Every 15 Minutes' => 'A cada 15 minutos',
    'Every 30 Minutes' => 'A cada 30 minutos',
    'Hourly' => 'A cada hora',
    'Every 2 Hours' => 'A cada 2 horas',
    'Every 3 Hours' => 'A cada 3 horas',
    'Every 4 Hours' => 'A cada 4 horas',
    'Every 6 Hours' => 'A cada 6 horas',
    'Every 12 Hours' => 'A cada 12 horas',
    'Daily' => 'Diário',
    'Daily at 2:00 AM' => 'Diariamente às 02:00',
    'Weekly' => 'Semanal',
    'Every 2 Weeks' => 'A cada 2 semanas',
    'Monthly' => 'Mensal',
    'Every 2 Months' => 'A cada 2 meses',
    'Quarterly' => 'Trimestral',
    'Every 6 Months' => 'A cada 6 meses',
    'Yearly' => 'Anual',

    // Export + editions
    'Nothing to export.' => 'Nada para exportar.',
    '{feature} requires the {edition} edition.' => '{feature} requer a edição {edition}.',
    'This feature requires the {edition} edition.' => 'Este recurso requer a edição {edition}.',
    'Export' => 'Export',
    'Export as Excel' => 'Exportar como Excel',
    'Export as CSV' => 'Exportar como CSV',
    'Export as JSON' => 'Exportar como JSON',

    // Search + filters
    'Search' => 'Pesquisar',
    'Search...' => 'Pesquisar...',
    'Clear' => 'Limpar',
    'Clear search' => 'Limpar pesquisa',
    'All' => 'Todos',

    // Table view + pagination
    'No items found.' => 'Nenhum item encontrado.',
    'View' => 'Ver',
    'Sort by' => 'Ordenar por',
    'Sort attribute' => 'Atributo de ordenação',
    'Sort direction' => 'Direção de ordenação',
    'Sort ascending' => 'Ordem crescente',
    'Sort descending' => 'Ordem decrescente',
    'Table Columns' => 'Colunas da tabela',
    'Use defaults' => 'Usar padrões',
    'Close' => 'Fechar',
    'New' => 'Novo',
    'Action' => 'Ação',
    'Actions' => 'Ações',
    'Select all' => 'Selecionar tudo',
    'Select' => 'Selecionar',
    'Cannot modify config items' => 'Não é possível modificar itens de configuração',
    'Previous Page' => 'Página anterior',
    'Next Page' => 'Próxima página',
    'no' => 'não',
    'of' => 'de',
    'Auto-refresh' => 'Atualização automática',
    'Refreshing' => 'A atualizar',

    // Import + backups
    'Import from CSV' => 'Importar de CSV',
    'CSV File' => 'Ficheiro CSV',
    'CSV Delimiter' => 'Delimitador CSV',
    'Auto (detect)' => 'Auto (detetar)',
    'Comma (,)' => 'Vírgula (,)',
    'Semicolon (;)' => 'Ponto e vírgula (;)',
    'Tab' => 'Tabulação',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Criar backup antes da importação',
    'Upload & Map Columns' => 'Carregar e mapear colunas',
    'CSV Import' => 'Importação CSV',
    'Alternate Import' => 'Importação alternativa',
    'Import History' => 'Histórico de importações',
    'Recent CSV imports and their results.' => 'Importações CSV recentes e os seus resultados.',
    'Created By' => 'Criado por',
    'Filename' => 'Nome do ficheiro',
    'Size' => 'Tamanho',
    'Imported' => 'Importado',
    'Failed' => 'Falhou',
    'Clear history' => 'Limpar histórico',
    'No import history yet.' => 'Ainda não há histórico de importações.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Limpar todos os logs de importação? Esta ação não pode ser anulada.',
    'Failed to clear history.' => 'Falha ao limpar o histórico.',
    'Loading backup history...' => 'Carregando histórico de backups...',
    'No backups found.' => 'Nenhum backup encontrado.',

    // Geo provider settings (shared via _partials/cascade-geo-settings.twig)
    'Geo Provider' => 'Fornecedor de Geolocalização',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Selecione o fornecedor de pesquisa de IP geográfico. Recomenda-se o uso de fornecedores HTTPS para maior privacidade.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratuito, HTTPS pago)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dia gratuito)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/mês gratuito)',
    'API Key' => 'Chave de API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Opcional. Necessário para planos pagos (ativa HTTPS para ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'Este valor está a ser substituído pela configuração <code>geoProvider</code> em <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'Este valor está a ser substituído pela configuração <code>geoApiKey</code> em <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'O plano gratuito do ip-api.com utiliza HTTP. Os endereços IP serão transmitidos sem encriptação. Adicione uma chave de API para HTTPS (plano Pro) ou mude para ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: plano gratuito HTTP (45 pedidos/min). Adicione uma chave de API para HTTPS (plano Pro, $13/month). Os endereços IP são transmitidos sem encriptação sem chave de API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS com 1.000 pedidos gratuitos/dia. Chave de API opcional (aumenta os limites de pedidos).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS com 50.000 pedidos gratuitos/mês. Chave de API opcional (aumenta os limites de pedidos).',

    // Date format settings (shared via _partials/cascade-date-format-settings.twig + _partials/cascade-base-overrides.twig)
    'Base Plugin Overrides' => 'Substituições do plugin base',
    'Settings marked "Use global default" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply. To customize globally, copy <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> to your project\'s <code>config/</code> directory.' => 'As definições marcadas como «Usar o padrão global» herdam de <code>config/lindemannrock-base.php</code>. Se esse ficheiro (ou a chave específica) estiver ausente, aplicam-se os valores predefinidos incorporados no código. Para personalizar globalmente, copie <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> para o diretório <code>config/</code> do seu projeto.',
    'Time' => 'Hora',
    'Date' => 'Data',
    'Time Format' => 'Formato de hora',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'Define como as horas são apresentadas neste plugin (12 horas com AM/PM ou 24 horas).',
    '24-hour (14:30)' => '24 horas (14:30)',
    '12-hour (2:30 PM)' => '12 horas (2:30 PM)',
    'Month Format' => 'Formato do mês',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'Define como os meses aparecem nas datas: numérico (01), abreviado (Jan) ou completo (January).',
    'Numeric (01)' => 'Numérico (01)',
    'Short (Jan)' => 'Abreviado (Jan)',
    'Long (January)' => 'Completo (January)',
    'Date Order' => 'Ordem da data',
    'Order of day, month, and year in date displays.' => 'Ordem do dia, mês e ano nas apresentações de datas.',
    'Day-Month-Year (31/01/2026)' => 'Dia-Mês-Ano (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'Mês-Dia-Ano (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'Ano-Mês-Dia (2026/01/31)',
    'Date Separator' => 'Separador de data',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'Caractere entre as partes numéricas da data. Aplica-se apenas quando o formato do mês é numérico.',
    'Slash (31/01/2026)' => 'Barra (31/01/2026)',
    'Dash (31-01-2026)' => 'Hífen (31-01-2026)',
    'Dot (31.01.2026)' => 'Ponto (31.01.2026)',
    'Show Seconds' => 'Mostrar segundos',
    'Whether to include seconds in time displays by default.' => 'Determina se os segundos são incluídos por defeito nas apresentações de hora.',
    'No (14:30)' => 'Não (14:30)',
    'Yes (14:30:25)' => 'Sim (14:30:25)',
    'Use global default' => 'Usar o padrão global',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'Este valor está a ser substituído pela configuração <code>{setting}</code> em <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/field-items-per-page.twig)
    'Items Per Page' => 'Itens por página',
    'Number of items to display per page in lists.' => 'Número de itens a apresentar por página nas listas.',

    // Plugin name field (shared via _partials/field-plugin-name.twig)
    'Plugin Name' => 'Nome do plugin',
    'The name of the plugin as it appears in the Control Panel menu.' => 'O nome do plugin tal como aparece no menu do painel de controlo.',

    // Log level field (shared via _partials/field-log-level.twig)
    'Log Level' => 'Nível de log',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Escolha que tipos de mensagens registar. O nível Debug requer que o devMode esteja ativado.',
    'Error (Critical errors only)' => 'Erro (apenas erros críticos)',
    'Warning (Errors and warnings)' => 'Aviso (erros e avisos)',
    'Info (General information)' => 'Info (informação geral)',
    'Debug (Detailed debugging)' => 'Debug (depuração detalhada)',

    // Date range settings (shared via _partials/cascade-date-range-settings.twig)
    'Default Date Range' => 'Intervalo de datas predefinido',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'Janela de tempo predefinida aplicada às páginas de análise, registos e painel de controlo deste plugin.',

    // Analytics layout (shared via _layouts/cp-analytics.twig + _partials/analytics-panel.twig)
    'Analytics' => 'Análises',
    'All Sites' => 'Todos os sites',
    'Loading' => 'A carregar',

    // Export format settings (shared via _partials/cascade-export-format-settings.twig)
    'CSV Export' => 'Exportação CSV',
    'JSON Export' => 'Exportação JSON',
    'Excel Export' => 'Exportação Excel',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'Determina se a opção de exportação CSV aparece nos menus de exportação deste plugin.',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'Determina se a opção de exportação JSON aparece nos menus de exportação deste plugin.',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'Determina se a opção de exportação Excel aparece nos menus de exportação deste plugin.',
    'Enabled' => 'Ativado',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Copiar',
    'Copied!' => 'Copiado!',
    'Failed to copy to clipboard' => 'Falha ao copiar para a área de transferência',

    // IP hash salt error (shared via _partials/ip-salt-error.twig)
    'Configuration Required' => 'Configuração necessária',
    'IP hash salt is missing.' => 'O salt de hash IP está em falta.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'O rastreamento analítico requer um salt seguro para proteção da privacidade.',
    'Run one of these commands in your terminal:' => 'Execute um destes comandos no seu terminal:',
    'Standard:' => 'Standard:',
    'DDEV:' => 'DDEV:',
    'This will automatically add {envVar} to your .env file.' => 'Isto adicionará automaticamente {envVar} ao seu ficheiro .env.',
    'Warning:' => 'Aviso:',
    'Copy the same salt to staging and production environments.' => 'Copie o mesmo salt para os ambientes de staging e produção.',
];
