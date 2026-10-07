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
            'title' => 'No se puede eliminar el rol del sistema',
            'body'  => 'Este es un rol del sistema y no se puede eliminar.',
        ],
    ],
];
