<?php

declare(strict_types=1);

return [
    'request_ttl_days' => 30,
    'repeat_cooldown_days' => 30,
    'inbox_limit' => 30,
    'relationship_limit' => 60,
    'directory_limit' => 20,
    'account_actor_limit' => 500,
    'account_block_limit' => 1000,
    'request_message_max' => 240,
    'request_limits' => [
        'verified_hour' => 20,
        'verified_day' => 60,
        'new_hour' => 5,
        'new_day' => 10,
        'new_account_days' => 7,
        'duplicate_message_day' => 3,
        'low_acceptance_minimum' => 10,
        'low_acceptance_floor' => 0.10,
        'low_acceptance_hour' => 3,
    ],
];
