<?php

return [
    'breadcrumb' => 'Marca',
    'title'      => 'Marca',
    'group'      => 'Geral',

    'navigation' => [
        'label' => 'Marca',
    ],

    'form' => [
        'sections' => [
            'identity' => [
                'title'       => 'Name & Theme',
                'description' => 'The name shown in the app and the theme everyone sees first. Each person can still switch between light and dark mode.',
            ],
            'logo' => [
                'title'       => 'Logo e favicon',
                'description' => 'Substitua os logos, o favicon e a altura do logo usados nos painéis administrativo e do cliente. Deixe um campo vazio para manter o padrão.',
            ],
            'colors' => [
                'title'       => 'Cores',
                'description' => 'Substitua as cores do tema usadas nos painéis administrativo e do cliente. Deixe uma cor vazia para manter o padrão.',
            ],
        ],
        'fields' => [
            'brand-name'           => 'Brand Name',
            'brand-name-helper'    => 'Shown in the browser tab and wherever the app names itself.',
            'default-theme'        => 'Default Theme',
            'default-theme-helper' => 'Used until a person picks their own.',
            'theme-system'         => 'Follow the device',
            'theme-light'          => 'Light',
            'theme-dark'           => 'Dark',
            'light-logo'           => 'Logo claro',
            'light-logo-helper'    => 'Exibido em fundos claros. Substitui o logo padrão.',
            'dark-logo'            => 'Logo escuro',
            'dark-logo-helper'     => 'Exibido quando o modo escuro está ativado.',
            'favicon'              => 'Favicon',
            'favicon-helper'       => 'Ícone da aba do navegador.',
            'logo-height'          => 'Altura do logo',
            'logo-height-helper'   => 'Um valor de altura CSS, por exemplo, 2rem ou 40px.',
            'primary-color'        => 'Primário',
            'gray-color'           => 'Cinza',
            'danger-color'         => 'Perigo',
            'info-color'           => 'Informação',
            'success-color'        => 'Sucesso',
            'warning-color'        => 'Aviso',
        ],
    ],

    'actions' => [
        'rerun-setup' => [
            'label' => 'Run setup wizard again',
        ],
        'reset' => [
            'label' => 'Redefinir para o padrão',
        ],
    ],
];
