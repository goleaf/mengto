<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Str;

final class CreatedContentPresenter
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly LocaleFormatter $formatter,
        private readonly AuthFactory $auth,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function posts(): array
    {
        return array_map(static fn (array $post): array => [
            'key' => 'created-post-'.$post['id'],
            'author' => $post['title'],
            'pet' => __('messages.new_moment'),
            'time' => __('messages.just_now'),
            'datetime' => now()->toAtomString(),
            'body' => $post['body'],
            'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=675&q=82',
            'image_alt' => __('messages.scout_relaxing_in_the_grass_after_a_neighborhood_outing'),
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
                'detail_route' => 'groups.created',
                'detail_parameters' => ['item' => $key],
                'name' => $group['title'],
                'category' => $group['category'] ?: __('messages.local'),
                'members' => __('messages.1_member'),
                'activity' => __('messages.just_created'),
                'topic' => $group['category'] ?: __('messages.new_community'),
                'description' => $group['body'],
                'privacy' => $group['privacy'] ?? 'public',
                'location' => $group['location'] ?: __('messages.location_not_set'),
                'language' => $group['language'] ?? __('messages.english'),
                'rules' => $group['rules'] ?? '',
                'pet_identity' => $group['pet_identity'] ?? 'mia',
                'posting_policy' => $group['posting_policy'] ?? 'members',
                'organizer' => __('messages.mia_carter'),
                'organizer_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_in_a_welcoming_home'),
                'tags' => array_values(array_filter([
                    $group['category'] ?: __('messages.new_group'),
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
        return array_map(function (array $meetup): array {
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
                'detail_route' => 'meetups.created',
                'detail_parameters' => ['item' => $key],
                'title' => $meetup['title'],
                'category' => Str::headline($meetup['category'] ?: __('messages.community')),
                'day' => $this->formatter->weekdayShort($startsAt),
                'date' => $this->formatter->dayNumber($startsAt),
                'date_label' => $this->formatter->weekdayMonthDay($startsAt),
                'date_accessible' => $this->formatter->accessibleDateTime($startsAt),
                'datetime' => $startsAt->toAtomString(),
                'time' => $this->formatter->time($startsAt, (string) $timezone),
                'timezone' => $timezone,
                'place' => ($meetup['format'] ?? 'offline') === 'online'
                    ? __('messages.online')
                    : ($meetup['location'] ?: __('messages.portland')),
                'neighborhood' => ($meetup['format'] ?? 'offline') === 'online'
                    ? __('messages.timezone_aware_online_access')
                    : __('messages.exact_entrance_after_confirmation'),
                'distance' => __('messages.nearby'),
                'attendees' => __('messages.1_neighbor_going'),
                'description' => $meetup['body'],
                'format' => $meetup['format'] ?? 'offline',
                'privacy' => $meetup['privacy'] ?? 'public',
                'capacity' => (int) ($meetup['capacity'] ?? 12),
                'registration_policy' => $meetup['registration_policy'] ?? 'approval',
                'ticket_model' => $meetup['ticket_model'] ?? 'free',
                'ticket_price' => (float) ($meetup['ticket_price'] ?? 0),
                'online_url' => $meetup['online_url'] ?? '',
                'rules' => $meetup['rules'] ?? __('messages.follow_the_organizer_instructions_and_respect_each_pet_s_space'),
                'safety_plan' => $meetup['detail'] ?? __('messages.use_a_public_arrival_point_and_keep_a_clear_exit_route'),
                'host' => match ($meetup['organizer'] ?? 'mia') {
                    'scout' => __('messages.scout_managed_by_mia'),
                    'group' => __('messages.richmond_pet_circle'),
                    'organization' => __('messages.brand.community_team'),
                    default => __('messages.mia_carter'),
                },
                'host_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.friendly_dogs_meeting_in_a_neighborhood_park'),
                'tags' => array_values(array_filter([
                    __('messages.new_event'),
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
        $user = $this->auth->guard()->user();
        $user = $user instanceof User ? $user : null;
        $profiles = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'breed',
                'visibility',
                'status',
                'profile_data',
                'created_at',
            ])
            ->with('user:id,name')
            ->visibleTo($user)
            ->where('profile_key', 'like', 'created-pet-%')
            ->latest('id')
            ->limit(8)
            ->get();

        return $profiles->map(static function (PetProfile $profile): array {
            $profileData = $profile->profile_data ?? [];
            $key = $profile->profile_key;
            $profileUrl = route('pets.created', ['item' => $key]);

            return [
                'key' => $key,
                'name' => $profile->name,
                'species' => $profile->species,
                'breed' => $profile->breed ?: __('messages.mixed_companion'),
                'age' => __('messages.new_profile'),
                'owner' => $profile->user->name,
                'neighborhood' => (string) ($profileData['location'] ?? __('messages.location_kept_private')),
                'status' => (string) ($profileData['status'] ?? $profileData['story'] ?? ''),
                'image' => (string) ($profileData['card_image'] ?? 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85'),
                'image_small' => (string) ($profileData['card_image_small'] ?? 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80'),
                'image_medium' => (string) ($profileData['card_image_medium'] ?? 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82'),
                'image_alt' => (string) ($profileData['card_image_alt'] ?? __('messages.pet_profile_image_alt', ['pet' => $profile->name])),
                'traits' => is_array($profileData['traits'] ?? null)
                    ? array_values($profileData['traits'])
                    : [__('messages.new_to_pawcircle')],
                'profile_route' => 'pets.created',
                'profile_parameters' => ['item' => $key],
                'media_target' => [
                    'url' => $profileUrl,
                    'label' => __('presentation.open_profile', ['name' => $profile->name]),
                ],
            ];
        })->all();
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
            'route' => 'groups.created',
            'page_title' => __('presentation.brand_title', ['title' => $group['name']]),
            'active_section' => 'groups',
            'back_route' => 'groups.index',
            'back_label' => __('messages.back_to_groups'),
            'section' => 'created-group-detail',
            'share_type' => __('messages.community'),
            'share_eyebrow' => __('messages.share_a_community'),
            'summary_label' => __('messages.community_summary'),
            'summary_icons' => ['users', 'messages-square', 'activity'],
            'hero' => [
                ...$this->media($group),
                'key' => $group['key'],
                'eyebrow' => __('messages.new_local_community'),
                'title' => $group['name'],
                'description' => $group['description'],
                'meta' => [
                    ['icon' => 'users-round', 'label' => $group['category']],
                    ['icon' => 'user-round', 'label' => __('messages.created_by').$group['organizer']],
                    ['icon' => 'activity', 'label' => __('messages.just_created')],
                ],
                'tags' => $group['tags'],
                'stats' => [
                    ['label' => __('messages.members'), 'value' => '1', 'detail' => __('messages.founding_neighbor')],
                    ['label' => __('messages.posts'), 'value' => '0', 'detail' => __('messages.ready_for_the_first_update')],
                    ['label' => __('messages.access'), 'value' => __('messages.open'), 'detail' => __('messages.neighbors_may_join')],
                ],
            ],
            'primary' => [
                'label' => __('messages.join_group'),
                'icon' => 'user-plus',
                'active_label' => __('messages.joined'),
                'active_icon' => 'check',
                'active' => $this->state->isActive('groups', $group['key']),
                'action' => 'toggle-group',
            ],
            'about' => [
                'eyebrow' => __('messages.community_purpose'),
                'title' => __('messages.what_neighbors_will_share'),
                'copy' => $group['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => __('messages.start_with_useful_context'),
                    'description' => __('messages.share_routines_locations_and_practical_details_that_help_neighbors_participate'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.protect_private_details'),
                    'description' => __('messages.move_addresses_access_instructions_and_sensitive_care_information_into_direct_messages'),
                ],
                [
                    'icon' => 'users',
                    'title' => __('messages.keep_introductions_calm'),
                    'description' => __('messages.let_every_person_and_pet_choose_their_own_pace_when_meeting_for_the_first_time'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.category'), 'value' => $group['category']],
                ['label' => __('messages.organizer'), 'value' => $group['organizer']],
                ['label' => __('messages.activity'), 'value' => $group['activity']],
            ],
            'notice' => [
                'icon' => 'sparkles',
                'title' => __('messages.ready_for_its_first_neighbors'),
                'description' => __('messages.share_the_community_or_add_the_first_useful_post_to_begin_the_conversation'),
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
            'route' => 'meetups.created',
            'page_title' => __('presentation.brand_title', ['title' => $meetup['title']]),
            'active_section' => 'meetups',
            'back_route' => 'meetups.index',
            'back_label' => __('messages.back_to_meetups'),
            'section' => 'created-meetup-detail',
            'share_type' => __('messages.meetup'),
            'share_eyebrow' => __('messages.share_a_meetup'),
            'summary_label' => __('messages.meetup_summary'),
            'summary_icons' => ['users', 'clock-3', 'paw-print'],
            'hero' => [
                ...$this->media($meetup),
                'key' => $meetup['key'],
                'eyebrow' => __('messages.new_neighborhood_plan'),
                'title' => $meetup['title'],
                'description' => $meetup['description'],
                'meta' => [
                    [
                        'icon' => 'calendar-days',
                        'label' => __('presentation.date_at_time', [
                            'date' => $meetup['date_label'],
                            'time' => $meetup['time'],
                        ]),
                        'datetime' => $meetup['datetime'],
                        'aria_label' => __('presentation.date_at_time', [
                            'date' => $meetup['date_accessible'],
                            'time' => $meetup['time'],
                        ]),
                    ],
                    ['icon' => 'map-pin', 'label' => $meetup['place']],
                    ['icon' => 'user-round', 'label' => __('messages.hosted_by').$meetup['host']],
                ],
                'tags' => $meetup['tags'],
                'stats' => [
                    ['label' => __('messages.going'), 'value' => '1', 'detail' => __('messages.founding_neighbor')],
                    ['label' => __('messages.starts'), 'value' => $meetup['time'], 'detail' => $meetup['date_label']],
                    ['label' => __('messages.pets'), 'value' => __('messages.welcome'), 'detail' => __('messages.at_a_comfortable_pace')],
                ],
            ],
            'primary' => [
                'label' => __('messages.rsvp'),
                'icon' => 'calendar-plus',
                'active_label' => __('messages.going'),
                'active_icon' => 'calendar-check',
                'active' => $this->state->isActive('meetups', $meetup['key']),
                'action' => 'toggle-meetup',
            ],
            'about' => [
                'eyebrow' => __('messages.meetup_plan'),
                'title' => __('messages.what_to_expect'),
                'copy' => $meetup['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'scroll-text',
                    'title' => __('messages.event_rules'),
                    'description' => $meetup['rules'],
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.safety_plan'),
                    'description' => $meetup['safety_plan'],
                ],
                [
                    'icon' => 'map-pinned',
                    'title' => __('messages.protected_meeting_access'),
                    'description' => $meetup['privacy'] === 'public'
                        ? __('messages.the_general_place_is_public_share_exact_arrival_details_only_when_they_are_safe_to_disclose')
                        : __('messages.this_event_has_limited_visibility_confirm_exact_arrival_details_only_with_approved_attendees'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.date'), 'value' => $meetup['date_accessible']],
                ['label' => __('messages.time'), 'value' => $meetup['time']],
                ['label' => __('messages.timezone'), 'value' => $meetup['timezone']],
                ['label' => __('messages.meeting_place'), 'value' => $meetup['place']],
                ['label' => __('messages.registration'), 'value' => Str::headline($meetup['registration_policy'])],
                [
                    'label' => __('messages.ticket'),
                    'value' => $meetup['ticket_model'] === 'paid'
                        ? $this->formatter->currency($meetup['ticket_price'], 'USD')
                        : __('presentation.free'),
                ],
            ],
            'notice' => [
                'icon' => 'shield-check',
                'title' => __('presentation.event_type', ['type' => Str::headline($meetup['privacy'])]),
                'description' => __('presentation.capacity_and_format', [
                    'capacity' => trans_choice('presentation.places_count', $meetup['capacity'], [
                        'count' => $meetup['capacity'],
                    ]),
                    'format' => Str::headline($meetup['format']),
                ]),
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
            'route' => 'pets.created',
            'page_title' => __('presentation.brand_title', ['title' => $pet['name']]),
            'active_section' => 'pets',
            'back_route' => 'pets.index',
            'back_label' => __('messages.back_to_pets'),
            'section' => 'created-pet-detail',
            'share_type' => __('messages.pet_profile'),
            'share_eyebrow' => __('messages.share_a_pet_profile'),
            'summary_label' => __('messages.pet_profile_summary'),
            'summary_icons' => ['paw-print', 'users', 'footprints'],
            'hero' => [
                ...$this->media($pet),
                'key' => $pet['key'],
                'eyebrow' => __('presentation.species_profile', ['species' => $pet['species']]),
                'title' => $pet['name'],
                'description' => $pet['status'],
                'meta' => [
                    ['icon' => 'paw-print', 'label' => $pet['breed']],
                    ['icon' => 'user-round', 'label' => __('messages.with').$pet['owner']],
                    [
                        'icon' => 'map-pin',
                        'label' => __('presentation.neighborhood_location', [
                            'neighborhood' => $pet['neighborhood'],
                        ]),
                    ],
                ],
                'tags' => $pet['traits'],
                'stats' => [
                    ['label' => __('messages.profile'), 'value' => __('messages.new'), 'detail' => __('messages.ready_to_discover')],
                    ['label' => __('messages.friends'), 'value' => '0', 'detail' => __('messages.connections_so_far')],
                    ['label' => __('messages.walks'), 'value' => '0', 'detail' => __('messages.plans_together')],
                ],
            ],
            'primary' => [
                'label' => __('messages.follow'),
                'icon' => 'user-plus',
                'active_label' => __('messages.following'),
                'active_icon' => 'user-check',
                'active' => $this->state->isActive('follows', $pet['key']),
                'action' => 'toggle-follow',
            ],
            'about' => [
                'eyebrow' => __('messages.daily_life'),
                'title' => __('messages.about_prefix').$pet['name'],
                'copy' => $pet['status'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle',
                    'title' => __('messages.ask_about_routines'),
                    'description' => __('messages.start_with_familiar_places_greeting_preferences_and_the_pace_that_feels_comfortable'),
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => __('messages.plan_an_easy_first_meeting'),
                    'description' => __('messages.choose_a_neutral_location_and_keep_the_first_introduction_short_and_optional'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.keep_care_details_private'),
                    'description' => __('messages.share_medical_or_access_information_only_with_the_people_directly_involved'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.species'), 'value' => $pet['species']],
                ['label' => __('messages.breed_or_type'), 'value' => $pet['breed']],
                ['label' => __('messages.neighborhood'), 'value' => $pet['neighborhood']],
            ],
            'notice' => [
                'icon' => 'paw-print',
                'title' => __('messages.a_new_neighborhood_profile'),
                'description' => __('messages.following_keeps_this_profile_in_my_circle_while_connections_begin_to_grow'),
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
