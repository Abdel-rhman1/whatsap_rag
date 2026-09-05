<?php

return [
    'title' => 'الحملات',
    'marketing_campaigns' => 'الحملات التسويقية',
    'manage_desc' => 'أنشئ وأدر حملات الواتساب الجماعية الخاصة بك.',
    'new_campaign' => 'حملة جديدة',
    'no_campaigns' => 'لا توجد حملات بعد',
    'start_reaching' => 'ارفع ملف إكسل وابدأ في الوصول إلى عملائك اليوم عبر الواتساب.',
    'create_first' => 'أنشئ حملتك الأولى',
    
    // Statuses
    'status' => [
        'draft' => 'مسودة',
        'scheduled' => 'مجدولة',
        'running' => 'جارية',
        'paused' => 'متوقفة مؤقتاً',
        'completed' => 'مكتملة',
        'failed' => 'فاشلة',
    ],

    // Table Headers
    'table' => [
        'name' => 'اسم الحملة',
        'status' => 'الحالة',
        'progress' => 'التقدم',
        'scheduled' => 'مجدولة لـ',
        'actions' => 'الإجراءات',
        'tenant' => 'المستأجر',
        'instance' => 'القناة',
        'recipient' => 'المستلم',
        'sent_at' => 'وقت الإرسال',
        'reason' => 'السبب / الخطأ',
    ],

    // Create Page
    'create' => [
        'title' => 'إنشاء حملة واتساب',
        'subtitle' => 'أطلق حملة تسويقية أو إشعارات جديدة في دقائق.',
        'step_details' => 'تفاصيل الحملة',
        'step_recipients' => 'رفع المستلمين',
        'step_schedule' => 'الجدولة',
        'name_label' => 'اسم الحملة',
        'name_placeholder' => 'مثال: تخفيضات صيف 2024',
        'instance_label' => 'قناة الواتساب',
        'instance_placeholder' => 'اختر قناة...',
        'template_label' => 'قالب الرسالة',
        'template_placeholder' => 'مرحباً @{{name}}، اطلع على أحدث عروضنا!',
        'variables_hint' => 'استخدم المتغيرات: "@{{name}}", "@{{phone}}"',
        'upload_title' => 'اضغط للرفع أو قم بالسحب والإفلات',
        'upload_hint' => 'الصيغ المدعومة: XLSX, XLS, CSV (بحد أقصى 10 ميجابايت)',
        'file_structure' => 'هيكلية الملف:',
        'file_structure_desc' => 'يجب أن تتضمن الأعمدة "phone" (مطلوب). اختياري: "name" و "message".',
        'schedule_now' => 'إرسال الآن',
        'schedule_later' => 'إرسال لاحقاً',
        'datetime_label' => 'اختر التاريخ والوقت',
        'launch_btn' => 'إطلاق الحملة',
        'spam_warning' => 'بإطلاقك للحملة، فإنك توافق على شروط مكافحة الرسائل المزعجة. تجنب إرسال أكثر من 1000 رسالة يومياً على الحسابات الجديدة.',
    ],

    // Show Page
    'show' => [
        'stats' => [
            'total' => 'إجمالي المستلمين',
            'sent' => 'تم الإرسال بنجاح',
            'failed' => 'رسائل فاشلة',
            'status' => 'الحالة الحالية',
            'completion_rate' => 'نسبة الاكتمال',
        ],
        'logs_title' => 'سجلات التسليم',
        'export' => 'تصدير السجلات',
        'template_title' => 'قالب الرسالة',
        'settings_title' => 'الإعدادات',
        'creation_date' => 'تاريخ الإنشاء',
        'tips_title' => 'نصائح للحملة',
        'tip_connected' => 'تأكد من أن قناة الواتساب متصلة.',
        'tip_delay' => 'نحن نضيف تأخيراً عشوائياً بين 5-10 ثوانٍ بين الرسائل للحفاظ على أمان حسابك.',
        'tip_retry' => 'الرسائل الفاشلة ستتم إعادة محاولتها تلقائياً مرة واحدة إذا كان الخطأ مؤقتاً.',
    ],

    // Admin Specific
    'admin' => [
        'all_campaigns' => 'جميع حملات المستأجرين',
        'monitoring' => 'مراقبة الحملة',
        'total_global' => 'إجمالي الحملات',
        'running_now' => 'جارية الآن',
        'failures_today' => 'إخفاقات اليوم',
        'filter_title' => 'الفلاتر',
        'filter_btn' => 'تصفية النتائج',
        'manage_btn' => 'إدارة',
        'audit_logs' => 'سجلات المراجعة الأخيرة',
        'no_audit' => 'لا توجد سجلات مراجعة',
        'retry_failures' => 'إعادة محاولة الفاشلة',
        'force_stop' => 'إيقاف إجباري',
        'force_stop_confirm' => 'إيقاف إجباري لهذه الحملة؟ لا يمكن التراجع عن هذا الإجراء.',
        'pause' => 'إيقاف مؤقت',
        'resume' => 'استئناف الحملة',
        'cancel_confirm' => 'إلغاء هذه الحملة؟',
        'delete_confirm' => 'حذف هذه الحملة؟',
    ],

    // Flash Messages
    'flash' => [
        'created' => 'تم إنشاء الحملة وبدء المعالجة.',
        'paused' => 'تم إيقاف الحملة مؤقتاً.',
        'resumed' => 'تم استئناف الحملة.',
        'toggle_error' => 'لا يمكن تغيير حالة الحملة في وضعها الحالي.',
        'cancelled' => 'تم إلغاء الحملة.',
        'deleted' => 'تم حذف الحملة.',
        'retrying' => 'جاري إعادة محاولة الرسائل الفاشلة...',
    ],
];
