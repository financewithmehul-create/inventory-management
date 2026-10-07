<?php

return [
    'breadcrumb' => 'Personalización de marca',
    'title'      => 'Personalización de marca',
    'group'      => 'General',

    'navigation' => [
        'label' => 'Personalización de marca',
    ],

    'form' => [
        'sections' => [
            'identity' => [
                'title'       => 'Name & Theme',
                'description' => 'The name shown in the app and the theme everyone sees first. Each person can still switch between light and dark mode.',
            ],
            'logo' => [
                'title'       => 'Logotipo y favicon',
                'description' => 'Sobrescribir los logotipos, el favicon y la altura del logotipo utilizados en los paneles de administración y de cliente. Dejar un campo vacío para conservar el valor predeterminado.',
            ],
            'colors' => [
                'title'       => 'Colores',
                'description' => 'Sobrescribir los colores del tema utilizados en los paneles de administración y de cliente. Dejar un color vacío para conservar el valor predeterminado.',
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
            'light-logo'           => 'Logotipo claro',
            'light-logo-helper'    => 'Se muestra sobre fondos claros. Reemplaza el logotipo predeterminado.',
            'dark-logo'            => 'Logotipo oscuro',
            'dark-logo-helper'     => 'Se muestra cuando el modo oscuro está activado.',
            'favicon'              => 'Favicon',
            'favicon-helper'       => 'Icono de la pestaña del navegador.',
            'logo-height'          => 'Altura del logotipo',
            'logo-height-helper'   => 'Un valor de altura CSS, p. ej. 2rem o 40px.',
            'primary-color'        => 'Primario',
            'gray-color'           => 'Gris',
            'danger-color'         => 'Peligro',
            'info-color'           => 'Información',
            'success-color'        => 'Éxito',
            'warning-color'        => 'Advertencia',
        ],
    ],

    'actions' => [
        'rerun-setup' => [
            'label' => 'Run setup wizard again',
        ],
        'reset' => [
            'label' => 'Restablecer al valor predeterminado',
        ],
    ],
];
