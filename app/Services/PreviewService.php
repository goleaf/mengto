<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class PreviewService
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly ComposerCatalog $composers,
        private readonly ThreadCatalog $threads,
        private readonly InteractionPresenter $interactions,
        private readonly CirclePresenter $circle,
        private readonly WalkPlanPresenter $walks,
        private readonly SharePresenter $shares,
        private readonly CreatedContentPresenter $created,
        private readonly ProfilePresenter $profiles,
        private readonly FeedPresenter $feed,
        private readonly GroupCatalog $groups,
        private readonly EventCatalog $events,
        private readonly PlaceCatalog $places,
        private readonly PlacePresenter $placePresenter,
        private readonly NeighborProfilePresenter $neighborProfile,
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
                ...$this->circlePets(),
            ]),
            neighbors: $this->interactions->neighbors($this->directoryNeighbors()),
            groups: $this->interactions->groups([
                ...$this->created->groups(),
                ...$this->directoryGroups(),
            ]),
            meetups: $this->interactions->meetups([
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
     *     summary: array{
     *         eyebrow: string,
     *         title: string,
     *         description: string,
     *         count: string,
     *         schedule: array<int, array{label: string, value: string, detail: string}>
     *     },
     *     filters: list<array{value: string, label: string}>,
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
                'eyebrow' => __('messages.portland_meetups'),
                'title' => __('messages.meet_your_neighborhood_pack'),
                'description' => __('messages.low_pressure_walks_and_social_time_planned_by_nearby_pet_people'),
                'count' => __('messages.3_meetups_portland_or'),
                'schedule' => [
                    ['label' => __('messages.next'), 'value' => __('messages.sat_aug_1'), 'detail' => __('messages.10_00_am')],
                    ['label' => __('messages.upcoming'), 'value' => __('messages.3_meetups'), 'detail' => __('messages.38_going')],
                    ['label' => __('messages.closest'), 'value' => __('messages.1_2_miles'), 'detail' => __('messages.laurelhurst')],
                ],
            ],
            'filters' => $this->filterOptions([
                'upcoming' => __('messages.upcoming'),
                'walks' => __('messages.walks'),
                'social' => __('messages.social'),
                'indoor' => __('messages.indoor'),
            ]),
            'directoryMeetups' => $this->interactions->meetups([
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
     *     filters: list<array{value: string, label: string}>,
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
                'eyebrow' => __('messages.portland_communities'),
                'title' => __('messages.find_your_people_and_their_pets'),
                'description' => __('messages.join_local_circles_built_around_routines_neighborhoods_and_the_pets_you_care_for'),
                'count' => __('messages.4_groups_portland_or'),
                'highlights' => [
                    ['label' => __('messages.members'), 'value' => '13.8k', 'detail' => __('messages.across_all_groups')],
                    ['label' => __('messages.activity'), 'value' => '420', 'detail' => __('messages.posts_this_week')],
                    ['label' => __('messages.circles'), 'value' => '4', 'detail' => __('messages.around_portland_lowercase')],
                ],
            ],
            'filters' => $this->filterOptions([
                'recommended' => __('messages.recommended'),
                'local' => __('messages.local'),
                'care' => __('messages.care'),
                'outdoors' => __('messages.outdoors'),
            ]),
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
                'eyebrow' => __('messages.neighborhood_meetup'),
                'long_description' => __('messages.this_small_host_guided_social_gives_dogs_time_to_arrive_observe_and_join_at_their_own_pace_the_fenced_lawn_is_split_into_an_active_play_area_and_a_quieter'),
                'meta' => [
                    [
                        'icon' => 'calendar-days',
                        'label' => $meetup['date_label'].' · '.$meetup['time'],
                        'datetime' => $meetup['datetime'],
                        'aria_label' => $meetup['date_accessible'],
                    ],
                    ['icon' => 'map-pin', 'label' => $meetup['place'].' · '.$meetup['neighborhood']],
                    ['icon' => 'navigation', 'label' => __('presentation.distance_from_you', ['distance' => $meetup['distance']])],
                ],
                'stats' => [
                    ['label' => __('messages.going'), 'value' => '18', 'detail' => __('messages.8_spots_left')],
                    ['label' => __('messages.duration'), 'value' => __('messages.60_min'), 'detail' => __('messages.easy_pace')],
                    ['label' => __('messages.dog_size'), 'value' => __('messages.under_30_lb'), 'detail' => __('messages.calm_arrivals')],
                ],
                'rsvp' => $this->state->isActive('meetups', $meetup['key']),
            ]),
            'expectations' => [
                [
                    'icon' => 'footprints',
                    'title' => __('messages.arrive_on_leash'),
                    'description' => __('messages.take_one_quiet_lap_outside_the_enclosure_before_choosing_a_comfortable_entry'),
                ],
                [
                    'icon' => 'waves',
                    'title' => __('messages.pause_when_needed'),
                    'description' => __('messages.a_shaded_reset_area_gives_pets_and_people_room_to_step_away_without_leaving'),
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => __('messages.follow_each_dog_s_pace'),
                    'description' => __('messages.ask_before_greetings_and_keep_toys_or_shared_treats_packed_until_the_host_invites_them'),
                ],
            ],
            'attendees' => [
                ['name' => __('messages.jamie_olive'), 'detail' => __('messages.host_corgi'), 'initials' => 'JO', 'tone' => 'sun'],
                ['name' => __('messages.mia_scout'), 'detail' => __('messages.border_collie'), 'initials' => 'MS', 'tone' => 'mint'],
                ['name' => __('messages.ari_mochi'), 'detail' => __('messages.shiba_mix'), 'initials' => 'AM', 'tone' => 'paper'],
                ['name' => __('messages.theo_bean'), 'detail' => __('messages.terrier_mix'), 'initials' => 'TB', 'tone' => 'mint'],
            ],
            'host' => [
                'name' => __('messages.jamie_cho'),
                'role' => __('messages.meetup_host_alberta_arts'),
                'bio' => __('messages.jamie_plans_low_pressure_gatherings_for_smaller_dogs_and_helps_new_arrivals_find_a_comfortable_starting_point'),
                'initials' => 'JC',
                'tone' => 'sun',
            ],
            'details' => [
                ['label' => __('messages.meeting_point'), 'value' => __('messages.se_ankeny_entrance_with_comma_beside_the_covered_picnic_tables')],
                ['label' => __('messages.parking'), 'value' => __('messages.street_parking_along_se_37th_avenue')],
                ['label' => __('messages.bring'), 'value' => __('messages.leash_water_bowl_waste_bags_and_your_dog_s_usual_rewards')],
                ['label' => __('messages.weather_plan'), 'value' => __('messages.moves_to_the_covered_pavilion_during_light_rain')],
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
                'eyebrow' => __('messages.portland_community'),
                'long_description' => __('messages.apartment_pets_pdx_is_a_practical_circle_for_sharing_calm_routines_enrichment_ideas_and_neighbor_friendly_solutions_members_compare_what_works_in_real_homes'),
                'meta' => [
                    ['icon' => 'map-pin', 'label' => __('messages.portland_oregon')],
                    ['icon' => 'lock-keyhole-open', 'label' => __('messages.public_group')],
                    ['icon' => 'calendar-days', 'label' => __('messages.started_in_2021')],
                ],
                'stats' => [
                    ['label' => __('messages.members'), 'value' => '2.4k', 'detail' => __('messages.local_pet_people')],
                    ['label' => __('messages.this_week'), 'value' => __('messages.86_posts'), 'detail' => __('messages.steady_activity')],
                    ['label' => __('messages.response'), 'value' => __('messages.42_min'), 'detail' => __('messages.typical_first_reply')],
                ],
                'joined' => $this->state->isActive('groups', $group['key']),
            ],
            'principles' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => __('messages.share_lived_experience'),
                    'description' => __('messages.offer_routines_you_have_tried_and_include_enough_context_for_neighbors_to_adapt_them_safely'),
                ],
                [
                    'icon' => 'volume-2',
                    'title' => __('messages.keep_buildings_peaceful'),
                    'description' => __('messages.discuss_sound_shared_hallways_elevators_and_outdoor_access_with_care_for_every_resident'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.lead_with_pet_welfare'),
                    'description' => __('messages.use_qualified_professionals_for_medical_or_behavioral_concerns_and_keep_advice_supportive'),
                ],
            ],
            'moderators' => [
                ['name' => __('messages.ari_jensen'), 'detail' => __('messages.lead_organizer_dog_routines'), 'initials' => 'AJ', 'tone' => 'sun'],
                ['name' => __('messages.lena_brooks'), 'detail' => __('messages.moderator_cat_enrichment'), 'initials' => 'LB', 'tone' => 'mint'],
                ['name' => __('messages.priya_shah'), 'detail' => __('messages.moderator_small_pets'), 'initials' => 'PS', 'tone' => 'paper'],
            ],
            'activity' => [
                [
                    'icon' => 'book-open-text',
                    'title' => __('messages.a_calmer_hallway_arrival'),
                    'description' => __('messages.community_guide_updated_yesterday'),
                ],
                [
                    'icon' => 'messages-square',
                    'title' => __('messages.window_perch_ideas_for_compact_rooms'),
                    'description' => __('messages.24_replies_active_today'),
                ],
                [
                    'icon' => 'calendar-clock',
                    'title' => __('messages.indoor_enrichment_swap'),
                    'description' => __('messages.thursday_aug_6_buckman_community_room'),
                ],
            ],
            'details' => [
                ['label' => __('messages.who_it_is_for'), 'value' => __('messages.renters_apartment_residents_and_neighbors_sharing_compact_spaces')],
                ['label' => __('messages.main_topics'), 'value' => __('messages.enrichment_sound_shared_spaces_routines_and_local_resources')],
                ['label' => __('messages.posting_pace'), 'value' => __('messages.about_12_new_discussions_each_day')],
                ['label' => __('messages.community_review'), 'value' => __('messages.new_posts_are_checked_against_the_group_guidelines')],
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
     *     filters: list<array{value: string, label: string}>,
     *     directoryNeighbors: array<int, array{
     *         key: string,
     *         name: string,
     *         category: string,
     *         category_icon: string,
     *         neighborhood: string,
     *         distance: string,
     *         distance_value: float,
     *         pet: string,
     *         status: string,
     *         mutual_count: int,
     *         image: string,
     *         image_small: string,
     *         image_medium: string,
     *         thumbnail: string,
     *         image_alt: string,
     *         interests: array<int, string>,
     *         search_tokens: array<int, string>,
     *         profile_route: string|null
     *     }>
     * }
     */
    public function neighborDirectoryData(): array
    {
        return [
            'owner' => $this->owner(),
            'summary' => [
                'eyebrow' => __('neighbors.page.eyebrow'),
                'title' => __('neighbors.page.heading'),
                'description' => __('neighbors.page.description'),
                'count' => __('neighbors.page.count'),
                'highlights' => [
                    ['label' => __('neighbors.summary.closest.label'), 'value' => __('neighbors.summary.closest.value'), 'detail' => __('neighbors.summary.closest.detail')],
                    ['label' => __('neighbors.summary.circles.label'), 'value' => __('neighbors.summary.circles.value'), 'detail' => __('neighbors.summary.circles.detail')],
                    ['label' => __('neighbors.summary.pets.label'), 'value' => __('neighbors.summary.pets.value'), 'detail' => __('neighbors.summary.pets.detail')],
                ],
            ],
            'filters' => $this->filterOptions([
                'recommended' => __('neighbors.filters.recommended'),
                'dog-people' => __('neighbors.filters.dog_people'),
                'cat-people' => __('neighbors.filters.cat_people'),
                'foster-network' => __('neighbors.filters.foster_network'),
            ]),
            'directoryNeighbors' => $this->interactions->neighbors($this->directoryNeighbors()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ariNeighborProfileData(): array
    {
        return $this->neighborProfile->present(
            owner: $this->owner(),
            recentMoments: $this->interactions->posts($this->ariMoments()),
            followed: $this->state->isActive('follows', 'ari'),
        );
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string, unread_count: int},
     *     filters: list<array{value: string, label: string}>,
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
                'label' => __('messages.today'),
                'items' => [
                    [
                        'category' => __('messages.paws'),
                        'title' => __('messages.ari_sent_scout_12_paws'),
                        'body' => __('messages.your_yellow_frisbee_moment_is_getting_attention_from_nearby_dog_people'),
                        'context' => __('messages.scout_recent_moment'),
                        'time' => __('messages.12_min'),
                        'datetime' => '2026-07-29T09:48:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['ari']['thumbnail'],
                        'image_alt' => $neighbors['ari']['image_alt'],
                    ],
                    [
                        'category' => __('messages.reply'),
                        'title' => __('messages.lena_replied_to_your_foster_checklist'),
                        'body' => __('messages.she_added_a_note_about_creating_a_quiet_room_before_a_new_pet_arrives'),
                        'context' => __('messages.foster_network_pdx'),
                        'time' => __('messages.1_hr'),
                        'datetime' => '2026-07-29T09:00:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['lena']['thumbnail'],
                        'image_alt' => $neighbors['lena']['image_alt'],
                    ],
                    [
                        'category' => __('messages.meetup'),
                        'title' => __('messages.calm_senior_dog_stroll_is_next_wednesday'),
                        'body' => __('messages.the_shaded_riverside_route_has_eight_neighbors_going'),
                        'context' => __('messages.sellwood_riverfront_park_6_00_pm'),
                        'time' => __('messages.2_hr'),
                        'datetime' => '2026-07-29T08:00:00-07:00',
                        'unread' => true,
                        'image' => $meetups['senior-stroll']['thumbnail'],
                        'image_alt' => $meetups['senior-stroll']['image_alt'],
                    ],
                ],
            ],
            [
                'label' => __('messages.earlier'),
                'items' => [
                    [
                        'category' => __('messages.follow'),
                        'title' => __('messages.priya_followed_you_and_scout'),
                        'body' => __('messages.you_both_share_an_interest_in_calm_routines_and_garden_time'),
                        'context' => __('messages.st_johns_3_8_mi_away'),
                        'time' => __('messages.yesterday'),
                        'datetime' => '2026-07-28T16:20:00-07:00',
                        'unread' => false,
                        'image' => $neighbors['priya']['thumbnail'],
                        'image_alt' => $neighbors['priya']['image_alt'],
                    ],
                    [
                        'category' => __('messages.group'),
                        'title' => __('messages.apartment_pets_pdx_shared_a_new_guide'),
                        'body' => __('messages.the_community_collected_practical_ideas_for_quieter_hallway_arrivals'),
                        'context' => __('messages.small_space_routines_2_4k_members'),
                        'time' => __('messages.yesterday'),
                        'datetime' => '2026-07-28T11:30:00-07:00',
                        'unread' => false,
                        'image' => $groups['apartment-pets']['thumbnail'],
                        'image_alt' => $groups['apartment-pets']['image_alt'],
                    ],
                    [
                        'category' => __('messages.saved'),
                        'title' => __('messages.noah_saved_your_shaded_route_note'),
                        'body' => __('messages.your_richmond_walk_is_now_part_of_noah_and_juniper_s_summer_list'),
                        'context' => __('messages.juniper_senior_care'),
                        'time' => __('messages.mon'),
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
                'eyebrow' => __('messages.brand.activity'),
                'title' => __('messages.what_happened_around_your_pack'),
                'description' => __('messages.reactions_replies_reminders_and_neighbor_updates_gathered_into_one_calm_timeline'),
                'count' => __('presentation.updates_with_new', [
                    'updates' => trans_choice('presentation.updates_count', count($activityItems), [
                        'count' => count($activityItems),
                    ]),
                    'new' => __('presentation.new_count', ['count' => $unreadCount]),
                ]),
                'unread_count' => $unreadCount,
            ],
            'filters' => $this->filterOptions([
                'all-activity' => __('messages.all_activity'),
                'mentions' => __('messages.mentions'),
                'walks' => __('messages.walks'),
                'groups' => __('messages.groups'),
            ]),
            'activityGroups' => $activityGroups,
            'weeklyStats' => [
                ['label' => __('messages.paws'), 'value' => '32'],
                ['label' => __('messages.replies'), 'value' => '8'],
                ['label' => __('messages.neighbors'), 'value' => '3'],
            ],
            'upcoming' => [
                'eyebrow' => __('messages.next_meetup'),
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
                ['key' => 'meetup-reminders', 'label' => __('messages.meetup_reminders'), 'description' => __('messages.a_day_before_local_events'), 'enabled' => $settings['meetup-reminders']],
                ['key' => 'neighbor-replies', 'label' => __('messages.neighbor_replies'), 'description' => __('messages.replies_and_mentions'), 'enabled' => $settings['neighbor-replies']],
                ['key' => 'weekly-digest', 'label' => __('messages.weekly_digest'), 'description' => __('messages.sunday_activity_summary'), 'enabled' => $settings['weekly-digest']],
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
                ...$this->circlePets(),
            ]), 0, 4),
            'recentMoments' => $this->interactions->posts($this->scoutMoments()),
            'availability' => [
                ['label' => __('messages.best_time'), 'value' => __('messages.weekend_mornings')],
                ['label' => __('messages.usual_pace'), 'value' => __('messages.easy_to_moderate')],
                ['label' => __('messages.home_base'), 'value' => __('messages.richmond_portland')],
            ],
            'interests' => [__('messages.trail_walks'), __('messages.foster_care'), __('messages.cat_enrichment'), __('messages.quiet_parks'), __('messages.positive_training_lowercase')],
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
                'type' => __('messages.pet_moment'),
                'active_section' => 'feed',
                'eyebrow' => __('messages.share_a_pet_moment'),
                'title' => __('presentation.with_author', [
                    'pet' => $post['pet'],
                    'author' => $post['author'],
                ]),
                'description' => $post['body'],
                'image' => $post['image'],
                'image_small' => $post['image_small'],
                'image_medium' => $post['image_medium'],
                'image_alt' => $post['image_alt'],
                'route' => 'posts.show',
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
                'type' => __('messages.community'),
                'active_section' => 'groups',
                'eyebrow' => __('messages.share_a_community'),
                'title' => $group['name'],
                'description' => $group['description'],
                'image' => $group['image'],
                'image_small' => $group['image_small'],
                'image_medium' => $group['image_medium'],
                'image_alt' => $group['image_alt'],
                'route' => 'groups.show',
                'route_parameters' => ['group' => $target],
            ];
        }

        $event = $this->events->find($target);

        if ($event !== null) {
            return [
                'target' => $target,
                'type' => __('messages.event'),
                'active_section' => 'meetups',
                'eyebrow' => __('messages.share_a_public_event_card'),
                'title' => $event['title'],
                'description' => $event['short_description'],
                'image' => $event['image'],
                'image_small' => $event['image_small'],
                'image_medium' => $event['image_medium'],
                'image_alt' => $event['image_alt'],
                'route' => 'meetups.show',
                'route_parameters' => ['event' => $target],
            ];
        }

        if ($target === 'mia-carter') {
            $owner = $this->miaOwner();

            return [
                'target' => $target,
                'type' => __('messages.member_profile'),
                'active_section' => 'profile',
                'eyebrow' => __('messages.share_a_neighbor_profile'),
                'title' => $owner['name'],
                'description' => $owner['bio'],
                'image' => $owner['cover_image'],
                'image_small' => $owner['cover_image_small'],
                'image_medium' => $owner['cover_image_medium'],
                'image_alt' => $owner['cover_image_alt'],
                'route' => 'profile.mia',
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
                'type' => __('messages.pet_profile'),
                'active_section' => 'pets',
                'eyebrow' => __('messages.share_a_pet_profile'),
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
    private function circlePets(): array
    {
        return [
            [
                'name' => __('messages.scout'),
                'species' => __('messages.dog'),
                'breed' => __('messages.border_collie_mix'),
                'age' => __('messages.4_years'),
                'owner' => __('messages.mia_carter'),
                'neighborhood' => __('messages.richmond'),
                'status' => __('messages.available_for_park_walks'),
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass'),
                'traits' => [__('messages.high_energy'), __('messages.trail_walks')],
                'profile_route' => 'pets.scout',
            ],
            [
                'name' => __('messages.nori'),
                'species' => __('messages.cat'),
                'breed' => __('messages.tabby'),
                'age' => __('messages.2_years'),
                'owner' => __('messages.mia_carter'),
                'neighborhood' => __('messages.richmond'),
                'status' => __('messages.window_watching_expert'),
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera'),
                'traits' => ['indoor', 'curious'],
                'profile_route' => 'pets.nori',
            ],
            [
                'name' => __('messages.maple'),
                'species' => __('messages.dog'),
                'breed' => __('messages.golden_retriever'),
                'age' => __('messages.6_years'),
                'owner' => __('messages.ari_jensen'),
                'neighborhood' => __('messages.sellwood'),
                'status' => __('messages.easy_trail_companion'),
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.maple_a_golden_retriever_sitting_outside_with_a_flower'),
                'traits' => ['calm', __('messages.water_fan')],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.olive'),
                'species' => __('messages.dog'),
                'breed' => __('messages.pembroke_corgi'),
                'age' => __('messages.3_years'),
                'owner' => __('messages.jamie_cho'),
                'neighborhood' => __('messages.alberta_arts'),
                'status' => __('messages.likes_short_social_walks'),
                'image' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.olive_a_corgi_sitting_on_a_sunny_path'),
                'traits' => [__('messages.small_dog'), 'social'],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.pico'),
                'species' => __('messages.bird'),
                'breed' => __('messages.green_cheek_conure'),
                'age' => __('messages.5_years'),
                'owner' => __('messages.sam_rivera'),
                'neighborhood' => __('messages.hawthorne'),
                'status' => __('messages.quiet_mornings_curious_afternoons'),
                'image' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.pico_a_green_cheek_conure_perched_on_a_camera_tripod'),
                'traits' => ['indoor', 'talkative'],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.clover'),
                'species' => __('messages.rabbit'),
                'breed' => __('messages.mini_lop_mix'),
                'age' => __('messages.2_years'),
                'owner' => __('messages.priya_shah'),
                'neighborhood' => __('messages.st_johns'),
                'status' => __('messages.gentle_garden_observer'),
                'image' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.clover_a_white_rabbit_sitting_in_grass'),
                'traits' => ['gentle', __('messages.garden_time')],
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
                'detail_route' => 'meetups.small_dog_social',
                'title' => __('messages.small_dog_social_hour'),
                'category' => __('messages.social'),
                'day' => 'SAT',
                'date' => '01',
                'date_label' => __('messages.sat_aug_1'),
                'date_accessible' => __('messages.saturday_august_1_2026_at_10_00_am'),
                'datetime' => '2026-08-01T10:00:00-07:00',
                'time' => __('messages.10_00_am'),
                'place' => __('messages.laurelhurst_park'),
                'neighborhood' => __('messages.southeast_portland'),
                'distance' => __('messages.1_2_mi'),
                'attendees' => __('messages.18_neighbors_going'),
                'description' => __('messages.a_calm_fenced_gathering_with_room_for_short_introductions_and_plenty_of_breaks'),
                'host' => __('messages.jamie_cho'),
                'host_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.small_dogs_meeting_in_a_fenced_neighborhood_park'),
                'tags' => [__('messages.small_dogs'), __('messages.fenced_area')],
            ],
            [
                'key' => 'foster-coffee-walk',
                'detail_route' => null,
                'title' => __('messages.rescue_foster_coffee_walk'),
                'category' => __('messages.coffee_walk'),
                'day' => 'SUN',
                'date' => '02',
                'date_label' => __('messages.sun_aug_2'),
                'date_accessible' => __('messages.sunday_august_2_2026_at_9_30_am'),
                'datetime' => '2026-08-02T09:30:00-07:00',
                'time' => __('messages.9_30_am'),
                'place' => __('messages.tabor_commons'),
                'neighborhood' => __('messages.mount_tabor'),
                'distance' => __('messages.2_4_mi'),
                'attendees' => __('messages.12_neighbors_going'),
                'description' => __('messages.an_easy_loop_for_foster_families_recent_adopters_and_dogs_building_city_confidence'),
                'host' => __('messages.lena_brooks'),
                'host_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.pet_owners_taking_a_relaxed_community_walk_through_a_park'),
                'tags' => [__('messages.foster_friendly'), __('messages.easy_pace')],
            ],
            [
                'key' => 'senior-stroll',
                'detail_route' => null,
                'title' => __('messages.calm_senior_dog_stroll'),
                'category' => __('messages.slow_walk'),
                'day' => 'WED',
                'date' => '05',
                'date_label' => __('messages.wed_aug_5'),
                'date_accessible' => __('messages.wednesday_august_5_2026_at_6_00_pm'),
                'datetime' => '2026-08-05T18:00:00-07:00',
                'time' => __('messages.6_00_pm'),
                'place' => __('messages.sellwood_riverfront_park'),
                'neighborhood' => __('messages.sellwood'),
                'distance' => __('messages.3_1_mi'),
                'attendees' => __('messages.8_neighbors_going'),
                'description' => __('messages.a_shaded_riverside_route_with_a_gentle_pace_and_frequent_sniff_stops'),
                'host' => __('messages.noah_patel'),
                'host_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.small_dogs_exploring_an_autumn_park_together'),
                'tags' => [__('messages.senior_pets'), __('messages.shaded_route')],
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
                'detail_route' => 'groups.apartment_pets',
                'name' => __('messages.apartment_pets_pdx'),
                'category' => __('messages.home_life'),
                'members' => __('messages.2_4k_members'),
                'activity' => __('messages.86_posts_this_week'),
                'topic' => __('messages.small_space_routines'),
                'description' => __('messages.swap_enrichment_ideas_neighbor_friendly_routines_and_practical_fixes_for_happy_pets_in_smaller_homes'),
                'organizer' => __('messages.ari_jensen'),
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_on_a_couch_at_home'),
                'tags' => ['apartments', __('messages.indoor_enrichment')],
            ],
            [
                'key' => 'trail-tails',
                'detail_route' => null,
                'name' => __('messages.trail_tails'),
                'category' => __('messages.outdoors'),
                'members' => __('messages.8_1k_members'),
                'activity' => __('messages.214_posts_this_week'),
                'topic' => __('messages.local_hikes_and_safety'),
                'description' => __('messages.plan_trail_days_share_seasonal_conditions_and_compare_low_stress_routes_around_portland'),
                'organizer' => __('messages.noah_patel'),
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dogs_running_together_in_a_neighborhood_park'),
                'tags' => [__('messages.trail_walks'), __('messages.route_reports')],
            ],
            [
                'key' => 'cat-people',
                'detail_route' => null,
                'name' => __('messages.cat_people_of_portland'),
                'category' => __('messages.cats'),
                'members' => __('messages.1_9k_members'),
                'activity' => __('messages.72_posts_this_week'),
                'topic' => __('messages.indoor_cats_and_neighborhood_care'),
                'description' => __('messages.compare_enrichment_share_cat_friendly_local_services_and_help_indoor_companions_thrive'),
                'organizer' => __('messages.lena_brooks'),
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.two_fluffy_cats_sitting_together_indoors'),
                'tags' => [__('messages.cat_care'), 'enrichment'],
            ],
            [
                'key' => 'foster-network',
                'detail_route' => null,
                'name' => __('messages.foster_network_pdx'),
                'category' => __('messages.care'),
                'members' => __('messages.1_4k_members'),
                'activity' => __('messages.48_posts_this_week'),
                'topic' => __('messages.foster_support_and_adoption'),
                'description' => __('messages.connect_with_experienced_fosters_coordinate_supplies_and_support_thoughtful_transitions_into_new_homes'),
                'organizer' => __('messages.priya_shah'),
                'organizer_initials' => 'PS',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.foster_dog_resting_on_a_blue_couch'),
                'tags' => [__('messages.foster_care'), __('messages.adoption_support')],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     name: string,
     *     category: string,
     *     category_icon: string,
     *     neighborhood: string,
     *     distance: string,
     *     distance_value: float,
     *     pet: string,
     *     status: string,
     *     mutual_count: int,
     *     image: string,
     *     image_small: string,
     *     image_medium: string,
     *     thumbnail: string,
     *     image_alt: string,
     *     interests: array<int, string>,
     *     search_tokens: array<int, string>,
     *     profile_route: string|null
     * }>
     */
    private function directoryNeighbors(): array
    {
        return [
            [
                'key' => 'ari',
                'name' => __('neighbors.catalog.ari.name'),
                'category' => __('neighbors.catalog.ari.category'),
                'category_icon' => 'footprints',
                'neighborhood' => __('neighbors.catalog.ari.neighborhood'),
                'distance' => __('neighbors.catalog.ari.distance'),
                'distance_value' => 0.8,
                'pet' => __('neighbors.catalog.ari.pet'),
                'status' => __('neighbors.catalog.ari.status'),
                'mutual_count' => 4,
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('neighbors.catalog.ari.image_alt'),
                'interests' => [__('neighbors.catalog.ari.interests.first'), __('neighbors.catalog.ari.interests.second')],
                'search_tokens' => ['dog-people', 'walk', 'training'],
                'profile_route' => 'neighbors.ari',
            ],
            [
                'key' => 'noah',
                'name' => __('neighbors.catalog.noah.name'),
                'category' => __('neighbors.catalog.noah.category'),
                'category_icon' => 'heart-handshake',
                'neighborhood' => __('neighbors.catalog.noah.neighborhood'),
                'distance' => __('neighbors.catalog.noah.distance'),
                'distance_value' => 1.7,
                'pet' => __('neighbors.catalog.noah.pet'),
                'status' => __('neighbors.catalog.noah.status'),
                'mutual_count' => 3,
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('neighbors.catalog.noah.image_alt'),
                'interests' => [__('neighbors.catalog.noah.interests.first'), __('neighbors.catalog.noah.interests.second')],
                'search_tokens' => ['dog-people', 'senior', 'care'],
                'profile_route' => null,
            ],
            [
                'key' => 'lena',
                'name' => __('neighbors.catalog.lena.name'),
                'category' => __('neighbors.catalog.lena.category'),
                'category_icon' => 'sparkles',
                'neighborhood' => __('neighbors.catalog.lena.neighborhood'),
                'distance' => __('neighbors.catalog.lena.distance'),
                'distance_value' => 2.1,
                'pet' => __('neighbors.catalog.lena.pet'),
                'status' => __('neighbors.catalog.lena.status'),
                'mutual_count' => 5,
                'image' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('neighbors.catalog.lena.image_alt'),
                'interests' => [__('neighbors.catalog.lena.interests.first'), __('neighbors.catalog.lena.interests.second')],
                'search_tokens' => ['cat-people', 'foster-network', 'fostering'],
                'profile_route' => null,
            ],
            [
                'key' => 'priya',
                'name' => __('neighbors.catalog.priya.name'),
                'category' => __('neighbors.catalog.priya.category'),
                'category_icon' => 'paw-print',
                'neighborhood' => __('neighbors.catalog.priya.neighborhood'),
                'distance' => __('neighbors.catalog.priya.distance'),
                'distance_value' => 3.8,
                'pet' => __('neighbors.catalog.priya.pet'),
                'status' => __('neighbors.catalog.priya.status'),
                'mutual_count' => 2,
                'image' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('neighbors.catalog.priya.image_alt'),
                'interests' => [__('neighbors.catalog.priya.interests.first'), __('neighbors.catalog.priya.interests.second')],
                'search_tokens' => ['foster-network', 'rabbits', 'care'],
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
                'author' => __('messages.noah_patel'),
                'pet' => __('messages.juniper'),
                'time' => __('messages.1_hr_ago'),
                'datetime' => '2026-07-29T09:00:00-07:00',
                'body' => __('messages.found_a_quiet_route_near_maple_loop_with_shade_almost_the_whole_way_good_for_senior_pups_on_warmer_afternoons'),
                'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => __('messages.juniper_relaxing_during_a_shady_afternoon_walk'),
                'tags' => [__('messages.senior_pets'), __('messages.walk_route')],
                'stats' => ['paws' => '86', 'replies' => '11'],
            ],
            [
                'author' => __('messages.lena_brooks'),
                'pet' => __('messages.pip'),
                'time' => __('messages.3_hrs_ago'),
                'datetime' => '2026-07-29T07:00:00-07:00',
                'body' => __('messages.first_successful_harness_session_pip_mostly_accepted_the_agreement_after_a_careful_review_of_the_snack_clause'),
                'image' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => __('messages.pip_looking_toward_the_camera_from_a_soft_blanket'),
                'tags' => [__('messages.cat_life'), __('messages.first_steps')],
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
                'author' => __('neighbors.profile.moments.second.author'),
                'pet' => __('neighbors.profile.moments.second.pet'),
                'time' => __('neighbors.profile.moments.second.time'),
                'datetime' => '2026-07-26T09:00:00-07:00',
                'body' => __('neighbors.profile.moments.second.body'),
                'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('neighbors.profile.moments.second.image_alt'),
                'tags' => [
                    __('neighbors.profile.moments.second.first_tag'),
                    __('neighbors.profile.moments.second.second_tag'),
                ],
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
            'author' => __('neighbors.profile.moments.first.author'),
            'pet' => __('neighbors.profile.moments.first.pet'),
            'time' => __('neighbors.profile.moments.first.time'),
            'datetime' => '2026-07-29T09:42:00-07:00',
            'body' => __('neighbors.profile.moments.first.body'),
            'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1200&h=900&q=80',
            'image_small' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=576&h=432&q=78',
            'image_medium' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=900&h=675&q=80',
            'image_alt' => __('neighbors.profile.moments.first.image_alt'),
            'tags' => [
                __('neighbors.profile.moments.first.first_tag'),
                __('neighbors.profile.moments.first.second_tag'),
            ],
            'stats' => ['paws' => '128', 'replies' => '24'],
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
                'author' => __('messages.mia_carter'),
                'pet' => __('messages.scout'),
                'time' => __('messages.yesterday'),
                'datetime' => '2026-07-28T17:30:00-07:00',
                'body' => __('messages.scout_locked_onto_the_yellow_frisbee_and_caught_it_on_the_second_try_the_trip_home_was_much_quieter'),
                'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_catching_a_yellow_frisbee_on_the_grass'),
                'tags' => ['fetch', __('messages.scout')],
                'stats' => ['paws' => '94', 'replies' => '16'],
            ],
            [
                'author' => __('messages.mia_carter'),
                'pet' => __('messages.scout'),
                'time' => __('messages.4_days_ago'),
                'datetime' => '2026-07-25T16:00:00-07:00',
                'body' => __('messages.after_a_calm_neighborhood_walk_scout_claimed_the_porch_and_watched_the_trees_until_dinner'),
                'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_resting_on_a_wooden_porch'),
                'tags' => [__('messages.slow_afternoon'), __('messages.small_wins')],
                'stats' => ['paws' => '121', 'replies' => '21'],
            ],
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @return list<array{value: string, label: string}>
     */
    private function filterOptions(array $options): array
    {
        return array_map(
            static fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ],
            array_values($options),
            array_keys($options),
        );
    }
}
