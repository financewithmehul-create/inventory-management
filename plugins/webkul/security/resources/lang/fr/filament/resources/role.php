<?php

return [
    'form' => [
        'fields' => [
            'avatar'        => 'Role picture',
            'avatar-helper' => 'Shown next to everyone with this role who has no photo of their own.',
            'web'           => 'Web',
            'sanctum'       => 'Sanctum',
        ],
    ],

    'notification' => [
        'system-role-delete' => [
            'title' => 'Le rôle système ne peut pas être supprimé',
            'body'  => 'Il s’agit d’un rôle système et il ne peut pas être supprimé.',
        ],
    ],
];
