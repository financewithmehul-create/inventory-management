<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enforce the trial / license
    |--------------------------------------------------------------------------
    |
    | After the trial ends, an install without a valid license key becomes read-only: people can still sign in
    | and look at everything, but nothing can be created, changed or deleted. Data is never removed.
    |
    */

    'enforce' => env('LICENSE_ENFORCE', true),

    /*
    |--------------------------------------------------------------------------
    | Public key used to check license keys
    |--------------------------------------------------------------------------
    |
    | Run `php artisan license:keygen` once on your own computer. Keep the private key secret (it signs
    | licenses), and put the PUBLIC key here (or in LICENSE_PUBLIC_KEY) on every install you deliver.
    |
    */

    'public_key' => env('LICENSE_PUBLIC_KEY'),

    'trial_days' => (int) env('LICENSE_TRIAL_DAYS', 14),

    'buy_url' => env('LICENSE_BUY_URL'),
];
