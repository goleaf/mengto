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
        private readonly ConversationPresenter $conversationDetails,
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
                'eyebrow' => __('messages.portland_meetups_9e001fe6a5'),
                'title' => __('messages.meet_your_neighborhood_pack_49366ac3d2'),
                'description' => __('messages.low_pressure_walks_and_social_time_planned_by_nearby_pet_9a2e842ae3'),
                'count' => __('messages.3_meetups_portland_or_d055a7fecf'),
                'schedule' => [
                    ['label' => __('messages.next_1ff57a29d7'), 'value' => __('messages.sat_aug_1_2989493e54'), 'detail' => __('messages.10_00_am_48ae3f036f')],
                    ['label' => __('messages.upcoming_5f1a2542e4'), 'value' => __('messages.3_meetups_2a3a1bae64'), 'detail' => __('messages.38_going_5d69771ed0')],
                    ['label' => __('messages.closest_63eaa6b03c'), 'value' => __('messages.1_2_miles_ba37e87f9f'), 'detail' => __('messages.laurelhurst_91ec2dfbac')],
                ],
            ],
            'filters' => $this->filterOptions([
                'upcoming' => __('messages.upcoming_5f1a2542e4'),
                'walks' => __('messages.walks_22e4ca854b'),
                'social' => __('messages.social_f1b7505afa'),
                'indoor' => __('messages.indoor_a9cb38b7dd'),
            ]),
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
                'eyebrow' => __('messages.portland_communities_48a16a45e9'),
                'title' => __('messages.find_your_people_and_their_pets_6224ec329e'),
                'description' => __('messages.join_local_circles_built_around_routines_neighborhoods_a_ebeb78c486'),
                'count' => __('messages.4_groups_portland_or_059c55d9a7'),
                'highlights' => [
                    ['label' => __('messages.members_1044a4c056'), 'value' => '13.8k', 'detail' => __('messages.across_all_groups_60dd55e0b0')],
                    ['label' => __('messages.activity_38da1505ca'), 'value' => '420', 'detail' => __('messages.posts_this_week_c25c79bb40')],
                    ['label' => __('messages.circles_aa74f400e5'), 'value' => '4', 'detail' => __('messages.around_portland_250951c1c1')],
                ],
            ],
            'filters' => $this->filterOptions([
                'recommended' => __('messages.recommended_d70604e843'),
                'local' => __('messages.local_8c31e6e722'),
                'care' => __('messages.care_4262074d6c'),
                'outdoors' => __('messages.outdoors_8bf8ef16e0'),
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
                'eyebrow' => __('messages.neighborhood_meetup_1b6d352bec'),
                'long_description' => __('messages.this_small_host_guided_social_gives_dogs_time_to_arrive__568eecaa86'),
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
                    ['label' => __('messages.going_7bd49cdc7d'), 'value' => '18', 'detail' => __('messages.8_spots_left_4656ad4f73')],
                    ['label' => __('messages.duration_4fc52a3c4c'), 'value' => __('messages.60_min_b63f1f48f7'), 'detail' => __('messages.easy_pace_bc8dc85055')],
                    ['label' => __('messages.dog_size_031f5bc25d'), 'value' => __('messages.under_30_lb_7c47c46825'), 'detail' => __('messages.calm_arrivals_fbb56318f0')],
                ],
                'rsvp' => $this->state->isActive('meetups', $meetup['key']),
            ]),
            'expectations' => [
                [
                    'icon' => 'footprints',
                    'title' => __('messages.arrive_on_leash_5b9f8d3be8'),
                    'description' => __('messages.take_one_quiet_lap_outside_the_enclosure_before_choosing_aaa6c07c4b'),
                ],
                [
                    'icon' => 'waves',
                    'title' => __('messages.pause_when_needed_05511756ba'),
                    'description' => __('messages.a_shaded_reset_area_gives_pets_and_people_room_to_step_a_28ed1966a3'),
                ],
                [
                    'icon' => 'heart-handshake',
                    'title' => __('messages.follow_each_dog_s_pace_c97ecb671e'),
                    'description' => __('messages.ask_before_greetings_and_keep_toys_or_shared_treats_pack_a57bb3a3c5'),
                ],
            ],
            'attendees' => [
                ['name' => __('messages.jamie_olive_a67bfaca63'), 'detail' => __('messages.host_corgi_55e734983b'), 'initials' => 'JO', 'tone' => 'sun'],
                ['name' => __('messages.mia_scout_a3ee0b10db'), 'detail' => __('messages.border_collie_7a978278fa'), 'initials' => 'MS', 'tone' => 'mint'],
                ['name' => __('messages.ari_mochi_a7832e9cd0'), 'detail' => __('messages.shiba_mix_384146dcff'), 'initials' => 'AM', 'tone' => 'paper'],
                ['name' => __('messages.theo_bean_af2faef4a7'), 'detail' => __('messages.terrier_mix_fbd55fd0b8'), 'initials' => 'TB', 'tone' => 'mint'],
            ],
            'host' => [
                'name' => __('messages.jamie_cho_5f313c129b'),
                'role' => __('messages.meetup_host_alberta_arts_41d546ae06'),
                'bio' => __('messages.jamie_plans_low_pressure_gatherings_for_smaller_dogs_and_a153c85e2b'),
                'initials' => 'JC',
                'tone' => 'sun',
            ],
            'details' => [
                ['label' => __('messages.meeting_point_f08183059f'), 'value' => __('messages.se_ankeny_entrance_beside_the_covered_picnic_tables_debc8b0d88')],
                ['label' => __('messages.parking_4a64d6c849'), 'value' => __('messages.street_parking_along_se_37th_avenue_4c504dedc5')],
                ['label' => __('messages.bring_d75025a6a1'), 'value' => __('messages.leash_water_bowl_waste_bags_and_your_dog_s_usual_rewards_3a1fcad7ea')],
                ['label' => __('messages.weather_plan_e873beb6ad'), 'value' => __('messages.moves_to_the_covered_pavilion_during_light_rain_887978372c')],
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
                'eyebrow' => __('messages.portland_community_f15a480da9'),
                'long_description' => __('messages.apartment_pets_pdx_is_a_practical_circle_for_sharing_cal_2dd92170e4'),
                'meta' => [
                    ['icon' => 'map-pin', 'label' => __('messages.portland_oregon_af1587f101')],
                    ['icon' => 'lock-keyhole-open', 'label' => __('messages.public_group_b99668e88a')],
                    ['icon' => 'calendar-days', 'label' => __('messages.started_in_2021_e43390e305')],
                ],
                'stats' => [
                    ['label' => __('messages.members_1044a4c056'), 'value' => '2.4k', 'detail' => __('messages.local_pet_people_d368831de8')],
                    ['label' => __('messages.this_week_8c4eef5ab2'), 'value' => __('messages.86_posts_5157e4054b'), 'detail' => __('messages.steady_activity_62e2b15a0b')],
                    ['label' => __('messages.response_9061383b8e'), 'value' => __('messages.42_min_f7e8a117b4'), 'detail' => __('messages.typical_first_reply_cb451b1c93')],
                ],
                'joined' => $this->state->isActive('groups', $group['key']),
            ],
            'principles' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => __('messages.share_lived_experience_49a8ed4e3c'),
                    'description' => __('messages.offer_routines_you_have_tried_and_include_enough_context_76150fdc5b'),
                ],
                [
                    'icon' => 'volume-2',
                    'title' => __('messages.keep_buildings_peaceful_df65b04ecd'),
                    'description' => __('messages.discuss_sound_shared_hallways_elevators_and_outdoor_acce_f1d0ed89b6'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.lead_with_pet_welfare_91f1d9b8ea'),
                    'description' => __('messages.use_qualified_professionals_for_medical_or_behavioral_co_2f960a1b79'),
                ],
            ],
            'moderators' => [
                ['name' => __('messages.ari_jensen_6c670df410'), 'detail' => __('messages.lead_organizer_dog_routines_7eadf0525a'), 'initials' => 'AJ', 'tone' => 'sun'],
                ['name' => __('messages.lena_brooks_ca42e74116'), 'detail' => __('messages.moderator_cat_enrichment_e430728130'), 'initials' => 'LB', 'tone' => 'mint'],
                ['name' => __('messages.priya_shah_8925523814'), 'detail' => __('messages.moderator_small_pets_d91818577c'), 'initials' => 'PS', 'tone' => 'paper'],
            ],
            'activity' => [
                [
                    'icon' => 'book-open-text',
                    'title' => __('messages.a_calmer_hallway_arrival_b9e21f1554'),
                    'description' => __('messages.community_guide_updated_yesterday_ea8f9bcc5f'),
                ],
                [
                    'icon' => 'messages-square',
                    'title' => __('messages.window_perch_ideas_for_compact_rooms_1b767eebba'),
                    'description' => __('messages.24_replies_active_today_364cd42a1b'),
                ],
                [
                    'icon' => 'calendar-clock',
                    'title' => __('messages.indoor_enrichment_swap_fd696bc75a'),
                    'description' => __('messages.thursday_aug_6_buckman_community_room_c4fcf1f9a6'),
                ],
            ],
            'details' => [
                ['label' => __('messages.who_it_is_for_bcdecc549d'), 'value' => __('messages.renters_apartment_residents_and_neighbors_sharing_compac_16a4af4356')],
                ['label' => __('messages.main_topics_d353451168'), 'value' => __('messages.enrichment_sound_shared_spaces_routines_and_local_resour_c89fb2a3d8')],
                ['label' => __('messages.posting_pace_2ac4ffe4b5'), 'value' => __('messages.about_12_new_discussions_each_day_28e90c7c7a')],
                ['label' => __('messages.community_review_712be9f45c'), 'value' => __('messages.new_posts_are_checked_against_the_group_guidelines_5f5af61b89')],
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
                'eyebrow' => __('messages.portland_neighbors_c6674bf8c7'),
                'title' => __('messages.meet_the_people_behind_the_pets_a0afb859f3'),
                'description' => __('messages.find_nearby_owners_who_share_your_routes_routines_and_ap_662718d443'),
                'count' => __('messages.4_people_portland_or_6d032af4c1'),
                'highlights' => [
                    ['label' => __('messages.closest_63eaa6b03c'), 'value' => __('messages.0_8_mi_a7c6601d0f'), 'detail' => __('messages.pearl_district_af25f9947a')],
                    ['label' => __('messages.shared_circles_a9bdd05b04'), 'value' => '7', 'detail' => __('messages.across_pawcircle_06906fcd26')],
                    ['label' => __('messages.pets_7dc1cd7eaf'), 'value' => '4', 'detail' => __('messages.dogs_cats_rabbits_03e9ec87cd')],
                ],
            ],
            'filters' => $this->filterOptions([
                'recommended' => __('messages.recommended_d70604e843'),
                'dog-people' => __('messages.dog_people_5b391f604e'),
                'cat-people' => __('messages.cat_people_6dcede448a'),
                'foster-network' => __('messages.foster_network_09c7f6184c'),
            ]),
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
            ['name' => __('messages.mia_carter_0e5b29cc3b'), 'initials' => 'MC', 'context' => __('messages.richmond_walks_d578ccde2c'), 'tone' => 'sun'],
            ['name' => __('messages.jamie_cho_5f313c129b'), 'initials' => 'JC', 'context' => __('messages.apartment_pets_pdx_6488f4db06'), 'tone' => 'mint'],
            ['name' => __('messages.noah_patel_147a9793ed'), 'initials' => 'NP', 'context' => __('messages.trail_tails_8c13c56b9f'), 'tone' => 'paper'],
            ['name' => __('messages.lena_brooks_ca42e74116'), 'initials' => 'LB', 'context' => __('messages.foster_network_pdx_790a8f59dc'), 'tone' => 'mint'],
        ];

        $mutualCount = count($mutualNeighbors);

        return [
            'owner' => $this->owner(),
            'neighbor' => [
                'key' => 'ari',
                'name' => __('messages.ari_jensen_6c670df410'),
                'handle' => '@ari-jensen',
                'role' => __('messages.dog_walks_b35dc82b9b'),
                'category' => __('messages.dog_walks_b35dc82b9b'),
                'location' => __('messages.pearl_district_portland_or_d015b48fc9'),
                'distance' => __('messages.0_8_mi_away_bf413142dd'),
                'member_since' => __('messages.member_since_2024_8cd8fa63cc'),
                'status' => __('messages.open_to_calm_cafe_walks_788a58087c'),
                'bio' => __('messages.ari_and_mochi_keep_a_steady_loop_between_quiet_pearl_dis_4f47d8892d'),
                'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'avatar_alt' => __('messages.ari_relaxing_with_mochi_in_a_neighborhood_park_2e4ba2f4ec'),
                'cover_image' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1600&h=720&q=85',
                'cover_image_small' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=720&h=480&q=80',
                'cover_image_medium' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1200&h=600&q=82',
                'cover_image_alt' => __('messages.two_shiba_inu_dogs_ready_for_a_neighborhood_walk_8c7d1d6fcf'),
                'mutual_count' => $mutualCount,
                'stats' => [
                    ['label' => __('messages.pet_8f0d1b30eb'), 'value' => __('messages.mochi_95114c81f3'), 'detail' => __('messages.shiba_mix_384146dcff')],
                    ['label' => __('messages.mutuals_12966208be'), 'value' => (string) $mutualCount, 'detail' => __('messages.nearby_neighbors_4a38cc9f05')],
                    ['label' => __('messages.home_3a78695388'), 'value' => __('messages.pearl_72bc556112'), 'detail' => __('messages.0_8_mi_away_bf413142dd')],
                ],
                'interests' => [__('messages.city_walks_a347b642ed'), 'training', __('messages.quiet_patios_c21699cb94'), __('messages.urban_routines_fa90da9faf')],
                'followed' => $this->state->isActive('follows', 'ari'),
                'actions' => [
                    [
                        'label' => __('messages.follow_641d1ef657'),
                        'icon' => 'user-plus',
                        'endpoint' => route('actions.perform'),
                        'payload' => [
                            'action' => 'toggle-follow',
                            'target' => 'ari',
                            'label' => __('messages.ari_jensen_6c670df410'),
                        ],
                        'variant' => 'primary',
                        'active' => $this->state->isActive('follows', 'ari'),
                        'active_label' => __('messages.following_344b4271ca'),
                        'active_icon' => 'user-check',
                        'pressed' => $this->state->isActive('follows', 'ari'),
                    ],
                    [
                        'label' => __('messages.message_2f77668a9d'),
                        'icon' => 'message-circle',
                        'href' => route('messages.index'),
                        'variant' => 'paper',
                    ],
                ],
            ],
            'pet' => [
                'name' => __('messages.mochi_95114c81f3'),
                'owner_name' => __('messages.ari_f6302850cc'),
                'breed' => __('messages.shiba_mix_384146dcff'),
                'age' => __('messages.3_years_50a85bc562'),
                'status' => __('messages.calm_in_familiar_places_and_happiest_with_patient_introd_c8734f8394'),
                'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.mochi_sitting_with_another_shiba_at_a_neighborhood_cafe_9815d90e67'),
                'traits' => [__('messages.patient_hellos_1903154287'), __('messages.city_confident_536e6c784f'), __('messages.treat_motivated_b026bf09d8')],
                'routine' => [
                    ['label' => __('messages.favorite_route_03964e230e'), 'value' => __('messages.nw_11th_to_fields_park_4ab904aab3')],
                    ['label' => __('messages.best_time_9bbcba7bd0'), 'value' => __('messages.early_morning_be2fe9ea30')],
                    ['label' => __('messages.cafe_rule_736a9bb5d1'), 'value' => __('messages.patio_first_table_second_4d97aca203')],
                ],
            ],
            'mutualNeighbors' => $mutualNeighbors,
            'communities' => [
                ['name' => __('messages.apartment_pets_pdx_6488f4db06'), 'topic' => __('messages.small_space_routines_31ea3a8e79'), 'members' => __('messages.2_4k_members_ef94699d39')],
                ['name' => __('messages.trail_tails_8c13c56b9f'), 'topic' => __('messages.weekend_city_loops_d83b794e49'), 'members' => __('messages.8_1k_members_bd48b96dc1')],
            ],
            'recentMoments' => $this->interactions->posts($this->ariMoments()),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     summary: array{eyebrow: string, title: string, description: string, count: string, unread_count: int},
     *     filters: list<array{value: string, label: string}>,
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
                'eyebrow' => __('messages.brand.inbox'),
                'title' => __('messages.neighborhood_messages_90502f603a'),
                'description' => __('messages.keep_walk_plans_care_notes_and_everyday_pet_updates_in_o_82e62fc748'),
                'count' => __('presentation.conversations_with_unread', [
                    'conversations' => trans_choice('presentation.conversations_count', count($conversations), [
                        'count' => count($conversations),
                    ]),
                    'unread' => __('presentation.unread_count', ['count' => $unreadCount]),
                ]),
                'unread_count' => $unreadCount,
            ],
            'filters' => $this->filterOptions([
                'all' => __('messages.all_a52ace420f'),
                'unread' => __('messages.unread_1b9f384c14'),
                'walk-plans' => __('messages.walk_plans_64510c27c8'),
            ]),
            'conversations' => $conversations,
            'walkPlans' => $this->walks->messagePlans(),
            'thread' => [
                'contact' => [
                    'key' => $selectedKey,
                    'name' => $selectedConversation['name'],
                    'detail' => $selectedConversation['pet'].' · '.$selectedNeighbor['neighborhood'],
                    'response_note' => __('messages.usually_replies_within_an_hour_e644fc7020'),
                    'avatar' => $selectedNeighbor['thumbnail'],
                    'avatar_alt' => $selectedNeighbor['image_alt'],
                    'call_requested' => $this->state->isActive('call-requests', $selectedKey),
                ],
                'context' => [
                    'eyebrow' => __('messages.conversation_context_06776d63ec'),
                    'title' => __('presentation.pet_pair', [
                        'person' => $selectedConversation['pet'],
                        'pet' => __('messages.scout_8a1db462be'),
                    ]),
                    'detail' => $selectedNeighbor['status'],
                    'image' => $selectedNeighbor['thumbnail'],
                    'image_alt' => $selectedNeighbor['image_alt'],
                ],
                'date_label' => __('messages.today_2b065c7c9c'),
                'reply_placeholder' => __('presentation.reply_to_person_and_pet', [
                    'person' => $selectedConversation['name'],
                    'pet' => $selectedConversation['pet'],
                ]),
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
                'name' => __('messages.ari_jensen_6c670df410'),
                'pet' => __('messages.mochi_95114c81f3'),
                'preview' => __('messages.perfect_we_will_take_the_quiet_patio_corner_24036eb7c5'),
                'time' => __('messages.18_min_db4f7854cb'),
                'datetime' => '2026-07-29T09:42:00-07:00',
                'unread' => 2,
                'selected' => true,
                'image' => $neighbors['ari']['thumbnail'],
                'image_alt' => $neighbors['ari']['image_alt'],
            ],
            [
                'key' => 'lena',
                'name' => __('messages.lena_brooks_ca42e74116'),
                'pet' => __('messages.pip_cf64881060'),
                'preview' => __('messages.pip_approved_the_new_foster_setup_after_one_lap_da69c6d5ce'),
                'time' => __('messages.2_hr_0cb81360b7'),
                'datetime' => '2026-07-29T08:00:00-07:00',
                'unread' => 0,
                'selected' => false,
                'image' => $neighbors['lena']['thumbnail'],
                'image_alt' => $neighbors['lena']['image_alt'],
            ],
            [
                'key' => 'noah',
                'name' => __('messages.noah_patel_147a9793ed'),
                'pet' => __('messages.juniper_fe6a448ec9'),
                'preview' => __('messages.that_shaded_route_stays_comfortable_before_sunset_c5c8f37f5c'),
                'time' => __('messages.yesterday_566181254b'),
                'datetime' => '2026-07-28T18:30:00-07:00',
                'unread' => 0,
                'selected' => false,
                'image' => $neighbors['noah']['thumbnail'],
                'image_alt' => $neighbors['noah']['image_alt'],
            ],
            [
                'key' => 'priya',
                'name' => __('messages.priya_shah_8925523814'),
                'pet' => __('messages.clover_a740edd9c1'),
                'preview' => __('messages.i_added_the_garden_routine_notes_you_asked_for_3048cafa68'),
                'time' => __('messages.mon_f40d7f51f6'),
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
                    'sender' => __('messages.ari_f6302850cc'),
                    'time' => __('messages.9_12_am_72623fe5bf'),
                    'datetime' => '2026-07-29T09:12:00-07:00',
                    'body' => __('messages.morning_mochi_did_well_near_the_cafe_yesterday_would_sco_895d24528b'),
                    'mine' => false,
                ],
                [
                    'sender' => __('messages.mia_4150950870'),
                    'time' => __('messages.9_18_am_577d5c5ddb'),
                    'datetime' => '2026-07-29T09:18:00-07:00',
                    'body' => __('messages.scout_and_i_can_meet_near_fields_park_at_ten_a_short_loo_cc3df79dd8'),
                    'mine' => true,
                ],
                [
                    'sender' => __('messages.ari_f6302850cc'),
                    'time' => __('messages.9_21_am_aef3227c33'),
                    'datetime' => '2026-07-29T09:21:00-07:00',
                    'body' => __('messages.perfect_we_will_take_the_quiet_patio_corner_and_bring_th_fd63ee8ff7'),
                    'mine' => false,
                ],
                [
                    'sender' => __('messages.mia_4150950870'),
                    'time' => __('messages.9_24_am_c09d1143de'),
                    'datetime' => '2026-07-29T09:24:00-07:00',
                    'body' => __('messages.thank_you_we_will_keep_the_first_hello_slow_and_meet_you_119e799bbb'),
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
                'sender' => __('messages.mia_4150950870'),
                'time' => __('messages.recently_f81ae5034f'),
                'datetime' => '2026-07-29T09:30:00-07:00',
                'body' => __('messages.thanks_for_the_update_i_saved_the_note_for_our_next_neig_e63dc4325b'),
                'mine' => true,
            ],
        ];
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
                'label' => __('messages.today_2b065c7c9c'),
                'items' => [
                    [
                        'category' => __('messages.paws_45f20e8148'),
                        'title' => __('messages.ari_sent_scout_12_paws_51adbba6d3'),
                        'body' => __('messages.your_yellow_frisbee_moment_is_getting_attention_from_nea_949b8eefa1'),
                        'context' => __('messages.scout_recent_moment_c541f4ae17'),
                        'time' => __('messages.12_min_a6bcf4bec3'),
                        'datetime' => '2026-07-29T09:48:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['ari']['thumbnail'],
                        'image_alt' => $neighbors['ari']['image_alt'],
                    ],
                    [
                        'category' => __('messages.reply_c253f451bd'),
                        'title' => __('messages.lena_replied_to_your_foster_checklist_41751df713'),
                        'body' => __('messages.she_added_a_note_about_creating_a_quiet_room_before_a_ne_d356c5c037'),
                        'context' => __('messages.foster_network_pdx_790a8f59dc'),
                        'time' => __('messages.1_hr_90c95962e8'),
                        'datetime' => '2026-07-29T09:00:00-07:00',
                        'unread' => true,
                        'image' => $neighbors['lena']['thumbnail'],
                        'image_alt' => $neighbors['lena']['image_alt'],
                    ],
                    [
                        'category' => __('messages.meetup_b8e99f52bc'),
                        'title' => __('messages.calm_senior_dog_stroll_is_next_wednesday_2e8c8e81a9'),
                        'body' => __('messages.the_shaded_riverside_route_has_eight_neighbors_going_4341483691'),
                        'context' => __('messages.sellwood_riverfront_park_6_00_pm_78976f2c0b'),
                        'time' => __('messages.2_hr_0cb81360b7'),
                        'datetime' => '2026-07-29T08:00:00-07:00',
                        'unread' => true,
                        'image' => $meetups['senior-stroll']['thumbnail'],
                        'image_alt' => $meetups['senior-stroll']['image_alt'],
                    ],
                ],
            ],
            [
                'label' => __('messages.earlier_e10ae99074'),
                'items' => [
                    [
                        'category' => __('messages.follow_641d1ef657'),
                        'title' => __('messages.priya_followed_you_and_scout_de36791ae6'),
                        'body' => __('messages.you_both_share_an_interest_in_calm_routines_and_garden_t_71d8687ea6'),
                        'context' => __('messages.st_johns_3_8_mi_away_5aed602795'),
                        'time' => __('messages.yesterday_566181254b'),
                        'datetime' => '2026-07-28T16:20:00-07:00',
                        'unread' => false,
                        'image' => $neighbors['priya']['thumbnail'],
                        'image_alt' => $neighbors['priya']['image_alt'],
                    ],
                    [
                        'category' => __('messages.group_34ca0e7660'),
                        'title' => __('messages.apartment_pets_pdx_shared_a_new_guide_e6503ff44d'),
                        'body' => __('messages.the_community_collected_practical_ideas_for_quieter_hall_27d6755297'),
                        'context' => __('messages.small_space_routines_2_4k_members_2886fb8ac2'),
                        'time' => __('messages.yesterday_566181254b'),
                        'datetime' => '2026-07-28T11:30:00-07:00',
                        'unread' => false,
                        'image' => $groups['apartment-pets']['thumbnail'],
                        'image_alt' => $groups['apartment-pets']['image_alt'],
                    ],
                    [
                        'category' => __('messages.saved_b5c120b316'),
                        'title' => __('messages.noah_saved_your_shaded_route_note_dde3e31908'),
                        'body' => __('messages.your_richmond_walk_is_now_part_of_noah_and_juniper_s_sum_f0493c1a09'),
                        'context' => __('messages.juniper_senior_care_7a65167d72'),
                        'time' => __('messages.mon_f40d7f51f6'),
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
                'title' => __('messages.what_happened_around_your_pack_6888c360ca'),
                'description' => __('messages.reactions_replies_reminders_and_neighbor_updates_gathere_f0859b0055'),
                'count' => __('presentation.updates_with_new', [
                    'updates' => trans_choice('presentation.updates_count', count($activityItems), [
                        'count' => count($activityItems),
                    ]),
                    'new' => __('presentation.new_count', ['count' => $unreadCount]),
                ]),
                'unread_count' => $unreadCount,
            ],
            'filters' => $this->filterOptions([
                'all-activity' => __('messages.all_activity_29ebb2ef2d'),
                'mentions' => __('messages.mentions_6f32e692ed'),
                'walks' => __('messages.walks_22e4ca854b'),
                'groups' => __('messages.groups_39bbb719fa'),
            ]),
            'activityGroups' => $activityGroups,
            'weeklyStats' => [
                ['label' => __('messages.paws_45f20e8148'), 'value' => '32'],
                ['label' => __('messages.replies_31ecb5e00f'), 'value' => '8'],
                ['label' => __('messages.neighbors_ecc05289ef'), 'value' => '3'],
            ],
            'upcoming' => [
                'eyebrow' => __('messages.next_meetup_196217d613'),
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
                ['key' => 'meetup-reminders', 'label' => __('messages.meetup_reminders_143fdbed00'), 'description' => __('messages.a_day_before_local_events_9e2b201f8e'), 'enabled' => $settings['meetup-reminders']],
                ['key' => 'neighbor-replies', 'label' => __('messages.neighbor_replies_033db77c54'), 'description' => __('messages.replies_and_mentions_ad28958302'), 'enabled' => $settings['neighbor-replies']],
                ['key' => 'weekly-digest', 'label' => __('messages.weekly_digest_b134b14f1c'), 'description' => __('messages.sunday_activity_summary_7ead6fba64'), 'enabled' => $settings['weekly-digest']],
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
                ['label' => __('messages.best_time_9bbcba7bd0'), 'value' => __('messages.weekend_mornings_7f491ceb6f')],
                ['label' => __('messages.usual_pace_cb92b55a8a'), 'value' => __('messages.easy_to_moderate_4a32157874')],
                ['label' => __('messages.home_base_3c3cbe73c2'), 'value' => __('messages.richmond_portland_45cfbdb042')],
            ],
            'interests' => [__('messages.trail_walks_e65914f579'), __('messages.foster_care_12c77089f0'), __('messages.cat_enrichment_064d3c0748'), __('messages.quiet_parks_42c6d28887'), __('messages.positive_training_265845eade')],
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
                'type' => __('messages.pet_moment_6f5e8758d7'),
                'active_section' => 'feed',
                'eyebrow' => __('messages.share_a_pet_moment_af170ae2fc'),
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
                'type' => __('messages.community_bb501d7877'),
                'active_section' => 'groups',
                'eyebrow' => __('messages.share_a_community_2103f4bd66'),
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
                'type' => __('messages.event_4e1f49a9c8'),
                'active_section' => 'meetups',
                'eyebrow' => __('messages.share_a_public_event_card_f63d7ab4f9'),
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
                'type' => __('messages.member_profile_4df3247310'),
                'active_section' => 'profile',
                'eyebrow' => __('messages.share_a_neighbor_profile_cac6379361'),
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
                'type' => __('messages.pet_profile_fc2c49bb42'),
                'active_section' => 'pets',
                'eyebrow' => __('messages.share_a_pet_profile_36e8858b2b'),
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
                'name' => __('messages.scout_8a1db462be'),
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.border_collie_mix_9b8f12e319'),
                'age' => __('messages.4_years_cfd73a0bc4'),
                'owner' => __('messages.mia_carter_0e5b29cc3b'),
                'neighborhood' => __('messages.richmond_128b2a6b11'),
                'status' => __('messages.available_for_park_walks_0a5d4afdb7'),
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass_4abc84adab'),
                'traits' => [__('messages.high_energy_e3873bb814'), __('messages.trail_walks_e65914f579')],
                'profile_route' => 'pets.scout',
            ],
            [
                'name' => __('messages.nori_a64203ba20'),
                'species' => __('messages.cat_48735c4fae'),
                'breed' => __('messages.tabby_2631668147'),
                'age' => __('messages.2_years_7dab2372ff'),
                'owner' => __('messages.mia_carter_0e5b29cc3b'),
                'neighborhood' => __('messages.richmond_128b2a6b11'),
                'status' => __('messages.window_watching_expert_ae6ec53130'),
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera_3f2b66069e'),
                'traits' => ['indoor', 'curious'],
                'profile_route' => 'pets.nori',
            ],
            [
                'name' => __('messages.maple_18a91bdea2'),
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.golden_retriever_2eb35bd40e'),
                'age' => __('messages.6_years_df138afebb'),
                'owner' => __('messages.ari_jensen_6c670df410'),
                'neighborhood' => __('messages.sellwood_d70a1edd4b'),
                'status' => __('messages.easy_trail_companion_c67c9a74f8'),
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.maple_a_golden_retriever_sitting_outside_with_a_flower_013cbf5247'),
                'traits' => ['calm', __('messages.water_fan_f86d4263d7')],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.olive_3038ab334a'),
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.pembroke_corgi_0679fe095d'),
                'age' => __('messages.3_years_50a85bc562'),
                'owner' => __('messages.jamie_cho_5f313c129b'),
                'neighborhood' => __('messages.alberta_arts_323da1169e'),
                'status' => __('messages.likes_short_social_walks_35d3cbcabb'),
                'image' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1744207503498-a0218ad58ff8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.olive_a_corgi_sitting_on_a_sunny_path_f8f682d479'),
                'traits' => [__('messages.small_dog_549c2cc898'), 'social'],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.pico_e78f763aa2'),
                'species' => __('messages.bird_68b515e190'),
                'breed' => __('messages.green_cheek_conure_5198933759'),
                'age' => __('messages.5_years_9d8ee593ed'),
                'owner' => __('messages.sam_rivera_aa5afb2d46'),
                'neighborhood' => __('messages.hawthorne_29d40296ba'),
                'status' => __('messages.quiet_mornings_curious_afternoons_acd26dcf07'),
                'image' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1705603476532-d7c91b4b3788?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.pico_a_green_cheek_conure_perched_on_a_camera_tripod_021287ca37'),
                'traits' => ['indoor', 'talkative'],
                'profile_route' => null,
            ],
            [
                'name' => __('messages.clover_a740edd9c1'),
                'species' => __('messages.rabbit_4ea93dfb21'),
                'breed' => __('messages.mini_lop_mix_f3b1bfc9f3'),
                'age' => __('messages.2_years_7dab2372ff'),
                'owner' => __('messages.priya_shah_8925523814'),
                'neighborhood' => __('messages.st_johns_80b3497785'),
                'status' => __('messages.gentle_garden_observer_27a24de724'),
                'image' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1591561582301-7ce6588cc286?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.clover_a_white_rabbit_sitting_in_grass_5f3e03d00a'),
                'traits' => ['gentle', __('messages.garden_time_4b71b81d59')],
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
                'title' => __('messages.small_dog_social_hour_1bf893f4e1'),
                'category' => __('messages.social_f1b7505afa'),
                'day' => 'SAT',
                'date' => '01',
                'date_label' => __('messages.sat_aug_1_2989493e54'),
                'date_accessible' => __('messages.saturday_august_1_2026_at_10_00_am_9b5da30186'),
                'datetime' => '2026-08-01T10:00:00-07:00',
                'time' => __('messages.10_00_am_48ae3f036f'),
                'place' => __('messages.laurelhurst_park_b88ab4320c'),
                'neighborhood' => __('messages.southeast_portland_b79edafe3a'),
                'distance' => __('messages.1_2_mi_7aa05aea3a'),
                'attendees' => __('messages.18_neighbors_going_9c673ce4a4'),
                'description' => __('messages.a_calm_fenced_gathering_with_room_for_short_introduction_7c72ce97d6'),
                'host' => __('messages.jamie_cho_5f313c129b'),
                'host_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1667230228326-c881966e2a29?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.small_dogs_meeting_in_a_fenced_neighborhood_park_f3f1603c9d'),
                'tags' => [__('messages.small_dogs_6777c5d747'), __('messages.fenced_area_82d1c3b4de')],
            ],
            [
                'key' => 'foster-coffee-walk',
                'detail_route' => null,
                'title' => __('messages.rescue_foster_coffee_walk_003d5af9fa'),
                'category' => __('messages.coffee_walk_c4f587b101'),
                'day' => 'SUN',
                'date' => '02',
                'date_label' => __('messages.sun_aug_2_e16cbbbc07'),
                'date_accessible' => __('messages.sunday_august_2_2026_at_9_30_am_81e3f57dc7'),
                'datetime' => '2026-08-02T09:30:00-07:00',
                'time' => __('messages.9_30_am_4d90d4ed3c'),
                'place' => __('messages.tabor_commons_bd61fdc703'),
                'neighborhood' => __('messages.mount_tabor_e5525db52a'),
                'distance' => __('messages.2_4_mi_22afe13d12'),
                'attendees' => __('messages.12_neighbors_going_fc1ef249ec'),
                'description' => __('messages.an_easy_loop_for_foster_families_recent_adopters_and_dog_97f137d54a'),
                'host' => __('messages.lena_brooks_ca42e74116'),
                'host_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1782218950117-e47d3b455938?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.pet_owners_taking_a_relaxed_community_walk_through_a_par_e26d58e3aa'),
                'tags' => [__('messages.foster_friendly_b17b2cab01'), __('messages.easy_pace_bc8dc85055')],
            ],
            [
                'key' => 'senior-stroll',
                'detail_route' => null,
                'title' => __('messages.calm_senior_dog_stroll_01600e7ab0'),
                'category' => __('messages.slow_walk_ed0a899459'),
                'day' => 'WED',
                'date' => '05',
                'date_label' => __('messages.wed_aug_5_06aea78930'),
                'date_accessible' => __('messages.wednesday_august_5_2026_at_6_00_pm_b15cdcb63a'),
                'datetime' => '2026-08-05T18:00:00-07:00',
                'time' => __('messages.6_00_pm_6bc03202f6'),
                'place' => __('messages.sellwood_riverfront_park_f0f724fe0e'),
                'neighborhood' => __('messages.sellwood_d70a1edd4b'),
                'distance' => __('messages.3_1_mi_d22fd52438'),
                'attendees' => __('messages.8_neighbors_going_9d96bdf4b5'),
                'description' => __('messages.a_shaded_riverside_route_with_a_gentle_pace_and_frequent_22f920b040'),
                'host' => __('messages.noah_patel_147a9793ed'),
                'host_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1766114314882-89b64589f2c5?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.small_dogs_exploring_an_autumn_park_together_0272510c14'),
                'tags' => [__('messages.senior_pets_a45178dd21'), __('messages.shaded_route_65477395fa')],
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
                'name' => __('messages.apartment_pets_pdx_6488f4db06'),
                'category' => __('messages.home_life_352f399f6c'),
                'members' => __('messages.2_4k_members_ef94699d39'),
                'activity' => __('messages.86_posts_this_week_24ac592950'),
                'topic' => __('messages.small_space_routines_31ea3a8e79'),
                'description' => __('messages.swap_enrichment_ideas_neighbor_friendly_routines_and_pra_d98db609d5'),
                'organizer' => __('messages.ari_jensen_6c670df410'),
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_on_a_couch_at_home_d166817b1a'),
                'tags' => ['apartments', __('messages.indoor_enrichment_e68e763445')],
            ],
            [
                'key' => 'trail-tails',
                'detail_route' => null,
                'name' => __('messages.trail_tails_8c13c56b9f'),
                'category' => __('messages.outdoors_8bf8ef16e0'),
                'members' => __('messages.8_1k_members_bd48b96dc1'),
                'activity' => __('messages.214_posts_this_week_8c04362a32'),
                'topic' => __('messages.local_hikes_and_safety_8ee75e71d3'),
                'description' => __('messages.plan_trail_days_share_seasonal_conditions_and_compare_lo_f4a706b6de'),
                'organizer' => __('messages.noah_patel_147a9793ed'),
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dogs_running_together_in_a_neighborhood_park_4cd06ae794'),
                'tags' => [__('messages.trail_walks_e65914f579'), __('messages.route_reports_9e2098ab10')],
            ],
            [
                'key' => 'cat-people',
                'detail_route' => null,
                'name' => __('messages.cat_people_of_portland_138905abe2'),
                'category' => __('messages.cats_ec05d70c6f'),
                'members' => __('messages.1_9k_members_876733b68f'),
                'activity' => __('messages.72_posts_this_week_363f9014fd'),
                'topic' => __('messages.indoor_cats_and_neighborhood_care_9b46494de2'),
                'description' => __('messages.compare_enrichment_share_cat_friendly_local_services_and_ebc65fe578'),
                'organizer' => __('messages.lena_brooks_ca42e74116'),
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.two_fluffy_cats_sitting_together_indoors_7664c2677d'),
                'tags' => [__('messages.cat_care_2b2df9413f'), 'enrichment'],
            ],
            [
                'key' => 'foster-network',
                'detail_route' => null,
                'name' => __('messages.foster_network_pdx_790a8f59dc'),
                'category' => __('messages.care_4262074d6c'),
                'members' => __('messages.1_4k_members_6455d768db'),
                'activity' => __('messages.48_posts_this_week_6bc2479d78'),
                'topic' => __('messages.foster_support_and_adoption_8f815f33b9'),
                'description' => __('messages.connect_with_experienced_fosters_coordinate_supplies_and_720f9b4d67'),
                'organizer' => __('messages.priya_shah_8925523814'),
                'organizer_initials' => 'PS',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.foster_dog_resting_on_a_blue_couch_802e2213e4'),
                'tags' => [__('messages.foster_care_12c77089f0'), __('messages.adoption_support_ac665642c9')],
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
                'name' => __('messages.ari_jensen_6c670df410'),
                'category' => __('messages.dog_walks_b35dc82b9b'),
                'neighborhood' => __('messages.pearl_district_af25f9947a'),
                'distance' => __('messages.0_8_mi_a7c6601d0f'),
                'pet' => __('messages.mochi_shiba_mix_26f7b63bad'),
                'status' => __('messages.open_to_calm_cafe_walks_788a58087c'),
                'mutual_count' => 4,
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('messages.ari_relaxing_with_mochi_in_a_neighborhood_park_2e4ba2f4ec'),
                'interests' => [__('messages.city_walks_a347b642ed'), 'training'],
                'profile_route' => 'neighbors.ari',
            ],
            [
                'key' => 'noah',
                'name' => __('messages.noah_patel_147a9793ed'),
                'category' => __('messages.senior_care_5750fcfaf4'),
                'neighborhood' => __('messages.sellwood_d70a1edd4b'),
                'distance' => __('messages.1_7_mi_51d51e1e1b'),
                'pet' => __('messages.juniper_senior_retriever_bd509af01b'),
                'status' => __('messages.usually_out_before_sunset_dabbe186fd'),
                'mutual_count' => 3,
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('messages.noah_practicing_with_a_small_dog_in_a_wooded_park_a01c6fa46c'),
                'interests' => [__('messages.senior_pets_a45178dd21'), __('messages.shaded_routes_ed3997a6a4')],
                'profile_route' => null,
            ],
            [
                'key' => 'lena',
                'name' => __('messages.lena_brooks_ca42e74116'),
                'category' => __('messages.cat_people_6dcede448a'),
                'neighborhood' => __('messages.alberta_arts_323da1169e'),
                'distance' => __('messages.2_1_mi_4bc7c661d2'),
                'pet' => __('messages.pip_domestic_shorthair_0bd9d53931'),
                'status' => __('messages.sharing_foster_setup_notes_d33dd44305'),
                'mutual_count' => 5,
                'image' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1602135058921-09ccd6112363?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('messages.lena_holding_a_white_kitten_at_home_29bce018d5'),
                'interests' => [__('messages.cat_care_2b2df9413f'), 'fostering'],
                'profile_route' => null,
            ],
            [
                'key' => 'priya',
                'name' => __('messages.priya_shah_8925523814'),
                'category' => __('messages.small_pets_30fac1c938'),
                'neighborhood' => __('messages.st_johns_80b3497785'),
                'distance' => __('messages.3_8_mi_78d3f4f4a1'),
                'pet' => __('messages.clover_mini_lop_mix_b67afa79b7'),
                'status' => __('messages.garden_routines_and_quiet_care_3db935e76a'),
                'mutual_count' => 2,
                'image' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1663363332899-7a2448f724f3?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'image_alt' => __('messages.priya_holding_a_spotted_rabbit_indoors_fe3cf67f40'),
                'interests' => ['rabbits', __('messages.garden_time_4b71b81d59')],
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
                'author' => __('messages.noah_patel_147a9793ed'),
                'pet' => __('messages.juniper_fe6a448ec9'),
                'time' => __('messages.1_hr_ago_f98c800e71'),
                'datetime' => '2026-07-29T09:00:00-07:00',
                'body' => __('messages.found_a_quiet_route_near_maple_loop_with_shade_almost_th_328981b518'),
                'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => __('messages.juniper_relaxing_during_a_shady_afternoon_walk_847b031948'),
                'tags' => [__('messages.senior_pets_a45178dd21'), __('messages.walk_route_6abe2b24b2')],
                'stats' => ['paws' => '86', 'replies' => '11'],
            ],
            [
                'author' => __('messages.lena_brooks_ca42e74116'),
                'pet' => __('messages.pip_cf64881060'),
                'time' => __('messages.3_hrs_ago_d641605714'),
                'datetime' => '2026-07-29T07:00:00-07:00',
                'body' => __('messages.first_successful_harness_session_pip_mostly_accepted_the_978ec8d833'),
                'image' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=1200&h=900&q=80',
                'image_small' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=576&h=432&q=78',
                'image_medium' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=900&h=675&q=80',
                'image_alt' => __('messages.pip_looking_toward_the_camera_from_a_soft_blanket_47441c1282'),
                'tags' => [__('messages.cat_life_c7546bc034'), __('messages.first_steps_972ec47af5')],
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
                'author' => __('messages.ari_jensen_6c670df410'),
                'pet' => __('messages.mochi_95114c81f3'),
                'time' => __('messages.3_days_ago_46463ba858'),
                'datetime' => '2026-07-26T09:00:00-07:00',
                'body' => __('messages.tried_the_quiet_corner_at_our_neighborhood_cafe_before_t_f3a1225faf'),
                'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.mochi_sitting_with_another_shiba_at_a_neighborhood_cafe_9815d90e67'),
                'tags' => [__('messages.cafe_routine_d41b2c0433'), __('messages.calm_introductions_f2d9031186')],
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
            'author' => __('messages.ari_jensen_6c670df410'),
            'pet' => __('messages.mochi_95114c81f3'),
            'time' => __('messages.18_min_ago_54f545e4a9'),
            'datetime' => '2026-07-29T09:42:00-07:00',
            'body' => __('messages.mochi_finally_made_it_through_the_whole_cafe_patio_witho_af598f06a5'),
            'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1200&h=900&q=80',
            'image_small' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=576&h=432&q=78',
            'image_medium' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=900&h=675&q=80',
            'image_alt' => __('messages.mochi_walking_beside_another_dog_on_a_tree_lined_path_3b678123d4'),
            'tags' => ['training', __('messages.city_walks_a347b642ed')],
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
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'pet' => __('messages.scout_8a1db462be'),
                'time' => __('messages.yesterday_566181254b'),
                'datetime' => '2026-07-28T17:30:00-07:00',
                'body' => __('messages.scout_locked_onto_the_yellow_frisbee_and_caught_it_on_th_f9a8c62c68'),
                'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_catching_a_yellow_frisbee_on_the_grass_2b0d0e4b11'),
                'tags' => ['fetch', __('messages.scout_8a1db462be')],
                'stats' => ['paws' => '94', 'replies' => '16'],
            ],
            [
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'pet' => __('messages.scout_8a1db462be'),
                'time' => __('messages.4_days_ago_6faa883aa9'),
                'datetime' => '2026-07-25T16:00:00-07:00',
                'body' => __('messages.after_a_calm_neighborhood_walk_scout_claimed_the_porch_a_555d80c9b5'),
                'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_resting_on_a_wooden_porch_0fce6ea345'),
                'tags' => [__('messages.slow_afternoon_101d3ac946'), __('messages.small_wins_965630ccc8')],
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
