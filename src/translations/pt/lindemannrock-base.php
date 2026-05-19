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
    'Last 7 days' => 'Últimos 7 dias',
    'Last 30 days' => 'Últimos 30 dias',
    'Last 90 days' => 'Últimos 90 dias',
    'This month' => 'Este mês',
    'Last month' => 'Mês passado',
    'This year' => 'Este ano',
    'Last year' => 'Ano passado',
    'All time' => 'Todo o período',
    'Custom Range' => 'Intervalo personalizado',

    // Schedule options
    'Disabled' => 'Desativado',
    'Every 6 Hours' => 'A cada 6 horas',
    'Every 12 Hours' => 'A cada 12 horas',
    'Daily' => 'Diário',
    'Daily at 2:00 AM' => 'Diariamente às 02:00',
    'Weekly' => 'Semanal',
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
    'View' => 'Visualizar',
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

    // Import + backups
    'Import from CSV' => 'Importar de CSV',
    'CSV File' => 'Arquivo CSV',
    'CSV Delimiter' => 'Delimitador CSV',
    'Auto (detect)' => 'Auto (detectar)',
    'Comma (,)' => 'Vírgula (,)',
    'Semicolon (;)' => 'Ponto e vírgula (;)',
    'Tab' => 'Tabulação',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'Criar backup antes de importar',
    'Upload & Map Columns' => 'Fazer upload e mapear colunas',
    'CSV Import' => 'Importação CSV',
    'Alternate Import' => 'Importação alternativa',
    'Import History' => 'Histórico de importações',
    'Clear history' => 'Limpar histórico',
    'No import history yet.' => 'Ainda não há histórico de importações.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Tem certeza de que deseja limpar todos os registros de importação? Esta ação não pode ser desfeita.',
    'Failed to clear history.' => 'Falha ao limpar o histórico.',
    'Loading backup history...' => 'Carregando histórico de backups...',
    'No backups found.' => 'Nenhum backup encontrado.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
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

    // Date format settings (shared via _partials/date-format-settings.twig + _partials/base-overrides.twig)
    'Base Plugin Overrides' => 'Substituições do plugin base',
    'Settings marked "Usar o padrão global" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply.' => 'As definições marcadas como «Usar o padrão global» herdam de <code>config/lindemannrock-base.php</code>. Se esse ficheiro (ou a chave específica) estiver ausente, aplicam-se os valores predefinidos incorporados no código.',
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

    // Items per page field (shared via _partials/items-per-page-field.twig)
    'Items Per Page' => 'Itens por página',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'Copiar',
    'Copied!' => 'Copiado!',
    'Failed to copy to clipboard' => 'Falha ao copiar para a área de transferência',
];
