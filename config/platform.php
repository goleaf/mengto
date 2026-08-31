<?php

declare(strict_types=1);

return [
    'supported_locales' => ['en', 'lt', 'ru'],
    'demo_seed_environments' => ['local', 'demo', 'testing'],
    'email_verification_enabled' => env('EMAIL_VERIFICATION_ENABLED', true),
    'observability' => [
        'application_logs' => [
            'owner' => 'platform-operations',
            'retention_days' => 14,
        ],
        'security_audit' => [
            'owner' => 'security-and-privacy',
            'retention_days' => 365,
        ],
        'integration_failures' => [
            'owner' => 'platform-operations',
            'retention_days' => 30,
        ],
        'slow_requests' => [
            'owner' => 'platform-operations',
            'retention_days' => 14,
            'enabled' => env('SLOW_REQUEST_LOGGING_ENABLED', true),
            'threshold_ms' => env('SLOW_REQUEST_THRESHOLD_MS', 1000),
            'max_per_minute' => 60,
        ],
        'temporary_files' => [
            'owner' => 'platform-operations',
            'retention_days' => 1,
        ],
    ],
];
