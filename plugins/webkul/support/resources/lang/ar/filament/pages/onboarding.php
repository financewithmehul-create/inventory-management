<?php

return [
    'title'   => 'Set up your workspace',
    'waiting' => 'Your administrator is still setting up the workspace. Please try again in a few minutes.',

    'steps' => [
        'company' => [
            'label'       => 'Your company',
            'description' => 'Tell us who you are',
        ],
        'branding' => [
            'label'       => 'Look & feel',
            'description' => 'Logo, name and colours',
        ],
        'modules' => [
            'label'       => 'Modules',
            'description' => 'Choose what you need',
        ],
        'finish' => [
            'label'       => 'Finish',
            'description' => 'Review and start',
        ],
    ],

    'fields' => [
        'company-name'    => 'Company name',
        'email'           => 'Email',
        'phone'           => 'Phone',
        'website'         => 'Website',
        'tax-id'          => 'Tax ID (for example GSTIN)',
        'street'          => 'Address',
        'city'            => 'City',
        'zip'             => 'Postal code',
        'country'         => 'Country',
        'state'           => 'State / province',
        'brand-name'      => 'Name shown in the app',
        'brand-name-help' => 'Usually your company name. Shown in the browser tab and the app.',
        'light-logo'      => 'Logo (for light mode)',
        'dark-logo'       => 'Logo (for dark mode)',
        'dark-logo-help'  => 'Optional. Use a version that is readable on dark backgrounds.',
        'favicon'         => 'Browser icon',
        'primary-color'   => 'Brand colour',
        'default-theme'   => 'Default look',
        'theme-system'    => 'Follow the device',
        'theme-light'     => 'Light',
        'theme-dark'      => 'Dark',
        'modules'         => 'Modules to install',
        'modules-help'    => 'Modules that others depend on are added automatically. You can add or remove modules later.',
    ],

    'presets' => 'Quick colours',

    'preview' => [
        'title'  => 'Preview',
        'button' => 'Primary button',
    ],

    'modules' => [
        'install'            => 'Install selected modules',
        'installing'         => 'Installing :name…',
        'done'               => 'Installed',
        'failed'             => 'Failed',
        'none'               => 'No optional modules are left to install.',
        'install-first'      => 'Install the selected modules first',
        'install-first-body' => 'Click "Install selected modules" and wait until it finishes, or untick the modules you do not need.',
    ],

    'finish' => [
        'heading'     => 'You are ready to go',
        'description' => 'Your company details and branding will be saved now. You can change everything later in Settings.',
        'submit'      => 'Finish setup',
    ],

    'notifications' => [
        'completed' => 'Setup complete. Welcome!',
    ],
];
