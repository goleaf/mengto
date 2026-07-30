<?php

namespace App\Services;

final class GroupCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return array_values($this->records());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        return $this->records()[$key] ?? null;
    }

    /**
     * @return array{target: string, label: string, route: string, route_parameters: array<string, string>}|null
     */
    public function reportContext(string $target): ?array
    {
        $group = $this->find($target);

        if ($group === null) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $group['name'],
            'route' => 'groups.show',
            'route_parameters' => ['group' => $target],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            'apartment-pets' => [
                'key' => 'apartment-pets',
                'name' => __('messages.apartment_pets_pdx_6488f4db06'),
                'category' => __('messages.home_life_352f399f6c'),
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('messages.portland_oregon_af1587f101'),
                'local' => true,
                'language' => __('messages.english_ba118bf7fc'),
                'member_count' => 2418,
                'pet_count' => 1640,
                'posts_week' => 86,
                'activity_score' => 96,
                'started' => '2021',
                'topic' => __('messages.small_space_routines_31ea3a8e79'),
                'description' => __('messages.practical_enrichment_calm_building_routines_and_neighbor_244ba95057'),
                'long_description' => __('messages.a_practical_local_circle_for_people_sharing_apartments_s_e088727cfb'),
                'organizer' => __('messages.ari_jensen_6c670df410'),
                'organizer_role' => __('messages.owner_community_organizer_00a2421e42'),
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_in_a_compact_home_6b0285743d'),
                'tags' => ['apartments', __('messages.indoor_enrichment_e68e763445'), __('messages.portland_f514070e53')],
                'recommendation_reason' => __('messages.popular_with_pet_owners_in_your_city_827b016cd7'),
                'requirements' => [__('messages.agree_to_the_community_rules_c92d358e5d'), __('messages.keep_exact_home_addresses_private_a0c5f244b0')],
                'next_event' => __('messages.quiet_home_enrichment_clinic_saturday_2b2335d9d0'),
            ],
            'trail-tails' => [
                'key' => 'trail-tails',
                'name' => __('messages.trail_tails_portland_fdcccefe4b'),
                'category' => __('messages.outdoors_8bf8ef16e0'),
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('messages.portland_metro_b6fc09a681'),
                'local' => true,
                'language' => __('messages.english_ba118bf7fc'),
                'member_count' => 8120,
                'pet_count' => 6034,
                'posts_week' => 214,
                'activity_score' => 99,
                'started' => '2019',
                'topic' => __('messages.hikes_route_reports_and_trail_safety_1cb17847bc'),
                'description' => __('messages.plan_trail_days_share_seasonal_conditions_and_compare_lo_c3c63d00d6'),
                'long_description' => __('messages.trail_tails_connects_outdoor_minded_owners_without_turni_98cb94014c'),
                'organizer' => __('messages.noah_patel_147a9793ed'),
                'organizer_role' => __('messages.administrator_trail_host_117ec2aafb'),
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.dogs_running_together_beside_a_wooded_trail_8003faf266'),
                'tags' => [__('messages.trail_walks_e65914f579'), __('messages.route_reports_9e2098ab10'), 'outdoors'],
                'recommendation_reason' => __('messages.scout_follows_outdoor_walking_topics_40adda1a87'),
                'requirements' => [__('messages.share_public_meeting_points_only_28416901b4'), __('messages.follow_posted_leash_rules_1649049f65')],
                'next_event' => __('messages.forest_park_shaded_loop_sunday_69cd5490d7'),
            ],
            'cat-people' => [
                'key' => 'cat-people',
                'name' => __('messages.cat_people_of_portland_138905abe2'),
                'category' => __('messages.cats_ec05d70c6f'),
                'group_type' => 'species',
                'privacy' => 'closed',
                'official' => false,
                'verified_label' => null,
                'location' => __('messages.portland_oregon_af1587f101'),
                'local' => true,
                'language' => __('messages.english_ba118bf7fc'),
                'member_count' => 1934,
                'pet_count' => 2280,
                'posts_week' => 72,
                'activity_score' => 91,
                'started' => '2022',
                'topic' => __('messages.indoor_cats_and_neighborhood_care_9b46494de2'),
                'description' => __('messages.compare_enrichment_share_cat_friendly_local_services_and_ebc65fe578'),
                'long_description' => __('messages.a_moderated_space_for_cat_guardians_to_exchange_indoor_e_fa597fcd8e'),
                'organizer' => __('messages.lena_brooks_ca42e74116'),
                'organizer_role' => __('messages.owner_cat_enrichment_moderator_c44ba68995'),
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.two_fluffy_cats_sitting_together_indoors_7664c2677d'),
                'tags' => [__('messages.cat_care_2b2df9413f'), 'enrichment', __('messages.indoor_life_f927e2f4a1')],
                'recommendation_reason' => __('messages.recommended_for_nori_s_profile_4d276b7591'),
                'requirements' => [__('messages.answer_one_joining_question_6da9437bd7'), __('messages.respect_closed_group_privacy_a0045f293e')],
                'next_event' => __('messages.carrier_confidence_q_a_wednesday_e2f2a152e2'),
            ],
            'foster-network' => [
                'key' => 'foster-network',
                'name' => __('messages.foster_network_pdx_790a8f59dc'),
                'category' => __('messages.adoption_9b33128339'),
                'group_type' => 'adoption',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => __('messages.verified_shelter_network_fc6ed0c7f4'),
                'location' => __('messages.portland_metro_b6fc09a681'),
                'local' => true,
                'language' => __('messages.english_spanish_b50c1cd84d'),
                'member_count' => 1420,
                'pet_count' => 816,
                'posts_week' => 48,
                'activity_score' => 89,
                'started' => '2020',
                'topic' => __('messages.foster_support_and_responsible_adoption_79e7f6cdff'),
                'description' => __('messages.coordinate_supplies_temporary_homes_transport_and_though_aa4fa91a7d'),
                'long_description' => __('messages.a_verified_network_for_approved_foster_volunteers_and_sh_ee7c075dae'),
                'organizer' => __('messages.rose_city_animal_aid_0ae16a6b88'),
                'organizer_role' => __('messages.verified_organization_bb7ed4c3c5'),
                'organizer_initials' => 'RC',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.foster_dog_resting_safely_on_a_blue_couch_31a2c52e5e'),
                'tags' => [__('messages.foster_care_12c77089f0'), 'adoption', 'volunteering'],
                'recommendation_reason' => __('messages.mia_follows_foster_and_adoption_topics_974975cfe2'),
                'requirements' => [__('messages.complete_the_volunteer_profile_d392b244ef'), __('messages.accept_confidential_location_rules_f26a586e1a')],
                'next_event' => __('messages.new_foster_orientation_july_31_70c916b4c7'),
            ],
            'portland-labradors' => [
                'key' => 'portland-labradors',
                'name' => __('messages.portland_labradors_3752d57480'),
                'category' => __('messages.breed_d1ac8a8093'),
                'group_type' => 'breed',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('messages.portland_oregon_af1587f101'),
                'local' => true,
                'language' => __('messages.english_ba118bf7fc'),
                'member_count' => 986,
                'pet_count' => 1108,
                'posts_week' => 39,
                'activity_score' => 84,
                'started' => '2023',
                'topic' => __('messages.labrador_life_without_stereotypes_7e5ed0a511'),
                'description' => __('messages.share_individual_routines_local_walks_training_progress__237656869a'),
                'long_description' => __('messages.a_local_breed_community_that_treats_every_dog_as_an_indi_9206937249'),
                'organizer' => __('messages.jamie_cho_5f313c129b'),
                'organizer_role' => __('messages.owner_walk_organizer_bb9add042b'),
                'organizer_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.yellow_labrador_sitting_outdoors_745cf5348a'),
                'tags' => [__('messages.labradors_c2166423f4'), 'training', __('messages.local_walks_17d54ef12d')],
                'recommendation_reason' => __('messages.a_breed_community_connected_to_your_pet_circle_62019db060'),
                'requirements' => [__('messages.breed_interest_is_enough_to_join_637cc369e4'), __('messages.no_unverified_breeding_sales_fb5fa266d5')],
                'next_event' => __('messages.calm_riverside_walk_august_2_70fdac01dd'),
            ],
            'senior-companions' => [
                'key' => 'senior-companions',
                'name' => __('messages.gentle_senior_companions_8934af635e'),
                'category' => __('messages.care_4262074d6c'),
                'group_type' => 'care',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => __('messages.expert_moderated_community_91d6ce581c'),
                'location' => __('messages.online_pacific_northwest_90316a18f7'),
                'local' => false,
                'language' => __('messages.english_ba118bf7fc'),
                'member_count' => 3260,
                'pet_count' => 2884,
                'posts_week' => 104,
                'activity_score' => 94,
                'started' => '2018',
                'topic' => __('messages.comfort_mobility_and_caregiver_support_ef6cefb02b'),
                'description' => __('messages.a_carefully_moderated_space_for_senior_pet_routines_mobi_dff9182ffd'),
                'long_description' => __('messages.owners_and_verified_professionals_share_supportive_routi_da9252ad3b'),
                'organizer' => __('messages.dr_elena_park_4db101e23c'),
                'organizer_role' => __('messages.verified_veterinarian_moderator_4d51632fe5'),
                'organizer_initials' => 'EP',
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.calm_older_dog_sitting_outdoors_56d4343d16'),
                'tags' => [__('messages.senior_pets_a45178dd21'), 'mobility', __('messages.caregiver_support_3d5e8016cb')],
                'recommendation_reason' => __('messages.matches_your_interest_in_thoughtful_care_6f5641308b'),
                'requirements' => [__('messages.no_diagnosis_or_dosage_instructions_68e926084e'), __('messages.use_content_warnings_for_sensitive_updates_baa518fc8a')],
                'next_event' => __('messages.mobility_at_home_webinar_august_4_c4c46602a3'),
            ],
        ];
    }
}
