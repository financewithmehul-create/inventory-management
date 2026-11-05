<?php

return [
    'title'   => 'Set up your business',
    'waiting' => 'The account is still being set up. Please try again in a few minutes.',

    'steps' => [
        'company' => [
            'label'       => 'Your business',
            'description' => 'Name and address',
        ],
        'branding' => [
            'label'       => 'Look',
            'description' => 'Logo and colour',
        ],
        'finish' => [
            'label'       => 'Done',
            'description' => 'Check and start',
        ],
    ],

    'fields' => [
        'company-name'    => 'Business name',
        'email'           => 'Email',
        'phone'           => 'Phone',
        'tax-id'          => 'Tax number (for example GSTIN)',
        'street'          => 'Address',
        'city'            => 'City',
        'zip'             => 'PIN / postal code',
        'country'         => 'Country',
        'state'           => 'State',
        'brand-name'      => 'Name shown at the top',
        'brand-name-help' => 'Usually your business name.',
        'light-logo'      => 'Logo',
        'logo-help'       => 'PNG or JPG, up to 2 MB. Optional.',
        'favicon'         => 'Small icon for the browser tab',
        'primary-color'   => 'Main colour',
    ],

    'presets' => 'Suggested colours',

    'preview' => [
        'title'   => 'Preview',
        'button'  => 'Save',
        'no-name' => 'Your business',
    ],

    'preparing' => [
        'progress' => 'Getting your account ready: :done of :total parts done. Please keep this page open.',
        'failed'   => ':name could not be set up.',
        'wait'     => 'Please wait until the account is ready.',
    ],

    'finish' => [
        'heading'     => 'All set',
        'description' => 'Your business details will be saved now. You can change them later in Settings.',
        'submit'      => 'Save and start',
    ],

    'notifications' => [
        'completed' => 'Setup saved.',
    ],
];
