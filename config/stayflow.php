<?php

return [
    'mode' => env('STAYFLOW_MODE', 'demo'),

    'payu' => [
        'pos_id' => env('PAYU_POS_ID'),
        'client_secret' => env('PAYU_CLIENT_SECRET'),
        'second_key' => env('PAYU_SECOND_KEY'),
        'sandbox' => env('PAYU_SANDBOX', true),
    ],

    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),

    'ical_sync_interval' => env('ICAL_SYNC_INTERVAL', 15),

    'deposit_percent' => env('STAYFLOW_DEPOSIT_PERCENT', 30),
];
