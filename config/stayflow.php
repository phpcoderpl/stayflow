<?php

return [
    'mode' => env('STAYFLOW_MODE', 'demo'),

    'payu' => [
        'pos_id' => env('PAYU_POS_ID'),
        'client_secret' => env('PAYU_CLIENT_SECRET'),
        'second_key' => env('PAYU_SECOND_KEY'),
        'sandbox' => env('PAYU_SANDBOX', true),
    ],

    'hotpay' => [
        'secret' => env('HOTPAY_SECRET'),
        'notification_password' => env('HOTPAY_NOTIFICATION_PASSWORD'),
    ],

    'stripe' => [
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'payment_provider' => env('PAYMENT_PROVIDER', 'stripe'), // stripe, hotpay or payu

    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),

    'ical_sync_interval' => env('ICAL_SYNC_INTERVAL', 15),

    'deposit_percent' => env('STAYFLOW_DEPOSIT_PERCENT', 30),
];
