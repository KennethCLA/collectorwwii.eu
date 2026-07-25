<?php

// config/collector.php

return [
    'contact_notify_address' => env('CONTACT_NOTIFY_EMAIL'),

    'enabled_sections' => [
        'books' => true,
        'items' => true,
        'magazines' => true,
        'newspapers' => true,
        'banknotes' => true,
        'coins' => true,
        'postcards' => true,
        'stamps' => true,
    ],
];
