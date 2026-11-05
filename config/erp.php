<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules that make up the product
    |--------------------------------------------------------------------------
    |
    | The product is a single-company inventory system with six areas: Dashboard, Sales, Inventory, Purchase,
    | Invoices and Accounting. These modules (plus the core ones) are installed; every other module is removed by
    | `php artisan erp:modules:prune`.
    |
    */

    'modules' => [
        'products',
        'accounts',
        'payments',
        'invoices',
        'accounting',
        'sales',
        'purchases',
        'inventories',
    ],

    /*
    | Removed in this order (a module is removed before the modules it depends on).
    */
    'removed_modules' => [
        'blogs',
        'website',
        'timesheets',
        'projects',
        'recruitments',
        'time-off',
        'barcode',
        'manufacturing',
        'maintenance',
        'employees',
        'contacts',
    ],

    /*
    |--------------------------------------------------------------------------
    | One person uses the system
    |--------------------------------------------------------------------------
    |
    | Hides sign-up, users, roles, teams and companies. Turn off to manage several people again.
    |
    */

    'single_user' => (bool) env('ERP_SINGLE_USER', true),

    /*
    | Navigation groups shown in the menu, in this order.
    */
    'navigation' => ['Dashboard', 'Sale', 'Inventory', 'Purchase', 'Invoice', 'Accounting', 'Setting'],
];
