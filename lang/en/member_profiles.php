<?php

return [
    'current' => [
        'avatar_alt' => 'Profile image for :name',
        'summary' => 'Your PawCircle account and private profile.',
    ],
    'page' => [
        'browser_title' => ':name | PawCircle',
        'eyebrow' => 'Community member',
        'description' => 'Only public profile details and publications available to you are shown here.',
        'public_status' => 'Discoverable profile',
    ],
    'actions' => [
        'back_to_discovery' => 'Back to member discovery',
    ],
    'details' => [
        'eyebrow' => 'Profile scope',
        'title' => 'Public details',
        'member_type' => 'Profile type',
        'joined' => 'Member since',
    ],
    'posts' => [
        'eyebrow' => 'Visible to you',
        'title' => 'Recent posts',
        'empty_title' => 'No visible posts',
        'empty_description' => 'This member has no current posts available to your audience.',
    ],
    'pets' => [
        'eyebrow' => 'Public profiles',
        'title' => 'Pets',
        'empty' => 'No public pet profiles are available.',
    ],
    'errors' => [
        'invalid_actor' => 'A member profile requires a user identity.',
        'demo_seed_environment' => 'Discovery demo data may only be created in an allowed environment.',
    ],
    'owner' => [
        'page' => [
            'title' => ':name :handle | PawCircle',
        ],
        'hero' => [
            'summary_label' => 'Owner profile summary',
            'summary_unavailable' => 'Owner profile summary is unavailable.',
            'actions_label' => 'Actions for :name',
        ],
        'tabs' => [
            'label' => 'Owner profile sections',
            'overview' => 'Overview',
            'pets' => 'Pets',
            'posts' => 'Posts',
            'about' => 'About',
        ],
        'preview' => [
            'title' => 'Preview visibility',
            'label' => 'Preview profile as',
            'options' => [
                'owner' => 'Owner',
                'public' => 'Public',
                'follower' => 'Follower',
                'friend' => 'Friend',
            ],
            'audiences' => [
                'owner' => 'You are viewing the complete owner profile.',
                'public' => 'You are viewing the profile as a public visitor.',
                'follower' => 'You are viewing the profile as a follower.',
                'friend' => 'You are viewing the profile as a friend.',
            ],
        ],
        'sections' => [
            'about' => [
                'eyebrow' => 'Around the neighborhood',
                'title' => 'About Mia',
            ],
            'pets' => [
                'eyebrow' => 'At home with Mia',
                'title' => 'Scout, Nori, and family',
                'tab_eyebrow' => 'Separate social profiles',
                'tab_title' => "Mia's pets",
                'empty' => 'No pet profiles are available.',
                'add' => 'Add pet',
            ],
            'posts' => [
                'eyebrow' => 'From Mia',
                'tab_eyebrow' => 'Published by Mia',
                'title' => 'Recent moments',
                'tab_title' => 'Owner posts',
                'empty' => 'No moments shared yet.',
            ],
            'details' => [
                'eyebrow' => 'Public identity',
                'title' => 'Profile details',
            ],
            'interests' => [
                'eyebrow' => 'Common ground',
                'title' => 'Interests',
                'empty' => 'No interests shared yet.',
            ],
            'languages' => [
                'eyebrow' => 'Conversation',
                'title' => 'Languages',
            ],
            'privacy' => [
                'eyebrow' => 'Audience controls',
                'title' => 'Privacy summary',
            ],
            'completion' => [
                'eyebrow' => 'Profile basics',
                'title' => 'Profile readiness',
            ],
            'badges' => [
                'eyebrow' => 'Trust signals',
                'title' => 'Badges',
            ],
            'availability' => [
                'eyebrow' => 'Walk profile',
                'title' => 'Availability',
            ],
            'safety' => [
                'eyebrow' => 'Your boundaries',
                'title' => 'Safety controls',
                'description' => 'Blocking is mutual. Reports are private and never notify the reported profile.',
                'actions_label' => 'Profile safety actions',
            ],
        ],
        'restrictions' => [
            'pets' => [
                'title' => 'Pet profiles are private',
                'tab_description' => 'Mia shares this list only with the selected audience.',
                'overview_description' => "This audience cannot see Mia's pet list.",
            ],
            'posts' => [
                'title' => 'Posts are limited',
                'tab_description' => 'Follow or connect with Mia to see the posts shared with a closer audience.',
                'overview_title' => 'Owner posts are limited',
                'overview_description' => 'Mia shares these moments with a closer audience.',
            ],
        ],
        'identity' => [
            'name' => 'Mia Carter',
            'handle' => '@mia-carter',
            'location' => 'Richmond, Portland, OR',
            'private_location' => 'Location kept private',
            'avatar_alt' => 'Mia Carter smiling outdoors',
            'summary' => 'Weekend trail walker, foster volunteer, and keeper of two very different pet routines.',
            'media_label' => "Open Mia Carter's profile",
            'role' => 'Pet parent and foster volunteer',
            'member_since' => 'Member since 2024',
            'status' => 'Open to weekend walks',
            'bio' => 'Mia plans low-pressure neighborhood walks, shares foster-care routines, and keeps introductions paced around each pet.',
            'cover_image_alt' => 'Scout lying in grass behind a tennis ball',
        ],
        'stats' => [
            'pets' => [
                'label' => 'Pets',
                'detail' => 'Separate profiles',
            ],
            'followers' => [
                'label' => 'Followers',
                'detail' => 'Owner audience',
            ],
            'following' => [
                'label' => 'Following',
                'detail' => 'People and pets',
            ],
            'posts' => [
                'label' => 'Posts',
                'detail' => 'From Mia',
            ],
        ],
        'actions' => [
            'edit' => 'Edit profile',
            'settings' => 'Settings',
            'privacy' => 'Privacy',
            'share' => 'Share',
            'profile_label' => 'Mia Carter profile',
            'follow' => 'Follow Mia',
            'following' => 'Following Mia',
            'friend' => 'Add friend',
            'request_sent' => 'Request sent',
            'message' => 'Message',
            'block' => 'Block profile',
            'unblock' => 'Unblock profile',
            'report' => 'Report profile',
        ],
        'availability' => [
            'time_label' => 'Best time',
            'time_value' => 'Weekend mornings',
            'pace_label' => 'Usual pace',
            'pace_value' => 'Easy to moderate',
            'home_label' => 'Home base',
            'home_value' => 'Richmond, Portland',
            'private_value' => 'Private',
        ],
        'interests' => [
            'trail_walks' => 'Trail walks',
            'foster_care' => 'Foster care',
            'cat_enrichment' => 'Cat enrichment',
            'quiet_parks' => 'Quiet parks',
            'positive_training' => 'Positive training',
        ],
        'languages' => [
            'english' => [
                'title' => 'English',
                'description' => 'Primary profile and conversation language',
            ],
            'spanish' => [
                'title' => 'Spanish',
                'description' => 'Available for conversational messages',
            ],
        ],
        'details' => [
            'username' => 'Username',
            'account_type' => 'Account type',
            'account_type_value' => 'Pet owner and volunteer',
            'joined' => 'Joined',
            'joined_value' => '2024',
            'language' => 'Profile language',
            'language_value' => 'English',
        ],
        'badges' => [
            'email_verified' => 'Email verified',
            'active_volunteer' => 'Active volunteer',
            'profile_complete' => 'Profile complete',
        ],
        'completion' => [
            'label' => 'Profile completeness',
            'detail' => 'Add an optional website to finish the public basics.',
        ],
        'privacy' => [
            'labels' => [
                'location' => 'Location',
                'pets' => 'Pet profiles',
                'posts' => 'Posts',
                'friends' => 'Friends',
                'activity' => 'Activity',
                'care' => 'Care details',
            ],
            'values' => [
                'public' => 'Everyone',
                'members' => 'Registered members',
                'followers' => 'Followers',
                'friends' => 'Friends',
                'owners' => 'Owners and managers',
                'hidden' => 'Hidden',
            ],
        ],
    ],
];
