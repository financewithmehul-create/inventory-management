<?php

return [
    'navigation' => [
        'title' => 'Goods in',
        'group' => 'Transfers',
    ],

    'global-search' => [
        'partner' => 'Customer or supplier',
        'origin'  => 'Origin',
    ],

    'table' => [
        'actions' => [
            'delete' => [
                'notification' => [
                    'success' => [
                        'title' => 'Receipt deleted',
                        'body'  => 'The receipt has been deleted successfully.',
                    ],

                    'error' => [
                        'title' => 'Receipt could not be deleted',
                        'body'  => 'The receipt cannot be deleted because it is currently in use.',
                    ],
                ],
            ],
        ],

        'bulk-actions' => [
            'delete' => [
                'notification' => [
                    'success' => [
                        'title' => 'Receipts deleted',
                        'body'  => 'The receipts has been deleted successfully.',
                    ],

                    'error' => [
                        'title' => 'Receipts could not be deleted',
                        'body'  => 'The receipts cannot be deleted because they are currently in use.',
                    ],
                ],
            ],
        ],
    ],
];
