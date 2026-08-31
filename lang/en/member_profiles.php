<?php

return [
    'current' => [
        'avatar_alt' => 'Profile image for :name',
        'summary' => 'Your PawCircle account and private profile.',
    ],
    'page' => [
        'browser_title' => ':name | PawCircle',
        'eyebrow' => 'Community member',
        'description' => 'Profile details and publications available to you are shown here.',
        'public_status' => 'Discoverable profile',
        'private_status' => 'Private profile',
    ],
    'actions' => ['back_to_discovery' => 'Back to member discovery'],
    'details' => [
        'eyebrow' => 'Profile scope', 'title' => 'Profile details', 'member_type' => 'Profile type',
        'joined' => 'Member since', 'verification' => 'Email status', 'email_verified' => 'Verified email',
    ],
    'stats' => ['label' => 'Profile activity', 'pets' => 'Pets', 'posts' => 'Posts'],
    'posts' => [
        'eyebrow' => 'Visible to you', 'title' => 'Recent posts', 'empty_title' => 'No visible posts',
        'empty_description' => 'No posts are available to you.',
    ],
    'pets' => ['eyebrow' => 'Visible to you', 'title' => 'Pets', 'empty' => 'No pet profiles are available to you.'],
    'errors' => [
        'invalid_actor' => 'A member profile requires a user identity.',
        'demo_seed_environment' => 'Discovery demo data may only be created in an allowed environment.',
    ],
];
