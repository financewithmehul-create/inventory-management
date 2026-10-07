<?php

return [
    'form' => [
        'fields' => [
            'avatar'        => 'Role picture',
            'avatar-helper' => 'Shown next to everyone with this role who has no photo of their own.',
            'web'           => 'ويب',
            'sanctum'       => 'Sanctum',
        ],
    ],

    'notification' => [
        'system-role-delete' => [
            'title' => 'لا يمكن حذف دور النظام',
            'body'  => 'هذا دور نظام ولا يمكن حذفه.',
        ],
    ],
];
