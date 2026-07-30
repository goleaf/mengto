<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class PawCircleCreatedContentPresenter
{
    public function __construct(private readonly PawCirclePrototypeState $state) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function posts(): array
    {
        return array_map(static fn (array $post): array => [
            'key' => 'created-post-'.$post['id'],
            'author' => $post['title'],
            'pet' => 'New moment',
            'time' => 'Just now',
            'datetime' => now()->toAtomString(),
            'body' => $post['body'],
            'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=675&q=82',
            'image_alt' => 'Scout relaxing in the grass after a neighborhood outing',
            'tags' => array_values(array_filter([$post['category']])),
            'stats' => ['paws' => '0', 'replies' => '0'],
        ], $this->state->created('posts'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function groups(): array
    {
        return array_map(static function (array $group): array {
            $key = 'created-group-'.$group['id'];

            return [
                'key' => $key,
                'detail_route' => 'pet-social.groups.created',
                'detail_parameters' => ['item' => $key],
                'name' => $group['title'],
                'category' => $group['category'] ?: 'Local',
                'members' => '1 member',
                'activity' => 'Just created',
                'topic' => $group['category'] ?: 'New community',
                'description' => $group['body'],
                'privacy' => $group['privacy'] ?? 'public',
                'location' => $group['location'] ?: 'Location not set',
                'language' => $group['language'] ?? 'English',
                'rules' => $group['rules'] ?? '',
                'pet_identity' => $group['pet_identity'] ?? 'mia',
                'posting_policy' => $group['posting_policy'] ?? 'members',
                'organizer' => 'Mia Carter',
                'organizer_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Dog and cat resting together in a welcoming home',
                'tags' => array_values(array_filter([
                    $group['category'] ?: 'new group',
                    $group['location'] ?: null,
                ])),
            ];
        }, $this->state->created('groups'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function meetups(): array
    {
        return array_map(static function (array $meetup): array {
            $key = 'created-meetup-'.$meetup['id'];
            $dateValue = $meetup['date'] !== ''
                ? $meetup['date']
                : now()->toImmutable()->addWeek()->format('Y-m-d');
            $timeValue = isset($meetup['time']) && $meetup['time'] !== ''
                ? $meetup['time']
                : '10:00';
            $timezone = $meetup['timezone'] ?? config('app.timezone');
            $startsAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $dateValue.' '.$timeValue,
                $timezone,
            );

            return [
                'key' => $key,
                'detail_route' => 'pet-social.meetups.created',
                'detail_parameters' => ['item' => $key],
                'title' => $meetup['title'],
                'category' => Str::headline($meetup['category'] ?: 'Community'),
                'day' => Str::upper($startsAt->format('D')),
                'date' => $startsAt->format('d'),
                'date_label' => $startsAt->format('D, M j'),
                'date_accessible' => $startsAt->format('l, F j, Y'),
                'datetime' => $startsAt->toAtomString(),
                'time' => $startsAt->format('g:i A'),
                'timezone' => $timezone,
                'place' => ($meetup['format'] ?? 'offline') === 'online'
                    ? 'Online'
                    : ($meetup['location'] ?: 'Portland'),
                'neighborhood' => ($meetup['format'] ?? 'offline') === 'online'
                    ? 'Timezone-aware online access'
                    : 'Exact entrance after confirmation',
                'distance' => 'Nearby',
                'attendees' => '1 neighbor going',
                'description' => $meetup['body'],
                'format' => $meetup['format'] ?? 'offline',
                'privacy' => $meetup['privacy'] ?? 'public',
                'capacity' => (int) ($meetup['capacity'] ?? 12),
                'registration_policy' => $meetup['registration_policy'] ?? 'approval',
                'ticket_model' => $meetup['ticket_model'] ?? 'free',
                'ticket_price' => (float) ($meetup['ticket_price'] ?? 0),
                'online_url' => $meetup['online_url'] ?? '',
                'rules' => $meetup['rules'] ?? 'Follow the organizer instructions and respect each pet’s space.',
                'safety_plan' => $meetup['detail'] ?? 'Use a public arrival point and keep a clear exit route.',
                'host' => match ($meetup['organizer'] ?? 'mia') {
                    'scout' => 'Scout, managed by Mia',
                    'group' => 'Richmond Pet Circle',
                    'organization' => 'PawCircle Community Team',
                    default => 'Mia Carter',
                },
                'host_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => 'Friendly dogs meeting in a neighborhood park',
                'tags' => array_values(array_filter([
                    'new event',
                    ($meetup['format'] ?? 'offline') === 'online' ? 'online' : 'local',
                    $meetup['privacy'] ?? null,
                ])),
            ];
        }, $this->state->created('meetups'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pets(): array
    {
        return array_map(static function (array $pet): array {
            $key = 'created-pet-'.$pet['id'];

            return [
                'key' => $key,
                'name' => $pet['title'],
                'species' => $pet['category'] ?: 'Companion',
                'breed' => $pet['detail'] ?: 'Mixed companion',
                'age' => 'New profile',
                'owner' => 'Mia Carter',
                'neighborhood' => 'Richmond',
                'status' => $pet['body'],
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => $pet['title'].' relaxing outdoors',
                'traits' => ['new to PawCircle'],
                'profile_route' => 'pet-social.pets.created',
                'profile_parameters' => ['item' => $key],
            ];
        }, $this->state->created('pets'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $kind, string $key): ?array
    {
        $item = $this->find($kind, $key);

        if ($item === null) {
            return null;
        }

        return match ($kind) {
            'group' => $this->groupDetail($item),
            'meetup' => $this->meetupDetail($item),
            'pet' => $this->petDetail($item),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function shareTarget(string $key): ?array
    {
        foreach (['group', 'meetup', 'pet'] as $kind) {
            $content = $this->detail($kind, $key);

            if ($content === null) {
                continue;
            }

            $hero = $content['hero'];

            return [
                'target' => $key,
                'type' => $content['share_type'],
                'active_section' => $content['active_section'],
                'eyebrow' => $content['share_eyebrow'],
                'title' => $hero['title'],
                'description' => $hero['description'],
                'image' => $hero['image'],
                'image_small' => $hero['image_small'],
                'image_medium' => $hero['image_medium'],
                'image_alt' => $hero['image_alt'],
                'route' => $content['route'],
                'route_parameters' => ['item' => $key],
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function find(string $kind, string $key): ?array
    {
        $items = match ($kind) {
            'group' => $this->groups(),
            'meetup' => $this->meetups(),
            'pet' => $this->pets(),
            default => [],
        };

        foreach ($items as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function groupDetail(array $group): array
    {
        return [
            'kind' => 'group',
            'route' => 'pet-social.groups.created',
            'page_title' => $group['name'].' | PawCircle',
            'active_section' => 'groups',
            'back_route' => 'pet-social.groups.index',
            'back_label' => 'Back to groups',
            'section' => 'created-group-detail',
            'share_type' => 'Community',
            'share_eyebrow' => 'Share a community',
            'summary_label' => 'Community summary',
            'summary_icons' => ['users', 'messages-square', 'activity'],
            'hero' => [
                ...$this->media($group),
                'key' => $group['key'],
                'eyebrow' => 'New local community',
                'title' => $group['name'],
                'description' => $group['description'],
                'meta' => [
                    ['icon' => 'users-round', 'label' => $group['category']],
                    ['icon' => 'user-round', 'label' => 'Created by '.$group['organizer']],
                    ['icon' => 'activity', 'label' => 'Just created'],
                ],
                'tags' => $group['tags'],
                'stats' => [
                    ['label' => 'Members', 'value' => '1', 'detail' => 'founding neighbor'],
                    ['label' => 'Posts', 'value' => '0', 'detail' => 'ready for the first update'],
                    ['label' => 'Access', 'value' => 'Open', 'detail' => 'neighbors may join'],
                ],
            ],
            'primary' => [
                'label' => 'Join group',
                'icon' => 'user-plus',
                'active_label' => 'Joined',
                'active_icon' => 'check',
                'active' => $this->state->isActive('groups', $group['key']),
                'action' => 'toggle-group',
            ],
            'about' => [
                'eyebrow' => 'Community purpose',
                'title' => 'What neighbors will share',
                'copy' => $group['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => 'Start with useful context',
                    'description' => 'Share routines, locations, and practical details that help neighbors participate.',
                ],
                [
                    'icon' => 'shield-check',
                    'title' => 'Protect private details',
                    'description' => 'Move addresses, access instructions, and sensitive care information into direct messages.',
                ],
                [
                    'icon' => 'users',
                    'title' => 'Keep introductions calm',
                    'description' => 'Let every person and pet choose their own pace when meeting for the first time.',
                ],
            ],
            'facts' => [
                ['label' => 'Category', 'value' => $group['category']],
                ['label' => 'Organizer', 'value' => $group['organizer']],
                ['label' => 'Activity', 'value' => $group['activity']],
            ],
            'notice' => [
                'icon' => 'sparkles',
                'title' => 'Ready for its first neighbors',
                'description' => 'Share the community or add the first useful post to begin the conversation.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $meetup
     * @return array<string, mixed>
     */
    private function meetupDetail(array $meetup): array
    {
        return [
            'kind' => 'meetup',
            'route' => 'pet-social.meetups.created',
            'page_title' => $meetup['title'].' | PawCircle',
            'active_section' => 'meetups',
            'back_route' => 'pet-social.meetups.index',
            'back_label' => 'Back to meetups',
            'section' => 'created-meetup-detail',
            'share_type' => 'Meetup',
            'share_eyebrow' => 'Share a meetup',
            'summary_label' => 'Meetup summary',
            'summary_icons' => ['users', 'clock-3', 'paw-print'],
            'hero' => [
                ...$this->media($meetup),
                'key' => $meetup['key'],
                'eyebrow' => 'New neighborhood plan',
                'title' => $meetup['title'],
                'description' => $meetup['description'],
                'meta' => [
                    [
                        'icon' => 'calendar-days',
                        'label' => $meetup['date_label'].' at '.$meetup['time'],
                        'datetime' => $meetup['datetime'],
                        'aria_label' => $meetup['date_accessible'].' at '.$meetup['time'],
                    ],
                    ['icon' => 'map-pin', 'label' => $meetup['place']],
                    ['icon' => 'user-round', 'label' => 'Hosted by '.$meetup['host']],
                ],
                'tags' => $meetup['tags'],
                'stats' => [
                    ['label' => 'Going', 'value' => '1', 'detail' => 'founding neighbor'],
                    ['label' => 'Starts', 'value' => $meetup['time'], 'detail' => $meetup['date_label']],
                    ['label' => 'Pets', 'value' => 'Welcome', 'detail' => 'at a comfortable pace'],
                ],
            ],
            'primary' => [
                'label' => 'RSVP',
                'icon' => 'calendar-plus',
                'active_label' => 'Going',
                'active_icon' => 'calendar-check',
                'active' => $this->state->isActive('meetups', $meetup['key']),
                'action' => 'toggle-meetup',
            ],
            'about' => [
                'eyebrow' => 'Meetup plan',
                'title' => 'What to expect',
                'copy' => $meetup['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'scroll-text',
                    'title' => 'Event rules',
                    'description' => $meetup['rules'],
                ],
                [
                    'icon' => 'shield-check',
                    'title' => 'Safety plan',
                    'description' => $meetup['safety_plan'],
                ],
                [
                    'icon' => 'map-pinned',
                    'title' => 'Protected meeting access',
                    'description' => $meetup['privacy'] === 'public'
                        ? 'The general place is public. Share exact arrival details only when they are safe to disclose.'
                        : 'This event has limited visibility. Confirm exact arrival details only with approved attendees.',
                ],
            ],
            'facts' => [
                ['label' => 'Date', 'value' => $meetup['date_accessible']],
                ['label' => 'Time', 'value' => $meetup['time']],
                ['label' => 'Timezone', 'value' => $meetup['timezone']],
                ['label' => 'Meeting place', 'value' => $meetup['place']],
                ['label' => 'Registration', 'value' => Str::headline($meetup['registration_policy'])],
                [
                    'label' => 'Ticket',
                    'value' => $meetup['ticket_model'] === 'paid'
                        ? '$'.number_format($meetup['ticket_price'], 2)
                        : 'Free',
                ],
            ],
            'notice' => [
                'icon' => 'shield-check',
                'title' => Str::headline($meetup['privacy']).' event',
                'description' => $meetup['capacity'].' places · '.Str::headline($meetup['format']).' format',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    private function petDetail(array $pet): array
    {
        return [
            'kind' => 'pet',
            'route' => 'pet-social.pets.created',
            'page_title' => $pet['name'].' | PawCircle',
            'active_section' => 'pets',
            'back_route' => 'pet-social.pets.index',
            'back_label' => 'Back to pets',
            'section' => 'created-pet-detail',
            'share_type' => 'Pet profile',
            'share_eyebrow' => 'Share a pet profile',
            'summary_label' => 'Pet profile summary',
            'summary_icons' => ['paw-print', 'users', 'footprints'],
            'hero' => [
                ...$this->media($pet),
                'key' => $pet['key'],
                'eyebrow' => $pet['species'].' profile',
                'title' => $pet['name'],
                'description' => $pet['status'],
                'meta' => [
                    ['icon' => 'paw-print', 'label' => $pet['breed']],
                    ['icon' => 'user-round', 'label' => 'With '.$pet['owner']],
                    ['icon' => 'map-pin', 'label' => $pet['neighborhood'].' - Portland, OR'],
                ],
                'tags' => $pet['traits'],
                'stats' => [
                    ['label' => 'Profile', 'value' => 'New', 'detail' => 'ready to discover'],
                    ['label' => 'Friends', 'value' => '0', 'detail' => 'connections so far'],
                    ['label' => 'Walks', 'value' => '0', 'detail' => 'plans together'],
                ],
            ],
            'primary' => [
                'label' => 'Follow',
                'icon' => 'user-plus',
                'active_label' => 'Following',
                'active_icon' => 'user-check',
                'active' => $this->state->isActive('follows', $pet['key']),
                'action' => 'toggle-follow',
            ],
            'about' => [
                'eyebrow' => 'Daily life',
                'title' => 'About '.$pet['name'],
                'copy' => $pet['status'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle',
                    'title' => 'Ask about routines',
                    'description' => 'Start with familiar places, greeting preferences, and the pace that feels comfortable.',
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => 'Plan an easy first meeting',
                    'description' => 'Choose a neutral location and keep the first introduction short and optional.',
                ],
                [
                    'icon' => 'shield-check',
                    'title' => 'Keep care details private',
                    'description' => 'Share medical or access information only with the people directly involved.',
                ],
            ],
            'facts' => [
                ['label' => 'Species', 'value' => $pet['species']],
                ['label' => 'Breed or type', 'value' => $pet['breed']],
                ['label' => 'Neighborhood', 'value' => $pet['neighborhood']],
            ],
            'notice' => [
                'icon' => 'paw-print',
                'title' => 'A new neighborhood profile',
                'description' => 'Following keeps this profile in My Circle while connections begin to grow.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{image: string, image_small: string, image_medium: string, image_alt: string}
     */
    private function media(array $item): array
    {
        return [
            'image' => $item['image'],
            'image_small' => $item['image_small'],
            'image_medium' => $item['image_medium'],
            'image_alt' => $item['image_alt'],
        ];
    }
}
