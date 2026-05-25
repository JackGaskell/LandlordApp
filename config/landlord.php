<?php

return [

    'reminders' => [
        'dispatch_time' => env('REMINDER_DISPATCH_TIME', '08:00'),
        'days_before_due' => [3, 1, 0],
        'days_after_due' => [1, 3, 7],
        /*
        | Channels the dispatcher will attempt. Email respects landlord_settings.
        | Add sms / push here once channel senders are implemented.
        */
        'enabled_channels' => ['email'],
    ],

    'collection' => [
        'due_soon_days' => 7,
        'cycle_time' => env('RENT_COLLECTION_CYCLE_TIME', '07:00'),
    ],

    'reliability' => [
        'on_time_weight' => 1.0,
        'late_weight' => -0.5,
        'missed_weight' => -1.5,
        'partial_weight' => -0.5,
        'consistency_window_months' => 12,
        'cache_ttl_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue names (stored in the jobs.queue column)
    |--------------------------------------------------------------------------
    |
    | default   — general background work
    | reminders — rent reminder emails
    | mail      — optional dedicated mail throughput
    | webhooks  — Stripe and other inbound webhooks (future)
    |
    */
    'queues' => [
        'default' => env('QUEUE_DEFAULT', 'default'),
        'reminders' => env('QUEUE_REMINDERS', 'reminders'),
        'mail' => env('QUEUE_MAIL', 'mail'),
        'webhooks' => env('QUEUE_WEBHOOKS', 'webhooks'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    |
    | When false (default), new users can use the app immediately without
    | verifying email. Set true + implement MustVerifyEmail on User to re-enable.
    |
    */
    'auth' => [
        'require_email_verification' => (bool) env('REQUIRE_EMAIL_VERIFICATION', false),
        /*
        | Public landlord signup at /register. Keep false in production until launch.
        | Tenant accounts remain invite-only via the portal welcome link.
        */
        'registration_enabled' => (bool) env('REGISTRATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment confirmation copy (tenant receipts awaiting landlord review)
    |--------------------------------------------------------------------------
    */
    'payment_confirmations' => [
        'nav' => 'Confirmations',
        'title' => 'Payment confirmations',
        'title_singular' => 'Payment confirmation',
    ],

    'automation' => [
        'auto_enable_portal_on_tenant_create' => (bool) env('AUTO_ENABLE_PORTAL_ON_TENANT_CREATE', true),
    ],

    'portal' => [
        'invite_expiry_days' => (int) env('TENANT_PORTAL_INVITE_EXPIRY_DAYS', 7),
        'proof_max_kb' => (int) env('TENANT_PAYMENT_PROOF_MAX_KB', 5120),
        'proof_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'heic', 'webp'],
        'pay_online_coming_soon' => true,
    ],

    'payment_proofs' => [
        'disk' => env('PAYMENT_PROOF_DISK', 'local'),
        'directory' => 'payment-proofs',
        'max_kb' => (int) env('TENANT_PAYMENT_PROOF_MAX_KB', 5120),
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'heic', 'webp'],
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'heic', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email branding & support
    |--------------------------------------------------------------------------
    |
    | Production providers (set MAIL_MAILER in .env):
    |   resend    — MAIL_MAILER=resend, RESEND_API_KEY=...
    |   postmark  — MAIL_MAILER=postmark, POSTMARK_API_KEY=...
    |   ses       — MAIL_MAILER=ses, AWS credentials
    |   mailgun   — MAIL_MAILER=mailgun (Symfony transport)
    |
    | Local development: MAIL_MAILER=mailpit (SMTP to Mailpit on port 1025).
    |   brew install mailpit && mailpit
    |   Web UI: http://127.0.0.1:8025
    |
    */
    'mail' => [
        'brand_color' => env('MAIL_BRAND_COLOR', '#3b82f6'),
        'support_address' => env('MAIL_SUPPORT_ADDRESS'),
        'currency_symbol' => env('MAIL_CURRENCY_SYMBOL', '£'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe (subscriptions + rent checkout)
    |--------------------------------------------------------------------------
    |
    | Two separate flows share one webhook endpoint; handlers filter by
    | checkout mode and metadata.type (subscription vs rent_payment).
    |
    | Enable: STRIPE_ENABLED=true, composer require stripe/stripe-php
    | Local webhooks: stripe listen --forward-to localhost:8000/stripe/webhook
    |
    */
    'stripe' => [
        'enabled' => (bool) env('STRIPE_ENABLED', false),
        'currency' => env('STRIPE_CURRENCY', 'gbp'),
        'subscription_price_id' => env('STRIPE_SUBSCRIPTION_PRICE_ID'),
        'webhook_tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
        'checkout_urls' => [
            'subscription_success' => env('STRIPE_SUBSCRIPTION_SUCCESS_URL', '/dashboard?billing=success'),
            'subscription_cancel' => env('STRIPE_SUBSCRIPTION_CANCEL_URL', '/dashboard?billing=cancel'),
            'rent_success' => env('STRIPE_RENT_SUCCESS_URL', '/tenants/{tenant}?payment=success'),
            'rent_cancel' => env('STRIPE_RENT_CANCEL_URL', '/tenants/{tenant}?payment=cancel'),
        ],
    ],

];
