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
            'title' => 'Função do sistema não pode ser excluída',
            'body'  => 'Esta é uma função do sistema e não pode ser excluída.',
        ],
    ],
];
