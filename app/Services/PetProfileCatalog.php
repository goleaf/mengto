<?php

namespace App\Services;

final class PetProfileCatalog
{
    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return match ($slug) {
            'scout' => $this->scout(),
            'nori' => $this->nori(),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    public function card(array $pet): array
    {
        return [
            'key' => $pet['slug'],
            'name' => $pet['name'],
            'species' => $pet['species'],
            'breed' => $pet['breed'],
            'age' => $pet['age'],
            'owner' => 'Mia Carter',
            'neighborhood' => 'Richmond',
            'status' => $pet['status'],
            'image' => $pet['card_image'],
            'image_small' => $pet['card_image_small'],
            'image_medium' => $pet['card_image_medium'],
            'image_alt' => $pet['card_image_alt'],
            'traits' => $pet['traits'],
            'profile_route' => $pet['route'],
            'profile_parameters' => [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function friends(string $slug): array
    {
        return $slug === 'scout'
            ? [
                [
                    'name' => 'Mochi',
                    'species' => 'Dog',
                    'breed' => 'Shiba Inu',
                    'age' => '3 years',
                    'owner' => 'Ari Jensen',
                    'neighborhood' => 'Pearl District',
                    'status' => 'Calm parallel-walk friend',
                    'image' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => 'Mochi, a Shiba Inu, standing outside',
                    'traits' => ['parallel walks', 'calm hello'],
                    'profile_route' => null,
                ],
                [
                    'name' => 'Juniper',
                    'species' => 'Dog',
                    'breed' => 'Australian Shepherd',
                    'age' => '5 years',
                    'owner' => 'Noah Patel',
                    'neighborhood' => 'Sellwood',
                    'status' => 'Trail companion',
                    'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => 'Juniper, an Australian Shepherd, sitting outdoors',
                    'traits' => ['trail walks', 'high energy'],
                    'profile_route' => null,
                ],
            ]
            : [
                [
                    'name' => 'Pip',
                    'species' => 'Cat',
                    'breed' => 'Domestic Shorthair',
                    'age' => '4 years',
                    'owner' => 'Lena Brooks',
                    'neighborhood' => 'Kerns',
                    'status' => 'Window-to-window friend',
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => 'Pip, a cat, looking up in soft light',
                    'traits' => ['indoor', 'quiet company'],
                    'profile_route' => null,
                ],
            ];
    }

    /**
     * @param  array<string, mixed>  $owner
     * @return array<int, array<string, string>>
     */
    public function managers(string $slug, array $owner): array
    {
        $managers = [
            [
                'name' => 'Mia Carter',
                'role' => 'Primary owner',
                'detail' => 'Profile, privacy, care, and access',
                'avatar' => $owner['avatar'],
            ],
        ];

        if ($slug === 'scout') {
            $managers[] = [
                'name' => 'Alex Carter',
                'role' => 'Caretaker',
                'detail' => 'Walk and feeding reminders',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
            ];
        }

        return $managers;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function moments(string $slug): array
    {
        return $slug === 'scout'
            ? $this->scoutMoments()
            : $this->noriMoments();
    }

    /**
     * @return array<string, mixed>
     */
    private function scout(): array
    {
        return [
            'slug' => 'scout',
            'route' => 'pet-social.pets.scout',
            'name' => 'Scout',
            'handle' => '@mia-carter/scout',
            'role' => 'Dog profile',
            'species' => 'Dog',
            'breed' => 'Border Collie mix',
            'age' => '4 years',
            'location' => 'Richmond, Portland, OR',
            'member_since' => 'With Mia since 2022',
            'status' => 'Available for park walks',
            'story' => 'Scout is happiest when a walk has a destination, a few new smells, and enough time to watch the world. At home, he settles quickly beside Mia and takes his role as window lookout very seriously.',
            'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => 'Scout, a black and white Border Collie, resting on grass',
            'card_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => 'Scout resting outside',
            'traits' => ['friendly', 'active', 'well trained', 'cautious with cats'],
            'facts' => [
                ['label' => 'Species', 'value' => 'Dog'],
                ['label' => 'Breed', 'value' => 'Border Collie mix'],
                ['label' => 'Age', 'value' => '4 years'],
                ['label' => 'Size', 'value' => 'Medium / 42 lb'],
                ['label' => 'Activity', 'value' => 'High with a calm indoor routine'],
            ],
            'care' => [
                ['label' => 'Best walk', 'value' => '45-60 minutes'],
                ['label' => 'Vaccinations', 'value' => 'Up to date'],
                ['label' => 'Food note', 'value' => 'Chicken-free treats'],
                ['label' => 'Special care', 'value' => 'Slow introductions to cats'],
            ],
            'compatibility' => [
                ['label' => 'Dogs', 'value' => 'Friendly after a calm hello'],
                ['label' => 'Children', 'value' => 'Comfortable with older children'],
                ['label' => 'Cats', 'value' => 'Needs a slow introduction'],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=675&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=324&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=506&q=82',
                    'alt' => 'Scout lying in grass behind a tennis ball',
                    'caption' => 'Waiting for one more throw.',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => 'Scout resting on a wooden porch',
                    'caption' => 'Settling in after a neighborhood walk.',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => 'Scout catching a yellow frisbee on grass',
                    'caption' => 'The catch that ended fetch practice.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nori(): array
    {
        return [
            'slug' => 'nori',
            'route' => 'pet-social.pets.nori',
            'name' => 'Nori',
            'handle' => '@mia-carter/nori',
            'role' => 'Cat profile',
            'species' => 'Cat',
            'breed' => 'Tabby',
            'age' => '2 years',
            'location' => 'Richmond, Portland, OR',
            'member_since' => 'With Mia since 2024',
            'status' => 'Indoor window-watching expert',
            'story' => 'Nori approaches new things from a safe perch, takes afternoon sun very seriously, and has a quiet chirp reserved for birds outside the kitchen window.',
            'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => 'Nori, a tabby cat, resting near a bright window',
            'card_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => 'Nori, a tabby cat, looking toward the camera',
            'traits' => ['calm', 'independent', 'curious', 'indoor'],
            'facts' => [
                ['label' => 'Species', 'value' => 'Cat'],
                ['label' => 'Breed', 'value' => 'Tabby'],
                ['label' => 'Age', 'value' => '2 years'],
                ['label' => 'Size', 'value' => 'Small / 9 lb'],
                ['label' => 'Activity', 'value' => 'Quiet mornings, curious afternoons'],
            ],
            'care' => [
                ['label' => 'Home', 'value' => 'Indoor only'],
                ['label' => 'Food note', 'value' => 'Small scheduled meals'],
                ['label' => 'Favorite routine', 'value' => 'Window perch after breakfast'],
                ['label' => 'Special care', 'value' => 'Needs a quiet room for introductions'],
            ],
            'compatibility' => [
                ['label' => 'Cats', 'value' => 'Curious at a distance'],
                ['label' => 'Dogs', 'value' => 'Prefers calm, separated spaces'],
                ['label' => 'Children', 'value' => 'Comfortable with quiet older children'],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => 'Nori looking toward the camera',
                    'caption' => 'Morning inspection complete.',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => 'A tabby cat looking up in soft light',
                    'caption' => 'Listening for the treat drawer.',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => 'A tabby cat resting near a window',
                    'caption' => 'The preferred afternoon office.',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function scoutMoments(): array
    {
        return [
            [
                'author' => 'Mia Carter',
                'pet' => 'Scout',
                'time' => 'Yesterday',
                'datetime' => '2026-07-28T17:30:00-07:00',
                'body' => 'Scout locked onto the yellow frisbee and caught it on the second try. The trip home was much quieter.',
                'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Scout catching a yellow frisbee on the grass',
                'tags' => ['fetch', 'Scout'],
                'stats' => ['paws' => '94', 'replies' => '16'],
            ],
            [
                'author' => 'Mia Carter',
                'pet' => 'Scout',
                'time' => '4 days ago',
                'datetime' => '2026-07-25T16:00:00-07:00',
                'body' => 'After a calm neighborhood walk, Scout claimed the porch and watched the trees until dinner.',
                'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Scout resting on a wooden porch',
                'tags' => ['slow afternoon', 'small wins'],
                'stats' => ['paws' => '121', 'replies' => '21'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function noriMoments(): array
    {
        return [
            [
                'author' => 'Mia Carter',
                'pet' => 'Nori',
                'time' => '2 days ago',
                'datetime' => '2026-07-27T14:10:00-07:00',
                'body' => 'Nori found a new stripe of afternoon sun and politely moved every notebook out of it.',
                'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Nori resting in a warm patch of light',
                'tags' => ['Nori', 'indoor life'],
                'stats' => ['paws' => '76', 'replies' => '9'],
            ],
        ];
    }
}
