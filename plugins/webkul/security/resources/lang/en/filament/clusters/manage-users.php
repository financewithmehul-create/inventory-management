<?php

return [
    'breadcrumb' => 'Manage Users',
    'title'      => 'Manage Users',
    'group'      => 'General',

    'navigation' => [
        'label' => 'Manage Users',
    ],

    'form' => [
        'enable-user-invitation' => [
            'label'       => 'Enable User Invitation',
            'helper-text' => 'Allow users to invite other users to the application.',
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
            'label'       => 'Enable Reset Password',
            'helper-text' => 'Allow users to reset their password.',
        ],

        'default-role' => [
            'label'       => 'Default Role',
            'helper-text' => 'The default role assigned to new users.',
        ],

        'default-company' => [
            'label'       => 'Default Company',
            'helper-text' => 'The default company assigned to new users.',
        ],
    ],
];
