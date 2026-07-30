<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PawCirclePreviewService
{
    public function __construct(
        private readonly PawCirclePrototypeState $state,
        private readonly PawCircleComposerCatalog $composers,
        private readonly PawCircleThreadCatalog $threads,
        private readonly PawCircleInteractionPresenter $interactions,
        private readonly PawCircleCirclePresenter $circle,
        private readonly PawCircleWalkPlanPresenter $walks,
        private readonly PawCircleSharePresenter $shares,
        private readonly PawCircleConversationPresenter $conversationDetails,
        private readonly PawCircleCreatedContentPresenter $created,
        private readonly PawCircleProfilePresenter $profiles,
        private readonly PawCircleFeedPresenter $feed,
        private readonly PawCircleGroupCatalog $groups,
        private readonly PawCircleEventCatalog $events,
        private readonly PawCirclePlaceCatalog $places,
        private readonly PawCirclePlacePresenter $placePresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function homePageData(): array
    {
        return $this->feed->page();
    }

    /**
     * @return array<string, mixed>
     */
    public function circleData(string $filter = 'overview'): array
    {
        return $this->circle->present(
            filter: $filter,
            owner: $this->owner(),
            posts: array_values(array_column($this->interactions->posts([
                ...$this->created->posts(),
                ...$this->posts(),
                ...$this->ariMoments(),
                ...$this->scoutMoments(),
            ]), null, 'key')),
            pets: $this->interactions->pets([
                ...$this->created->pets(),
                ...$this->directoryPets(),
            ]),
            neighbors: $this->interactions->neighbors($this->directoryNeighbors()),
            groups: $this->interactions->groups([
                ...$this->created->groups(),
                ...$this->directoryGroups(),
            ]),
            meetups: $this->interactions->meetups([
                ...$this->created->meetups(),
                ...$this->directoryMeetups(),
            ]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function walkPlanData(string $filter = 'upcoming'): array
    {
        return $this->walks->present($filter, $this->owner());
    }

    /**
     * @return array{name: string, location: string, avatar: string, summary: string}
     */
    public function ownerData(): array
    {
        return $this->profiles->owner();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function shareData(string $target): ?array
    {
        $item = $this->shareTarget($target);

        if ($item === null) {
            return null;
        }

        return [
            'owner' => $this->owner(),
            'share' => $this->shares->present($item, $this->directoryNeighbors()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function postThreadData(string $key): ?array
    {
        $post = $this->feed->post($key);

        if ($post === null) {
            return null;
        }

        $comments = [
            ...$this->threads->comments($post),
            ...$this->state->comments($key),
        ];
        $comments = array_map(
            static fn (array $comment, int $index): array => [
                ...$comment,
                'id' => $comment['id'] ?? 'comment-'.$key.'-'.$index,
                'parent' => $comment['parent'] ?? '',
            ],
            $comments,
            array_keys($comments),
        );

        return [
            'owner' => $this->owner(),
            'post' => $post,
            'comments' => $comments,
            'commentCount' => count($comments),
            'threadGuide' => $this->threads->guide(),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     pet: array{
     *         name: string,
     *         species: string,
     *         breed: string,
     *         age: string,
     *         location: string,
     *         status: string,
     *         story: string,
     *         profile_image: string,
     *         cover_image: string,
     *         cover_image_small: string,
     *         cover_image_medium: string,
     *         facts: array<int, array{label: string, value: string}>,
     *         compatibility: array<int, array{label: string, value: string}>,
     *         gallery: array<int, array{image: string, image_small: string, image_medium: string, alt: string, caption: string}>
     *     },
     *     recentMoments: array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>
     * }
     */
    public function scoutProfileData(): array
    {
        return [
            'owner' => $this->owner(),
            'pet' => $this->scout(),
            'recentMoments' => $this->interactions->posts($this->scoutMoments()),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string},
     *     filters: array<int, string>,
     *     directoryPets: array<int, array{
     *         name: string,
     *         species: string,
     *         breed: string,
     *         age: string,
     *         owner: string,
     *         neighborhood: string,
     *         status: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         image_alt: string,
     *         traits: array<int, string>,
     *         profile_route: string|null
     *     }>
     * }
     */
    public function petDirectoryData(): array
    {
        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'Portland neighbors',
                'title' => 'Pets nearby',
                'description' => 'Meet companions whose routines, favorite places, and people overlap with yours.',
                'count' => '6 companions across 5 neighborhoods',
            ],
            'filters' => ['All pets', 'Dogs', 'Cats', 'Small pets'],
            'directoryPets' => $this->interactions->pets([
                ...$this->created->pets(),
                ...$this->directoryPets(),
            ]),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{
     *         eyebrow: string,
     *         title: string,
     *         description: string,
     *         count: string,
     *         schedule: array<int, array{label: string, value: string, detail: string}>
     *     },
     *     filters: array<int, string>,
     *     directoryMeetups: array<int, array{
     *         key: string,
     *         title: string,
     *         category: string,
     *         day: string,
     *         date: string,
     *         date_label: string,
     *         date_accessible: string,
     *         datetime: string,
     *         time: string,
     *         place: string,
     *         neighborhood: string,
     *         distance: string,
     *         attendees: string,
     *         description: string,
     *         host: string,
     *         host_initials: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         thumbnail: string,
     *         image_alt: string,
     *         tags: array<int, string>
     *     }>
     * }
     */
    public function meetupDirectoryData(): array
    {
        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'Portland meetups',
                'title' => 'Meet your neighborhood pack',
                'description' => 'Low-pressure walks and social time planned by nearby pet people.',
                'count' => '3 meetups · Portland, OR',
                'schedule' => [
                    ['label' => 'Next', 'value' => 'Sat, Aug 1', 'detail' => '10:00 AM'],
                    ['label' => 'Upcoming', 'value' => '3 meetups', 'detail' => '38 going'],
                    ['label' => 'Closest', 'value' => '1.2 miles', 'detail' => 'Laurelhurst'],
                ],
            ],
            'filters' => ['Upcoming', 'Walks', 'Social', 'Indoor'],
            'directoryMeetups' => $this->interactions->meetups([
                ...$this->created->meetups(),
                ...$this->directoryMeetups(),
            ]),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{
     *         eyebrow: string,
     *         title: string,
     *         description: string,
     *         count: string,
     *         highlights: array<int, array{label: string, value: string, detail: string}>
     *     },
     *     filters: array<int, string>,
     *     directoryGroups: array<int, array{
     *         key: string,
     *         name: string,
     *         category: string,
     *         members: string,
     *         activity: string,
     *         topic: string,
     *         description: string,
     *         organizer: string,
     *         organizer_initials: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         thumbnail: string,
     *         image_alt: string,
     *         tags: array<int, string>
     *     }>
     * }
     */
    public function groupDirectoryData(): array
    {
        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'Portland communities',
                'title' => 'Find your people and their pets',
                'description' => 'Join local circles built around routines, neighborhoods, and the pets you care for.',
                'count' => '4 groups · Portland, OR',
                'highlights' => [
                    ['label' => 'Members', 'value' => '13.8k', 'detail' => 'across all groups'],
                    ['label' => 'Activity', 'value' => '420', 'detail' => 'posts this week'],
                    ['label' => 'Circles', 'value' => '4', 'detail' => 'around Portland'],
                ],
            ],
            'filters' => ['Recommended', 'Local', 'Care', 'Outdoors'],
            'directoryGroups' => $this->interactions->groups([
                ...$this->created->groups(),
                ...$this->directoryGroups(),
            ]),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     meetup: array<string, mixed>,
     *     expectations: array<int, array{icon: string, title: string, description: string}>,
     *     attendees: array<int, array{name: string, detail: string, initials: string, tone: string}>,
     *     host: array{name: string, role: string, bio: string, initials: string, tone: string},
     *     details: array<int, array{label: string, value: string}>
     * }
     */
    public function smallDogSocialData(): array
    {
        $meetups = array_column($this->directoryMeetups(), null, 'key');
        $meetup = $meetups['small-dog-social'];

        return [
            'owner' => $this->owner(),
            'meetup' => array_merge($meetup, [
                'eyebrow' => 'Neighborhood meetup',
                'long_description' => 'This small, host-guided social gives dogs time to arrive, observe, and join at their own pace. The fenced lawn is split into an active play area and a quieter decompression corner, with water and shaded benches close by.',
                'meta' => [
                    [
                        'icon' => 'calendar-days',
                        'label' => $meetup['date_label'].' · '.$meetup['time'],
                        'datetime' => $meetup['datetime'],
                        'aria_label' => $meetup['date_accessible'],
                    ],
                    ['icon' => 'map-pin', 'label' => $meetup['place'].' · '.$meetup['neighborhood']],
                    ['icon' => 'navigation', 'label' => $meetup['distance'].' from you'],
                ],
                'stats' => [
                    ['label' => 'Going', 'value' => '18', 'detail' => '8 spots left'],
                    ['label' => 'Duration', 'value' => '60 min', 'detail' => 'easy pace'],
                    ['label' => 'Dog size', 'value' => 'Under 30 lb', 'detail' => 'calm arrivals'],
                ],
                'rsvp' => $this->state->isActive('meetups', $meetup['key']),
            ]),
            'expectations' => [
                [
                    'icon' => 'footprints',
                    'title' => 'Arrive on leash',
                    'description' => 'Take one quiet lap outside the enclosure before choosing a comfortable entry.',
                ],
                [
                    'icon' => 'waves',
                    'title' => 'Pause when needed',
                    'description' => 'A shaded reset area gives pets and people room to step away without leaving.',
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => 'Follow each dog’s pace',
                    'description' => 'Ask before greetings and keep toys or shared treats packed until the host invites them.',
                ],
            ],
            'attendees' => [
                ['name' => 'Jamie & Olive', 'detail' => 'Host · Corgi', 'initials' => 'JO', 'tone' => 'sun'],
                ['name' => 'Mia & Scout', 'detail' => 'Border Collie', 'initials' => 'MS', 'tone' => 'mint'],
                ['name' => 'Ari & Mochi', 'detail' => 'Shiba mix', 'initials' => 'AM', 'tone' => 'paper'],
                ['name' => 'Theo & Bean', 'detail' => 'Terrier mix', 'initials' => 'TB', 'tone' => 'mint'],
            ],
            'host' => [
                'name' => 'Jamie Cho',
                'role' => 'Meetup host · Alberta Arts',
                'bio' => 'Jamie plans low-pressure gatherings for smaller dogs and helps new arrivals find a comfortable starting point.',
                'initials' => 'JC',
                'tone' => 'sun',
            ],
            'details' => [
                ['label' => 'Meeting point', 'value' => 'SE Ankeny entrance, beside the covered picnic tables'],
                ['label' => 'Parking', 'value' => 'Street parking along SE 37th Avenue'],
                ['label' => 'Bring', 'value' => 'Leash, water bowl, waste bags, and your dog’s usual rewards'],
                ['label' => 'Weather plan', 'value' => 'Moves to the covered pavilion during light rain'],
            ],
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     group: array<string, mixed>,
     *     principles: array<int, array{icon: string, title: string, description: string}>,
     *     moderators: array<int, array{name: string, detail: string, initials: string, tone: string}>,
     *     activity: array<int, array{icon: string, title: string, description: string}>,
     *     details: array<int, array{label: string, value: string}>
     * }
     */
    public function apartmentPetsGroupData(): array
    {
        $groups = array_column($this->directoryGroups(), null, 'key');
        $group = $groups['apartment-pets'];

        return [
            'owner' => $this->owner(),
            'group' => [
                ...$group,
                'title' => $group['name'],
                'eyebrow' => 'Portland community',
                'long_description' => 'Apartment Pets PDX is a practical circle for sharing calm routines, enrichment ideas, and neighbor-friendly solutions. Members compare what works in real homes, from hallway training and sound management to small-space play for every kind of companion.',
                'meta' => [
                    ['icon' => 'map-pin', 'label' => 'Portland, Oregon'],
                    ['icon' => 'lock-keyhole-open', 'label' => 'Public group'],
                    ['icon' => 'calendar-days', 'label' => 'Started in 2021'],
                ],
                'stats' => [
                    ['label' => 'Members', 'value' => '2.4k', 'detail' => 'local pet people'],
                    ['label' => 'This week', 'value' => '86 posts', 'detail' => 'steady activity'],
                    ['label' => 'Response', 'value' => '42 min', 'detail' => 'typical first reply'],
                ],
                'joined' => $this->state->isActive('groups', $group['key']),
            ],
            'principles' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => 'Share lived experience',
                    'description' => 'Offer routines you have tried and include enough context for neighbors to adapt them safely.',
                ],
                [
                    'icon' => 'volume-2',
                    'title' => 'Keep buildings peaceful',
                    'description' => 'Discuss sound, shared hallways, elevators, and outdoor access with care for every resident.',
                ],
                [
                    'icon' => 'shield-check',
                    'title' => 'Lead with pet welfare',
                    'description' => 'Use qualified professionals for medical or behavioral concerns and keep advice supportive.',
                ],
            ],
            'moderators' => [
                ['name' => 'Ari Jensen', 'detail' => 'Lead organizer · Dog routines', 'initials' => 'AJ', 'tone' => 'sun'],
                ['name' => 'Lena Brooks', 'detail' => 'Moderator · Cat enrichment', 'initials' => 'LB', 'tone' => 'mint'],
                ['name' => 'Priya Shah', 'detail' => 'Moderator · Small pets', 'initials' => 'PS', 'tone' => 'paper'],
            ],
            'activity' => [
                [
                    'icon' => 'book-open-text',
                    'title' => 'A calmer hallway arrival',
                    'description' => 'Community guide · Updated yesterday',
                ],
                [
                    'icon' => 'messages-square',
                    'title' => 'Window perch ideas for compact rooms',
                    'description' => '24 replies · Active today',
                ],
                [
                    'icon' => 'calendar-clock',
                    'title' => 'Indoor enrichment swap',
                    'description' => 'Thursday, Aug 6 · Buckman Community Room',
                ],
            ],
            'details' => [
                ['label' => 'Who it is for', 'value' => 'Renters, apartment residents, and neighbors sharing compact spaces'],
                ['label' => 'Main topics', 'value' => 'Enrichment, sound, shared spaces, routines, and local resources'],
                ['label' => 'Posting pace', 'value' => 'About 12 new discussions each day'],
                ['label' => 'Community review', 'value' => 'New posts are checked against the group guidelines'],
            ],
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{
     *         eyebrow: string,
     *         title: string,
     *         description: string,
     *         count: string,
     *         highlights: array<int, array{label: string, value: string, detail: string}>
     *     },
     *     filters: array<int, string>,
     *     directoryNeighbors: array<int, array{
     *         key: string,
     *         name: string,
     *         category: string,
     *         neighborhood: string,
     *         distance: string,
     *         pet: string,
     *         status: string,
     *         mutual_count: int,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         thumbnail: string,
     *         image_alt: string,
     *         interests: array<int, string>,
     *         profile_route: string|null
     *     }>
     * }
     */
    public function neighborDirectoryData(): array
    {
        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'Portland neighbors',
                'title' => 'Meet the people behind the pets',
                'description' => 'Find nearby owners who share your routes, routines, and approach to everyday care.',
                'count' => '4 people · Portland, OR',
                'highlights' => [
                    ['label' => 'Closest', 'value' => '0.8 mi', 'detail' => 'Pearl District'],
                    ['label' => 'Shared circles', 'value' => '7', 'detail' => 'across PawCircle'],
                    ['label' => 'Pets', 'value' => '4', 'detail' => 'dogs, cats & rabbits'],
                ],
            ],
            'filters' => ['Recommended', 'Dog people', 'Cat people', 'Foster network'],
            'directoryNeighbors' => $this->interactions->neighbors($this->directoryNeighbors()),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     neighbor: array{
     *         name: string,
     *         category: string,
     *         location: string,
     *         distance: string,
     *         member_since: string,
     *         status: string,
     *         bio: string,
     *         avatar: string,
     *         avatar_alt: string,
     *         cover_image: string,
     *         cover_image_small: string,
     *         cover_image_medium: string,
     *         cover_image_alt: string,
     *         mutual_count: int,
     *         stats: array<int, array{label: string, value: string, detail: string}>,
     *         interests: array<int, string>
     *     },
     *     pet: array{
     *         name: string,
     *         owner_name: string,
     *         breed: string,
     *         age: string,
     *         status: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         image_alt: string,
     *         traits: array<int, string>,
     *         routine: array<int, array{label: string, value: string}>
     *     },
     *     mutualNeighbors: array<int, array{name: string, initials: string, context: string, tone: string}>,
     *     communities: array<int, array{name: string, topic: string, members: string}>,
     *     recentMoments: array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>
     * }
     */
    public function ariNeighborProfileData(): array
    {
        $mutualNeighbors = [
            ['name' => 'Mia Carter', 'initials' => 'MC', 'context' => 'Richmond Walks', 'tone' => 'sun'],
            ['name' => 'Jamie Cho', 'initials' => 'JC', 'context' => 'Apartment Pets PDX', 'tone' => 'mint'],
            ['name' => 'Noah Patel', 'initials' => 'NP', 'context' => 'Trail Tails', 'tone' => 'paper'],
            ['name' => 'Lena Brooks', 'initials' => 'LB', 'context' => 'Foster Network PDX', 'tone' => 'mint'],
        ];

        $mutualCount = count($mutualNeighbors);

        return [
            'owner' => $this->owner(),
            'neighbor' => [
                'key' => 'ari',
                'name' => 'Ari Jensen',
                'handle' => '@ari-jensen',
                'role' => 'Dog walks',
                'category' => 'Dog walks',
                'location' => 'Pearl District · Portland, OR',
                'distance' => '0.8 mi away',
                'member_since' => 'Member since 2024',
                'status' => 'Open to calm cafe walks',
                'bio' => 'Ari and Mochi keep a steady loop between quiet Pearl District blocks, Fields Park, and patios with enough room for a patient hello. They enjoy trading practical training notes with neighbors and helping newer city dogs settle into familiar routes.',
                'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'avatar_alt' => 'Ari relaxing with Mochi in a neighborhood park',
                'cover_image' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1600&h=720&q=85',
                'cover_image_small' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=720&h=480&q=80',
                'cover_image_medium' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1200&h=600&q=82',
                'cover_image_alt' => 'Two Shiba Inu dogs ready for a neighborhood walk',
                'mutual_count' => $mutualCount,
                'stats' => [
                    ['label' => 'Pet', 'value' => 'Mochi', 'detail' => 'Shiba mix'],
                    ['label' => 'Mutuals', 'value' => (string) $mutualCount, 'detail' => 'nearby neighbors'],
                    ['label' => 'Home', 'value' => 'Pearl', 'detail' => '0.8 mi away'],
                ],
                'interests' => ['city walks', 'training', 'quiet patios', 'urban routines'],
                'followed' => $this->state->isActive('follows', 'ari'),
                'actions' => [
                    [
                        'label' => 'Follow',
                        'icon' => 'user-plus',
                        'endpoint' => route('pet-social.actions.perform'),
                        'payload' => [
                            'action' => 'toggle-follow',
                            'target' => 'ari',
                            'label' => 'Ari Jensen',
                        ],
                        'variant' => 'primary',
                        'active' => $this->state->isActive('follows', 'ari'),
                        'active_label' => 'Following',
                        'active_icon' => 'user-check',
                        'pressed' => $this->state->isActive('follows', 'ari'),
                    ],
                    [
                        'label' => 'Message',
                        'icon' => 'message-circle',
                        'href' => route('pet-social.messages.index'),
                        'variant' => 'paper',
                    ],
                ],
            ],
            'pet' => [
                'name' => 'Mochi',
                'owner_name' => 'Ari',
                'breed' => 'Shiba mix',
                'age' => '3 years',
                'status' => 'Calm in familiar places and happiest with patient introductions.',
                'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Mochi sitting with another Shiba at a neighborhood cafe',
                'traits' => ['patient hellos', 'city confident', 'treat motivated'],
                'routine' => [
                    ['label' => 'Favorite route', 'value' => 'NW 11th to Fields Park'],
                    ['label' => 'Best time', 'value' => 'Early morning'],
                    ['label' => 'Cafe rule', 'value' => 'Patio first, table second'],
                ],
            ],
            'mutualNeighbors' => $mutualNeighbors,
            'communities' => [
                ['name' => 'Apartment Pets PDX', 'topic' => 'Small-space routines', 'members' => '2.4k members'],
                ['name' => 'Trail Tails', 'topic' => 'Weekend city loops', 'members' => '8.1k members'],
            ],
            'recentMoments' => $this->interactions->posts($this->ariMoments()),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string, unread_count: int},
     *     filters: array<int, string>,
     *     conversations: array<int, array{
     *         key: string,
     *         name: string,
     *         pet: string,
     *         preview: string,
     *         time: string,
     *         datetime: string,
     *         unread: int,
     *         selected: bool,
     *         image: string,
     *         image_alt: string
     *     }>,
     *     thread: array{
     *         contact: array{key: string, name: string, detail: string, response_note: string, avatar: string, avatar_alt: string, call_requested: bool},
     *         context: array{eyebrow: string, title: string, detail: string, image: string, image_alt: string},
     *         date_label: string,
     *         reply_placeholder: string,
     *         messages: array<int, array{sender: string, time: string, datetime: string, body: string, mine: bool}>
     *     }
     * }
     */
    public function messageCenterData(string $selectedKey = 'ari'): array
    {
        $neighbors = array_column($this->directoryNeighbors(), null, 'key');
        $selectedKey = isset($neighbors[$selectedKey]) ? $selectedKey : 'ari';
        $this->state->markConversationRead($selectedKey);
        $conversations = $this->messageConversations($neighbors);
        $walkPlanConversations = $this->walks->conversationKeys();
        $conversations = array_map(fn (array $conversation): array => [
            ...$conversation,
            'selected' => $conversation['key'] === $selectedKey,
            'unread' => $this->state->conversationIsRead($conversation['key']) ? 0 : $conversation['unread'],
            'walk_plan' => in_array($conversation['key'], $walkPlanConversations, true) ? 'planned' : '',
        ], $conversations);
        $unreadCount = array_sum(array_column($conversations, 'unread'));
        $selectedConversation = array_values(array_filter(
            $conversations,
            static fn (array $conversation): bool => $conversation['key'] === $selectedKey,
        ))[0];
        $selectedNeighbor = $neighbors[$selectedKey];

        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'PawCircle inbox',
                'title' => 'Neighborhood messages',
                'description' => 'Keep walk plans, care notes, and everyday pet updates in one quiet place.',
                'count' => count($conversations).' conversations · '.$unreadCount.' unread',
                'unread_count' => $unreadCount,
            ],
            'filters' => ['All', 'Unread', 'Walk plans'],
            'conversations' => $conversations,
            'walkPlans' => $this->walks->messagePlans(),
            'thread' => [
                'contact' => [
                    'key' => $selectedKey,
                    'name' => $selectedConversation['name'],
                    'detail' => $selectedConversation['pet'].' · '.$selectedNeighbor['neighborhood'],
                    'response_note' => 'Usually replies within an hour',
                    'avatar' => $selectedNeighbor['thumbnail'],
                    'avatar_alt' => $selectedNeighbor['image_alt'],
                    'call_requested' => $this->state->isActive('call-requests', $selectedKey),
                ],
                'context' => [
                    'eyebrow' => 'Conversation context',
                    'title' => $selectedConversation['pet'].' and Scout',
                    'detail' => $selectedNeighbor['status'],
                    'image' => $selectedNeighbor['thumbnail'],
                    'image_alt' => $selectedNeighbor['image_alt'],
                ],
                'date_label' => 'Today',
                'reply_placeholder' => 'Reply to '.$selectedConversation['name'].' and '.$selectedConversation['pet'],
                'messages' => [
                    ...$this->messageThreadMessages($selectedKey, $selectedConversation),
                    ...$this->state->messages($selectedKey),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function conversationDetailsData(string $selectedKey): ?array
    {
        $neighbors = array_column($this->directoryNeighbors(), null, 'key');
        $conversations = array_column($this->messageConversations($neighbors), null, 'key');
        $neighbor = $neighbors[$selectedKey] ?? null;
        $conversation = $conversations[$selectedKey] ?? null;

        if ($neighbor === null || $conversation === null) {
            return null;
        }

        $messages = [
            ...$this->messageThreadMessages($selectedKey, $conversation),
            ...$this->state->messages($selectedKey),
        ];

        return $this->conversationDetails->present(
            owner: $this->owner(),
            neighbor: $neighbor,
            conversation: $conversation,
            messages: $messages,
        );
    }

    /**
     * @param  array<string, array{thumbnail: string, image_alt: string}>  $neighbors
     * @return array<int, array{
     *     key: string,
     *     name: string,
     *     pet: string,
     *     preview: string,
     *     time: string,
     *     datetime: string,
     *     unread: int,
     *     selected: bool,
     *     image: string,
     *     image_alt: string
     * }>
     */
    private function messageConversations(array $neighbors): array
    {
        return [
            [
                'key' => 'ari',
                'name' => 'Ari Jensen',
                'pet' => 'Mochi',
                'preview' => 'Perfect. We will take the quiet patio corner.',
                'time' => '18 min',
                'datetime' => '2026-07-29T09:42:00-07:00',
                'unread' => 2,
                'selected' => true,
                'image' => $neighbors['ari']['thumbnail'],
                'image_alt' => $neighbors['ari']['image_alt'],
            ],
            [
                'key' => 'lena',
                'name' => 'Lena Brooks',
                'pet' => 'Pip',
                'preview' => 'Pip approved the new foster setup after one lap.',
                'time' => '2 hr',
                'datetime' => '2026-07-29T08:00:00-07:00',
                'unread' => 0,
                'selected' => false,
                'image' => $neighbors['lena']['thumbnail'],
                'image_alt' => $neighbors['lena']['image_alt'],
            ],
            [
                'key' => 'noah',
                'name' => 'Noah Patel',
                'pet' => 'Juniper',
                'preview' => 'That shaded route stays comfortable before sunset.',
                'time' => 'Yesterday',
                'datetime' => '2026-07-28T18:30:00-07:00',
                'unread' => 0,
                'selected' => false,
                'image' => $neighbors['noah']['thumbnail'],
                'image_alt' => $neighbors['noah']['image_alt'],
            ],
            [
                'key' => 'priya',
                'name' => 'Priya Shah',
                'pet' => 'Clover',
                'preview' => 'I added the garden routine notes you asked for.',
                'time' => 'Mon',
                'datetime' => '2026-07-27T11:15:00-07:00',
                'unread' => 0,
                'selected' => false,
                'image' => $neighbors['priya']['thumbnail'],
                'image_alt' => $neighbors['priya']['image_alt'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $conversation
     * @return array<int, array{sender: string, time: string, datetime: string, body: string, mine: bool}>
     */
    private function messageThreadMessages(string $selectedKey, array $conversation): array
    {
        if ($selectedKey === 'ari') {
            return [
                [
                    'sender' => 'Ari',
                    'time' => '9:12 AM',
                    'datetime' => '2026-07-29T09:12:00-07:00',
                    'body' => 'Morning! Mochi did well near the cafe yesterday. Would Scout like a short loop before the patio gets busy?',
                    'mine' => false,
                ],
                [
                    'sender' => 'Mia',
                    'time' => '9:18 AM',
                    'datetime' => '2026-07-29T09:18:00-07:00',
                    'body' => 'Scout and I can meet near Fields Park at ten. A short loop sounds perfect.',
                    'mine' => true,
                ],
                [
                    'sender' => 'Ari',
                    'time' => '9:21 AM',
                    'datetime' => '2026-07-29T09:21:00-07:00',
                    'body' => 'Perfect. We will take the quiet patio corner and bring the chicken-free treats for Scout.',
                    'mine' => false,
                ],
                [
                    'sender' => 'Mia',
                    'time' => '9:24 AM',
                    'datetime' => '2026-07-29T09:24:00-07:00',
                    'body' => 'Thank you. We will keep the first hello slow and meet you by the park entrance.',
                    'mine' => true,
                ],
            ];
        }

        return [
            [
                'sender' => Str::before($conversation['name'], ' '),
                'time' => $conversation['time'],
                'datetime' => $conversation['datetime'],
                'body' => $conversation['preview'],
                'mine' => false,
            ],
            [
                'sender' => 'Mia',
                'time' => 'Recently',
                'datetime' => '2026-07-29T09:30:00-07:00',
                'body' => 'Thanks for the update. I saved the note for our next neighborhood plan.',
                'mine' => true,
            ],
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string, unread_count: int},
     *     filters: array<int, string>,
     *     activityGroups: array<int, array{
     *         label: string,
     *         items: array<int, array{
     *             category: string,
     *             title: string,
     *             body: string,
     *             context: string,
     *             time: string,
     *             datetime: string,
     *             unread: bool,
     *             image: string,
     *             image_alt: string
     *         }>
     *     }>,
     *     weeklyStats: array<int, array{label: string, value: string}>,
     *     upcoming: array{eyebrow: string, title: string, date: string, place: string, attendees: string, image: string, image_alt: string},
     *     settings: array<int, array{label: string, description: string, enabled: bool}>
     * }
     */
    public function notificationCenterData(): array
    {
        $neighbors = array_column($this->directoryNeighbors(), null, 'key');
        $meetups = array_column($this->directoryMeetups(), null, 'key');
        $groups = array_column($this->directoryGroups(), null, 'key');

        $activityGroups = [
            [
                'label' => 'Today',
                'items' => [
                    [
                        'category' => 'Paws',
                        'title' => 'Ari sent Scout 12 paws',
                        'body' => 'Your yellow-frisbee moment is getting attention from nearby dog people.',
                        'context' => 'Scout · Recent moment',
                        'time' => '12 min',
                        'datetime' => '2026-07-29T09:48:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['ari']['thumbnail'],
                        'image_alt' => $neighbors['ari']['image_alt'],
                    ],
                    [
                        'category' => 'Reply',
                        'title' => 'Lena replied to your foster checklist',
                        'body' => 'She added a note about creating a quiet room before a new pet arrives.',
                        'context' => 'Foster Network PDX',
                        'time' => '1 hr',
                        'datetime' => '2026-07-29T09:00:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['lena']['thumbnail'],
                        'image_alt' => $neighbors['lena']['image_alt'],
                    ],
                    [
                        'category' => 'Meetup',
                        'title' => 'Calm senior dog stroll is next Wednesday',
                        'body' => 'The shaded riverside route has eight neighbors going.',
                        'context' => 'Sellwood Riverfront Park · 6:00 PM',
                        'time' => '2 hr',
                        'datetime' => '2026-07-29T08:00:00-07:00',
                        'unread' => true,
                        'image' => $meetups['senior-stroll']['thumbnail'],
                        'image_alt' => $meetups['senior-stroll']['image_alt'],
                    ],
                ],
            ],
            [
                'label' => 'Earlier',
                'items' => [
                    [
                        'category' => 'Follow',
                        'title' => 'Priya followed you and Scout',
                        'body' => 'You both share an interest in calm routines and garden time.',
                        'context' => 'St. Johns · 3.8 mi away',
                        'time' => 'Yesterday',
                        'datetime' => '2026-07-28T16:20:00-07:00',
                        'unread' => false,
                        'image' => $neighbors['priya']['thumbnail'],
                        'image_alt' => $neighbors['priya']['image_alt'],
                    ],
                    [
                        'category' => 'Group',
                        'title' => 'Apartment Pets PDX shared a new guide',
                        'body' => 'The community collected practical ideas for quieter hallway arrivals.',
                        'context' => 'Small-space routines · 2.4k members',
                        'time' => 'Yesterday',
                        'datetime' => '2026-07-28T11:30:00-07:00',
                        'unread' => false,
                        'image' => $groups['apartment-pets']['thumbnail'],
                        'image_alt' => $groups['apartment-pets']['image_alt'],
                    ],
                    [
                        'category' => 'Saved',
                        'title' => 'Noah saved your shaded-route note',
                        'body' => 'Your Richmond walk is now part of Noah and Juniper’s summer list.',
                        'context' => 'Juniper · Senior care',
                        'time' => 'Mon',
                        'datetime' => '2026-07-27T15:10:00-07:00',
                        'unread' => false,
                        'image' => $neighbors['noah']['thumbnail'],
                        'image_alt' => $neighbors['noah']['image_alt'],
                    ],
                ],
            ],
        ];

        if ($this->state->notificationsAreRead()) {
            $activityGroups = array_map(
                static fn (array $group): array => [
                    ...$group,
                    'items' => array_map(
                        static fn (array $item): array => [...$item, 'unread' => false],
                        $group['items'],
                    ),
                ],
                $activityGroups,
            );
        }

        $activityItems = array_merge(...array_column($activityGroups, 'items'));
        $unreadCount = count(array_filter($activityItems, static fn (array $item): bool => $item['unread']));
        $settings = $this->state->settings([
            'meetup-reminders' => true,
            'neighbor-replies' => true,
            'weekly-digest' => false,
        ]);

        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'PawCircle activity',
                'title' => 'What happened around your pack',
                'description' => 'Reactions, replies, reminders, and neighbor updates gathered into one calm timeline.',
                'count' => count($activityItems).' updates · '.$unreadCount.' new',
                'unread_count' => $unreadCount,
            ],
            'filters' => ['All activity', 'Mentions', 'Walks', 'Groups'],
            'activityGroups' => $activityGroups,
            'weeklyStats' => [
                ['label' => 'Paws', 'value' => '32'],
                ['label' => 'Replies', 'value' => '8'],
                ['label' => 'Neighbors', 'value' => '3'],
            ],
            'upcoming' => [
                'eyebrow' => 'Next meetup',
                'title' => $meetups['small-dog-social']['title'],
                'date' => $meetups['small-dog-social']['date_label'].' · '.$meetups['small-dog-social']['time'],
                'datetime' => $meetups['small-dog-social']['datetime'],
                'date_accessible' => $meetups['small-dog-social']['date_accessible'],
                'place' => $meetups['small-dog-social']['place'],
                'attendees' => $meetups['small-dog-social']['attendees'],
                'image' => $meetups['small-dog-social']['image'],
                'image_small' => $meetups['small-dog-social']['image_small'],
                'image_medium' => $meetups['small-dog-social']['image_medium'],
                'image_alt' => $meetups['small-dog-social']['image_alt'],
            ],
            'settings' => [
                ['key' => 'meetup-reminders', 'label' => 'Meetup reminders', 'description' => 'A day before local events', 'enabled' => $settings['meetup-reminders']],
                ['key' => 'neighbor-replies', 'label' => 'Neighbor replies', 'description' => 'Replies and mentions', 'enabled' => $settings['neighbor-replies']],
                ['key' => 'weekly-digest', 'label' => 'Weekly digest', 'description' => 'Sunday activity summary', 'enabled' => $settings['weekly-digest']],
            ],
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string},
     *     query: array{label: string, text: string, location: string},
     *     filters: array<int, string>,
     *     results: array<int, array{
     *         kind: string,
     *         title: string,
     *         meta: string,
     *         description: string,
     *         detail: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         image_alt: string,
     *         route: string,
     *         tags: array<int, string>
     *     }>,
     *     pulse: array<int, array{label: string, value: string, detail: string}>,
     *     trending: array<int, array{topic: string, category: string, count: string}>,
     *     weekend: array{eyebrow: string, title: string, date: string, datetime: string, date_accessible: string, place: string, image: string, image_small: string, image_medium: string, image_alt: string}
     * }
     */
    public function discoverData(): array
    {
        $pets = array_column($this->directoryPets(), null, 'name');
        $neighbors = array_column($this->directoryNeighbors(), null, 'key');
        $meetups = array_column($this->directoryMeetups(), null, 'key');
        $groups = array_column($this->directoryGroups(), null, 'key');

        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => 'Local discovery',
                'title' => 'Find your next pet-friendly plan',
                'description' => 'A focused mix of nearby pets, people, walks, and circles that match your pace.',
                'count' => '4 top matches',
            ],
            'query' => [
                'label' => 'Showing matches for',
                'text' => 'calm weekend walks',
                'location' => 'Richmond · within 5 miles',
            ],
            'filters' => ['Top matches', 'Pets', 'People', 'Meetups', 'Groups'],
            'results' => [
                [
                    'kind' => 'Pet',
                    'title' => $pets['Scout']['name'],
                    'meta' => $pets['Scout']['breed'].' · '.$pets['Scout']['neighborhood'],
                    'description' => $pets['Scout']['status'].'. A bright, social companion who likes focused games and roomy park loops.',
                    'detail' => $pets['Scout']['age'].' · with '.$pets['Scout']['owner'],
                    'image' => $pets['Scout']['image'],
                    'image_small' => $pets['Scout']['image_small'],
                    'image_medium' => $pets['Scout']['image_medium'],
                    'image_alt' => $pets['Scout']['image_alt'],
                    'route' => $pets['Scout']['profile_route'],
                    'tags' => $pets['Scout']['traits'],
                ],
                [
                    'kind' => 'Neighbor',
                    'title' => $neighbors['ari']['name'],
                    'meta' => $neighbors['ari']['neighborhood'].' · '.$neighbors['ari']['distance'],
                    'description' => $neighbors['ari']['status'].'. Usually planning easy neighborhood routes with '.$neighbors['ari']['pet'].'.',
                    'detail' => $neighbors['ari']['mutual_count'].' mutual neighbors',
                    'image' => $neighbors['ari']['image'],
                    'image_small' => $neighbors['ari']['image_small'],
                    'image_medium' => $neighbors['ari']['image_medium'],
                    'image_alt' => $neighbors['ari']['image_alt'],
                    'route' => $neighbors['ari']['profile_route'],
                    'tags' => $neighbors['ari']['interests'],
                ],
                [
                    'kind' => 'Meetup',
                    'title' => $meetups['senior-stroll']['title'],
                    'meta' => $meetups['senior-stroll']['date_label'].' · '.$meetups['senior-stroll']['time'],
                    'description' => $meetups['senior-stroll']['description'],
                    'detail' => $meetups['senior-stroll']['place'].' · '.$meetups['senior-stroll']['distance'],
                    'image' => $meetups['senior-stroll']['image'],
                    'image_small' => $meetups['senior-stroll']['image_small'],
                    'image_medium' => $meetups['senior-stroll']['image_medium'],
                    'image_alt' => $meetups['senior-stroll']['image_alt'],
                    'route' => 'pet-social.meetups.index',
                    'tags' => $meetups['senior-stroll']['tags'],
                ],
                [
                    'kind' => 'Group',
                    'title' => $groups['trail-tails']['name'],
                    'meta' => $groups['trail-tails']['topic'],
                    'description' => $groups['trail-tails']['description'],
                    'detail' => $groups['trail-tails']['members'].' · '.$groups['trail-tails']['activity'],
                    'image' => $groups['trail-tails']['image'],
                    'image_small' => $groups['trail-tails']['image_small'],
                    'image_medium' => $groups['trail-tails']['image_medium'],
                    'image_alt' => $groups['trail-tails']['image_alt'],
                    'route' => 'pet-social.groups.index',
                    'tags' => $groups['trail-tails']['tags'],
                ],
            ],
            'pulse' => [
                ['label' => 'Walk plans', 'value' => '27', 'detail' => 'this week'],
                ['label' => 'Fresh posts', 'value' => '14', 'detail' => 'near Richmond'],
                ['label' => 'New neighbors', 'value' => '9', 'detail' => 'within 5 miles'],
            ],
            'trending' => [
                ['topic' => 'Shaded summer routes', 'category' => 'Walks', 'count' => '46 posts'],
                ['topic' => 'Calm dog introductions', 'category' => 'Care', 'count' => '31 posts'],
                ['topic' => 'Indoor cat enrichment', 'category' => 'Cats', 'count' => '28 posts'],
                ['topic' => 'Foster supply swaps', 'category' => 'Community', 'count' => '19 posts'],
            ],
            'weekend' => [
                'eyebrow' => 'This weekend',
                'title' => $meetups['small-dog-social']['title'],
                'date' => $meetups['small-dog-social']['date_label'].' · '.$meetups['small-dog-social']['time'],
                'datetime' => $meetups['small-dog-social']['datetime'],
                'date_accessible' => $meetups['small-dog-social']['date_accessible'],
                'place' => $meetups['small-dog-social']['place'],
                'image' => $meetups['small-dog-social']['image'],
                'image_small' => $meetups['small-dog-social']['image_small'],
                'image_medium' => $meetups['small-dog-social']['image_medium'],
                'image_alt' => $meetups['small-dog-social']['image_alt'],
            ],
        ];
    }

    /**
     * @return array{
     *     owner: array{
     *         name: string,
     *         location: string,
     *         avatar: string,
     *         summary: string,
     *         role: string,
     *         member_since: string,
     *         status: string,
     *         bio: string,
     *         cover_image: string,
     *         cover_image_small: string,
     *         cover_image_medium: string,
     *         cover_image_alt: string,
     *         stats: array<int, array{label: string, value: string, detail: string}>
     *     },
     *     pets: array<int, array{
     *         name: string,
     *         species: string,
     *         breed: string,
     *         age: string,
     *         owner: string,
     *         neighborhood: string,
     *         status: string,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         image_alt: string,
     *         traits: array<int, string>,
     *         profile_route: string|null
     *     }>,
     *     recentMoments: array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>,
     *     availability: array<int, array{label: string, value: string}>,
     *     interests: array<int, string>,
     *     communities: array<int, array{name: string, topic: string, members: string}>
     * }
     */
    public function miaProfileData(): array
    {
        $communities = array_map(
            static fn (array $group): array => [
                'name' => $group['name'],
                'topic' => $group['topic'],
                'members' => $group['members'],
            ],
            array_slice($this->directoryGroups(), 0, 3),
        );

        return [
            'owner' => $this->miaOwner(),
            'pets' => array_slice($this->interactions->pets([
                ...$this->created->pets(),
                ...$this->directoryPets(),
            ]), 0, 4),
            'recentMoments' => $this->interactions->posts($this->scoutMoments()),
            'availability' => [
                ['label' => 'Best time', 'value' => 'Weekend mornings'],
                ['label' => 'Usual pace', 'value' => 'Easy to moderate'],
                ['label' => 'Home base', 'value' => 'Richmond, Portland'],
            ],
            'interests' => ['trail walks', 'foster care', 'cat enrichment', 'quiet parks', 'positive training'],
            'communities' => $communities,
        ];
    }

    /**
     * @return array{
     *     owner: array<string, mixed>,
     *     form: array{
     *         eyebrow: string,
     *         title: string,
     *         description: string,
     *         action: string,
     *         submit_label: string,
     *         submit_icon: string,
     *         cancel_route: string,
     *         active_section: string,
     *         fields: array<int, array<string, mixed>>
     *     }
     * }
     */
    public function composerData(string $kind, array $context = []): array
    {
        $owner = $this->miaOwner();
        $petSlug = (string) ($context['pet'] ?? 'scout');
        $pet = $this->profiles->pet($petSlug) ?? $this->scout();
        $report = isset($context['target'])
            ? $this->profiles->reportContext((string) $context['target'])
            : null;
        $post = isset($context['post'])
            ? $this->feed->editablePost((string) $context['post'])
            : null;
        $postReport = isset($context['target'])
            ? $this->feed->reportContext((string) $context['target'])
            : null;
        $groupReport = isset($context['target'])
            ? $this->groups->reportContext((string) $context['target'])
            : null;
        $eventReport = isset($context['target'])
            ? $this->events->reportContext((string) $context['target'])
            : null;
        $placeReport = isset($context['target'])
            ? $this->placePresenter->reportContext((string) $context['target'])
            : null;
        $placeCorrection = isset($context['target'])
            ? $this->placePresenter->correctionContext((string) $context['target'])
            : null;
        $placeContext = isset($context['place'])
            ? $this->places->find((string) $context['place'])
            : null;

        return [
            'owner' => $owner,
            'form' => $this->composers->form(
                kind: $kind,
                owner: $owner,
                pet: $pet,
                context: [
                    ...$context,
                    'owner_privacy' => $this->state->ownerPrivacy(),
                    'pet_privacy' => $this->state->petPrivacy($petSlug),
                    'report' => $report,
                    'post' => $post,
                    'post_report' => $postReport,
                    'group_report' => $groupReport,
                    'event_report' => $eventReport,
                    'place_report' => $placeReport,
                    'place_correction' => $placeCorrection,
                    'place_context' => $placeContext,
                    'identities' => $this->feed->identities(),
                    'topics' => $this->feed->topics(),
                    'audiences' => $this->feed->audiences(),
                    'comment_policies' => $this->feed->commentPolicies(),
                    'safe_places' => $this->feed->safePlaces(),
                    'media_presets' => $this->feed->mediaPresets(),
                    'post_report_reasons' => $this->feed->reportReasons(),
                ],
                visibilityOptions: $this->profiles->visibilityOptions(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function miaOwner(): array
    {
        return $this->profiles->ownerProfile();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function shareTarget(string $target): ?array
    {
        $postThread = $this->postThreadData($target);

        if ($postThread !== null) {
            $post = $postThread['post'];

            return [
                'target' => $target,
                'type' => 'Pet moment',
                'active_section' => 'feed',
                'eyebrow' => 'Share a pet moment',
                'title' => $post['pet'].' with '.$post['author'],
                'description' => $post['body'],
                'image' => $post['image'],
                'image_small' => $post['image_small'],
                'image_medium' => $post['image_medium'],
                'image_alt' => $post['image_alt'],
                'route' => 'pet-social.posts.show',
                'route_parameters' => ['post' => $target],
            ];
        }

        $createdTarget = $this->created->shareTarget($target);

        if ($createdTarget !== null) {
            return $createdTarget;
        }

        $group = $this->groups->find($target);

        if ($group !== null) {
            return [
                'target' => $target,
                'type' => 'Community',
                'active_section' => 'groups',
                'eyebrow' => 'Share a community',
                'title' => $group['name'],
                'description' => $group['description'],
                'image' => $group['image'],
                'image_small' => $group['image_small'],
                'image_medium' => $group['image_medium'],
                'image_alt' => $group['image_alt'],
                'route' => 'pet-social.groups.show',
                'route_parameters' => ['group' => $target],
            ];
        }

        $event = $this->events->find($target);

        if ($event !== null) {
            return [
                'target' => $target,
                'type' => 'Event',
                'active_section' => 'meetups',
                'eyebrow' => 'Share a public event card',
                'title' => $event['title'],
                'description' => $event['short_description'],
                'image' => $event['image'],
                'image_small' => $event['image_small'],
                'image_medium' => $event['image_medium'],
                'image_alt' => $event['image_alt'],
                'route' => 'pet-social.meetups.show',
                'route_parameters' => ['event' => $target],
            ];
        }

        if ($target === 'mia-carter') {
            $owner = $this->miaOwner();

            return [
                'target' => $target,
                'type' => 'Member profile',
                'active_section' => 'profile',
                'eyebrow' => 'Share a neighbor profile',
                'title' => $owner['name'],
                'description' => $owner['bio'],
                'image' => $owner['cover_image'],
                'image_small' => $owner['cover_image_small'],
                'image_medium' => $owner['cover_image_medium'],
                'image_alt' => $owner['cover_image_alt'],
                'route' => 'pet-social.profile.mia',
                'route_parameters' => [],
            ];
        }

        if (in_array($target, ['scout', 'nori'], true)) {
            $pet = $this->profiles->pet($target);

            if ($pet === null) {
                return null;
            }

            return [
                'target' => $target,
                'type' => 'Pet profile',
                'active_section' => 'pets',
                'eyebrow' => 'Share a pet profile',
                'title' => $pet['name'],
                'description' => $pet['story'],
                'image' => $pet['cover_image'],
                'image_small' => $pet['cover_image_small'],
                'image_medium' => $pet['cover_image_medium'],
                'image_alt' => $pet['cover_image_alt'],
                'route' => $pet['route'],
                'route_parameters' => [],
            ];
        }

        return null;
    }

    /**
     * @return array{name: string, location: string, avatar: string, summary: string}
     */
    private function owner(): array
    {
        return $this->profiles->owner();
    }

    /**
     * @return array<int, array{name: string, type: string, breed: string, age: string, status: string, profile_route: string|null}>
     */
    private function pets(): array
    {
        $scout = $this->profiles->pet('scout');
        $nori = $this->profiles->pet('nori');

        return [
            [
                'name' => $scout['name'] ?? 'Scout',
                'type' => 'Dog',
                'breed' => $scout['breed'] ?? 'Border Collie mix',
                'age' => $scout['age'] ?? '4 years',
                'status' => $scout['status'] ?? 'Available for park walks',
                'profile_route' => 'pet-social.pets.scout',
            ],
            [
                'name' => $nori['name'] ?? 'Nori',
                'type' => 'Cat',
                'breed' => $nori['breed'] ?? 'Tabby',
                'age' => $nori['age'] ?? '2 years',
                'status' => $nori['status'] ?? 'Indoor window watcher',
                'profile_route' => 'pet-social.pets.nori',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     species: string,
     *     breed: string,
     *     age: string,
     *     owner: string,
     *     neighborhood: string,
     *     status: string,
     *     image: string,
     *     image_small: string,
     *     image_medium: string,
     *     image_alt: string,
     *     traits: array<int, string>,
     *     profile_route: string|null
     * }>
     */
    private function directoryPets(): array
    {
        return [
            [
                'name' => 'Scout',
                'species' => 'Dog',
                'breed' => 'Border Collie mix',
                'age' => '4 years',
                'owner' => 'Mia Carter',
                'neighborhood' => 'Richmond',
                'status' => 'Available for park walks',
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Scout, a black and white Border Collie, resting on grass',
                'traits' => ['high energy', 'trail walks'],
                'profile_route' => 'pet-social.pets.scout',
            ],
            [
                'name' => 'Nori',
                'species' => 'Cat',
                'breed' => 'Tabby',
                'age' => '2 years',
                'owner' => 'Mia Carter',
                'neighborhood' => 'Richmond',
                'status' => 'Window-watching expert',
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Nori, a tabby cat, looking toward the camera',
                'traits' => ['indoor', 'curious'],
                'profile_route' => 'pet-social.pets.nori',
            ],
            [
                'name' => 'Maple',
                'species' => 'Dog',
                'breed' => 'Golden Retriever',
                'age' => '6 years',
                'owner' => 'Ari Jensen',
                'neighborhood' => 'Sellwood',
                'status' => 'Easy trail companion',
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Maple, a golden retriever, sitting outside with a flower',
                'traits' => ['calm', 'water fan'],
                'profile_route' => null,
            ],
            [
                'name' => 'Olive',
                'species' => 'Dog',
                'breed' => 'Pembroke Corgi',
                'age' => '3 years',
                'owner' => 'Jamie Cho',
                'neighborhood' => 'Alberta Arts',
                'status' => 'Likes short social walks',
                'image' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Olive, a corgi, sitting on a sunny path',
                'traits' => ['small dog', 'social'],
                'profile_route' => null,
            ],
            [
                'name' => 'Pico',
                'species' => 'Bird',
                'breed' => 'Green-cheek Conure',
                'age' => '5 years',
                'owner' => 'Sam Rivera',
                'neighborhood' => 'Hawthorne',
                'status' => 'Quiet mornings, curious afternoons',
                'image' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Pico, a green-cheek conure, perched on a camera tripod',
                'traits' => ['indoor', 'talkative'],
                'profile_route' => null,
            ],
            [
                'name' => 'Clover',
                'species' => 'Rabbit',
                'breed' => 'Mini Lop mix',
                'age' => '2 years',
                'owner' => 'Priya Shah',
                'neighborhood' => 'St. Johns',
                'status' => 'Gentle garden observer',
                'image' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Clover, a white rabbit, sitting in grass',
                'traits' => ['gentle', 'garden time'],
                'profile_route' => null,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     title: string,
     *     category: string,
     *     day: string,
     *     date: string,
     *     date_label: string,
     *     date_accessible: string,
     *     datetime: string,
     *     time: string,
     *     place: string,
     *     neighborhood: string,
     *     distance: string,
     *     attendees: string,
     *     description: string,
     *     host: string,
     *     host_initials: string,
     *     image: string,
     *     image_small: string,
     *     image_medium: string,
     *     thumbnail: string,
     *     image_alt: string,
     *     tags: array<int, string>
     * }>
     */
    private function directoryMeetups(): array
    {
        return [
            [
                'key' => 'small-dog-social',
                'detail_route' => 'pet-social.meetups.small_dog_social',
                'title' => 'Small dog social hour',
                'category' => 'Social',
                'day' => 'SAT',
                'date' => '01',
                'date_label' => 'Sat, Aug 1',
                'date_accessible' => 'Saturday, August 1, 2026 at 10:00 AM',
                'datetime' => '2026-08-01T10:00:00-07:00',
                'time' => '10:00 AM',
                'place' => 'Laurelhurst Park',
                'neighborhood' => 'Southeast Portland',
                'distance' => '1.2 mi',
                'attendees' => '18 neighbors going',
                'description' => 'A calm, fenced gathering with room for short introductions and plenty of breaks.',
                'host' => 'Jamie Cho',
                'host_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Small dogs meeting in a fenced neighborhood park',
                'tags' => ['small dogs', 'fenced area'],
            ],
            [
                'key' => 'foster-coffee-walk',
                'detail_route' => null,
                'title' => 'Rescue foster coffee walk',
                'category' => 'Coffee walk',
                'day' => 'SUN',
                'date' => '02',
                'date_label' => 'Sun, Aug 2',
                'date_accessible' => 'Sunday, August 2, 2026 at 9:30 AM',
                'datetime' => '2026-08-02T09:30:00-07:00',
                'time' => '9:30 AM',
                'place' => 'Tabor Commons',
                'neighborhood' => 'Mount Tabor',
                'distance' => '2.4 mi',
                'attendees' => '12 neighbors going',
                'description' => 'An easy loop for foster families, recent adopters, and dogs building city confidence.',
                'host' => 'Lena Brooks',
                'host_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Pet owners taking a relaxed community walk through a park',
                'tags' => ['foster friendly', 'easy pace'],
            ],
            [
                'key' => 'senior-stroll',
                'detail_route' => null,
                'title' => 'Calm senior dog stroll',
                'category' => 'Slow walk',
                'day' => 'WED',
                'date' => '05',
                'date_label' => 'Wed, Aug 5',
                'date_accessible' => 'Wednesday, August 5, 2026 at 6:00 PM',
                'datetime' => '2026-08-05T18:00:00-07:00',
                'time' => '6:00 PM',
                'place' => 'Sellwood Riverfront Park',
                'neighborhood' => 'Sellwood',
                'distance' => '3.1 mi',
                'attendees' => '8 neighbors going',
                'description' => 'A shaded riverside route with a gentle pace and frequent sniff stops.',
                'host' => 'Noah Patel',
                'host_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Small dogs exploring an autumn park together',
                'tags' => ['senior pets', 'shaded route'],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     name: string,
     *     category: string,
     *     members: string,
     *     activity: string,
     *     topic: string,
     *     description: string,
     *     organizer: string,
     *     organizer_initials: string,
     *     image: string,
     *     image_small: string,
     *     image_medium: string,
     *     thumbnail: string,
     *     image_alt: string,
     *     tags: array<int, string>
     * }>
     */
    private function directoryGroups(): array
    {
        return [
            [
                'key' => 'apartment-pets',
                'detail_route' => 'pet-social.groups.apartment_pets',
                'name' => 'Apartment Pets PDX',
                'category' => 'Home life',
                'members' => '2.4k members',
                'activity' => '86 posts this week',
                'topic' => 'Small-space routines',
                'description' => 'Swap enrichment ideas, neighbor-friendly routines, and practical fixes for happy pets in smaller homes.',
                'organizer' => 'Ari Jensen',
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Dog and cat resting together on a couch at home',
                'tags' => ['apartments', 'indoor enrichment'],
            ],
            [
                'key' => 'trail-tails',
                'detail_route' => null,
                'name' => 'Trail Tails',
                'category' => 'Outdoors',
                'members' => '8.1k members',
                'activity' => '214 posts this week',
                'topic' => 'Local hikes and safety',
                'description' => 'Plan trail days, share seasonal conditions, and compare low-stress routes around Portland.',
                'organizer' => 'Noah Patel',
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Dogs running together in a neighborhood park',
                'tags' => ['trail walks', 'route reports'],
            ],
            [
                'key' => 'cat-people',
                'detail_route' => null,
                'name' => 'Cat People of Portland',
                'category' => 'Cats',
                'members' => '1.9k members',
                'activity' => '72 posts this week',
                'topic' => 'Indoor cats and neighborhood care',
                'description' => 'Compare enrichment, share cat-friendly local services, and help indoor companions thrive.',
                'organizer' => 'Lena Brooks',
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Two fluffy cats sitting together indoors',
                'tags' => ['cat care', 'enrichment'],
            ],
            [
                'key' => 'foster-network',
                'detail_route' => null,
                'name' => 'Foster Network PDX',
                'category' => 'Care',
                'members' => '1.4k members',
                'activity' => '48 posts this week',
                'topic' => 'Foster support and adoption',
                'description' => 'Connect with experienced fosters, coordinate supplies, and support thoughtful transitions into new homes.',
                'organizer' => 'Priya Shah',
                'organizer_initials' => 'PS',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Foster dog resting on a blue couch',
                'tags' => ['foster care', 'adoption support'],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     name: string,
     *     category: string,
     *     neighborhood: string,
     *     distance: string,
     *     pet: string,
     *     status: string,
     *     mutual_count: int,
     *     image: string,
     *     image_small: string,
     *     image_medium: string,
     *     thumbnail: string,
     *     image_alt: string,
     *     interests: array<int, string>,
     *     profile_route: string|null
     * }>
     */
    private function directoryNeighbors(): array
    {
        return [
            [
                'key' => 'ari',
                'name' => 'Ari Jensen',
                'category' => 'Dog walks',
                'neighborhood' => 'Pearl District',
                'distance' => '0.8 mi',
                'pet' => 'Mochi · Shiba mix',
                'status' => 'Open to calm cafe walks',
                'mutual_count' => 4,
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => 'Ari relaxing with Mochi in a neighborhood park',
                'interests' => ['city walks', 'training'],
                'profile_route' => 'pet-social.neighbors.ari',
            ],
            [
                'key' => 'noah',
                'name' => 'Noah Patel',
                'category' => 'Senior care',
                'neighborhood' => 'Sellwood',
                'distance' => '1.7 mi',
                'pet' => 'Juniper · Senior retriever',
                'status' => 'Usually out before sunset',
                'mutual_count' => 3,
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => 'Noah practicing with a small dog in a wooded park',
                'interests' => ['senior pets', 'shaded routes'],
                'profile_route' => null,
            ],
            [
                'key' => 'lena',
                'name' => 'Lena Brooks',
                'category' => 'Cat people',
                'neighborhood' => 'Alberta Arts',
                'distance' => '2.1 mi',
                'pet' => 'Pip · Domestic shorthair',
                'status' => 'Sharing foster setup notes',
                'mutual_count' => 5,
                'image' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => 'Lena holding a white kitten at home',
                'interests' => ['cat care', 'fostering'],
                'profile_route' => null,
            ],
            [
                'key' => 'priya',
                'name' => 'Priya Shah',
                'category' => 'Small pets',
                'neighborhood' => 'St. Johns',
                'distance' => '3.8 mi',
                'pet' => 'Clover · Mini Lop mix',
                'status' => 'Garden routines and quiet care',
                'mutual_count' => 2,
                'image' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => 'Priya holding a spotted rabbit indoors',
                'interests' => ['rabbits', 'garden time'],
                'profile_route' => null,
            ],
        ];
    }

    /**
     * @return array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>
     */
    private function posts(): array
    {
        return [
            $this->ariFirstMoment(),
            [
                'author' => 'Noah Patel',
                'pet' => 'Juniper',
                'time' => '1 hr ago',
                'datetime' => '2026-07-29T09:00:00-07:00',
                'body' => 'Found a quiet route near Maple Loop with shade almost the whole way. Good for senior pups on warmer afternoons.',
                'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => 'Juniper relaxing during a shady afternoon walk',
                'tags' => ['senior pets', 'walk route'],
                'stats' => ['paws' => '86', 'replies' => '11'],
            ],
            [
                'author' => 'Lena Brooks',
                'pet' => 'Pip',
                'time' => '3 hrs ago',
                'datetime' => '2026-07-29T07:00:00-07:00',
                'body' => 'First successful harness session. Pip mostly accepted the agreement after a careful review of the snack clause.',
                'image' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => 'Pip looking toward the camera from a soft blanket',
                'tags' => ['cat life', 'first steps'],
                'stats' => ['paws' => '203', 'replies' => '37'],
            ],
        ];
    }

    /**
     * @return array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>
     */
    private function ariMoments(): array
    {
        return [
            $this->ariFirstMoment(),
            [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => '3 days ago',
                'datetime' => '2026-07-26T09:00:00-07:00',
                'body' => 'Tried the quiet corner at our neighborhood cafe before the morning rush. Mochi shared the space with another Shiba and settled after one careful lap.',
                'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => 'Mochi sitting with another Shiba at a neighborhood cafe',
                'tags' => ['cafe routine', 'calm introductions'],
                'stats' => ['paws' => '96', 'replies' => '18'],
            ],
        ];
    }

    /**
     * @return array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}
     */
    private function ariFirstMoment(): array
    {
        return [
            'author' => 'Ari Jensen',
            'pet' => 'Mochi',
            'time' => '18 min ago',
            'datetime' => '2026-07-29T09:42:00-07:00',
            'body' => 'Mochi finally made it through the whole cafe patio without asking to inspect every chair. Small wins, very proud.',
            'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1200&h=900&q=80',
            'image_small' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=576&h=432&q=78',
            'image_medium' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=900&h=675&q=80',
            'image_alt' => 'Mochi walking beside another dog on a tree-lined path',
            'tags' => ['training', 'city walks'],
            'stats' => ['paws' => '128', 'replies' => '24'],
        ];
    }

    /**
     * @return array<int, array{title: string, place: string, time: string, datetime: string, date_accessible: string, attendees: string}>
     */
    private function meetups(): array
    {
        return array_map(
            static fn (array $meetup): array => [
                'key' => $meetup['key'],
                'detail_route' => $meetup['detail_route'],
                'title' => $meetup['title'],
                'place' => $meetup['place'],
                'time' => $meetup['day'].' '.$meetup['time'],
                'datetime' => $meetup['datetime'],
                'date_accessible' => $meetup['date_accessible'],
                'attendees' => $meetup['attendees'],
            ],
            array_slice($this->directoryMeetups(), 0, 2),
        );
    }

    /**
     * @return array<int, array{name: string, members: string, topic: string}>
     */
    private function groups(): array
    {
        return array_map(
            static fn (array $group): array => [
                'key' => $group['key'],
                'detail_route' => $group['detail_route'],
                'name' => $group['name'],
                'members' => $group['members'],
                'topic' => $group['topic'],
            ],
            array_slice($this->directoryGroups(), 0, 2),
        );
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function tips(): array
    {
        return [
            [
                'title' => 'Warm sidewalk check',
                'description' => 'Hold the back of your hand to pavement before longer walks.',
            ],
            [
                'title' => 'Quiet intro rule',
                'description' => 'Let new pets meet side by side before face-to-face greetings.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scout(): array
    {
        return $this->profiles->pet('scout') ?? [];
    }

    /**
     * @return array<int, array{author: string, pet: string, time: string, datetime: string, body: string, image: string, image_small: string, image_medium: string, image_alt: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>
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
}
