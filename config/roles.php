<?php

/*
|--------------------------------------------------------------------------
| Built-in roles
|--------------------------------------------------------------------------
|
| Each role lists rules. A rule gives one level of access to every permission whose resource starts with one
| of the given prefixes (permissions are named {action}_{prefix}_{resource}, for example create_sale_order).
|
| Access levels:  full   = view, create, edit, delete, restore and reorder, plus pages and widgets
|                 staff  = view, create and edit, plus pages and widgets (no deleting, no settings pages)
|                 view   = look only
|
| "priority" decides which role's picture a person gets when they hold several roles.
|
*/

return [
    'priority' => [
        'Admin',
        'Inventory Manager',
        'Purchase Manager',
        'Sales Manager',
        'Accountant',
        'Manufacturing Manager',
        'HR Manager',
        'Project Manager',
        'Warehouse Staff',
        'Maintenance Technician',
        'Employee',
        'Viewer',
    ],

    'definitions' => [
        'Inventory Manager' => [
            ['full', ['inventory', 'product', 'barcode']],
            ['view', ['partner', 'contact', 'purchase', 'sale', 'manufacturing']],
        ],
        'Warehouse Staff' => [
            ['staff', ['inventory']],
            ['view', ['product', 'partner', 'contact', 'purchase', 'sale']],
        ],
        'Purchase Manager' => [
            ['full', ['purchase', 'partner', 'contact']],
            ['staff', ['product']],
            ['view', ['inventory', 'account', 'accounting', 'invoice']],
        ],
        'Sales Manager' => [
            ['full', ['sale', 'partner', 'contact']],
            ['staff', ['product']],
            ['view', ['inventory', 'account', 'accounting', 'invoice']],
        ],
        'Accountant' => [
            ['full', ['account', 'accounting', 'invoice', 'payment']],
            ['staff', ['partner', 'contact']],
            ['view', ['product', 'inventory', 'purchase', 'sale']],
        ],
        'HR Manager' => [
            ['full', ['employee', 'recruitment', 'time', 'timesheet']],
            ['view', ['project']],
        ],
        'Employee' => [
            ['staff', ['time', 'timesheet']],
            ['view', ['employee']],
        ],
        'Project Manager' => [
            ['full', ['project', 'timesheet']],
            ['view', ['partner', 'contact', 'employee']],
        ],
        'Manufacturing Manager' => [
            ['full', ['manufacturing', 'product']],
            ['staff', ['inventory', 'maintenance']],
            ['view', ['purchase', 'sale']],
        ],
        'Maintenance Technician' => [
            ['staff', ['maintenance']],
            ['view', ['inventory', 'manufacturing']],
        ],
        'Viewer' => [
            ['view', [
                'account', 'accounting', 'blog', 'contact', 'employee', 'inventory', 'invoice', 'maintenance',
                'manufacturing', 'partner', 'product', 'project', 'purchase', 'recruitment', 'sale', 'time',
                'timesheet', 'website',
            ]],
        ],
    ],
];
