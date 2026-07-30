<?php

namespace App\Services;

final class FeedCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function posts(): array
    {
        return [
            [
                'key' => 'mochi-cafe-win',
                'format' => 'photo',
                'type_label' => 'Photo update',
                'author' => 'Ari Jensen',
                'handle' => '@ari-jensen',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.neighbors.ari',
                'author_parameters' => [],
                'represented' => 'Mochi',
                'represented_kind' => 'Pet profile',
                'manager' => 'Managed by Ari Jensen',
                'pet_slug' => 'mochi',
                'species' => 'dogs',
                'published_at' => '2026-07-29T09:42:00-07:00',
                'time' => '18 min ago',
                'title' => null,
                'body' => 'Mochi made it through the whole cafe patio without inspecting every chair. We kept the first visit short, chose the quiet corner, and left while it was still easy.',
                'topic' => 'Training',
                'location' => 'Pearl District',
                'audience' => 'Followers and friends',
                'comment_policy' => 'Followers',
                'tags' => ['training', 'city walks'],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => 'You follow Mochi and save calm-training posts.',
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1769635325695-dead509dc5b3',
                        'Mochi, a Shiba Inu, standing inside a quiet cafe',
                        'A calm moment inside the pet-friendly cafe.',
                    ),
                ],
                'reaction_counts' => ['like' => 84, 'love' => 31, 'support' => 9, 'useful' => 4],
                'replies' => 24,
                'reposts' => 6,
            ],
            [
                'key' => 'scout-shaded-loop',
                'format' => 'photo',
                'type_label' => 'Photo carousel',
                'author' => 'Scout',
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.pets.scout',
                'author_parameters' => [],
                'represented' => 'Scout',
                'represented_kind' => 'Pet profile',
                'manager' => 'Published as Scout · managed by Mia Carter',
                'pet_slug' => 'scout',
                'species' => 'dogs',
                'published_at' => '2026-07-29T08:25:00-07:00',
                'time' => '1 hr ago',
                'title' => 'A shaded loop worth repeating',
                'body' => 'We tried the east loop before breakfast. The route stayed shaded, the first greeting was calm, and Scout found enough room to settle before heading home.',
                'topic' => 'Walks',
                'location' => 'Laurelhurst Park',
                'audience' => 'Everyone',
                'comment_policy' => 'Everyone',
                'tags' => ['Scout', 'walk route', 'Portland'],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => 'Scout is one of your managed pet profiles.',
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        'Scout resting on grass after a shaded park walk',
                        'A calm pause near the east loop.',
                    ),
                    $this->image(
                        'photo-1624361239583-7ba5ffb376f5',
                        'Scout lying in grass behind a tennis ball',
                        'One last throw before heading home.',
                    ),
                    $this->image(
                        'photo-1621169225409-5de158d10015',
                        'Scout resting on a wooden porch',
                        'Back home and fully settled.',
                    ),
                ],
                'reaction_counts' => ['like' => 91, 'love' => 42, 'support' => 8, 'useful' => 18],
                'replies' => 16,
                'reposts' => 11,
            ],
            [
                'key' => 'dr-elena-heat-check',
                'format' => 'expert',
                'type_label' => 'Expert note',
                'author' => 'Dr. Elena Ruiz',
                'handle' => '@dr-elena-ruiz',
                'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => 'Veterinary profile',
                'represented_kind' => 'Verified specialist',
                'manager' => 'Licensed in Oregon · general practice',
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-29T07:40:00-07:00',
                'time' => '2 hrs ago',
                'title' => 'Three signs a warm walk should end early',
                'body' => 'Slowing down, seeking shade, and unusually heavy panting are reasons to stop and cool down. Social advice cannot diagnose heat illness. Contact a veterinarian promptly when symptoms continue or become severe.',
                'topic' => 'Health',
                'location' => 'Portland, OR',
                'audience' => 'Everyone',
                'comment_policy' => 'Everyone',
                'tags' => ['summer safety', 'veterinary note'],
                'feeds' => ['home', 'experts'],
                'why' => 'You follow local care and summer-safety topics.',
                'verified' => true,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [],
                'reaction_counts' => ['like' => 36, 'love' => 4, 'support' => 14, 'useful' => 112],
                'replies' => 19,
                'reposts' => 38,
            ],
            [
                'key' => 'rose-city-mabel-home',
                'format' => 'adoption',
                'type_label' => 'Adoption profile',
                'author' => 'Rose City Animal Shelter',
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => 'Mabel',
                'represented_kind' => 'Shelter pet',
                'manager' => 'Verified shelter · adoption team',
                'pet_slug' => 'mabel',
                'species' => 'dogs',
                'published_at' => '2026-07-29T06:30:00-07:00',
                'time' => '3 hrs ago',
                'title' => 'Mabel is ready for a quiet home',
                'body' => 'Mabel is a five-year-old mixed-breed dog who settles well after a short introduction. She would thrive with predictable routines, a secure yard, and a family comfortable continuing positive training.',
                'topic' => 'Adoption',
                'location' => 'North Portland',
                'audience' => 'Everyone',
                'comment_policy' => 'Followers',
                'tags' => ['adoption', 'quiet home'],
                'feeds' => ['home', 'shelters'],
                'why' => 'You asked to see verified shelters near Portland.',
                'verified' => true,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1558788353-f76d92427f16',
                        'Mabel, a brown shelter dog, standing outside',
                        'Mabel during a quiet afternoon yard break.',
                    ),
                ],
                'reaction_counts' => ['like' => 44, 'love' => 75, 'support' => 63, 'useful' => 12],
                'replies' => 28,
                'reposts' => 54,
            ],
            [
                'key' => 'willow-lost-richmond',
                'format' => 'lost',
                'type_label' => 'Lost pet alert',
                'author' => 'Lena Brooks',
                'handle' => '@lena-brooks',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => 'Willow',
                'represented_kind' => 'Lost pet',
                'manager' => 'Contact the owner through PawCircle',
                'pet_slug' => 'willow',
                'species' => 'cats',
                'published_at' => '2026-07-29T09:20:00-07:00',
                'time' => '40 min ago',
                'title' => 'Willow was last seen near Richmond School',
                'body' => 'Willow is a small grey tabby wearing a green breakaway collar. Please do not chase her. Use the in-platform contact button with a safe public location if you see her.',
                'topic' => 'Lost and found',
                'location' => 'Richmond neighborhood · approximate area',
                'audience' => 'Local emergency reach',
                'comment_policy' => 'Registered members',
                'tags' => ['lost cat', 'Richmond'],
                'feeds' => ['home', 'local', 'alerts'],
                'why' => 'This active alert is within your approximate neighborhood.',
                'verified' => false,
                'urgent' => true,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1573865526739-10659fec78a5',
                        'Willow, a grey tabby cat, looking upward',
                        'Recent photo provided by Willow’s owner.',
                    ),
                ],
                'reaction_counts' => ['support' => 198, 'useful' => 86],
                'replies' => 31,
                'reposts' => 117,
            ],
            [
                'key' => 'sunny-first-play-video',
                'format' => 'video',
                'type_label' => 'Shelter pet video',
                'author' => 'Rose City Animal Shelter',
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => 'Sunny',
                'represented_kind' => 'Pet profile',
                'manager' => 'Published by verified shelter staff',
                'pet_slug' => 'sunny',
                'species' => 'dogs',
                'published_at' => '2026-07-28T17:10:00-07:00',
                'time' => 'Yesterday',
                'title' => 'Sunny’s first play session',
                'body' => 'A young foster puppy gets a calm play break while waiting for a permanent home. Video never autoplays.',
                'topic' => 'Enrichment',
                'location' => 'Portland, Oregon',
                'audience' => 'Public',
                'comment_policy' => 'Registered members',
                'tags' => ['foster puppy', 'play enrichment'],
                'feeds' => ['home', 'pets', 'shelters', 'video'],
                'why' => 'You follow verified animal shelters near Portland.',
                'verified' => true,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    [
                        'type' => 'video',
                        'source' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/Puppy_playing.webm',
                        'mime' => 'video/webm',
                        'poster' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=675&q=85',
                        'poster_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=324&q=80',
                        'poster_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=506&q=82',
                        'alt' => 'A young puppy during a supervised play session',
                        'caption' => 'Short play session with sound controls and native playback.',
                        'attribution' => 'Puppy playing video by Subhashish Panigrahi, CC BY-SA 3.0',
                        'attribution_url' => 'https://commons.wikimedia.org/wiki/File:Puppy_playing.webm',
                    ],
                ],
                'reaction_counts' => ['like' => 73, 'love' => 41, 'support' => 3],
                'replies' => 12,
                'reposts' => 4,
            ],
            [
                'key' => 'apartment-pets-rain-plan',
                'format' => 'poll',
                'type_label' => 'Community poll',
                'author' => 'Apartment Pets PDX',
                'handle' => '@apartment-pets-pdx',
                'avatar' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.groups.apartment_pets',
                'author_parameters' => [],
                'represented' => 'Apartment Pets PDX',
                'represented_kind' => 'Open group',
                'manager' => 'Posted by group moderator Ari Jensen',
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-28T13:00:00-07:00',
                'time' => 'Yesterday',
                'title' => 'Which rainy-day meetup should we plan?',
                'body' => 'Choose the easiest low-pressure option. Results help with planning and are not scientific community statistics.',
                'topic' => 'Community',
                'location' => 'Portland',
                'audience' => 'Group members',
                'comment_policy' => 'Group members',
                'tags' => ['indoor meetup', 'community poll'],
                'feeds' => ['home', 'groups'],
                'why' => 'You joined Apartment Pets PDX.',
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [],
                'poll_options' => [
                    ['label' => 'Quiet cafe patio', 'votes' => 46],
                    ['label' => 'Covered park pavilion', 'votes' => 71],
                    ['label' => 'Indoor training room', 'votes' => 39],
                ],
                'reaction_counts' => ['like' => 24, 'useful' => 19],
                'replies' => 22,
                'reposts' => 2,
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    public function stories(): array
    {
        return [
            $this->story('Scout', 'Park loop', 'photo-1654256578072-b932c33cb92e', 'pet-social.pets.scout', true),
            $this->story('Nori', 'Window watch', 'photo-1518791841217-8f162f1e1131', 'pet-social.pets.nori', true),
            $this->story('Mochi', 'Cafe win', 'photo-1769635325695-dead509dc5b3', 'pet-social.neighbors.ari'),
            $this->story('Rose City', 'Adoption day', 'photo-1558788353-f76d92427f16'),
            $this->story('Dr. Elena', 'Heat safety', 'photo-1559839734-2b71ea197ec2'),
            $this->story('Juniper', 'Trail note', 'photo-1605568427561-40dd23c2acea'),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function modes(): array
    {
        return [
            'home' => ['label' => 'For you', 'icon' => 'sparkles'],
            'following' => ['label' => 'Following', 'icon' => 'user-check'],
            'friends' => ['label' => 'Friends', 'icon' => 'users-round'],
            'pets' => ['label' => 'Pets', 'icon' => 'paw-print'],
            'local' => ['label' => 'Local', 'icon' => 'map-pin'],
            'groups' => ['label' => 'Groups', 'icon' => 'messages-square'],
            'experts' => ['label' => 'Experts', 'icon' => 'badge-check'],
            'shelters' => ['label' => 'Shelters', 'icon' => 'house-heart'],
            'alerts' => ['label' => 'Lost & found', 'icon' => 'siren'],
            'video' => ['label' => 'Video', 'icon' => 'play'],
            'photos' => ['label' => 'Photos', 'icon' => 'images'],
            'saved' => ['label' => 'Saved', 'icon' => 'bookmark'],
            'drafts' => ['label' => 'Drafts', 'icon' => 'file-pen-line'],
            'archive' => ['label' => 'Archive', 'icon' => 'archive'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function mediaPresets(): array
    {
        return [
            'none' => [
                'label' => 'Text only',
                'format' => 'text',
                'media' => [],
            ],
            'scout-field' => [
                'label' => 'Scout in the field',
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        'Scout resting in a green field',
                        'A calm break outdoors.',
                    ),
                ],
            ],
            'nori-window' => [
                'label' => 'Nori by the window',
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1495360010541-f48722b34f7d',
                        'Nori resting beside a bright window',
                        'Afternoon window watch.',
                    ),
                ],
            ],
            'park-carousel' => [
                'label' => 'Three-photo park carousel',
                'format' => 'photo',
                'media' => [
                    $this->image('photo-1624361239583-7ba5ffb376f5', 'Scout waiting behind a tennis ball', 'Ready for another throw.'),
                    $this->image('photo-1625679895477-526b21a77f0c', 'Scout catching a yellow frisbee', 'A clean catch on the grass.'),
                    $this->image('photo-1621169225409-5de158d10015', 'Scout resting on a wooden porch', 'Quiet time after the park.'),
                ],
            ],
            'play-video' => [
                'label' => 'Short pet video',
                'format' => 'video',
                'media' => [
                    [
                        'type' => 'video',
                        'source' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/Puppy_playing.webm',
                        'mime' => 'video/webm',
                        'poster' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=675&q=85',
                        'poster_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=324&q=80',
                        'poster_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=506&q=82',
                        'alt' => 'A young dog playing indoors',
                        'caption' => 'Short pet play video.',
                        'attribution' => 'Puppy playing video by Subhashish Panigrahi, CC BY-SA 3.0',
                        'attribution_url' => 'https://commons.wikimedia.org/wiki/File:Puppy_playing.webm',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function topics(): array
    {
        return [
            'walks' => 'Walks',
            'care' => 'Care',
            'health' => 'Health',
            'training' => 'Training',
            'enrichment' => 'Enrichment',
            'adoption' => 'Adoption',
            'lost-found' => 'Lost and found',
            'community' => 'Community',
            'photography' => 'Photography',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function audiences(): array
    {
        return [
            'public' => 'Everyone',
            'members' => 'Registered members',
            'followers' => 'Followers',
            'friends' => 'Friends',
            'close-friends' => 'Close friends',
            'owners' => 'Pet owners and managers',
            'private' => 'Only me',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function commentPolicies(): array
    {
        return [
            'all' => 'Everyone',
            'followers' => 'Followers',
            'friends' => 'Friends',
            'mentioned' => 'Mentioned profiles',
            'none' => 'Comments off',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identities(): array
    {
        return [
            'mia' => 'Mia Carter · owner profile',
            'scout' => 'Scout · managed pet profile',
            'nori' => 'Nori · managed pet profile',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function safePlaces(): array
    {
        return [
            'none' => 'Do not show a place',
            'portland' => 'Portland, OR',
            'richmond' => 'Richmond neighborhood',
            'laurelhurst' => 'Laurelhurst Park',
            'fields-park' => 'Fields Park',
            'pearl-district' => 'Pearl District',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reactionOptions(bool $supportiveOnly = false): array
    {
        if ($supportiveOnly) {
            return [
                'support' => 'Support',
                'useful' => 'Useful',
            ];
        }

        return [
            'like' => 'Like',
            'love' => 'Love',
            'funny' => 'Funny',
            'support' => 'Support',
            'useful' => 'Useful',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reportReasons(): array
    {
        return [
            'spam' => 'Spam or repetitive promotion',
            'fraud' => 'Fraud or scam',
            'animal-safety' => 'Animal safety concern',
            'dangerous-advice' => 'Dangerous medical advice',
            'stolen-media' => 'Stolen photos or video',
            'personal-data' => 'Personal information exposed',
            'false-alert' => 'False lost-pet alert',
            'harassment' => 'Harassment or hate',
            'other' => 'Other concern',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identity(string $key): array
    {
        return match ($key) {
            'scout' => [
                'author' => 'Scout',
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.pets.scout',
                'represented' => 'Scout',
                'represented_kind' => 'Pet profile',
                'manager' => 'Published as Scout · managed by Mia Carter',
                'pet_slug' => 'scout',
                'species' => 'dogs',
            ],
            'nori' => [
                'author' => 'Nori',
                'handle' => '@mia-carter/nori',
                'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.pets.nori',
                'represented' => 'Nori',
                'represented_kind' => 'Pet profile',
                'manager' => 'Published as Nori · managed by Mia Carter',
                'pet_slug' => 'nori',
                'species' => 'cats',
            ],
            default => [
                'author' => 'Mia Carter',
                'handle' => '@mia-carter',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pet-social.profile.mia',
                'represented' => 'Mia Carter',
                'represented_kind' => 'Owner profile',
                'manager' => 'Published by Mia Carter',
                'pet_slug' => '',
                'species' => 'all',
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function image(string $id, string $alt, string $caption): array
    {
        $base = 'https://images.unsplash.com/'.$id;

        return [
            'type' => 'image',
            'image' => $base.'?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => $base.'?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => $base.'?auto=format&fit=crop&w=900&h=675&q=82',
            'alt' => $alt,
            'caption' => $caption,
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    private function story(
        string $name,
        string $label,
        string $image,
        ?string $route = null,
        bool $mine = false,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'caption' => $label,
            'image' => 'https://images.unsplash.com/'.$image.'?auto=format&fit=crop&crop=faces&w=240&h=240&q=80',
            'route' => $route ?? 'pet-social.preview',
            'mine' => $mine,
            'unseen' => ! $mine,
        ];
    }
}
