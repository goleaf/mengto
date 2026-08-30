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
                name: __('messages.mia_carter'),
                handle: '@mia-carter',
                type: 'people',
                typeLabel: __('messages.pet_owner_and_volunteer'),
                description: __('messages.trail_walks_foster_setup_notes_and_quiet_portland_routes'),
                location: __('messages.richmond_portland'),
                image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.mia_carter_profile_portrait'),
                followers: __('messages.2_4k_followers'),
                routeName: 'profile.mia',
                tags: ['foster', 'walks', 'local'],
            ),
            'pet-scout' => $this->record(
                key: 'pet-scout',
                name: __('messages.scout'),
                handle: '@mia-carter/scout',
                type: 'pets',
                typeLabel: __('messages.border_collie_mix'),
                description: __('messages.shaded_park_loops_calm_greetings_and_positive_training'),
                location: __('messages.richmond_portland'),
                image: 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.scout_a_black_and_white_border_collie_mix'),
                followers: __('messages.1_8k_followers'),
                routeName: 'pets.scout',
                tags: ['dog', 'walks', 'training'],
            ),
            'pet-nori' => $this->record(
                key: 'pet-nori',
                name: __('messages.nori'),
                handle: '@mia-carter/nori',
                type: 'pets',
                typeLabel: __('messages.tabby_cat'),
                description: __('messages.indoor_enrichment_window_watching_and_quiet_routines'),
                location: __('messages.richmond_portland'),
                image: 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.nori_a_tabby_cat_looking_toward_the_camera'),
                followers: __('messages.690_followers'),
                routeName: 'pets.nori',
                tags: ['cat', __('messages.indoor_enrichment'), 'quiet'],
            ),
            'owner-ari-jensen' => $this->record(
                key: 'owner-ari-jensen',
                name: __('messages.ari_jensen'),
                handle: '@ari-jensen',
                type: 'people',
                typeLabel: __('messages.pet_owner'),
                description: __('messages.quiet_city_walks_with_mochi_and_practical_training_notes'),
                location: __('messages.pearl_district_portland'),
                image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.ari_jensen_profile_portrait'),
                followers: __('messages.1_3k_followers'),
                routeName: 'neighbors.ari',
                tags: ['local', 'walks', 'training'],
            ),
            'pet-mochi' => $this->record(
                key: 'pet-mochi',
                name: __('messages.mochi'),
                handle: '@ari-jensen/mochi',
                type: 'pets',
                typeLabel: __('messages.shiba_inu_mix'),
                description: __('messages.careful_greetings_cafe_practice_and_calm_city_routines'),
                location: __('messages.pearl_district_portland'),
                image: 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.mochi_a_shiba_inu_looking_toward_the_camera'),
                followers: __('messages.860_followers'),
                routeName: 'neighbors.ari',
                tags: ['dog', __('messages.shiba_mix'), __('messages.young_adult')],
            ),
            'organization-rose-city' => $this->record(
                key: 'organization-rose-city',
                name: __('messages.rose_city_animal_shelter'),
                handle: '@rose-city-shelter',
                type: 'organizations',
                typeLabel: __('messages.verified_shelter'),
                description: __('messages.adoption_profiles_foster_updates_and_local_volunteer_needs'),
                location: __('messages.north_portland'),
                image: 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.brown_shelter_dog_standing_outside'),
                followers: __('messages.4_8k_followers'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.rose_city_animal_shelter')],
                tags: ['adoption', 'foster', 'verified'],
                verified: true,
            ),
            'specialist-elena-ruiz' => $this->record(
                key: 'specialist-elena-ruiz',
                name: __('messages.dr_elena_ruiz'),
                handle: '@dr-elena-ruiz',
                type: 'specialists',
                typeLabel: __('messages.verified_veterinarian'),
                description: __('messages.general_practice_preventive_care_and_practical_summer_safety'),
                location: __('messages.portland_oregon'),
                image: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.dr_elena_ruiz_profile_portrait'),
                followers: __('messages.3_2k_followers'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.dr_elena_ruiz_without_title_period')],
                tags: ['veterinary', 'health', 'verified'],
                verified: true,
            ),
            'group-apartment-pets' => $this->record(
                key: 'group-apartment-pets',
                name: __('messages.apartment_pets_pdx'),
                handle: '@apartment-pets-pdx',
                type: 'groups',
                typeLabel: __('messages.open_community'),
                description: __('messages.small_space_routines_indoor_enrichment_and_low_pressure_meetups'),
                location: __('messages.portland'),
                image: 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.a_dog_and_cat_relaxing_together'),
                followers: __('messages.2_4k_members'),
                routeName: 'groups.apartment_pets',
                tags: ['community', __('messages.indoor_pets'), 'local'],
            ),
            'topic-positive-training' => $this->record(
                key: 'topic-positive-training',
                name: __('messages.positive_training'),
                handle: '#positive-training',
                type: 'topics',
                typeLabel: __('messages.topic'),
                description: __('messages.reward_based_routines_confidence_building_and_calm_introductions'),
                location: __('messages.all_regions'),
                image: 'https://images.unsplash.com/photo-1554456854-55a089fd4cb2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.a_dog_looking_attentively_during_a_training_session'),
                followers: __('messages.12k_followers'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.positive_training_lowercase')],
                tags: ['training', 'behavior', 'topic'],
            ),
            'owner-lena-brooks' => $this->record(
                key: 'owner-lena-brooks',
                name: __('messages.lena_brooks'),
                handle: '@lena-brooks',
                type: 'people',
                typeLabel: __('messages.cat_owner'),
                description: __('messages.neighborhood_cat_safety_and_updates_about_willow'),
                location: __('messages.richmond_portland'),
                image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.lena_brooks_profile_portrait'),
                followers: __('messages.420_followers'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.lena_brooks')],
                tags: ['cats', 'local', 'safety'],
            ),
            'pet-willow' => $this->record(
                key: 'pet-willow',
                name: __('messages.willow'),
                handle: '@lena-brooks/willow',
                type: 'pets',
                typeLabel: __('messages.tabby_cat'),
                description: __('messages.indoor_routines_window_watching_and_neighborhood_safety'),
                location: __('messages.richmond_portland'),
                image: 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.willow_a_grey_tabby_cat_looking_upward'),
                followers: __('messages.380_followers'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.willow')],
                tags: ['cat', 'tabby', 'local'],
            ),
            'owner-noah-kim' => $this->record(
                key: 'owner-noah-kim',
                name: __('messages.noah_kim'),
                handle: '@noah-and-juniper',
                type: 'people',
                typeLabel: __('messages.private_pet_owner'),
                description: __('messages.weekend_trail_notes_and_confidence_building_walks_with_juniper'),
                location: __('messages.portland'),
                image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.noah_kim_profile_portrait'),
                followers: __('messages.private_profile'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.noah_kim')],
                tags: ['dogs', 'trails', 'private'],
                private: true,
            ),
            'pet-juniper' => $this->record(
                key: 'pet-juniper',
                name: __('messages.juniper'),
                handle: '@noah-and-juniper/juniper',
                type: 'pets',
                typeLabel: __('messages.private_dog_profile'),
                description: __('messages.a_thoughtful_trail_companion_who_prefers_careful_introductions'),
                location: __('messages.portland'),
                image: 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.juniper_a_dog_sitting_outdoors'),
                followers: __('messages.private_profile'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.juniper')],
                tags: ['dog', 'trails', 'private'],
                private: true,
            ),
            'owner-priya-shah' => $this->record(
                key: 'owner-priya-shah',
                name: __('messages.priya_shah'),
                handle: '@priya-shah',
                type: 'people',
                typeLabel: __('messages.foster_volunteer'),
                description: __('messages.senior_foster_care_accessible_walks_and_local_volunteer_shifts'),
                location: __('messages.sellwood_portland'),
                image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.priya_shah_profile_portrait'),
                followers: __('messages.970_followers'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.priya_shah')],
                tags: ['foster', __('messages.senior_pets'), 'local'],
            ),
            'owner-zoe-patel' => $this->record(
                key: 'owner-zoe-patel',
                name: __('messages.zoe_patel'),
                handle: '@zoe-patel',
                type: 'people',
                typeLabel: __('messages.dog_owner'),
                description: __('messages.young_dog_training_routines_and_relaxed_neighborhood_walks_with_luna'),
                location: __('messages.alberta_portland'),
                image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.zoe_patel_profile_portrait'),
                followers: __('messages.540_followers'),
                routeName: 'neighbors.index',
                routeParameters: ['q' => __('messages.zoe_patel')],
                tags: ['dogs', 'training', 'local'],
            ),
            'pet-luna-labrador' => $this->record(
                key: 'pet-luna-labrador',
                name: __('messages.luna'),
                handle: '@zoe-patel/luna',
                type: 'pets',
                typeLabel: __('messages.young_labrador'),
                description: __('messages.friendly_training_walks_and_gentle_play_with_similar_sized_dogs'),
                location: __('messages.alberta_portland'),
                image: 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.luna_a_young_golden_labrador_sitting_outside'),
                followers: __('messages.610_followers'),
                routeName: 'pets.index',
                routeParameters: ['q' => __('messages.luna_labrador')],
                tags: ['dog', __('messages.labrador'), 'young'],
            ),
            'specialist-cam-lee' => $this->record(
                key: 'specialist-cam-lee',
                name: __('messages.cam_lee'),
                handle: '@cam-positive-dogs',
                type: 'specialists',
                typeLabel: __('messages.verified_dog_trainer'),
                description: __('messages.low_pressure_introductions_and_reward_based_city_training'),
                location: __('messages.southeast_portland'),
                image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
                imageAlt: __('messages.cam_lee_profile_portrait'),
                followers: __('messages.1_9k_followers'),
                routeName: 'discover.index',
                routeParameters: ['q' => __('messages.cam_lee_dog_trainer')],
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
                'group' => __('messages.pet_matches'),
                'reason' => __('messages.young_dog_in_your_city_with_a_similar_activity_level'),
                'signals' => [__('messages.portland'), __('messages.young_dog'), __('messages.group_walks')],
            ],
            [
                'target' => 'pet-juniper',
                'group' => __('messages.pet_matches'),
                'reason' => __('messages.scout_and_juniper_both_prefer_careful_introductions'),
                'signals' => [__('messages.calm_greetings'), __('messages.trail_walks')],
            ],
            [
                'target' => 'owner-priya-shah',
                'group' => __('messages.nearby_people'),
                'reason' => __('messages.you_share_foster_care_interests_and_two_local_connections'),
                'signals' => [__('messages.2_mutuals'), __('messages.foster_care'), __('messages.portland')],
            ],
            [
                'target' => 'specialist-elena-ruiz',
                'group' => __('messages.trusted_help'),
                'reason' => __('messages.verified_veterinarian_near_portland_who_publishes_about_preventive_care'),
                'signals' => ['verified', 'nearby', 'health'],
            ],
            [
                'target' => 'specialist-cam-lee',
                'group' => __('messages.trusted_help'),
                'reason' => __('messages.you_save_positive_training_posts_and_follow_local_dog_routines'),
                'signals' => ['verified', 'training', 'dogs'],
            ],
            [
                'target' => 'organization-rose-city',
                'group' => __('messages.local_organizations'),
                'reason' => __('messages.verified_shelter_near_you_with_active_foster_and_adoption_updates'),
                'signals' => ['verified', 'adoption', 'local'],
            ],
            [
                'target' => 'group-apartment-pets',
                'group' => __('messages.communities'),
                'reason' => __('messages.your_cat_enrichment_interests_overlap_with_this_local_group'),
                'signals' => [__('messages.local_group'), __('messages.indoor_pets')],
            ],
            [
                'target' => 'topic-positive-training',
                'group' => __('messages.topics'),
                'reason' => __('messages.you_recently_saved_several_calm_training_publications'),
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
