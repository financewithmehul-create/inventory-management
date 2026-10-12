<?php

return [
    'breadcrumb' => 'Gérer les utilisateurs',
    'title'      => 'Gérer les utilisateurs',
    'group'      => 'Général',

    'navigation' => [
        'label' => 'Gérer les utilisateurs',
    ],

    'form' => [
        'enable-user-invitation' => [
            'label'       => 'Activer l’invitation des utilisateurs',
            'helper-text' => 'Autoriser les utilisateurs à inviter d’autres utilisateurs dans l’application.',
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
            'label'       => 'Activer la réinitialisation du mot de passe',
            'helper-text' => 'Autoriser les utilisateurs à réinitialiser leur mot de passe.',
        ],

        'default-role' => [
            'label'       => 'Rôle par défaut',
            'helper-text' => 'Le rôle par défaut attribué aux nouveaux utilisateurs.',
        ],

        'default-company' => [
            'label'       => 'Société par défaut',
            'helper-text' => 'La société par défaut attribuée aux nouveaux utilisateurs.',
        ],
    ],
];
