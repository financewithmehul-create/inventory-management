<?php

return [
    'breadcrumb' => 'Gerenciar usuários',
    'title'      => 'Gerenciar usuários',
    'group'      => 'Geral',

    'navigation' => [
        'label' => 'Gerenciar usuários',
    ],

    'form' => [
        'enable-user-invitation' => [
            'label'       => 'Habilitar convite de usuário',
            'helper-text' => 'Permitir que usuários convidem outros usuários para a aplicação.',
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
            'label'       => 'Habilitar redefinição de senha',
            'helper-text' => 'Permitir que usuários redefinam suas senhas.',
        ],

        'default-role' => [
            'label'       => 'Função padrão',
            'helper-text' => 'A função padrão atribuída a novos usuários.',
        ],

        'default-company' => [
            'label'       => 'Empresa padrão',
            'helper-text' => 'A empresa padrão atribuída a novos usuários.',
        ],
    ],
];
