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
];
