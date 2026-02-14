<?php

return [
    'export' => [
        'modal' => [
            'form' => [
                'columns' => [
                    'label' => 'الأعمدة',
                    'actions' => [
                        'deselect_all' => [
                            'label' => 'إلغاء تحديد الكل',
                        ],
                        'select_all' => [
                            'label' => 'تحديد الكل',
                        ],
                    ],
                ],
            ],
            'heading' => 'تصدير',
            'description' => 'اختر الأعمدة التي تريد تصديرها.',
            'actions' => [
                'export' => [
                    'label' => 'تصدير',
                ],
                'cancel' => [
                    'label' => 'إلغاء',
                ],
            ],
        ],
        'notifications' => [
            'completed' => [
                'title' => 'اكتمل التصدير',
                'body' => 'اكتمل تصدير :count صف.',
            ],
        ],
    ],
    'import' => [
        'modal' => [
            'heading' => 'استيراد',
            'description' => 'ارفع ملف للاستيراد.',
            'form' => [
                'file' => [
                    'label' => 'الملف',
                ],
            ],
            'actions' => [
                'import' => [
                    'label' => 'استيراد',
                ],
                'cancel' => [
                    'label' => 'إلغاء',
                ],
            ],
        ],
        'notifications' => [
            'completed' => [
                'title' => 'اكتمل الاستيراد',
                'body' => 'تم استيراد :count صف.',
            ],
            'failed' => [
                'title' => 'فشل الاستيراد',
                'body' => 'حدث خطأ أثناء الاستيراد.',
            ],
        ],
    ],
];
