<?php

declare(strict_types=1);

return [
    'class_namespace' => 'App\\Livewire',
    'class_path' => app_path('Livewire'),
    'view_path' => resource_path('views/livewire'),

    // Rotate when a deployment changes the trusted shape of signed snapshots.
    'release_token' => '2026-08-30-onboarding-account-binding-v1',

    'make_command' => [
        'type' => 'class',
        'emoji' => false,
        'with' => [
            'js' => false,
            'css' => false,
            'test' => false,
        ],
    ],
];
