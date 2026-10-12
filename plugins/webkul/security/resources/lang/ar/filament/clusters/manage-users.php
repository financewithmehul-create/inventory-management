<?php

return [
    'breadcrumb' => 'إدارة المستخدمين',
    'title'      => 'إدارة المستخدمين',
    'group'      => 'عام',

    'navigation' => [
        'label' => 'إدارة المستخدمين',
    ],

    'form' => [
        'enable-user-invitation' => [
            'label'       => 'تفعيل دعوة المستخدمين',
            'helper-text' => 'السماح للمستخدمين بدعوة مستخدمين آخرين إلى التطبيق.',
        ],

        'allow-registration' => [
            'label'       => 'Allow people to sign up',
            'helper-text' => 'Shows a "Create account" link on the sign-in page. New accounts get the default role below and only see their own records.',
        ],

        'registration-requires-approval' => [
            'label'       => 'New sign-ups need approval',
            'helper-text' => 'New accounts stay inactive until an administrator activates them under Users.',
        ],

        'enable-reset-password' => [
            'label'       => 'تفعيل إعادة تعيين كلمة المرور',
            'helper-text' => 'السماح للمستخدمين بإعادة تعيين كلمة المرور الخاصة بهم.',
        ],

        'default-role' => [
            'label'       => 'الدور الافتراضي',
            'helper-text' => 'الدور الافتراضي المُعيَّن للمستخدمين الجدد.',
        ],

        'default-company' => [
            'label'       => 'الشركة الافتراضية',
            'helper-text' => 'الشركة الافتراضية المُعيَّنة للمستخدمين الجدد.',
        ],
    ],
];
