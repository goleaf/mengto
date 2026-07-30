<?php

declare(strict_types=1);

return [
    'supported_locales' => ['en', 'lt', 'ru'],
    'demo_seed_environments' => ['local', 'testing'],
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
        'temporary_files' => [
            'owner' => 'platform-operations',
            'retention_days' => 1,
        ],
    ],
];
