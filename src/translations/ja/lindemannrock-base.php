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
    'Last 7 days' => '過去 7 日間',
    'Last 30 days' => '過去 30 日間',
    'Last 90 days' => '過去 90 日間',
    'This month' => '今月',
    'Last month' => '先月',
    'This year' => '今年',
    'Last year' => '昨年',
    'All time' => '全期間',
    'Custom Range' => 'カスタム範囲',

    // Schedule options
    'Disabled' => '無効',
    'Every 6 Hours' => '6 時間ごと',
    'Every 12 Hours' => '12 時間ごと',
    'Daily' => '毎日',
    'Daily at 2:00 AM' => '毎日 02:00',
    'Weekly' => '毎週',
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
    'Clear' => 'クリア',
    'Clear search' => '検索をクリア',
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
    'Import from CSV' => 'CSV からインポート',
    'CSV File' => 'CSV ファイル',
    'CSV Delimiter' => 'CSV 区切り文字',
    'Auto (detect)' => '自動 (検出)',
    'Comma (,)' => 'カンマ (,)',
    'Semicolon (;)' => 'セミコロン (;)',
    'Tab' => 'タブ',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'インポート前にバックアップを作成',
    'Upload & Map Columns' => 'アップロードして列をマッピング',
    'CSV Import' => 'CSV インポート',
    'Alternate Import' => '代替インポート',
    'Import History' => 'インポート履歴',
    'Clear history' => '履歴をクリア',
    'No import history yet.' => 'まだインポート履歴がありません。',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'すべてのインポートログを削除してもよろしいですか？この操作は元に戻せません。',
    'Failed to clear history.' => '履歴のクリアに失敗しました。',
    'Loading backup history...' => 'バックアップ履歴を読み込んでいます...',
    'No backups found.' => 'バックアップが見つかりませんでした。',

    // Geo provider settings (shared via _partials/geo-settings.twig)
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
];
