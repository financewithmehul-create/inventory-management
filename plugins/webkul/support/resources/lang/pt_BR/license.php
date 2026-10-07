<?php

return [
    'title'      => 'License',
    'key'        => 'License key',
    'key-helper' => 'Paste the key you received after purchase.',
    'activate'   => 'Activate',
    'buy'        => 'Buy a license',
    'invalid'    => 'This license key cannot be used',
    'activated'  => 'License activated for :name',
    'read-only'  => 'The trial has ended. This workspace is read-only until a license key is entered under Settings > License.',

    'banner' => [
        'trial'     => '{0} Your free trial ends today.|{1} Your free trial ends tomorrow.|[2,*] Free trial: :days days left.',
        'expired'   => 'Your free trial has ended. You can still view everything, but changes are blocked until you enter a license key.',
        'enter-key' => 'Enter license key',
    ],

    'status' => [
        'licensed'    => 'Licensed',
        'trial'       => 'Free trial',
        'expired'     => 'Trial ended',
        'licensed-to' => 'Licensed to :name, valid until :until.',
        'lifetime'    => 'lifetime',
    ],

    'reasons' => [
        'no-public-key' => 'This install has no license public key configured.',
        'malformed'     => 'The key is incomplete or mistyped.',
        'bad-signature' => 'The key is not genuine.',
        'expired'       => 'This license has expired.',
        'wrong-domain'  => 'This key was issued for a different domain.',
        'trial-ended'   => 'The free trial has ended.',
    ],
];
