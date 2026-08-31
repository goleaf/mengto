<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class PreviewService
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly AuthenticatedUserPresenter $authenticatedUsers,
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
            posts: array_values(array_column($this->interactions->posts([
                ...$this->created->posts(),
            ]), null, 'key')),
            pets: $this->interactions->pets($this->created->pets()),
            neighbors: [],
            groups: $this->interactions->groups($this->created->groups()),
            meetups: [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function walkPlanData(string $filter = 'upcoming'): array
    {
        return $this->walks->present($filter);
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
    /**
     * @return array{
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
            'summary' => [
                'eyebrow' => __('neighbors.page.eyebrow'),
                'title' => __('neighbors.page.heading'),
                'description' => __('neighbors.page.description'),
                'count' => '0',
                'highlights' => [],
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
     * @return array{
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
     *     upcoming: array{eyebrow: string, title: string, date: string, place: string, attendees: string, image: string, image_alt: string}|null,
     *     settings: array<int, array{label: string, description: string, enabled: bool}>
     * }
     */
    public function notificationCenterData(): array
    {
        $settings = $this->state->settings([
            'meetup-reminders' => true,
            'neighbor-replies' => true,
            'weekly-digest' => false,
        ]);

        return [
            'summary' => [
                'eyebrow' => __('messages.brand.activity'),
                'title' => __('messages.what_happened_around_your_pack'),
                'description' => __('messages.reactions_replies_reminders_and_neighbor_updates_gathered_into_one_calm_timeline'),
                'count' => __('presentation.updates_with_new', [
                    'updates' => trans_choice('presentation.updates_count', 0, ['count' => 0]),
                    'new' => __('presentation.new_count', ['count' => 0]),
                ]),
                'unread_count' => 0,
            ],
            'filters' => $this->filterOptions([
                'all-activity' => __('messages.all_activity'),
                'mentions' => __('messages.mentions'),
                'walks' => __('messages.walks'),
                'groups' => __('messages.groups'),
            ]),
            'activityGroups' => [],
            'weeklyStats' => [],
            'upcoming' => null,
            'settings' => [
                ['key' => 'meetup-reminders', 'label' => __('messages.meetup_reminders'), 'description' => __('messages.a_day_before_local_events'), 'enabled' => $settings['meetup-reminders']],
                ['key' => 'neighbor-replies', 'label' => __('messages.neighbor_replies'), 'description' => __('messages.replies_and_mentions'), 'enabled' => $settings['neighbor-replies']],
                ['key' => 'weekly-digest', 'label' => __('messages.weekly_digest'), 'description' => __('messages.sunday_activity_summary'), 'enabled' => $settings['weekly-digest']],
            ],
        ];
    }

    public function composerData(string $kind, array $context, User $user): array
    {
        $owner = $this->authenticatedUsers->present($user);
        $petKey = (string) ($context['pet'] ?? '');
        $pet = $petKey !== '' ? ($this->profiles->pet($petKey) ?? []) : [];
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
                    'pet_privacy' => [],
                    'report' => $report,
                    'post' => $post,
                    'post_report' => $postReport,
                    'group_report' => $groupReport,
                    'place_report' => $placeReport,
                    'place_correction' => $placeCorrection,
                    'place_context' => $placeContext,
                    'identities' => [
                        $owner['profile_route_parameters']['socialActor'] => $user->name,
                    ],
                    'pet_options' => collect($this->profiles->pets())->mapWithKeys(
                        static fn (array $managedPet): array => [
                            $managedPet['profile_key'] => $managedPet['name'],
                        ],
                    )->all(),
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

        if (str_starts_with($target, 'pet-')) {
            $pet = $this->profiles->pet(substr($target, 4));

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
                'route_parameters' => $pet['route_parameters'],
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
                'detail_route' => null,
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
        return [];
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
