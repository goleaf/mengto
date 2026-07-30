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
                'type_label' => __('messages.photo_update_e9b85666f8'),
                'author' => __('messages.ari_jensen_6c670df410'),
                'handle' => '@ari-jensen',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'neighbors.ari',
                'author_parameters' => [],
                'represented' => __('messages.mochi_95114c81f3'),
                'represented_kind' => __('messages.pet_profile_fc2c49bb42'),
                'manager' => __('messages.managed_by_ari_jensen_2f3fff87d9'),
                'pet_slug' => 'mochi',
                'species' => 'dogs',
                'published_at' => '2026-07-29T09:42:00-07:00',
                'time' => __('messages.18_min_ago_54f545e4a9'),
                'title' => null,
                'body' => __('messages.mochi_made_it_through_the_whole_cafe_patio_without_inspe_c04855367d'),
                'topic' => __('messages.training_36a798e3f3'),
                'location' => __('messages.pearl_district_af25f9947a'),
                'audience' => __('messages.followers_and_friends_7336d4054e'),
                'comment_policy' => __('messages.followers_a145ab342a'),
                'tags' => ['training', __('messages.city_walks_a347b642ed')],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => __('messages.you_follow_mochi_and_save_calm_training_posts_2874a7f284'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1769635325695-dead509dc5b3',
                        __('messages.mochi_a_shiba_inu_standing_inside_a_quiet_cafe_c6c0acb785'),
                        __('messages.a_calm_moment_inside_the_pet_friendly_cafe_1791d94618'),
                    ),
                ],
                'reaction_counts' => ['like' => 84, 'love' => 31, 'support' => 9, 'useful' => 4],
                'replies' => 24,
                'reposts' => 6,
            ],
            [
                'key' => 'scout-shaded-loop',
                'format' => 'photo',
                'type_label' => __('messages.photo_carousel_b31724a092'),
                'author' => __('messages.scout_8a1db462be'),
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.scout',
                'author_parameters' => [],
                'represented' => __('messages.scout_8a1db462be'),
                'represented_kind' => __('messages.pet_profile_fc2c49bb42'),
                'manager' => __('messages.published_as_scout_managed_by_mia_carter_46cc870cd0'),
                'pet_slug' => 'scout',
                'species' => 'dogs',
                'published_at' => '2026-07-29T08:25:00-07:00',
                'time' => __('messages.1_hr_ago_f98c800e71'),
                'title' => __('messages.a_shaded_loop_worth_repeating_452d1b9bf8'),
                'body' => __('messages.we_tried_the_east_loop_before_breakfast_the_route_stayed_ced5a1e301'),
                'topic' => __('messages.walks_22e4ca854b'),
                'location' => __('messages.laurelhurst_park_b88ab4320c'),
                'audience' => __('messages.everyone_da2e5dc515'),
                'comment_policy' => __('messages.everyone_da2e5dc515'),
                'tags' => [__('messages.scout_8a1db462be'), __('messages.walk_route_6abe2b24b2'), __('messages.portland_f514070e53')],
                'feeds' => ['home', 'following', 'friends', 'pets', 'local', 'photos'],
                'why' => __('messages.scout_is_one_of_your_managed_pet_profiles_c07f7ca2b5'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        __('messages.scout_resting_on_grass_after_a_shaded_park_walk_aa4d0797de'),
                        __('messages.a_calm_pause_near_the_east_loop_14e09ecc94'),
                    ),
                    $this->image(
                        'photo-1624361239583-7ba5ffb376f5',
                        __('messages.scout_lying_in_grass_behind_a_tennis_ball_e7cfee5e55'),
                        __('messages.one_last_throw_before_heading_home_496bd93900'),
                    ),
                    $this->image(
                        'photo-1621169225409-5de158d10015',
                        __('messages.scout_resting_on_a_wooden_porch_0fce6ea345'),
                        __('messages.back_home_and_fully_settled_b24ef152ea'),
                    ),
                ],
                'reaction_counts' => ['like' => 91, 'love' => 42, 'support' => 8, 'useful' => 18],
                'replies' => 16,
                'reposts' => 11,
            ],
            [
                'key' => 'dr-elena-heat-check',
                'format' => 'expert',
                'type_label' => __('messages.expert_note_5280705b5d'),
                'author' => __('messages.dr_elena_ruiz_553c9ab30a'),
                'handle' => '@dr-elena-ruiz',
                'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.veterinary_profile_730658de61'),
                'represented_kind' => __('messages.verified_specialist_f32a83c30d'),
                'manager' => __('messages.licensed_in_oregon_general_practice_6d9ba43b64'),
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-29T07:40:00-07:00',
                'time' => __('messages.2_hrs_ago_d7ec83bc13'),
                'title' => __('messages.three_signs_a_warm_walk_should_end_early_0e19e362ce'),
                'body' => __('messages.slowing_down_seeking_shade_and_unusually_heavy_panting_a_e8594ee4ca'),
                'topic' => __('messages.health_55898449eb'),
                'location' => __('messages.portland_or_591666d10e'),
                'audience' => __('messages.everyone_da2e5dc515'),
                'comment_policy' => __('messages.everyone_da2e5dc515'),
                'tags' => [__('messages.summer_safety_957bdf8a95'), __('messages.veterinary_note_8dee22893b')],
                'feeds' => ['home', 'experts'],
                'why' => __('messages.you_follow_local_care_and_summer_safety_topics_e17aad8c01'),
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
                'type_label' => __('messages.adoption_profile_6c7cd4fe9a'),
                'author' => __('messages.rose_city_animal_shelter_344c43d61e'),
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.mabel_dc64d38946'),
                'represented_kind' => __('messages.shelter_pet_56bfdc75df'),
                'manager' => __('messages.verified_shelter_adoption_team_03f8d8fa15'),
                'pet_slug' => 'mabel',
                'species' => 'dogs',
                'published_at' => '2026-07-29T06:30:00-07:00',
                'time' => __('messages.3_hrs_ago_d641605714'),
                'title' => __('messages.mabel_is_ready_for_a_quiet_home_40b25195f3'),
                'body' => __('messages.mabel_is_a_five_year_old_mixed_breed_dog_who_settles_wel_3e73b5b301'),
                'topic' => __('messages.adoption_9b33128339'),
                'location' => __('messages.north_portland_b2b99b4c9a'),
                'audience' => __('messages.everyone_da2e5dc515'),
                'comment_policy' => __('messages.followers_a145ab342a'),
                'tags' => ['adoption', __('messages.quiet_home_d69e1e779c')],
                'feeds' => ['home', 'shelters'],
                'why' => __('messages.you_asked_to_see_verified_shelters_near_portland_40a8284aab'),
                'verified' => true,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1558788353-f76d92427f16',
                        __('messages.mabel_a_brown_shelter_dog_standing_outside_d2156596e2'),
                        __('messages.mabel_during_a_quiet_afternoon_yard_break_76602691eb'),
                    ),
                ],
                'reaction_counts' => ['like' => 44, 'love' => 75, 'support' => 63, 'useful' => 12],
                'replies' => 28,
                'reposts' => 54,
            ],
            [
                'key' => 'willow-lost-richmond',
                'format' => 'lost',
                'type_label' => __('messages.lost_pet_alert_43b8776771'),
                'author' => __('messages.lena_brooks_ca42e74116'),
                'handle' => '@lena-brooks',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.willow_0a8a36409c'),
                'represented_kind' => __('messages.lost_pet_8e6d3f74c1'),
                'manager' => __('messages.contact_the_owner_through_pawcircle_453401f6d0'),
                'pet_slug' => 'willow',
                'species' => 'cats',
                'published_at' => '2026-07-29T09:20:00-07:00',
                'time' => __('messages.40_min_ago_58451c33d3'),
                'title' => __('messages.willow_was_last_seen_near_richmond_school_1dbe801b39'),
                'body' => __('messages.willow_is_a_small_grey_tabby_wearing_a_green_breakaway_c_b8dec0e447'),
                'topic' => __('messages.lost_and_found_a64c586261'),
                'location' => __('messages.richmond_neighborhood_approximate_area_0b385c5fbb'),
                'audience' => __('messages.local_emergency_reach_8a3c561f6c'),
                'comment_policy' => __('messages.registered_members_1757e26849'),
                'tags' => [__('messages.lost_cat_2686c74e4c'), __('messages.richmond_128b2a6b11')],
                'feeds' => ['home', 'local', 'alerts'],
                'why' => __('messages.this_active_alert_is_within_your_approximate_neighborhoo_a721f13b1e'),
                'verified' => false,
                'urgent' => true,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [
                    $this->image(
                        'photo-1573865526739-10659fec78a5',
                        __('messages.willow_a_grey_tabby_cat_looking_upward_15adfc68af'),
                        __('messages.recent_photo_provided_by_willow_s_owner_099d2b6755'),
                    ),
                ],
                'reaction_counts' => ['support' => 198, 'useful' => 86],
                'replies' => 31,
                'reposts' => 117,
            ],
            [
                'key' => 'sunny-first-play-video',
                'format' => 'video',
                'type_label' => __('messages.shelter_pet_video_7740125793'),
                'author' => __('messages.rose_city_animal_shelter_344c43d61e'),
                'handle' => '@rose-city-shelter',
                'avatar' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => null,
                'author_parameters' => [],
                'represented' => __('messages.sunny_adf259f684'),
                'represented_kind' => __('messages.pet_profile_fc2c49bb42'),
                'manager' => __('messages.published_by_verified_shelter_staff_e82376fce6'),
                'pet_slug' => 'sunny',
                'species' => 'dogs',
                'published_at' => '2026-07-28T17:10:00-07:00',
                'time' => __('messages.yesterday_566181254b'),
                'title' => __('messages.sunny_s_first_play_session_54d606751e'),
                'body' => __('messages.a_young_foster_puppy_gets_a_calm_play_break_while_waitin_b0ba23d204'),
                'topic' => __('messages.enrichment_780395fa62'),
                'location' => __('messages.portland_oregon_af1587f101'),
                'audience' => __('messages.public_591935b15b'),
                'comment_policy' => __('messages.registered_members_1757e26849'),
                'tags' => [__('messages.foster_puppy_046e3a7079'), __('messages.play_enrichment_8cb04caa7f')],
                'feeds' => ['home', 'pets', 'shelters', 'video'],
                'why' => __('messages.you_follow_verified_animal_shelters_near_portland_33c1ab23b5'),
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
                        'alt' => __('messages.a_young_puppy_during_a_supervised_play_session_1351eecb16'),
                        'caption' => __('messages.short_play_session_with_sound_controls_and_native_playba_a4136f9f52'),
                        'attribution' => __('messages.puppy_playing_video_by_subhashish_panigrahi_cc_by_sa_3_0_a9a0bd5c87'),
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
                'type_label' => __('messages.community_poll_99cc51c224'),
                'author' => __('messages.apartment_pets_pdx_6488f4db06'),
                'handle' => '@apartment-pets-pdx',
                'avatar' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'groups.apartment_pets',
                'author_parameters' => [],
                'represented' => __('messages.apartment_pets_pdx_6488f4db06'),
                'represented_kind' => __('messages.open_group_83ffa7c96e'),
                'manager' => __('messages.posted_by_group_moderator_ari_jensen_6876589e40'),
                'pet_slug' => null,
                'species' => 'all',
                'published_at' => '2026-07-28T13:00:00-07:00',
                'time' => __('messages.yesterday_566181254b'),
                'title' => __('messages.which_rainy_day_meetup_should_we_plan_448f0fc870'),
                'body' => __('messages.choose_the_easiest_low_pressure_option_results_help_with_04f433eb18'),
                'topic' => __('messages.community_bb501d7877'),
                'location' => __('messages.portland_f514070e53'),
                'audience' => __('messages.group_members_dd0fd917e7'),
                'comment_policy' => __('messages.group_members_dd0fd917e7'),
                'tags' => [__('messages.indoor_meetup_dd59308ceb'), __('messages.community_poll_9f80d10437')],
                'feeds' => ['home', 'groups'],
                'why' => __('messages.you_joined_apartment_pets_pdx_9a4494cdd8'),
                'verified' => false,
                'urgent' => false,
                'sensitive' => false,
                'created_by_current' => false,
                'media' => [],
                'poll_options' => [
                    ['label' => __('messages.quiet_cafe_patio_8d2fa922fa'), 'votes' => 46],
                    ['label' => __('messages.covered_park_pavilion_82ccd65365'), 'votes' => 71],
                    ['label' => __('messages.indoor_training_room_4b48f62ae5'), 'votes' => 39],
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
            $this->story(__('messages.scout_8a1db462be'), __('messages.park_loop_1b867b369e'), 'photo-1654256578072-b932c33cb92e', 'pets.scout', true),
            $this->story(__('messages.nori_a64203ba20'), __('messages.window_watch_931e9ebf71'), 'photo-1518791841217-8f162f1e1131', 'pets.nori', true),
            $this->story(__('messages.mochi_95114c81f3'), __('messages.cafe_win_f1900a886a'), 'photo-1769635325695-dead509dc5b3', 'neighbors.ari'),
            $this->story(__('messages.rose_city_66cabafa65'), __('messages.adoption_day_f7aecb1fd3'), 'photo-1558788353-f76d92427f16'),
            $this->story(__('messages.dr_elena_f97768615f'), __('messages.heat_safety_a8f7d0ab85'), 'photo-1559839734-2b71ea197ec2'),
            $this->story(__('messages.juniper_fe6a448ec9'), __('messages.trail_note_254ccbe2da'), 'photo-1605568427561-40dd23c2acea'),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function modes(): array
    {
        return [
            'home' => ['label' => __('messages.for_you_143134f939'), 'icon' => 'sparkles'],
            'following' => ['label' => __('messages.following_344b4271ca'), 'icon' => 'user-check'],
            'friends' => ['label' => __('messages.friends_bd104d1b98'), 'icon' => 'users-round'],
            'pets' => ['label' => __('messages.pets_7dc1cd7eaf'), 'icon' => 'paw-print'],
            'local' => ['label' => __('messages.local_8c31e6e722'), 'icon' => 'map-pin'],
            'groups' => ['label' => __('messages.groups_39bbb719fa'), 'icon' => 'messages-square'],
            'experts' => ['label' => __('messages.experts_5e12750596'), 'icon' => 'badge-check'],
            'shelters' => ['label' => __('messages.shelters_2e5e231d5f'), 'icon' => 'house-heart'],
            'alerts' => ['label' => __('messages.lost_found_217c655848'), 'icon' => 'siren'],
            'video' => ['label' => __('messages.video_d534be829e'), 'icon' => 'play'],
            'photos' => ['label' => __('messages.photos_5e3147ab51'), 'icon' => 'images'],
            'saved' => ['label' => __('messages.saved_b5c120b316'), 'icon' => 'bookmark'],
            'drafts' => ['label' => __('messages.drafts_f592e6a4db'), 'icon' => 'file-pen-line'],
            'archive' => ['label' => __('messages.archive_66f4804ee2'), 'icon' => 'archive'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function mediaPresets(): array
    {
        return [
            'none' => [
                'label' => __('messages.text_only_8bbfc646f3'),
                'format' => 'text',
                'media' => [],
            ],
            'scout-field' => [
                'label' => __('messages.scout_in_the_field_1841a9cdc0'),
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1654256578072-b932c33cb92e',
                        __('messages.scout_resting_in_a_green_field_55ce7940bd'),
                        __('messages.a_calm_break_outdoors_ed0f835166'),
                    ),
                ],
            ],
            'nori-window' => [
                'label' => __('messages.nori_by_the_window_4457d8e97c'),
                'format' => 'photo',
                'media' => [
                    $this->image(
                        'photo-1495360010541-f48722b34f7d',
                        __('messages.nori_resting_beside_a_bright_window_70f9f4a3bb'),
                        __('messages.afternoon_window_watch_05ace8e596'),
                    ),
                ],
            ],
            'park-carousel' => [
                'label' => __('messages.three_photo_park_carousel_3bb8002de3'),
                'format' => 'photo',
                'media' => [
                    $this->image('photo-1624361239583-7ba5ffb376f5', __('messages.scout_waiting_behind_a_tennis_ball_7a678c608a'), __('messages.ready_for_another_throw_494fb59a0b')),
                    $this->image('photo-1625679895477-526b21a77f0c', __('messages.scout_catching_a_yellow_frisbee_e3dbd30329'), __('messages.a_clean_catch_on_the_grass_006d24fa60')),
                    $this->image('photo-1621169225409-5de158d10015', __('messages.scout_resting_on_a_wooden_porch_0fce6ea345'), __('messages.quiet_time_after_the_park_6c4e76a175')),
                ],
            ],
            'play-video' => [
                'label' => __('messages.short_pet_video_a78268fd41'),
                'format' => 'video',
                'media' => [
                    [
                        'type' => 'video',
                        'source' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/Puppy_playing.webm',
                        'mime' => 'video/webm',
                        'poster' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=675&q=85',
                        'poster_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=576&h=324&q=80',
                        'poster_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=506&q=82',
                        'alt' => __('messages.a_young_dog_playing_indoors_bbcd322fc4'),
                        'caption' => __('messages.short_pet_play_video_a49bcd4d2c'),
                        'attribution' => __('messages.puppy_playing_video_by_subhashish_panigrahi_cc_by_sa_3_0_a9a0bd5c87'),
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
            'walks' => __('messages.walks_22e4ca854b'),
            'care' => __('messages.care_4262074d6c'),
            'health' => __('messages.health_55898449eb'),
            'training' => __('messages.training_36a798e3f3'),
            'enrichment' => __('messages.enrichment_780395fa62'),
            'adoption' => __('messages.adoption_9b33128339'),
            'lost-found' => __('messages.lost_and_found_a64c586261'),
            'community' => __('messages.community_bb501d7877'),
            'photography' => __('messages.photography_7be8b75c22'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function audiences(): array
    {
        return [
            'public' => __('messages.everyone_da2e5dc515'),
            'members' => __('messages.registered_members_1757e26849'),
            'followers' => __('messages.followers_a145ab342a'),
            'friends' => __('messages.friends_bd104d1b98'),
            'close-friends' => __('messages.close_friends_64e9cddb8e'),
            'owners' => __('messages.pet_owners_and_managers_8b23130305'),
            'private' => __('messages.only_me_bdc0857b99'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function commentPolicies(): array
    {
        return [
            'all' => __('messages.everyone_da2e5dc515'),
            'followers' => __('messages.followers_a145ab342a'),
            'friends' => __('messages.friends_bd104d1b98'),
            'mentioned' => __('messages.mentioned_profiles_af3c2a1718'),
            'none' => __('messages.comments_off_1563c516de'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identities(): array
    {
        return [
            'mia' => __('messages.mia_carter_owner_profile_e194cae164'),
            'scout' => __('messages.scout_managed_pet_profile_0ada0ff682'),
            'nori' => __('messages.nori_managed_pet_profile_aada9fa744'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function safePlaces(): array
    {
        return [
            'none' => __('messages.do_not_show_a_place_136734f727'),
            'portland' => __('messages.portland_or_591666d10e'),
            'richmond' => __('messages.richmond_neighborhood_119d350bae'),
            'laurelhurst' => __('messages.laurelhurst_park_b88ab4320c'),
            'fields-park' => __('messages.fields_park_82bb556189'),
            'pearl-district' => __('messages.pearl_district_af25f9947a'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reactionOptions(bool $supportiveOnly = false): array
    {
        if ($supportiveOnly) {
            return [
                'support' => __('messages.support_be91940b79'),
                'useful' => __('messages.useful_27a728b507'),
            ];
        }

        return [
            'like' => __('messages.like_64f915cb8b'),
            'love' => __('messages.love_9f4024faec'),
            'funny' => __('messages.funny_9c3b22ab33'),
            'support' => __('messages.support_be91940b79'),
            'useful' => __('messages.useful_27a728b507'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function reportReasons(): array
    {
        return [
            'spam' => __('messages.spam_or_repetitive_promotion_f3f4e18b16'),
            'fraud' => __('messages.fraud_or_scam_556d825bf8'),
            'animal-safety' => __('messages.animal_safety_concern_9907245780'),
            'dangerous-advice' => __('messages.dangerous_medical_advice_4e602e83b0'),
            'stolen-media' => __('messages.stolen_photos_or_video_ac4a8e6108'),
            'personal-data' => __('messages.personal_information_exposed_b051eb39b7'),
            'false-alert' => __('messages.false_lost_pet_alert_527cc3e7cf'),
            'harassment' => __('messages.harassment_or_hate_e4a897151e'),
            'other' => __('messages.other_concern_910bb13965'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identity(string $key): array
    {
        return match ($key) {
            'scout' => [
                'author' => __('messages.scout_8a1db462be'),
                'handle' => '@mia-carter/scout',
                'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.scout',
                'represented' => __('messages.scout_8a1db462be'),
                'represented_kind' => __('messages.pet_profile_fc2c49bb42'),
                'manager' => __('messages.published_as_scout_managed_by_mia_carter_46cc870cd0'),
                'pet_slug' => 'scout',
                'species' => 'dogs',
            ],
            'nori' => [
                'author' => __('messages.nori_a64203ba20'),
                'handle' => '@mia-carter/nori',
                'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'pets.nori',
                'represented' => __('messages.nori_a64203ba20'),
                'represented_kind' => __('messages.pet_profile_fc2c49bb42'),
                'manager' => __('messages.published_as_nori_managed_by_mia_carter_f55ef60f59'),
                'pet_slug' => 'nori',
                'species' => 'cats',
            ],
            default => [
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'handle' => '@mia-carter',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=160&h=160&q=80',
                'author_route' => 'profile.mia',
                'represented' => __('messages.mia_carter_0e5b29cc3b'),
                'represented_kind' => __('messages.owner_profile_980e9b1087'),
                'manager' => __('messages.published_by_mia_carter_63eabfb402'),
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
            'route' => $route ?? 'home',
            'mine' => $mine,
            'unseen' => ! $mine,
        ];
    }
}
