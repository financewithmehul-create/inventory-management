<?php

return [
    'breadcrumb' => 'Branding',
    'title'      => 'Business and look',
    'group'      => 'General',

    'navigation' => [
        'label' => 'Branding',
    ],

    'form' => [
        'sections' => [
            'identity' => [
                'title'       => 'Name',
                'description' => 'The name shown at the top of every page.',
            ],
            'logo' => [
                'title'       => 'Logo',
                'description' => 'Your logo and the small icon shown in the browser tab. Leave empty to keep the current one.',
            ],
            'colors' => [
                'title'       => 'Main colour',
                'description' => 'Used for buttons and links. Dark, calm colours are easiest to read.',
            ],
        ],
        'fields' => [
            'brand-name'           => 'Business name',
            'brand-name-helper'    => 'Shown at the top of every page and in the browser tab.',
            'default-theme'        => 'Default Theme',
            'default-theme-helper' => 'Used until a person picks their own.',
            'theme-system'         => 'Follow the device',
            'theme-light'          => 'Light',
            'theme-dark'           => 'Dark',
            'light-logo'           => 'Logo',
            'light-logo-helper'    => 'PNG or JPG, up to 2 MB. Shown at the top left instead of the name.',
            'dark-logo'            => 'Dark Logo',
            'dark-logo-helper'     => 'Shown when dark mode is enabled.',
            'favicon'              => 'Browser tab icon',
            'favicon-helper'       => 'A small square picture.',
            'logo-height'          => 'Logo Height',
            'logo-height-helper'   => 'A CSS height value, e.g. 2rem or 40px.',
            'primary-color'        => 'Colour',
            'gray-color'           => 'Gray',
            'danger-color'         => 'Danger',
            'info-color'           => 'Info',
            'success-color'        => 'Success',
            'warning-color'        => 'Warning',
        ],
    ],

    'actions' => [
        'rerun-setup' => [
            'label' => 'Change business details',
        ],
        'reset' => [
            'label' => 'Reset to default',
        ],
    ],
];
