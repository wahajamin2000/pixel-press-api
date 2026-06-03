<?php

return [

    'storage' => [
        'store' => [
            'images' => [
                'users' => 'public/users/',
            ],

            'documents' => [
                'tax_exemption' => 'public/tax-exemption-docs/'
            ]
        ],

        'retrieve' => [
            'images' => [
                'users' => 'storage/users/',
            ],
            'documents' => [
                'tax_exemption' => 'storage/tax-exemption-docs/'
            ]
        ],
    ],

    'pickup' => [
        'address' => env('PICKUP_ADDRESS', '123 Main Street, Your City, State 00000'),
        'message' => env('PICKUP_MESSAGE', 'WE WILL CONTACT YOU WHEN IT\'S READY'),
    ],

    'admin_notification_emails' => array_filter(array_map('trim', explode(',', env('ADMIN_NOTIFICATION_EMAILS', '')))),
];



















