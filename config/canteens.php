<?php

/**
 * College / canteen slugs used across registration, staff assignment, orders, and browse list.
 * Keys must match users.college and orders.canteen_id.
 * Display ratings come from the canteen_feedbacks table (average); see CanteenFeedback::averageRatingForCollege().
 */
return [
    'ceit' => [
        'label' => 'CEIT Canteen',
        'dist' => '50m',
    ],
    'cass' => [
        'label' => 'CASS Food Hub',
        'dist' => '120m',
    ],
    'chefs' => [
        'label' => 'CHEFS Dining',
        'dist' => '200m',
    ],
    'cti' => [
        'label' => 'CTI Canteen',
        'dist' => '20m',
    ],
    'cbdem' => [
        'label' => 'CBDEM Snack Bar',
        'dist' => '180m',
    ],
    'ced' => [
        'label' => 'CED Canteen',
        'dist' => '90m',
    ],
    'chk' => [
        'label' => 'CHK Canteen',
        'dist' => '110m',
    ],
    'imeas' => [
        'label' => 'IMEAS Canteen',
        'dist' => '140m',
    ],
    'ca' => [
        'label' => 'CA Canteen',
        'dist' => '160m',
    ],
    'csm' => [
        'label' => 'CSM Canteen',
        'dist' => '70m',
    ],
    'chs' => [
        'label' => 'CHS Canteen',
        'dist' => '130m',
    ],
    'cvm' => [
        'label' => 'CVM Canteen',
        'dist' => '150m',
    ],
];
