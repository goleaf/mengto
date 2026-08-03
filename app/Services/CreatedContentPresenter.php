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
            'pet' => __('messages.new_moment_cae3619581'),
            'time' => __('messages.just_now_66f53417d3'),
            'datetime' => now()->toAtomString(),
            'body' => $post['body'],
            'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=675&q=82',
            'image_alt' => __('messages.scout_relaxing_in_the_grass_after_a_neighborhood_outing_893680d0e4'),
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
                'category' => $group['category'] ?: __('messages.local_8c31e6e722'),
                'members' => __('messages.1_member_895022bc50'),
                'activity' => __('messages.just_created_0602060ac8'),
                'topic' => $group['category'] ?: __('messages.new_community_3d86379bef'),
                'description' => $group['body'],
                'privacy' => $group['privacy'] ?? 'public',
                'location' => $group['location'] ?: __('messages.location_not_set_0ccd1c0bfb'),
                'language' => $group['language'] ?? __('messages.english_ba118bf7fc'),
                'rules' => $group['rules'] ?? '',
                'pet_identity' => $group['pet_identity'] ?? 'mia',
                'posting_policy' => $group['posting_policy'] ?? 'members',
                'organizer' => __('messages.mia_carter_0e5b29cc3b'),
                'organizer_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_in_a_welcoming_home_02de860d6c'),
                'tags' => array_values(array_filter([
                    $group['category'] ?: __('messages.new_group_73af5a135c'),
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
                'category' => Str::headline($meetup['category'] ?: __('messages.community_bb501d7877')),
                'day' => $this->formatter->weekdayShort($startsAt),
                'date' => $this->formatter->dayNumber($startsAt),
                'date_label' => $this->formatter->weekdayMonthDay($startsAt),
                'date_accessible' => $this->formatter->accessibleDateTime($startsAt),
                'datetime' => $startsAt->toAtomString(),
                'time' => $this->formatter->time($startsAt, (string) $timezone),
                'timezone' => $timezone,
                'place' => ($meetup['format'] ?? 'offline') === 'online'
                    ? __('messages.online_0d21bd5202')
                    : ($meetup['location'] ?: __('messages.portland_f514070e53')),
                'neighborhood' => ($meetup['format'] ?? 'offline') === 'online'
                    ? __('messages.timezone_aware_online_access_89162dea4a')
                    : __('messages.exact_entrance_after_confirmation_bbc63d0c75'),
                'distance' => __('messages.nearby_a994cd47d4'),
                'attendees' => __('messages.1_neighbor_going_34dffb12f5'),
                'description' => $meetup['body'],
                'format' => $meetup['format'] ?? 'offline',
                'privacy' => $meetup['privacy'] ?? 'public',
                'capacity' => (int) ($meetup['capacity'] ?? 12),
                'registration_policy' => $meetup['registration_policy'] ?? 'approval',
                'ticket_model' => $meetup['ticket_model'] ?? 'free',
                'ticket_price' => (float) ($meetup['ticket_price'] ?? 0),
                'online_url' => $meetup['online_url'] ?? '',
                'rules' => $meetup['rules'] ?? __('messages.follow_the_organizer_instructions_and_respect_each_pet_s_6709be34fe'),
                'safety_plan' => $meetup['detail'] ?? __('messages.use_a_public_arrival_point_and_keep_a_clear_exit_route_229320a69a'),
                'host' => match ($meetup['organizer'] ?? 'mia') {
                    'scout' => __('messages.scout_managed_by_mia_68a8dadfc0'),
                    'group' => __('messages.richmond_pet_circle_34411217bc'),
                    'organization' => __('messages.brand.community_team'),
                    default => __('messages.mia_carter_0e5b29cc3b'),
                },
                'host_initials' => 'MC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.friendly_dogs_meeting_in_a_neighborhood_park_1f224393de'),
                'tags' => array_values(array_filter([
                    __('messages.new_event_4e660bbf62'),
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
            'back_label' => __('messages.back_to_groups_033bcce2e9'),
            'section' => 'created-group-detail',
            'share_type' => __('messages.community_bb501d7877'),
            'share_eyebrow' => __('messages.share_a_community_2103f4bd66'),
            'summary_label' => __('messages.community_summary_2014a7ddb6'),
            'summary_icons' => ['users', 'messages-square', 'activity'],
            'hero' => [
                ...$this->media($group),
                'key' => $group['key'],
                'eyebrow' => __('messages.new_local_community_3005dd4d1d'),
                'title' => $group['name'],
                'description' => $group['description'],
                'meta' => [
                    ['icon' => 'users-round', 'label' => $group['category']],
                    ['icon' => 'user-round', 'label' => __('messages.created_by_b2742e6cae').$group['organizer']],
                    ['icon' => 'activity', 'label' => __('messages.just_created_0602060ac8')],
                ],
                'tags' => $group['tags'],
                'stats' => [
                    ['label' => __('messages.members_1044a4c056'), 'value' => '1', 'detail' => __('messages.founding_neighbor_a38c5f85d2')],
                    ['label' => __('messages.posts_a80811cf68'), 'value' => '0', 'detail' => __('messages.ready_for_the_first_update_77c00328d7')],
                    ['label' => __('messages.access_ec5ba0abb7'), 'value' => __('messages.open_ed077f3d81'), 'detail' => __('messages.neighbors_may_join_7ab610778d')],
                ],
            ],
            'primary' => [
                'label' => __('messages.join_group_48a2587a6c'),
                'icon' => 'user-plus',
                'active_label' => __('messages.joined_69318b0c6a'),
                'active_icon' => 'check',
                'active' => $this->state->isActive('groups', $group['key']),
                'action' => 'toggle-group',
            ],
            'about' => [
                'eyebrow' => __('messages.community_purpose_5c47de84d6'),
                'title' => __('messages.what_neighbors_will_share_1f14cecf15'),
                'copy' => $group['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => __('messages.start_with_useful_context_24574eb4da'),
                    'description' => __('messages.share_routines_locations_and_practical_details_that_help_c79c9c63c8'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.protect_private_details_4c379e5475'),
                    'description' => __('messages.move_addresses_access_instructions_and_sensitive_care_in_c71abade1e'),
                ],
                [
                    'icon' => 'users',
                    'title' => __('messages.keep_introductions_calm_f63f881250'),
                    'description' => __('messages.let_every_person_and_pet_choose_their_own_pace_when_meet_5c07072011'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.category_292c06f004'), 'value' => $group['category']],
                ['label' => __('messages.organizer_715a9cc0c3'), 'value' => $group['organizer']],
                ['label' => __('messages.activity_38da1505ca'), 'value' => $group['activity']],
            ],
            'notice' => [
                'icon' => 'sparkles',
                'title' => __('messages.ready_for_its_first_neighbors_b8359d58ba'),
                'description' => __('messages.share_the_community_or_add_the_first_useful_post_to_begi_750056d2e3'),
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
            'back_label' => __('messages.back_to_meetups_e4a73b09c0'),
            'section' => 'created-meetup-detail',
            'share_type' => __('messages.meetup_b8e99f52bc'),
            'share_eyebrow' => __('messages.share_a_meetup_88cb422377'),
            'summary_label' => __('messages.meetup_summary_74f7a3dc24'),
            'summary_icons' => ['users', 'clock-3', 'paw-print'],
            'hero' => [
                ...$this->media($meetup),
                'key' => $meetup['key'],
                'eyebrow' => __('messages.new_neighborhood_plan_19b6d973ba'),
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
                    ['icon' => 'user-round', 'label' => __('messages.hosted_by_d04444a24a').$meetup['host']],
                ],
                'tags' => $meetup['tags'],
                'stats' => [
                    ['label' => __('messages.going_7bd49cdc7d'), 'value' => '1', 'detail' => __('messages.founding_neighbor_a38c5f85d2')],
                    ['label' => __('messages.starts_96dbedeca7'), 'value' => $meetup['time'], 'detail' => $meetup['date_label']],
                    ['label' => __('messages.pets_7dc1cd7eaf'), 'value' => __('messages.welcome_0e2226b523'), 'detail' => __('messages.at_a_comfortable_pace_855da3f274')],
                ],
            ],
            'primary' => [
                'label' => __('messages.rsvp_1dfe8a8e0c'),
                'icon' => 'calendar-plus',
                'active_label' => __('messages.going_7bd49cdc7d'),
                'active_icon' => 'calendar-check',
                'active' => $this->state->isActive('meetups', $meetup['key']),
                'action' => 'toggle-meetup',
            ],
            'about' => [
                'eyebrow' => __('messages.meetup_plan_f3c89e450f'),
                'title' => __('messages.what_to_expect_7ef84dcd83'),
                'copy' => $meetup['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'scroll-text',
                    'title' => __('messages.event_rules_99803b8f3b'),
                    'description' => $meetup['rules'],
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.safety_plan_502d6dbea8'),
                    'description' => $meetup['safety_plan'],
                ],
                [
                    'icon' => 'map-pinned',
                    'title' => __('messages.protected_meeting_access_f9121e0af7'),
                    'description' => $meetup['privacy'] === 'public'
                        ? __('messages.the_general_place_is_public_share_exact_arrival_details__deddba407d')
                        : __('messages.this_event_has_limited_visibility_confirm_exact_arrival__1ec6ed83e2'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.date_99c40ab405'), 'value' => $meetup['date_accessible']],
                ['label' => __('messages.time_33b93476cf'), 'value' => $meetup['time']],
                ['label' => __('messages.timezone_4ceca1d52c'), 'value' => $meetup['timezone']],
                ['label' => __('messages.meeting_place_46d1e79522'), 'value' => $meetup['place']],
                ['label' => __('messages.registration_c793e0d9a1'), 'value' => Str::headline($meetup['registration_policy'])],
                [
                    'label' => __('messages.ticket_567a8b5f8f'),
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
            'back_label' => __('messages.back_to_pets_2d6d9a57e5'),
            'section' => 'created-pet-detail',
            'share_type' => __('messages.pet_profile_fc2c49bb42'),
            'share_eyebrow' => __('messages.share_a_pet_profile_36e8858b2b'),
            'summary_label' => __('messages.pet_profile_summary_eeffba5e83'),
            'summary_icons' => ['paw-print', 'users', 'footprints'],
            'hero' => [
                ...$this->media($pet),
                'key' => $pet['key'],
                'eyebrow' => __('presentation.species_profile', ['species' => $pet['species']]),
                'title' => $pet['name'],
                'description' => $pet['status'],
                'meta' => [
                    ['icon' => 'paw-print', 'label' => $pet['breed']],
                    ['icon' => 'user-round', 'label' => __('messages.with_205bff7725').$pet['owner']],
                    [
                        'icon' => 'map-pin',
                        'label' => __('presentation.neighborhood_location', [
                            'neighborhood' => $pet['neighborhood'],
                        ]),
                    ],
                ],
                'tags' => $pet['traits'],
                'stats' => [
                    ['label' => __('messages.profile_d696a35bdd'), 'value' => __('messages.new_18fdd549b2'), 'detail' => __('messages.ready_to_discover_ab973383c6')],
                    ['label' => __('messages.friends_bd104d1b98'), 'value' => '0', 'detail' => __('messages.connections_so_far_91ce8b4644')],
                    ['label' => __('messages.walks_22e4ca854b'), 'value' => '0', 'detail' => __('messages.plans_together_826cd5211d')],
                ],
            ],
            'primary' => [
                'label' => __('messages.follow_641d1ef657'),
                'icon' => 'user-plus',
                'active_label' => __('messages.following_344b4271ca'),
                'active_icon' => 'user-check',
                'active' => $this->state->isActive('follows', $pet['key']),
                'action' => 'toggle-follow',
            ],
            'about' => [
                'eyebrow' => __('messages.daily_life_e51ed38f2a'),
                'title' => __('messages.about_c381a5010d').$pet['name'],
                'copy' => $pet['status'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle',
                    'title' => __('messages.ask_about_routines_93ad137d13'),
                    'description' => __('messages.start_with_familiar_places_greeting_preferences_and_the__f036988df8'),
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => __('messages.plan_an_easy_first_meeting_ce933e51b1'),
                    'description' => __('messages.choose_a_neutral_location_and_keep_the_first_introductio_643426c1f0'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.keep_care_details_private_b117672658'),
                    'description' => __('messages.share_medical_or_access_information_only_with_the_people_f1f732f494'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.species_56205e12c2'), 'value' => $pet['species']],
                ['label' => __('messages.breed_or_type_fe0b9a5ca2'), 'value' => $pet['breed']],
                ['label' => __('messages.neighborhood_1e99f12669'), 'value' => $pet['neighborhood']],
            ],
            'notice' => [
                'icon' => 'paw-print',
                'title' => __('messages.a_new_neighborhood_profile_ff4e20499d'),
                'description' => __('messages.following_keeps_this_profile_in_my_circle_while_connecti_c1bd81abcf'),
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
