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
                'type_label' => __('messages.photo_update'),
                'author' => __('messages.ari_jensen'),
                'handle' => '@ari-jensen',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'neighbors.ari',
                'author_parameters' => [],
                'represented' => __('messages.mochi'),
                'represented_kind' => __('messages.pet_profile'),
                'manager' => __('messages.managed_by_ari_jensen'),
                'pet_slug' => 'mochi',
                'species' => 'dogs',
                'published_at' => '2026-07-29T09:42:00-07:00',
                'time' => __('messages.18_min_ago'),
                'title' => null,
                'body' => __('messages.mochi_made_it_through_the_whole_cafe_patio_without_inspecting_every_chair_we_kept_the_first_visit_short_chose_the_quiet_corner_and_left_while_it_was_still_easy'),
                'topic' => __('messages.training'),
                'location' => __('messages.pearl_district'),
                'audience' => __('messages.followers_and_friends'),
                'comment_policy' => __('messages.followers'),
                'tags' => ['training', __('messages.city_walks')],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => __('messages.you_follow_mochi_and_save_calm_training_posts'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1769635325695-dead509dc5b3',
                        __('messages.mochi_a_shiba_inu_standing_inside_a_quiet_cafe'),
                        __('messages.a_calm_moment_inside_the_pet_friendly_cafe'),
                    ),
                ],
                'reaction_counts' => ['like' => 84, 'love' => 31, 'support' => 9, 'useful' => 4],
                'replies' => 24,
                'reposts' => 6,
            ],
            [
                'key' => 'scout-shaded-loop',
                'format' => 'photo',
                'type_label' => __('messages.photo_carousel'),
                'author' => __('messages.scout'),
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.scout',
                'author_parameters' => [],
                'represented' => __('messages.scout'),
                'represented_kind' => __('messages.pet_profile'),
                'manager' => __('messages.published_as_scout_managed_by_mia_carter'),
                'pet_slug' => 'scout',
                'species' => 'dogs',
                'published_at' => '2026-07-29T08:25:00-07:00',
                'time' => __('messages.1_hr_ago'),
                'title' => __('messages.a_shaded_loop_worth_repeating'),
                'body' => __('messages.we_tried_the_east_loop_before_breakfast_the_route_stayed_shaded_the_first_greeting_was_calm_and_scout_found_enough_room_to_settle_before_heading_home'),
                'topic' => __('messages.walks'),
                'location' => __('messages.laurelhurst_park'),
                'audience' => __('messages.everyone'),
                'comment_policy' => __('messages.everyone'),
                'tags' => [__('messages.scout'), __('messages.walk_route'), __('messages.portland')],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => __('messages.scout_is_one_of_your_managed_pet_profiles'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        __('messages.scout_resting_on_grass_after_a_shaded_park_walk'),
                        __('messages.a_calm_pause_near_the_east_loop'),
                    ),
                    $this->image(
                        'photo-1624361239583-7ba5ffb376f5',
                        __('messages.scout_lying_in_grass_behind_a_tennis_ball'),
                        __('messages.one_last_throw_before_heading_home'),
                    ),
                    $this->image(
                        'photo-1621169225409-5de158d10015',
                        __('messages.scout_resting_on_a_wooden_porch'),
                        __('messages.back_home_and_fully_settled'),
                    ),
                ],
                'reaction_counts' => ['like' => 91, 'love' => 42, 'support' => 8, 'useful' => 18],
                'replies' => 16,
                'reposts' => 11,
            ],
            [
                'key' => 'dr-elena-heat-check',
                'format' => 'expert',
                'type_label' => __('messages.expert_note'),
                'author' => __('messages.dr_elena_ruiz'),
                'handle' => '@dr-elena-ruiz',
                'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.veterinary_profile'),
                'represented_kind' => __('messages.verified_specialist'),
                'manager' => __('messages.licensed_in_oregon_general_practice'),
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-29T07:40:00-07:00',
                'time' => __('messages.2_hrs_ago'),
                'title' => __('messages.three_signs_a_warm_walk_should_end_early'),
                'body' => __('messages.slowing_down_seeking_shade_and_unusually_heavy_panting_are_reasons_to_stop_and_cool_down_social_advice_cannot_diagnose_heat_illness_contact_a_veterinarian'),
                'topic' => __('messages.health'),
                'location' => __('messages.portland_or'),
                'audience' => __('messages.everyone'),
                'comment_policy' => __('messages.everyone'),
                'tags' => [__('messages.summer_safety'), __('messages.veterinary_note')],
                'feeds' => ['home', 'experts'],
                'why' => __('messages.you_follow_local_care_and_summer_safety_topics'),
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
                'type_label' => __('messages.adoption_profile'),
                'author' => __('messages.rose_city_animal_shelter'),
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.mabel'),
                'represented_kind' => __('messages.shelter_pet'),
                'manager' => __('messages.verified_shelter_adoption_team'),
                'pet_slug' => 'mabel',
                'species' => 'dogs',
                'published_at' => '2026-07-29T06:30:00-07:00',
                'time' => __('messages.3_hrs_ago'),
                'title' => __('messages.mabel_is_ready_for_a_quiet_home'),
                'body' => __('messages.mabel_is_a_five_year_old_mixed_breed_dog_who_settles_well_after_a_short_introduction_she_would_thrive_with_predictable_routines_a_secure_yard_and_a_family'),
                'topic' => __('messages.adoption'),
                'location' => __('messages.north_portland'),
                'audience' => __('messages.everyone'),
                'comment_policy' => __('messages.followers'),
                'tags' => ['adoption', __('messages.quiet_home')],
                'feeds' => ['home', 'shelters'],
                'why' => __('messages.you_asked_to_see_verified_shelters_near_portland'),
                'verified' => true,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1558788353-f76d92427f16',
                        __('messages.mabel_a_brown_shelter_dog_standing_outside'),
                        __('messages.mabel_during_a_quiet_afternoon_yard_break'),
                    ),
                ],
                'reaction_counts' => ['like' => 44, 'love' => 75, 'support' => 63, 'useful' => 12],
                'replies' => 28,
                'reposts' => 54,
            ],
            [
                'key' => 'willow-lost-richmond',
                'format' => 'lost',
                'type_label' => __('messages.lost_pet_alert'),
                'author' => __('messages.lena_brooks'),
                'handle' => '@lena-brooks',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.willow'),
                'represented_kind' => __('messages.lost_pet'),
                'manager' => __('messages.contact_the_owner_through_brand'),
                'pet_slug' => 'willow',
                'species' => 'cats',
                'published_at' => '2026-07-29T09:20:00-07:00',
                'time' => __('messages.40_min_ago'),
                'title' => __('messages.willow_was_last_seen_near_richmond_school'),
                'body' => __('messages.willow_is_a_small_grey_tabby_wearing_a_green_breakaway_collar_please_do_not_chase_her_use_the_in_platform_contact_button_with_a_safe_public_location_if_you_see'),
                'topic' => __('messages.lost_and_found'),
                'location' => __('messages.richmond_neighborhood_approximate_area'),
                'audience' => __('messages.local_emergency_reach'),
                'comment_policy' => __('messages.registered_members'),
                'tags' => [__('messages.lost_cat'), __('messages.richmond')],
                'feeds' => ['home', 'local', 'alerts'],
                'why' => __('messages.this_active_alert_is_within_your_approximate_neighborhood'),
                'verified' => false,
                'urgent' => true,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1573865526739-10659fec78a5',
                        __('messages.willow_a_grey_tabby_cat_looking_upward'),
                        __('messages.recent_photo_provided_by_willow_s_owner'),
                    ),
                ],
                'reaction_counts' => ['support' => 198, 'useful' => 86],
                'replies' => 31,
                'reposts' => 117,
            ],
            [
                'key' => 'sunny-first-play-video',
                'format' => 'video',
                'type_label' => __('messages.shelter_pet_video'),
                'author' => __('messages.rose_city_animal_shelter'),
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.sunny'),
                'represented_kind' => __('messages.pet_profile'),
                'manager' => __('messages.published_by_verified_shelter_staff'),
                'pet_slug' => 'sunny',
                'species' => 'dogs',
                'published_at' => '2026-07-28T17:10:00-07:00',
                'time' => __('messages.yesterday'),
                'title' => __('messages.sunny_s_first_play_session'),
                'body' => __('messages.a_young_foster_puppy_gets_a_calm_play_break_while_waiting_for_a_permanent_home_video_never_autoplays'),
                'topic' => __('messages.enrichment'),
                'location' => __('messages.portland_oregon'),
                'audience' => __('messages.public'),
                'comment_policy' => __('messages.registered_members'),
                'tags' => [__('messages.foster_puppy'), __('messages.play_enrichment')],
                'feeds' => ['home', 'pets', 'shelters', 'video'],
                'why' => __('messages.you_follow_verified_animal_shelters_near_portland'),
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
                        'alt' => __('messages.a_young_puppy_during_a_supervised_play_session'),
                        'caption' => __('messages.short_play_session_with_sound_controls_and_native_playback'),
                        'attribution' => __('messages.puppy_playing_video_by_subhashish_panigrahi_cc_by_sa_3_0'),
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
                'type_label' => __('messages.community_poll'),
                'author' => __('messages.apartment_pets_pdx'),
                'handle' => '@apartment-pets-pdx',
                'avatar' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'groups.apartment_pets',
                'author_parameters' => [],
                'represented' => __('messages.apartment_pets_pdx'),
                'represented_kind' => __('messages.open_group'),
                'manager' => __('messages.posted_by_group_moderator_ari_jensen'),
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-28T13:00:00-07:00',
                'time' => __('messages.yesterday'),
                'title' => __('messages.which_rainy_day_meetup_should_we_plan'),
                'body' => __('messages.choose_the_easiest_low_pressure_option_results_help_with_planning_and_are_not_scientific_community_statistics'),
                'topic' => __('messages.community'),
                'location' => __('messages.portland'),
                'audience' => __('messages.group_members'),
                'comment_policy' => __('messages.group_members'),
                'tags' => [__('messages.indoor_meetup'), __('messages.community_poll_lowercase')],
                'feeds' => ['home', 'groups'],
                'why' => __('messages.you_joined_apartment_pets_pdx'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [],
                'poll_options' => [
                    ['label' => __('messages.quiet_cafe_patio'), 'votes' => 46],
                    ['label' => __('messages.covered_park_pavilion'), 'votes' => 71],
                    ['label' => __('messages.indoor_training_room'), 'votes' => 39],
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
            $this->story(__('messages.scout'), __('messages.park_loop'), 'photo-1654256578072-b932c33cb92e', 'pets.scout', true),
            $this->story(__('messages.nori'), __('messages.window_watch'), 'photo-1518791841217-8f162f1e1131', 'pets.nori', true),
            $this->story(__('messages.mochi'), __('messages.cafe_win'), 'photo-1769635325695-dead509dc5b3', 'neighbors.ari'),
            $this->story(__('messages.rose_city'), __('messages.adoption_day'), 'photo-1558788353-f76d92427f16'),
            $this->story(__('messages.dr_elena'), __('messages.heat_safety'), 'photo-1559839734-2b71ea197ec2'),
            $this->story(__('messages.juniper'), __('messages.trail_note'), 'photo-1605568427561-40dd23c2acea'),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function modes(): array
    {
        return [
            'home' => ['label' => __('messages.for_you'), 'icon' => 'sparkles'],
            'following' => ['label' => __('messages.following'), 'icon' => 'user-check'],
            'friends' => ['label' => __('messages.friends'), 'icon' => 'users-round'],
            'pets' => ['label' => __('messages.pets'), 'icon' => 'paw-print'],
            'local' => ['label' => __('messages.local'), 'icon' => 'map-pin'],
            'groups' => ['label' => __('messages.groups'), 'icon' => 'messages-square'],
            'experts' => ['label' => __('messages.experts'), 'icon' => 'badge-check'],
            'shelters' => ['label' => __('messages.shelters'), 'icon' => 'house-heart'],
            'alerts' => ['label' => __('messages.lost_found'), 'icon' => 'siren'],
            'video' => ['label' => __('messages.video'), 'icon' => 'play'],
            'photos' => ['label' => __('messages.photos'), 'icon' => 'images'],
            'saved' => ['label' => __('messages.saved'), 'icon' => 'bookmark'],
            'drafts' => ['label' => __('messages.drafts'), 'icon' => 'file-pen-line'],
            'archive' => ['label' => __('messages.archive'), 'icon' => 'archive'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function mediaPresets(): array
    {
        return [
            'none' => [
                'label' => __('messages.text_only'),
                'format' => 'text',
                'media' => [],
            ],
            'scout-field' => [
                'label' => __('messages.scout_in_the_field'),
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        __('messages.scout_resting_in_a_green_field'),
                        __('messages.a_calm_break_outdoors'),
                    ),
                ],
            ],
            'nori-window' => [
                'label' => __('messages.nori_by_the_window'),
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1495360010541-f48722b34f7d',
                        __('messages.nori_resting_beside_a_bright_window'),
                        __('messages.afternoon_window_watch'),
                    ),
                ],
            ],
            'park-carousel' => [
                'label' => __('messages.three_photo_park_carousel'),
                'format' => 'photo',
                'media' => [
                    $this->image('photo-1624361239583-7ba5ffb376f5', __('messages.scout_waiting_behind_a_tennis_ball'), __('messages.ready_for_another_throw')),
                    $this->image('photo-1625679895477-526b21a77f0c', __('messages.scout_catching_a_yellow_frisbee'), __('messages.a_clean_catch_on_the_grass')),
                    $this->image('photo-1621169225409-5de158d10015', __('messages.scout_resting_on_a_wooden_porch'), __('messages.quiet_time_after_the_park')),
                ],
            ],
            'play-video' => [
                'label' => __('messages.short_pet_video'),
                'format' => 'video',
                'media' => [
                    [
                        'type' => 'video',
                        'source' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/Puppy_playing.webm',
                        'mime' => 'video/webm',
                        'poster' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=675&q=85',
                        'poster_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=324&q=80',
                        'poster_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=506&q=82',
                        'alt' => __('messages.a_young_dog_playing_indoors'),
                        'caption' => __('messages.short_pet_play_video'),
                        'attribution' => __('messages.puppy_playing_video_by_subhashish_panigrahi_cc_by_sa_3_0'),
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
            'walks' => __('messages.walks'),
            'care' => __('messages.care'),
            'health' => __('messages.health'),
            'training' => __('messages.training'),
            'enrichment' => __('messages.enrichment'),
            'adoption' => __('messages.adoption'),
            'lost-found' => __('messages.lost_and_found'),
            'community' => __('messages.community'),
            'photography' => __('messages.photography'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function audiences(): array
    {
        return [
            'public' => __('messages.everyone'),
            'members' => __('messages.registered_members'),
            'followers' => __('messages.followers'),
            'friends' => __('messages.friends'),
            'close-friends' => __('messages.close_friends'),
            'owners' => __('messages.pet_owners_and_managers'),
            'private' => __('messages.only_me'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function commentPolicies(): array
    {
        return [
            'all' => __('messages.everyone'),
            'followers' => __('messages.followers'),
            'friends' => __('messages.friends'),
            'mentioned' => __('messages.mentioned_profiles'),
            'none' => __('messages.comments_off'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identities(): array
    {
        return [
            'mia' => __('messages.mia_carter_owner_profile'),
            'scout' => __('messages.scout_managed_pet_profile'),
            'nori' => __('messages.nori_managed_pet_profile'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function safePlaces(): array
    {
        return [
            'none' => __('messages.do_not_show_a_place'),
            'portland' => __('messages.portland_or'),
            'richmond' => __('messages.richmond_neighborhood'),
            'laurelhurst' => __('messages.laurelhurst_park'),
            'fields-park' => __('messages.fields_park'),
            'pearl-district' => __('messages.pearl_district'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reactionOptions(bool $supportiveOnly = false): array
    {
        if ($supportiveOnly) {
            return [
                'support' => __('messages.support'),
                'useful' => __('messages.useful'),
            ];
        }

        return [
            'like' => __('messages.like'),
            'love' => __('messages.love'),
            'funny' => __('messages.funny'),
            'support' => __('messages.support'),
            'useful' => __('messages.useful'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reportReasons(): array
    {
        return [
            'spam' => __('messages.spam_or_repetitive_promotion'),
            'fraud' => __('messages.fraud_or_scam'),
            'animal-safety' => __('messages.animal_safety_concern'),
            'dangerous-advice' => __('messages.dangerous_medical_advice'),
            'stolen-media' => __('messages.stolen_photos_or_video'),
            'personal-data' => __('messages.personal_information_exposed'),
            'false-alert' => __('messages.false_lost_pet_alert'),
            'harassment' => __('messages.harassment_or_hate'),
            'other' => __('messages.other_concern'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identity(string $key): array
    {
        return match ($key) {
            'scout' => [
                'author' => __('messages.scout'),
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.scout',
                'represented' => __('messages.scout'),
                'represented_kind' => __('messages.pet_profile'),
                'manager' => __('messages.published_as_scout_managed_by_mia_carter'),
                'pet_slug' => 'scout',
                'species' => 'dogs',
            ],
            'nori' => [
                'author' => __('messages.nori'),
                'handle' => '@mia-carter/nori',
                'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.nori',
                'represented' => __('messages.nori'),
                'represented_kind' => __('messages.pet_profile'),
                'manager' => __('messages.published_as_nori_managed_by_mia_carter'),
                'pet_slug' => 'nori',
                'species' => 'cats',
            ],
            default => [
                'author' => __('messages.mia_carter'),
                'handle' => '@mia-carter',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'profile.mia',
                'represented' => __('messages.mia_carter'),
                'represented_kind' => __('messages.owner_profile'),
                'manager' => __('messages.published_by_mia_carter'),
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
            'route' => $route ?? 'preview.feed',
            'mine' => $mine,
            'unseen' => ! $mine,
        ];
    }
}
