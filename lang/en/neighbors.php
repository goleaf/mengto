<?php

declare(strict_types=1);

return [
    'page' => [
        'title' => 'Neighbors | PawCircle',
        'eyebrow' => 'Portland neighbors',
        'heading' => 'Meet the people behind the pets',
        'description' => 'Find nearby owners who share your routes, routines, and approach to pet care.',
        'count' => '4 people · Portland, OR',
    ],
    'actions' => [
        'new_message' => 'New message',
    ],
    'summary' => [
        'label' => 'Neighbor summary',
        'unavailable' => 'Neighbor summary is unavailable.',
        'closest' => [
            'label' => 'Closest',
            'value' => '0.8 mi',
            'detail' => 'Pearl District',
        ],
        'circles' => [
            'label' => 'Shared circles',
            'value' => '7',
            'detail' => 'Across PawCircle',
        ],
        'pets' => [
            'label' => 'Pets',
            'value' => '4',
            'detail' => 'Dogs, cats & rabbits',
        ],
    ],
    'filters' => [
        'toolbar_label' => 'Neighbor filters',
        'category_label' => 'Neighbor category filters',
        'recommended' => 'Recommended',
        'dog_people' => 'Dog people',
        'cat_people' => 'Cat people',
        'foster_network' => 'Foster network',
    ],
    'sort' => [
        'label' => 'Neighbor sort order',
        'closest' => 'Closest first',
        'name' => 'Name',
    ],
    'search' => [
        'label' => 'Search neighbors',
        'placeholder' => 'Search by person, pet, or neighborhood',
    ],
    'results' => [
        'title' => 'People nearby',
        'empty_title' => 'No neighbors match these filters',
        'empty_description' => 'Try a broader person, pet, or neighborhood search.',
    ],
    'card' => [
        'empty_interests' => 'Open to new pet circles',
        'brand_initials' => 'PC',
        'follow' => 'Follow',
        'following' => 'Following',
    ],
    'catalog' => [
        'ari' => [
            'name' => 'Ari Jensen',
            'category' => 'Dog walks',
            'neighborhood' => 'Pearl District',
            'distance' => '0.8 mi',
            'pet' => 'Mochi · Shiba mix',
            'status' => 'Open to calm cafe walks',
            'image_alt' => 'Ari relaxing with Mochi in a neighborhood park',
            'interests' => [
                'first' => 'City walks',
                'second' => 'Training',
            ],
        ],
        'noah' => [
            'name' => 'Noah Patel',
            'category' => 'Senior care',
            'neighborhood' => 'Sellwood',
            'distance' => '1.7 mi',
            'pet' => 'Juniper · Senior retriever',
            'status' => 'Usually out before sunset',
            'image_alt' => 'Noah practicing with a small dog in a wooded park',
            'interests' => [
                'first' => 'Senior pets',
                'second' => 'Shaded routes',
            ],
        ],
        'lena' => [
            'name' => 'Lena Brooks',
            'category' => 'Cat people',
            'neighborhood' => 'Alberta Arts',
            'distance' => '2.1 mi',
            'pet' => 'Pip · Domestic shorthair',
            'status' => 'Sharing foster setup notes',
            'image_alt' => 'Lena holding a white kitten at home',
            'interests' => [
                'first' => 'Cat care',
                'second' => 'Fostering',
            ],
        ],
        'priya' => [
            'name' => 'Priya Shah',
            'category' => 'Small pets',
            'neighborhood' => 'St. Johns',
            'distance' => '3.8 mi',
            'pet' => 'Clover · Mini Lop mix',
            'status' => 'Garden routines and quiet care',
            'image_alt' => 'Priya holding a spotted rabbit indoors',
            'interests' => [
                'first' => 'Rabbits',
                'second' => 'Garden time',
            ],
        ],
    ],
    'profile' => [
        'page' => [
            'title' => 'Ari Jensen | PawCircle',
            'back' => 'Back to neighbors',
            'actions_label' => 'Actions for :name',
        ],
        'hero' => [
            'summary_label' => 'Neighbor profile summary',
            'summary_unavailable' => 'Neighbor profile summary is unavailable.',
        ],
        'sections' => [
            'about' => [
                'eyebrow' => 'Around the neighborhood',
                'title' => 'About Ari',
            ],
            'interests' => [
                'title' => 'Shared interests',
                'empty' => 'No shared interests yet.',
            ],
            'mutual_neighbors' => [
                'title' => 'Mutual neighbors',
                'count' => '{0} No mutual neighbors|{1} :count mutual neighbor|[2,*] :count mutual neighbors',
                'empty' => 'No mutual neighbors yet.',
            ],
            'communities' => [
                'title' => 'Communities',
                'empty' => 'No communities joined yet.',
            ],
            'moments' => [
                'eyebrow' => 'From Ari and Mochi',
                'title' => 'Recent moments',
                'empty' => 'No moments shared yet.',
            ],
        ],
        'actions' => [
            'follow' => 'Follow',
            'following' => 'Following',
            'message' => 'Message',
            'plan_walk' => 'Plan a walk',
        ],
        'identity' => [
            'name' => 'Ari Jensen',
            'handle' => '@ari-jensen',
            'category' => 'Dog walks',
            'location' => 'Pearl District, Portland, OR',
            'neighborhood' => 'Pearl',
            'distance' => '0.8 mi away',
            'member_since' => 'Member since 2024',
            'status' => 'Open to calm cafe walks',
            'bio' => 'Ari and Mochi keep a steady loop between quiet Pearl District streets, shaded parks, and patient cafe introductions. They are always happy to compare low-pressure city routines with nearby pet people.',
            'avatar_alt' => 'Ari relaxing with Mochi in a neighborhood park',
            'cover_image_alt' => 'Two Shiba Inu dogs ready for a neighborhood walk',
        ],
        'stats' => [
            'pet' => [
                'label' => 'Pet',
                'detail' => 'Shiba mix',
            ],
            'mutuals' => [
                'label' => 'Mutuals',
                'detail' => 'Nearby neighbors',
            ],
            'home' => [
                'label' => 'Home',
                'value' => 'Pearl',
                'detail' => '0.8 mi away',
            ],
        ],
        'interests' => [
            'city_walks' => 'City walks',
            'training' => 'Training',
            'quiet_patios' => 'Quiet patios',
            'urban_routines' => 'Urban routines',
        ],
        'pet' => [
            'name' => 'Mochi',
            'owner_name' => 'Ari',
            'breed' => 'Shiba mix',
            'age' => '3 years',
            'status' => 'Calm in familiar places and happiest with patient introductions.',
            'image_alt' => 'Mochi sitting with another Shiba at a neighborhood cafe',
            'lives_with' => 'Lives with :owner',
            'traits_empty' => 'Routine traits are unavailable.',
            'routine_empty' => 'No routine details yet.',
            'traits' => [
                'patient_hellos' => 'Patient hellos',
                'city_confident' => 'City confident',
                'treat_motivated' => 'Treat motivated',
            ],
            'routine' => [
                'route_label' => 'Favorite route',
                'route_value' => 'NW 11th to Fields Park',
                'time_label' => 'Best time',
                'time_value' => 'Early morning',
                'cafe_label' => 'Cafe rule',
                'cafe_value' => 'Patio first, table second',
            ],
        ],
        'mutual_neighbors' => [
            'mia' => [
                'name' => 'Mia Carter',
                'context' => 'Richmond walks',
            ],
            'jamie' => [
                'name' => 'Jamie Cho',
                'context' => 'Apartment Pets PDX',
            ],
            'noah' => [
                'name' => 'Noah Patel',
                'context' => 'Trail Tails',
            ],
            'lena' => [
                'name' => 'Lena Brooks',
                'context' => 'Foster Network PDX',
            ],
        ],
        'communities' => [
            'apartment_pets' => [
                'name' => 'Apartment Pets PDX',
                'topic' => 'Small-space routines',
                'members' => '2.4k members',
            ],
            'trail_tails' => [
                'name' => 'Trail Tails',
                'topic' => 'Weekend city loops',
                'members' => '8.1k members',
            ],
        ],
        'moments' => [
            'first' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => '18 min ago',
                'body' => 'Mochi finally made it through the whole cafe patio without rushing a hello. Quiet corners and a pocket of treats helped.',
                'image_alt' => 'Mochi walking beside another dog on a tree-lined path',
                'first_tag' => 'Training',
                'second_tag' => 'City walks',
            ],
            'second' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => '3 days ago',
                'body' => 'We tried the quiet corner at our neighborhood cafe before the morning crowd arrived. Mochi settled in after one slow lap around the patio.',
                'image_alt' => 'Mochi sitting with another Shiba at a neighborhood cafe',
                'first_tag' => 'Cafe routine',
                'second_tag' => 'Calm introductions',
            ],
        ],
    ],
];
