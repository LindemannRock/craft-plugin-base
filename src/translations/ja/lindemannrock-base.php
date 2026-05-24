<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'インストールが完了しました',
    'Plugin installed' => 'プラグインがインストールされました',
    'Version' => 'バージョン',
    'Continue' => '続行',
    'Open plugin' => 'プラグインを開く',
    'Open settings' => '設定を開く',
    'Close dialog' => 'ダイアログを閉じる',
    'Everything is wired up. You can start configuring the plugin right away.' => 'すべての準備が整いました。すぐにプラグインの設定を開始できます。',

    // Date ranges
    'Today' => '今日',
    'Yesterday' => '昨日',
    'This week' => '今週',
    'Last week' => '先週',
    'Last 7 days' => '過去 7 日間',
    'Last 14 days' => '過去 14 日間',
    'Last 30 days' => '過去 30 日間',
    'Last 90 days' => '過去 90 日間',
    'This month' => '今月',
    'Last month' => '先月',
    'This quarter' => '今四半期',
    'Last quarter' => '前四半期',
    'This year' => '今年',
    'Last year' => '昨年',
    'Last 12 months' => '過去 12 か月',
    'All time' => '全期間',
    'Custom Range' => 'カスタム範囲',

    // Schedule options
    'Disabled' => '無効',
    'Every 15 Minutes' => '15 分ごと',
    'Every 30 Minutes' => '30 分ごと',
    'Hourly' => '1 時間ごと',
    'Every 2 Hours' => '2 時間ごと',
    'Every 3 Hours' => '3 時間ごと',
    'Every 4 Hours' => '4 時間ごと',
    'Every 6 Hours' => '6 時間ごと',
    'Every 12 Hours' => '12 時間ごと',
    'Daily' => '毎日',
    'Daily at 2:00 AM' => '毎日 02:00',
    'Weekly' => '毎週',
    'Every 2 Weeks' => '2 週間ごと',
    'Monthly' => '毎月',
    'Every 2 Months' => '2 か月ごと',
    'Quarterly' => '四半期ごと',
    'Every 6 Months' => '6 か月ごと',
    'Yearly' => '毎年',

    // Export + editions
    'Nothing to export.' => 'エクスポートするものがありません。',
    '{feature} requires the {edition} edition.' => '{feature} には {edition} エディションが必要です。',
    'This feature requires the {edition} edition.' => 'この機能には {edition} エディションが必要です。',
    'Export' => 'エクスポート',
    'Export as Excel' => 'Excel としてエクスポート',
    'Export as CSV' => 'CSV としてエクスポート',
    'Export as JSON' => 'JSON としてエクスポート',

    // Search + filters
    'Search' => '検索',
    'Search...' => '検索...',
    'Clear' => '削除する',
    'Clear search' => '検索を削除',
    'All' => 'すべて',

    // Table view + pagination
    'No items found.' => 'アイテムが見つかりませんでした。',
    'View' => '表示',
    'Sort by' => '並び替え',
    'Sort attribute' => '並び替え属性',
    'Sort direction' => '並び替え方向',
    'Sort ascending' => '昇順に並び替え',
    'Sort descending' => '降順に並び替え',
    'Table Columns' => 'テーブル列',
    'Use defaults' => 'デフォルトを使用',
    'Close' => '閉じる',
    'New' => '新規',
    'Action' => 'アクション',
    'Actions' => 'アクション',
    'Select all' => 'すべて選択',
    'Select' => '選択',
    'Cannot modify config items' => '設定項目を変更できません',
    'Previous Page' => '前のページ',
    'Next Page' => '次のページ',
    'no' => 'いいえ',
    'of' => '/',
    'Auto-refresh' => '自動更新',

    // Import + backups
    'Import from CSV' => 'CSV からインポートする',
    'CSV File' => 'CSV ファイル',
    'CSV Delimiter' => 'CSV 区切り文字',
    'Auto (detect)' => '自動（検出）',
    'Comma (,)' => 'カンマ（,）',
    'Semicolon (;)' => 'セミコロン（;）',
    'Tab' => 'タブ',
    'Pipe (|)' => 'パイプ（|）',
    'Create Backup Before Import' => 'インポート前にバックアップを作成する',
    'Upload & Map Columns' => 'アップロードして列をマッピングする',
    'CSV Import' => 'CSV インポート',
    'Alternate Import' => '代替インポート',
    'Import History' => 'インポート履歴',
    'Clear history' => '履歴を削除する',
    'No import history yet.' => 'インポート履歴はまだありません。',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'すべてのインポートログを削除しますか？この操作は取り消せません。',
    'Failed to clear history.' => '履歴の削除に失敗しました。',
    'Loading backup history...' => 'バックアップ履歴を読み込んでいます...',
    'No backups found.' => 'バックアップが見つかりませんでした。',

    // Geo provider settings (shared via _partials/cascade-geo-settings.twig)
    'Geo Provider' => 'ジオプロバイダー',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'ジオ IP 検索プロバイダーを選択してください。プライバシーのために HTTPS プロバイダーを推奨します。',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com（HTTP 無料、HTTPS 有料）',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co（HTTPS、1k/日 無料）',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io（HTTPS、50k/月 無料）',
    'API Key' => 'API キー',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => '任意です。有料プランには必要です（ip-api.com Pro の HTTPS を有効にします）。',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => '<code>config/{handle}.php</code> の <code>geoProvider</code> 設定によって上書きされています。',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => '<code>config/{handle}.php</code> の <code>geoApiKey</code> 設定によって上書きされています。',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com の無料プランは HTTP を使用します。IP アドレスは暗号化されずに送信されます。HTTPS には API キーを追加（Pro プラン）するか、ipapi.co/ipinfo.io に切り替えてください。',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP 無料プラン（45 リクエスト/分）。HTTPS には API キーを追加（Pro プラン、$13/月）。API キーなしでは IP アドレスが暗号化されずに送信されます。',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS、1,000 リクエスト/日 無料。API キーは任意です（レート制限を増加させます）。',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS、50,000 リクエスト/月 無料。API キーは任意です（レート制限を増加させます）。',

    // Date format settings (shared via _partials/cascade-date-format-settings.twig + _partials/cascade-base-overrides.twig)
    'Base Plugin Overrides' => 'ベースプラグインのオーバーライド',
    'Settings marked "Use global default" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply. To customize globally, copy <code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> to your project\'s <code>config/</code> directory.' => '「グローバルデフォルトを使用」と表示された設定は <code>config/lindemannrock-base.php</code> から継承されます。そのファイル（または特定のキー）が存在しない場合は、コードに組み込まれたデフォルト値が適用されます。グローバルにカスタマイズするには、<code>vendor/lindemannrock/craft-plugin-base/src/config.php</code> をプロジェクトの <code>config/</code> ディレクトリにコピーしてください。',
    'Time' => '時刻',
    'Date' => '日付',
    'Time Format' => '時刻フォーマット',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'このプラグイン全体での時刻の表示方法を設定します（AM/PM 付き 12 時間制または 24 時間制）。',
    '24-hour (14:30)' => '24 時間制 (14:30)',
    '12-hour (2:30 PM)' => '12 時間制 (2:30 PM)',
    'Month Format' => '月のフォーマット',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => '日付における月の表示方法を設定します：数字 (01)、略称 (Jan)、またはフル (January)。',
    'Numeric (01)' => '数字 (01)',
    'Short (Jan)' => '略称 (Jan)',
    'Long (January)' => 'フル (January)',
    'Date Order' => '日付の並び順',
    'Order of day, month, and year in date displays.' => '日付表示における日、月、年の順序を設定します。',
    'Day-Month-Year (31/01/2026)' => '日-月-年 (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => '月-日-年 (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => '年-月-日 (2026/01/31)',
    'Date Separator' => '日付の区切り文字',
    'Character between numeric date parts. Only applies when month format is numeric.' => '数字の日付部分の間の区切り文字です。月のフォーマットが数字の場合にのみ適用されます。',
    'Slash (31/01/2026)' => 'スラッシュ (31/01/2026)',
    'Dash (31-01-2026)' => 'ハイフン (31-01-2026)',
    'Dot (31.01.2026)' => 'ドット (31.01.2026)',
    'Show Seconds' => '秒を表示',
    'Whether to include seconds in time displays by default.' => 'デフォルトで時刻表示に秒を含めるかどうかを設定します。',
    'No (14:30)' => 'いいえ (14:30)',
    'Yes (14:30:25)' => 'はい (14:30:25)',
    'Use global default' => 'グローバルデフォルトを使用',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => '<code>config/{handle}.php</code> の <code>{setting}</code> 設定によって上書きされています。',

    // Items per page field (shared via _partials/field-items-per-page.twig)
    'Items Per Page' => '1 ページあたりの件数',
    'Number of items to display per page in lists.' => 'リストで 1 ページあたりに表示する件数です。',

    // Plugin name field (shared via _partials/field-plugin-name.twig)
    'Plugin Name' => 'プラグイン名',
    'The name of the plugin as it appears in the Control Panel menu.' => 'コントロールパネルのメニューに表示されるプラグインの名前です。',

    // Log level field (shared via _partials/field-log-level.twig)
    'Log Level' => 'ログレベル',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => '記録するメッセージの種類を選択してください。Debug レベルには devMode の有効化が必要です。',
    'Error (Critical errors only)' => 'エラー（重大なエラーのみ）',
    'Warning (Errors and warnings)' => '警告（エラーと警告）',
    'Info (General information)' => '情報（一般情報）',
    'Debug (Detailed debugging)' => 'Debug（詳細なデバッグ）',

    // Date range settings (shared via _partials/cascade-date-range-settings.twig)
    'Default Date Range' => 'デフォルトの日付範囲',
    'Default time window applied to analytics, logs, and dashboard pages in this plugin.' => 'このプラグインのアナリティクス、ログ、およびダッシュボードページに適用されるデフォルトの期間です。',

    // Export format settings (shared via _partials/cascade-export-format-settings.twig)
    'CSV Export' => 'CSV エクスポート',
    'JSON Export' => 'JSON エクスポート',
    'Excel Export' => 'Excel エクスポート',
    'Whether the CSV export option appears in this plugin\'s export menus.' => 'このプラグインのエクスポートメニューに CSV エクスポートオプションを表示するかどうかを設定します。',
    'Whether the JSON export option appears in this plugin\'s export menus.' => 'このプラグインのエクスポートメニューに JSON エクスポートオプションを表示するかどうかを設定します。',
    'Whether the Excel export option appears in this plugin\'s export menus.' => 'このプラグインのエクスポートメニューに Excel エクスポートオプションを表示するかどうかを設定します。',
    'Enabled' => '有効',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'コピー',
    'Copied!' => 'コピーしました！',
    'Failed to copy to clipboard' => 'クリップボードへのコピーに失敗しました',

    // IP hash salt error (shared via _partials/ip-salt-error.twig)
    'Configuration Required' => '設定が必要です',
    'IP hash salt is missing.' => 'IP ハッシュソルトが設定されていません。',
    'Analytics tracking requires a secure salt for privacy protection.' => 'アナリティクストラッキングには、プライバシー保護のための安全なソルトが必要です。',
    'Run one of these commands in your terminal:' => '次のいずれかのコマンドをターミナルで実行してください。',
    'Standard:' => '標準:',
    'DDEV:' => 'DDEV:',
    'This will automatically add {envVar} to your .env file.' => 'これにより、 {envVar} が .env ファイルに自動的に追加されます。',
    'Warning:' => '警告:',
    'Copy the same salt to staging and production environments.' => 'ステージング環境および本番環境に同じソルトをコピーしてください。',
];
