<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Install experience
    'Installed successfully' => 'تم التثبيت بنجاح',
    'Plugin installed' => 'تم تثبيت الإضافة',
    'Version' => 'الإصدار',
    'Continue' => 'متابعة',
    'Open plugin' => 'فتح الإضافة',
    'Open settings' => 'فتح الإعدادات',
    'Close dialog' => 'إغلاق النافذة',
    'Everything is wired up. You can start configuring the plugin right away.' => 'كل شيء جاهز. يمكنك البدء بتهيئة الإضافة فورًا.',

    // Date ranges
    'Today' => 'اليوم',
    'Yesterday' => 'أمس',
    'Last 7 days' => 'آخر 7 أيام',
    'Last 30 days' => 'آخر 30 يومًا',
    'Last 90 days' => 'آخر 90 يومًا',
    'This month' => 'هذا الشهر',
    'Last month' => 'الشهر الماضي',
    'This year' => 'هذا العام',
    'Last year' => 'العام الماضي',
    'All time' => 'كل الوقت',
    'Custom Range' => 'نطاق مخصص',

    // Schedule options
    'Disabled' => 'معطّل',
    'Every 6 Hours' => 'كل 6 ساعات',
    'Every 12 Hours' => 'كل 12 ساعة',
    'Daily' => 'يومياً',
    'Daily at 2:00 AM' => 'يومياً في 02:00',
    'Weekly' => 'أسبوعياً',
    'Monthly' => 'شهرياً',
    'Every 2 Months' => 'كل شهرين',
    'Quarterly' => 'ربع سنوي',
    'Every 6 Months' => 'كل 6 أشهر',
    'Yearly' => 'سنوياً',

    // Export + editions
    'Nothing to export.' => 'لا يوجد شيء للتصدير.',
    '{feature} requires the {edition} edition.' => '{feature} يتطلب إصدار {edition}.',
    'This feature requires the {edition} edition.' => 'هذه الميزة تتطلب إصدار {edition}.',
    'Export' => 'تصدير',
    'Export as Excel' => 'تصدير كـ Excel',
    'Export as CSV' => 'تصدير كـ CSV',
    'Export as JSON' => 'تصدير كـ JSON',

    // Search + filters
    'Search' => 'بحث',
    'Search...' => 'بحث...',
    'Clear' => 'مسح',
    'Clear search' => 'مسح البحث',
    'All' => 'الكل',

    // Table view + pagination
    'No items found.' => 'لم يتم العثور على عناصر.',
    'View' => 'عرض',
    'Sort by' => 'ترتيب حسب',
    'Sort attribute' => 'خاصية الترتيب',
    'Sort direction' => 'اتجاه الترتيب',
    'Sort ascending' => 'ترتيب تصاعدي',
    'Sort descending' => 'ترتيب تنازلي',
    'Table Columns' => 'أعمدة الجدول',
    'Use defaults' => 'استخدام الإعدادات الافتراضية',
    'Close' => 'إغلاق',
    'New' => 'جديد',
    'Action' => 'إجراء',
    'Actions' => 'إجراءات',
    'Select all' => 'تحديد الكل',
    'Select' => 'تحديد',
    'Cannot modify config items' => 'لا يمكن تعديل عناصر الإعداد',
    'Previous Page' => 'الصفحة السابقة',
    'Next Page' => 'الصفحة التالية',
    'no' => 'لا',
    'of' => 'من',
    'Auto-refresh' => 'تحديث تلقائي',

    // Import + backups
    'Import from CSV' => 'استيراد من CSV',
    'CSV File' => 'ملف CSV',
    'CSV Delimiter' => 'فاصل CSV',
    'Auto (detect)' => 'تلقائي (اكتشاف)',
    'Comma (,)' => 'فاصلة (,)',
    'Semicolon (;)' => 'فاصلة منقوطة (;)',
    'Tab' => 'مسافة جدولة',
    'Pipe (|)' => 'Pipe (|)',
    'Create Backup Before Import' => 'إنشاء نسخة احتياطية قبل الاستيراد',
    'Upload & Map Columns' => 'رفع وتعيين الأعمدة',
    'CSV Import' => 'استيراد CSV',
    'Alternate Import' => 'استيراد بديل',
    'Import History' => 'سجل الاستيراد',
    'Clear history' => 'مسح السجل',
    'No import history yet.' => 'لا يوجد سجل استيراد حتى الآن.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'هل أنت متأكد من رغبتك في مسح جميع سجلات الاستيراد؟ لا يمكن التراجع عن هذا الإجراء.',
    'Failed to clear history.' => 'فشل مسح السجل.',
    'Loading backup history...' => 'جارٍ تحميل سجل النسخ الاحتياطية...',
    'No backups found.' => 'لم يتم العثور على نسخ احتياطية.',

    // Geo provider settings (shared via _partials/geo-settings.twig)
    'Geo Provider' => 'مزوّد الموقع الجغرافي',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'اختر مزوّد البحث عن الموقع الجغرافي بواسطة IP. يُوصى باستخدام مزوّدي HTTPS للحفاظ على الخصوصية.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP مجاني، HTTPS مدفوع)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS، 1000 طلب/يوم مجانًا)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS، 50,000 طلب/شهر مجانًا)',
    'API Key' => 'مفتاح API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'اختياري. مطلوب للخطط المدفوعة (يتيح HTTPS لـ ip-api.com Pro).',
    'This is being overridden by the <code>geoProvider</code> setting in <code>config/{handle}.php</code>.' => 'يتم تجاوز هذا الإعداد بواسطة إعداد <code>geoProvider</code> في <code>config/{handle}.php</code>.',
    'This is being overridden by the <code>geoApiKey</code> setting in <code>config/{handle}.php</code>.' => 'يتم تجاوز هذا الإعداد بواسطة إعداد <code>geoApiKey</code> في <code>config/{handle}.php</code>.',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'تستخدم الخطة المجانية لـ ip-api.com بروتوكول HTTP. ستُرسل عناوين IP بدون تشفير. أضف مفتاح API للحصول على HTTPS (خطة Pro) أو انتقل إلى ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: الخطة المجانية تعمل بـ HTTP (45 طلب/دقيقة). أضف مفتاح API للحصول على HTTPS (خطة Pro، $13/month). تُرسل عناوين IP بدون تشفير في غياب مفتاح API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS مع 1,000 طلب مجاني يوميًا. مفتاح API اختياري (يرفع حدود الاستخدام).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS مع 50,000 طلب مجاني شهريًا. مفتاح API اختياري (يرفع حدود الاستخدام).',

    // Date format settings (shared via _partials/date-format-settings.twig + _partials/base-overrides.twig)
    'Base Plugin Overrides' => 'تجاوزات الإضافة الأساسية',
    'Settings marked "استخدام الإعداد الافتراضي العام" inherit from <code>config/lindemannrock-base.php</code>. If that file (or the specific key) is absent, hardcoded defaults apply.' => 'الإعدادات المحددة بـ «استخدام الإعداد الافتراضي العام» ترث قيمها من <code>config/lindemannrock-base.php</code>. إذا كان هذا الملف (أو المفتاح المحدد) غائبًا، تُطبَّق القيم الافتراضية المضمّنة في الكود.',
    'Time' => 'الوقت',
    'Date' => 'التاريخ',
    'Time Format' => 'تنسيق الوقت',
    'How times display throughout this plugin (12-hour with AM/PM or 24-hour military).' => 'يحدد كيفية عرض الأوقات في هذه الإضافة (12 ساعة مع AM/PM أو 24 ساعة).',
    '24-hour (14:30)' => '24 ساعة (14:30)',
    '12-hour (2:30 PM)' => '12 ساعة (2:30 PM)',
    'Month Format' => 'تنسيق الشهر',
    'How months appear in dates: numeric (01), short (Jan), or long (January).' => 'يحدد كيفية ظهور الأشهر في التواريخ: رقمي (01)، مختصر (Jan)، أو كامل (January).',
    'Numeric (01)' => 'رقمي (01)',
    'Short (Jan)' => 'مختصر (Jan)',
    'Long (January)' => 'كامل (January)',
    'Date Order' => 'ترتيب التاريخ',
    'Order of day, month, and year in date displays.' => 'ترتيب اليوم والشهر والسنة في عرض التواريخ.',
    'Day-Month-Year (31/01/2026)' => 'يوم-شهر-سنة (31/01/2026)',
    'Month-Day-Year (01/31/2026)' => 'شهر-يوم-سنة (01/31/2026)',
    'Year-Month-Day (2026/01/31)' => 'سنة-شهر-يوم (2026/01/31)',
    'Date Separator' => 'فاصل التاريخ',
    'Character between numeric date parts. Only applies when month format is numeric.' => 'الحرف الفاصل بين أجزاء التاريخ الرقمية. ينطبق فقط عندما يكون تنسيق الشهر رقميًا.',
    'Slash (31/01/2026)' => 'شرطة مائلة (31/01/2026)',
    'Dash (31-01-2026)' => 'شرطة (31-01-2026)',
    'Dot (31.01.2026)' => 'نقطة (31.01.2026)',
    'Show Seconds' => 'عرض الثواني',
    'Whether to include seconds in time displays by default.' => 'يحدد ما إذا كانت الثواني تُدرج افتراضيًا في عروض الوقت.',
    'No (14:30)' => 'لا (14:30)',
    'Yes (14:30:25)' => 'نعم (14:30:25)',
    'Use global default' => 'استخدام الإعداد الافتراضي العام',
    'This is being overridden by the <code>{setting}</code> setting in <code>config/{handle}.php</code>.' => 'يتم تجاوز هذا الإعداد بواسطة إعداد <code>{setting}</code> في <code>config/{handle}.php</code>.',

    // Items per page field (shared via _partials/items-per-page-field.twig)
    'Items Per Page' => 'العناصر لكل صفحة',

    // Copy-to-clipboard controls — shared across any base component or partial
    // that exposes a copy action (secret-reveal, ip-salt-error, future ones).
    'Copy' => 'نسخ',
    'Copied!' => 'تم النسخ!',
    'Failed to copy to clipboard' => 'فشل النسخ إلى الحافظة',
];
