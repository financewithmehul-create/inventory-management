<?php

return [
    'navigation' => [
        'title' => 'Reorder',
        'group' => 'Procurement',
    ],

    'form' => [
        'fields' => [
        ],
    ],

    'table' => [
        'columns' => [
            'product'           => 'Product',
            'location'          => 'Location',
            'route'             => 'Route',
            'vendor'            => 'Supplier',
            'trigger'           => 'Trigger',
            'on-hand'           => 'In stock',
            'min'               => 'Min',
            'max'               => 'Max',
            'multiple-quantity' => 'Multiple Quantity',
            'to-order'          => 'To Order',
            'uom'               => 'UOM',
            'company'           => 'Company',
        ],

        'groups' => [
            'location' => 'Location',
            'product'  => 'Product',
            'category' => 'Category',
        ],

        'filters' => [
        ],

        'header-actions' => [
            'create' => [
                'label' => 'Add Replenishment',

                'notification' => [
                    'title' => 'Replenishment added',
                    'body'  => 'The replenishment has been added successfully.',
                ],

                'before' => [
                    'notification' => [
                        'title' => 'Replenishment already exists',
                        'body'  => 'A replenishment already exists for this configuration. Please update the existing replenishment instead.',
                    ],
                ],
            ],
        ],

        'actions' => [
        ],
    ],
];
