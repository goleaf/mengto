<?php

return [
    'page' => [
        'title' => 'Share :title | PawCircle',
        'back_to_original' => 'Back to original',
    ],
    'channels' => [
        'eyebrow' => 'Outside PawCircle',
        'title' => 'Choose a channel',
        'count' => '{0} No channels|{1} :count channel|[2,*] :count channels',
        'email' => [
            'title' => 'Email',
            'description' => 'Open a ready-to-send email with the PawCircle link.',
            'action' => 'Open email',
        ],
        'text' => [
            'title' => 'Text message',
            'description' => 'Open your messaging app with the link already included.',
            'action' => 'Open messages',
        ],
        'original' => [
            'title' => 'Original page',
            'description' => 'Review the full PawCircle page before you send it.',
            'action' => 'Open original',
        ],
        'empty' => [
            'title' => 'No sharing channels',
            'description' => 'No sharing channels are available.',
        ],
    ],
    'neighbors' => [
        'eyebrow' => 'Inside PawCircle',
        'title' => 'Send to a neighbor',
        'count' => '{0} No neighbors|{1} :count neighbor|[2,*] :count neighbors',
        'send' => 'Send',
        'empty' => [
            'title' => 'No neighbors to send to',
            'description' => 'No PawCircle neighbors are available.',
        ],
    ],
    'details' => [
        'title' => 'Share details',
        'type' => 'Share type',
        'destination' => 'Destination',
        'link' => 'Link',
        'empty' => 'No share details are available.',
    ],
    'privacy' => [
        'title' => 'You choose the audience',
        'description' => 'The link opens public PawCircle content. Private messages and contact details are never included.',
    ],
    'message' => [
        'body' => 'I thought you would enjoy :title on PawCircle: :url',
        'subject' => 'From PawCircle: :title',
    ],
    'targets' => [
        'pet_moment' => [
            'type' => 'Pet moment',
            'eyebrow' => 'Share a pet moment',
        ],
        'community' => [
            'type' => 'Community',
            'eyebrow' => 'Share a community',
        ],
        'meetup' => [
            'type' => 'Meetup',
            'eyebrow' => 'Share a meetup',
        ],
        'member_profile' => [
            'type' => 'Member profile',
            'eyebrow' => 'Share a neighbor profile',
        ],
        'pet_profile' => [
            'type' => 'Pet profile',
            'eyebrow' => 'Share a pet profile',
        ],
    ],
];
