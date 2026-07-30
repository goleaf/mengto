<?php

namespace App\Services;

final class ConnectionCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            'owner-mia-carter' => $this->record(
                key: 'owner-mia-carter',
                name: __('messages.mia_carter_0e5b29cc3b'),
                handle: '@mia-carter',
                type: 'people',
                typeLabel: __('messages.pet_owner_and_volunteer_526f09cd38'),
                description: __('messages.trail_walks_foster_setup_notes_and_quiet_portland_routes_44721ac473'),
                location: __('messages.richmond_portland_45cfbdb042'),
                image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.mia_carter_profile_portrait_a94e82290b'),
                followers: __('messages.2_4k_followers_1e36fbdd1b'),
                routeName: 'profile.mia',
                tags: ['foster', 'walks', 'local'],
            ),
            'pet-scout' => $this->record(
                key: 'pet-scout',
                name: __('messages.scout_8a1db462be'),
                handle: '@mia-carter/scout',
                type: 'pets',
                typeLabel: __('messages.border_collie_mix_9b8f12e319'),
                description: __('messages.shaded_park_loops_calm_greetings_and_positive_training_618055220c'),
                location: __('messages.richmond_portland_45cfbdb042'),
                image: 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.scout_a_black_and_white_border_collie_mix_d577b174fa'),
                followers: __('messages.1_8k_followers_f46ffc89e4'),
                routeName: 'pets.scout',
                tags: ['dog', 'walks', 'training'],
            ),
            'pet-nori' => $this->record(
                key: 'pet-nori',
                name: __('messages.nori_a64203ba20'),
                handle: '@mia-carter/nori',
                type: 'pets',
                typeLabel: __('messages.tabby_cat_adb7278965'),
                description: __('messages.indoor_enrichment_window_watching_and_quiet_routines_d276033190'),
                location: __('messages.richmond_portland_45cfbdb042'),
                image: 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.nori_a_tabby_cat_looking_toward_the_camera_3f2b66069e'),
                followers: __('messages.690_followers_2c4683404a'),
                routeName: 'pets.nori',
                tags: ['cat', __('messages.indoor_enrichment_e68e763445'), 'quiet'],
            ),
            'owner-ari-jensen' => $this->record(
                key: 'owner-ari-jensen',
                name: __('messages.ari_jensen_6c670df410'),
                handle: '@ari-jensen',
                type: 'people',
                typeLabel: __('messages.pet_owner_bbca9a2b0c'),
                description: __('messages.quiet_city_walks_with_mochi_and_practical_training_notes_1606e15952'),
                location: __('messages.pearl_district_portland_b6573f597e'),
                image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.ari_jensen_profile_portrait_43e2181f95'),
                followers: __('messages.1_3k_followers_9df2cda9dc'),
                routeName: 'neighbors.ari',
                tags: ['local', 'walks', 'training'],
            ),
            'pet-mochi' => $this->record(
                key: 'pet-mochi',
                name: __('messages.mochi_95114c81f3'),
                handle: '@ari-jensen/mochi',
                type: 'pets',
                typeLabel: __('messages.shiba_inu_mix_a2565c3a7e'),
                description: __('messages.careful_greetings_cafe_practice_and_calm_city_routines_426b7d5d7d'),
                location: __('messages.pearl_district_portland_b6573f597e'),
                image: 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.mochi_a_shiba_inu_looking_toward_the_camera_f32e0115f6'),
                followers: __('messages.860_followers_d2750e7570'),
                routeName: 'neighbors.ari',
                tags: ['dog', __('messages.shiba_mix_384146dcff'), __('messages.young_adult_121e88b501')],
            ),
            'organization-rose-city' => $this->record(
                key: 'organization-rose-city',
                name: __('messages.rose_city_animal_shelter_344c43d61e'),
                handle: '@rose-city-shelter',
                type: 'organizations',
                typeLabel: __('messages.verified_shelter_52247645ed'),
                description: __('messages.adoption_profiles_foster_updates_and_local_volunteer_nee_a573a1a507'),
                location: __('messages.north_portland_b2b99b4c9a'),
                image: 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.brown_shelter_dog_standing_outside_874f4f3546'),
                followers: __('messages.4_8k_followers_fe12be4fb3'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.rose_city_animal_shelter_344c43d61e')],
                tags: ['adoption', 'foster', 'verified'],
                verified: true,
            ),
            'specialist-elena-ruiz' => $this->record(
                key: 'specialist-elena-ruiz',
                name: __('messages.dr_elena_ruiz_553c9ab30a'),
                handle: '@dr-elena-ruiz',
                type: 'specialists',
                typeLabel: __('messages.verified_veterinarian_30ade68387'),
                description: __('messages.general_practice_preventive_care_and_practical_summer_sa_f24f3eaa0c'),
                location: __('messages.portland_oregon_af1587f101'),
                image: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.dr_elena_ruiz_profile_portrait_0935b09a2a'),
                followers: __('messages.3_2k_followers_e302df7e16'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.dr_elena_ruiz_d9696b188f')],
                tags: ['veterinary', 'health', 'verified'],
                verified: true,
            ),
            'group-apartment-pets' => $this->record(
                key: 'group-apartment-pets',
                name: __('messages.apartment_pets_pdx_6488f4db06'),
                handle: '@apartment-pets-pdx',
                type: 'groups',
                typeLabel: __('messages.open_community_1429f683ba'),
                description: __('messages.small_space_routines_indoor_enrichment_and_low_pressure__3f0aa66a50'),
                location: __('messages.portland_f514070e53'),
                image: 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.a_dog_and_cat_relaxing_together_0bbf96802a'),
                followers: __('messages.2_4k_members_ef94699d39'),
                routeName: 'groups.apartment_pets',
                tags: ['community', __('messages.indoor_pets_53a0e5af32'), 'local'],
            ),
            'topic-positive-training' => $this->record(
                key: 'topic-positive-training',
                name: __('messages.positive_training_875ecb6238'),
                handle: '#positive-training',
                type: 'topics',
                typeLabel: __('messages.topic_7e61847d61'),
                description: __('messages.reward_based_routines_confidence_building_and_calm_intro_6f61d5290c'),
                location: __('messages.all_regions_8968425df0'),
                image: 'https://images.unsplash.com/photo-1554456854-55a089fd4cb2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.a_dog_looking_attentively_during_a_training_session_070c28d97c'),
                followers: __('messages.12k_followers_da044dd0c4'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.positive_training_265845eade')],
                tags: ['training', 'behavior', 'topic'],
            ),
            'owner-lena-brooks' => $this->record(
                key: 'owner-lena-brooks',
                name: __('messages.lena_brooks_ca42e74116'),
                handle: '@lena-brooks',
                type: 'people',
                typeLabel: __('messages.cat_owner_dd13c39fa7'),
                description: __('messages.neighborhood_cat_safety_and_updates_about_willow_64bdf95913'),
                location: __('messages.richmond_portland_45cfbdb042'),
                image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.lena_brooks_profile_portrait_accd3e35e3'),
                followers: __('messages.420_followers_f1511f493c'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.lena_brooks_ca42e74116')],
                tags: ['cats', 'local', 'safety'],
            ),
            'pet-willow' => $this->record(
                key: 'pet-willow',
                name: __('messages.willow_0a8a36409c'),
                handle: '@lena-brooks/willow',
                type: 'pets',
                typeLabel: __('messages.tabby_cat_adb7278965'),
                description: __('messages.indoor_routines_window_watching_and_neighborhood_safety_f1037f88ae'),
                location: __('messages.richmond_portland_45cfbdb042'),
                image: 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.willow_a_grey_tabby_cat_looking_upward_15adfc68af'),
                followers: __('messages.380_followers_7a743dd9ed'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.willow_0a8a36409c')],
                tags: ['cat', 'tabby', 'local'],
            ),
            'owner-noah-kim' => $this->record(
                key: 'owner-noah-kim',
                name: __('messages.noah_kim_1ff9787ac4'),
                handle: '@noah-and-juniper',
                type: 'people',
                typeLabel: __('messages.private_pet_owner_6429f48128'),
                description: __('messages.weekend_trail_notes_and_confidence_building_walks_with_j_57682e7b01'),
                location: __('messages.portland_f514070e53'),
                image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.noah_kim_profile_portrait_78b9b45909'),
                followers: __('messages.private_profile_5a024309a3'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.noah_kim_1ff9787ac4')],
                tags: ['dogs', 'trails', 'private'],
                private: true,
            ),
            'pet-juniper' => $this->record(
                key: 'pet-juniper',
                name: __('messages.juniper_fe6a448ec9'),
                handle: '@noah-and-juniper/juniper',
                type: 'pets',
                typeLabel: __('messages.private_dog_profile_f2c0daa2be'),
                description: __('messages.a_thoughtful_trail_companion_who_prefers_careful_introdu_5b0be3258d'),
                location: __('messages.portland_f514070e53'),
                image: 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.juniper_a_dog_sitting_outdoors_703f37968c'),
                followers: __('messages.private_profile_5a024309a3'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.juniper_fe6a448ec9')],
                tags: ['dog', 'trails', 'private'],
                private: true,
            ),
            'owner-priya-shah' => $this->record(
                key: 'owner-priya-shah',
                name: __('messages.priya_shah_8925523814'),
                handle: '@priya-shah',
                type: 'people',
                typeLabel: __('messages.foster_volunteer_9e52662878'),
                description: __('messages.senior_foster_care_accessible_walks_and_local_volunteer__74bf66f081'),
                location: __('messages.sellwood_portland_d5578f4db2'),
                image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.priya_shah_profile_portrait_8801d2bb4f'),
                followers: __('messages.970_followers_080c87a4ee'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.priya_shah_8925523814')],
                tags: ['foster', __('messages.senior_pets_a45178dd21'), 'local'],
            ),
            'owner-zoe-patel' => $this->record(
                key: 'owner-zoe-patel',
                name: __('messages.zoe_patel_330ba10552'),
                handle: '@zoe-patel',
                type: 'people',
                typeLabel: __('messages.dog_owner_40a9514333'),
                description: __('messages.young_dog_training_routines_and_relaxed_neighborhood_wal_31304b3abc'),
                location: __('messages.alberta_portland_5aaf7123cf'),
                image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.zoe_patel_profile_portrait_5ff8e8ec00'),
                followers: __('messages.540_followers_7eeabc553e'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.zoe_patel_330ba10552')],
                tags: ['dogs', 'training', 'local'],
            ),
            'pet-luna-labrador' => $this->record(
                key: 'pet-luna-labrador',
                name: __('messages.luna_9d77a24d0f'),
                handle: '@zoe-patel/luna',
                type: 'pets',
                typeLabel: __('messages.young_labrador_d51d3212c8'),
                description: __('messages.friendly_training_walks_and_gentle_play_with_similar_siz_73e414d871'),
                location: __('messages.alberta_portland_5aaf7123cf'),
                image: 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.luna_a_young_golden_labrador_sitting_outside_9b56dfd2e0'),
                followers: __('messages.610_followers_befd300544'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.luna_labrador_22b0025c9c')],
                tags: ['dog', __('messages.labrador_d7801fd342'), 'young'],
            ),
            'specialist-cam-lee' => $this->record(
                key: 'specialist-cam-lee',
                name: __('messages.cam_lee_18d258e644'),
                handle: '@cam-positive-dogs',
                type: 'specialists',
                typeLabel: __('messages.verified_dog_trainer_df9a00ae99'),
                description: __('messages.low_pressure_introductions_and_reward_based_city_trainin_08d92728a8'),
                location: __('messages.southeast_portland_b79edafe3a'),
                image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.cam_lee_profile_portrait_67c959ee3c'),
                followers: __('messages.1_9k_followers_0f924b1f4c'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.cam_lee_dog_trainer_940d9fbda7')],
                tags: ['training', 'dogs', 'verified'],
                verified: true,
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $target): ?array
    {
        return $this->records()[$target] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function followerTargets(): array
    {
        return ['owner-ari-jensen', 'owner-lena-brooks', 'owner-priya-shah'];
    }

    /**
     * @return array<int, string>
     */
    public function incomingRequestTargets(): array
    {
        return ['owner-noah-kim', 'owner-zoe-patel'];
    }

    /**
     * @return array<int, array{target: string, group: string, reason: string, signals: array<int, string>}>
     */
    public function recommendations(): array
    {
        return [
            [
                'target' => 'pet-luna-labrador',
                'group' => __('messages.pet_matches_76ecb76d21'),
                'reason' => __('messages.young_dog_in_your_city_with_a_similar_activity_level_b823ad27dc'),
                'signals' => [__('messages.portland_f514070e53'), __('messages.young_dog_914a14c0e8'), __('messages.group_walks_67d596fca7')],
            ],
            [
                'target' => 'pet-juniper',
                'group' => __('messages.pet_matches_76ecb76d21'),
                'reason' => __('messages.scout_and_juniper_both_prefer_careful_introductions_bdf6d81045'),
                'signals' => [__('messages.calm_greetings_372cc25a1e'), __('messages.trail_walks_e65914f579')],
            ],
            [
                'target' => 'owner-priya-shah',
                'group' => __('messages.nearby_people_828d4735a3'),
                'reason' => __('messages.you_share_foster_care_interests_and_two_local_connection_b2cc689939'),
                'signals' => [__('messages.2_mutuals_6a5b0600c8'), __('messages.foster_care_12c77089f0'), __('messages.portland_f514070e53')],
            ],
            [
                'target' => 'specialist-elena-ruiz',
                'group' => __('messages.trusted_help_5cdadcf27b'),
                'reason' => __('messages.verified_veterinarian_near_portland_who_publishes_about__d260020b68'),
                'signals' => ['verified', 'nearby', 'health'],
            ],
            [
                'target' => 'specialist-cam-lee',
                'group' => __('messages.trusted_help_5cdadcf27b'),
                'reason' => __('messages.you_save_positive_training_posts_and_follow_local_dog_ro_d48e750099'),
                'signals' => ['verified', 'training', 'dogs'],
            ],
            [
                'target' => 'organization-rose-city',
                'group' => __('messages.local_organizations_003b1cc925'),
                'reason' => __('messages.verified_shelter_near_you_with_active_foster_and_adoptio_7b66d53a59'),
                'signals' => ['verified', 'adoption', 'local'],
            ],
            [
                'target' => 'group-apartment-pets',
                'group' => __('messages.communities_c864f329f5'),
                'reason' => __('messages.your_cat_enrichment_interests_overlap_with_this_local_gr_b15c403410'),
                'signals' => [__('messages.local_group_52b8d19d41'), __('messages.indoor_pets_53a0e5af32')],
            ],
            [
                'target' => 'topic-positive-training',
                'group' => __('messages.topics_e22820fcf5'),
                'reason' => __('messages.you_recently_saved_several_calm_training_publications_40d9ddcb43'),
                'signals' => ['training', 'behavior'],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $tags
     * @param  array<string, string>  $routeParameters
     * @return array<string, mixed>
     */
    private function record(
        string $key,
        string $name,
        string $handle,
        string $type,
        string $typeLabel,
        string $description,
        string $location,
        string $image,
        string $imageAlt,
        string $followers,
        string $routeName,
        array $tags,
        array $routeParameters = [],
        bool $verified = false,
        bool $private = false,
    ): array {
        return compact(
            'key',
            'name',
            'handle',
            'type',
            'typeLabel',
            'description',
            'location',
            'image',
            'imageAlt',
            'followers',
            'routeName',
            'routeParameters',
            'tags',
            'verified',
            'private',
        );
    }
}
