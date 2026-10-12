<?php

return [
    'breadcrumb' => 'Gestionar usuarios',
    'title'      => 'Gestionar usuarios',
    'group'      => 'General',

    'navigation' => [
        'label' => 'Gestionar usuarios',
    ],

    'form' => [
        'enable-user-invitation' => [
            'label'       => 'Habilitar invitación de usuarios',
            'helper-text' => 'Permitir que los usuarios inviten a otros usuarios a la aplicación.',
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
            'label'       => 'Habilitar restablecimiento de contraseña',
            'helper-text' => 'Permitir que los usuarios restablezcan su contraseña.',
        ],

        'default-role' => [
            'label'       => 'Rol predeterminado',
            'helper-text' => 'El rol predeterminado asignado a los nuevos usuarios.',
        ],

        'default-company' => [
            'label'       => 'Empresa predeterminada',
            'helper-text' => 'La empresa predeterminada asignada a los nuevos usuarios.',
        ],
    ],
];
