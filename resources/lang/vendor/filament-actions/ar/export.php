<?php

return [

    'label' => 'تصدير :label',

    'modal' => [

        'heading' => 'تصدير :label',

        'form' => [

            'columns' => [

                'label' => 'الأعمدة',

                'actions' => [

                    'select_all' => [
                        'label' => 'تحديد الكل',
                    ],

                    'deselect_all' => [
                        'label' => 'إلغاء تحديد الكل',
                    ],

                ],

                'form' => [

                    'is_enabled' => [
                        'label' => ':column مفعل',
                    ],

                    'label' => [
                        'label' => ':column عنوان',
                    ],

                ],

            ],

        ],

        'actions' => [

            'export' => [
                'label' => 'تصدير',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'اكتمل التصدير',

            'actions' => [

                'download_csv' => [
                    'label' => 'تحميل بصيغة .csv',
                ],

                'download_xlsx' => [
                    'label' => 'تحميل بصيغة .xlsx',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'الملف الذي تم تحميله كبير جداً',
            'body' => 'لا يمكنك تصدير أكثر من صف واحد في كل مرة.|لا يمكنك تصدير أكثر من :count صف في كل مرة.',
        ],

        'no_columns' => [
            'title' => 'لم يتم تحديد أعمدة',
            'body' => 'يرجى تحديد عمود واحد على الأقل للتصدير.',
        ],

        'started' => [
            'title' => 'بدأ تصدير طلبات التدريب',
            'body' => 'راجع الإشعارات خلال دقيقة للحصول على رابط التحميل.',

        ],

    ],

    'file_name' => 'export-:export_id-:model',

];
