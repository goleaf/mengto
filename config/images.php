<?php

declare(strict_types=1);

return [
    'default' => env('IMAGE_DRIVER', 'gd'),

    'public_uploads' => [
        'max_width' => 2560,
        'max_height' => 2560,
        'format' => 'webp',
        'quality' => 82,
    ],

    'pet_profile_uploads' => [
        'max_width' => 2560,
        'max_height' => 2560,
        'format' => 'webp',
        'quality' => 82,
        'recovery_days' => 30,
    ],

    'place_uploads' => [
        'max_upload_kib' => 5120,
        'max_active_per_place' => 10,
        'format' => 'webp',
        'quality' => 82,
        'recovery_days' => 30,
        'pending_retention_days' => 30,
        'failed_retention_days' => 7,
        'variants' => [
            'fallback' => ['width' => 1200, 'height' => 900],
            'small' => ['width' => 576, 'height' => 432],
            'medium' => ['width' => 900, 'height' => 675],
            'large' => ['width' => 1200, 'height' => 900],
        ],
    ],
];
