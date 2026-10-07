<?php

return [
    'breadcrumb' => 'Image de marque',
    'title'      => 'Image de marque',
    'group'      => 'Général',

    'navigation' => [
        'label' => 'Image de marque',
    ],

    'form' => [
        'sections' => [
            'identity' => [
                'title'       => 'Name & Theme',
                'description' => 'The name shown in the app and the theme everyone sees first. Each person can still switch between light and dark mode.',
            ],
            'logo' => [
                'title'       => 'Logo et favicon',
                'description' => "Remplacez les logos, le favicon et la hauteur du logo utilisés dans les panneaux d'administration et client. Laissez un champ vide pour conserver la valeur par défaut.",
            ],
            'colors' => [
                'title'       => 'Couleurs',
                'description' => "Remplacez les couleurs du thème utilisées dans les panneaux d'administration et client. Laissez une couleur vide pour conserver la valeur par défaut.",
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
            'light-logo'           => 'Logo clair',
            'light-logo-helper'    => 'Affiché sur les fonds clairs. Remplace le logo par défaut.',
            'dark-logo'            => 'Logo sombre',
            'dark-logo-helper'     => 'Affiché lorsque le mode sombre est activé.',
            'favicon'              => 'Favicon',
            'favicon-helper'       => "Icône de l'onglet du navigateur.",
            'logo-height'          => 'Hauteur du logo',
            'logo-height-helper'   => 'Une valeur de hauteur CSS, par exemple 2rem ou 40px.',
            'primary-color'        => 'Principale',
            'gray-color'           => 'Gris',
            'danger-color'         => 'Danger',
            'info-color'           => 'Info',
            'success-color'        => 'Succès',
            'warning-color'        => 'Avertissement',
        ],
    ],

    'actions' => [
        'rerun-setup' => [
            'label' => 'Run setup wizard again',
        ],
        'reset' => [
            'label' => 'Réinitialiser par défaut',
        ],
    ],
];
