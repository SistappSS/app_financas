<?php

return [
    'version' => env('ASAAS_VERSION', 'v3'),
    'sandbox_url' => env('ASAAS_SANDBOX_URL', 'https://sandbox.asaas.com/api'),
    'sandbox_token' => env('ASAAS_SANDBOX_TOKEN'),
    'production_url' => env('ASAAS_PRODUCTION_URL', 'https://api.asaas.com'),
    'production_token' => env('ASAAS_PRODUCTION_TOKEN'),
    'sandbox' => env('ASAAS_USE_SANDBOX', true),
    'webhook_token_sandbox' => env('ASAAS_WEBHOOK_TOKEN_SANDBOX'),
    'webhook_token_production' => env('ASAAS_WEBHOOK_TOKEN_PRODUCTION'),

    'plan' => [
        'name' => env('SUBSCRIPTION_PLAN_NAME', 'Plano Premium'),
        'amount' => (float) env('SUBSCRIPTION_PLAN_AMOUNT', 29.90),
        'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 14),
        'grace_days_for_pix' => (int) env('SUBSCRIPTION_PIX_DUE_DAYS', 2),
    ],

    'limits' => [
        'accounts' => (int) env('FREE_LIMIT_ACCOUNTS', 2),
        'cards' => (int) env('FREE_LIMIT_CARDS', 2),
        'transaction_categories' => (int) env('FREE_LIMIT_CATEGORIES', 12),
        'transactions' => (int) env('FREE_LIMIT_TRANSACTIONS', 120),
        'additional_users' => (int) env('FREE_LIMIT_ADDITIONAL_USERS', 0),
    ],

    'read_limits' => [
        'transactions' => (int) env('FREE_READ_LIMIT_TRANSACTIONS', 50),
    ],
];
