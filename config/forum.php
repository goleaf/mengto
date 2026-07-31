<?php

declare(strict_types=1);

return [
    'lifecycle' => [
        'stale_after_days' => 180,
        'necropost_after_days' => 90,
        'archive_review_after_days' => 365,
        'retention_review_after_days' => 2555,
        'bump_cooldown_hours' => 168,
        'allow_author_reopen' => true,
        'allow_author_archive' => true,
        'allow_author_remove' => true,
        'allow_bumping' => true,
        'auto_archive_enabled' => false,
        'update_requests_per_day' => 5,
        'visible_history_limit' => 20,
        'visible_request_limit' => 20,
    ],
];
