<?php

return [
    'columns' => [
        'number'          => 'Number',
        'state'           => 'State',
        'customer'        => 'Customer',
        'bill-date'       => 'Bill Date',
        'due-date'        => 'Due Date',
        'tax-excluded'    => 'Tax Excluded',
        'tax'             => 'Tax',
        'total'           => 'Total',
        'amount-due'      => 'Amount Due',
        'payment-state'   => 'Payment State',
        'checked'         => 'Checked',
        'accounting-date' => 'Accounting Date',
        'source-document' => 'Reference',
        'reference'       => 'Reference',
        'sales-person'    => 'Salesperson',
        'bill-currency'   => 'Bill Currency',
    ],

    'values' => [
        'yes' => 'Yes',
        'no'  => 'No',
    ],

    'notification' => [
        'completed' => 'Your bill export has completed and :count row(s) exported.',
        'failed'    => ':count row(s) failed to export.',
    ],
];
