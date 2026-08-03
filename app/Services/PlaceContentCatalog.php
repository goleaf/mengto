<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PlaceContentCatalog
{
    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    public function content(array $place): array
    {
        return [
            'gallery' => $this->gallery($place),
            'hours' => $this->hours($place),
            'rules' => $this->rules($place),
            'services' => $this->services($place),
            'facilities' => $this->facilities($place),
            'accessibility' => $this->accessibility($place),
            'safety' => $this->safety($place),
            'specialists' => $this->specialists($place),
            'reviews' => $this->reviews($place),
            'questions' => $this->questions($place),
            'updates' => $this->updates($place),
            'social' => $this->social($place),
            'weather' => $this->weather($place),
            'nearby' => $this->nearby($place),
            'analytics' => $this->analytics($place),
            'verification' => $this->verification($place),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function gallery(array $place): array
    {
        $category = (string) $place['primary_category'];

        return [
            [
                'image' => (string) $place['image'],
                'image_small' => (string) $place['image_small'],
                'image_medium' => (string) $place['image_medium'],
                'alt' => (string) $place['image_alt'],
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.current_conditions_8e4f7d71d2') : __('messages.official_overview_4e52f8942d'),
                'date' => __('messages.july_2026_012fc02ad4'),
                'source' => $place['owner_managed'] ? __('messages.place_profile_2354022ce8') : __('messages.brand.community'),
            ],
            [
                'image' => $this->secondaryImage($category),
                'image_small' => $this->secondaryImage($category, 720, 540),
                'image_medium' => $this->secondaryImage($category, 1200, 750),
                'alt' => $this->secondaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.entrance_and_surface_3737b5b86f') : __('messages.arrival_and_access_ee16555f9f'),
                'date' => __('messages.june_2026_ee00ffb56d'),
                'source' => __('messages.verified_visitor_f4b89fc97c'),
            ],
            [
                'image' => $this->tertiaryImage($category),
                'image_small' => $this->tertiaryImage($category, 720, 540),
                'image_medium' => $this->tertiaryImage($category, 1200, 750),
                'alt' => $this->tertiaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.facilities_89f957ebb1') : __('messages.service_area_72aa13fe85'),
                'date' => __('messages.may_2026_4f7d169525'),
                'source' => __('messages.community_contributor_1c6845cfc6'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{day: string, hours: string, note: string}>
     */
    private function hours(array $place): array
    {
        if ($place['primary_category'] === 'emergency-vet') {
            return [
                ['day' => __('messages.every_day_c4e42b974c'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.call_before_travel_when_possible_0185184f84')],
                ['day' => __('messages.overnight_a6b86e26c4'), 'hours' => __('messages.emergency_triage_63ad538d45'), 'note' => (string) $place['special_hours']],
            ];
        }

        if (in_array($place['primary_category'], ['park', 'dog-park', 'route'], true)) {
            return [
                ['day' => __('messages.monday_friday_628a2b7d3a'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.conditions_may_change_after_storms_cc7780ccde')],
                ['day' => __('messages.saturday_sunday_05512f8a83'), 'hours' => (string) $place['hours_summary'], 'note' => (string) $place['special_hours']],
            ];
        }

        return [
            ['day' => __('messages.monday_friday_628a2b7d3a'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.check_special_hours_before_travel_c14dd35371')],
            ['day' => __('messages.weekend_25014bff5f'), 'hours' => __('messages.see_current_place_schedule_fc64143a50'), 'note' => (string) $place['special_hours']],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, icon: string}>
     */
    private function rules(array $place): array
    {
        return array_map(
            static fn (string $rule, int $index): array => [
                'title' => __('messages.rule_af54679466').($index + 1),
                'detail' => $rule,
                'icon' => ['shield-check', 'paw-print', 'circle-alert'][$index % 3],
            ],
            $place['rules'],
            array_keys($place['rules']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, status: string}>
     */
    private function services(array $place): array
    {
        $prices = $place['pricing'];

        return array_map(
            static fn (string $service, int $index): array => [
                'title' => Str::headline($service),
                'detail' => array_values($prices)[$index % max(1, count($prices))] ?? __('messages.ask_the_place_for_current_details_6c4da052a1'),
                'status' => in_array($place['primary_category'], ['pet-store', 'grooming', 'vet', 'emergency-vet'], true)
                    ? __('messages.availability_may_change_29cde31d92')
                    : __('messages.available_e674447337'),
            ],
            $place['services'],
            array_keys($place['services']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    private function facilities(array $place): array
    {
        return array_map(
            static fn (string $feature, int $index): array => [
                'label' => Str::headline($feature),
                'value' => __('presentation.listed_by', [
                    'owner' => $place['owner_managed'] ? __('messages.the_place_2fa6c77ce8') : __('messages.the_community_3c1ea1c4e9'),
                ]),
                'icon' => ['circle-check-big', 'sparkles', 'map-pinned', 'badge-info'][$index % 4],
            ],
            $place['features'],
            array_keys($place['features']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string}>
     */
    private function accessibility(array $place): array
    {
        return array_map(
            static fn (string $item): array => [
                'label' => Str::headline($item),
                'value' => __('messages.available_according_to_the_latest_place_data_c71009cd6e'),
            ],
            $place['accessibility'],
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, tone: string}>
     */
    private function safety(array $place): array
    {
        return array_map(
            static fn (string $item, int $index): array => [
                'title' => Str::headline($item),
                'detail' => $index === 0
                    ? __('messages.confirm_current_conditions_before_relying_on_this_featur_3b8e254e68')
                    : __('messages.use_the_setting_that_matches_your_pet_and_leave_if_the_s_2bba2d28ef'),
                'tone' => $index === 0 ? 'positive' : 'neutral',
            ],
            $place['safety'],
            array_keys($place['safety']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function specialists(array $place): array
    {
        return match ($place['primary_category']) {
            'grooming' => [
                [
                    'name' => __('messages.emilia_v_c54c057b60'),
                    'initials' => 'EV',
                    'role' => __('messages.cat_and_low_stress_groomer_9dfa276e49'),
                    'experience' => __('messages.8_years_quiet_handling_and_senior_pets_8659d90c8b'),
                    'languages' => __('messages.lithuanian_english_russian_305c331270'),
                    'verification' => __('messages.identity_and_demo_studio_role_checked_6ef2958a91'),
                ],
                [
                    'name' => __('messages.tomas_k_046d52ebb4'),
                    'initials' => 'TK',
                    'role' => __('messages.coat_care_specialist_465236b6a7'),
                    'experience' => __('messages.6_years_de_shedding_and_show_preparation_8bdaa0dd10'),
                    'languages' => __('messages.lithuanian_english_d98e4181a8'),
                    'verification' => __('messages.demo_specialist_profile_5d7623be39'),
                ],
            ],
            'vet', 'emergency-vet' => [
                [
                    'name' => __('messages.dr_lina_petrausk_3c9906a084'),
                    'initials' => 'LP',
                    'role' => $place['primary_category'] === 'emergency-vet' ? __('messages.emergency_and_avian_clinician_77a61b49ee') : __('messages.general_veterinary_clinician_e13b4469a8'),
                    'experience' => __('messages.demo_profile_on_site_availability_varies_638bf81450'),
                    'languages' => __('messages.lithuanian_english_d98e4181a8'),
                    'verification' => __('places.presentation.professional_information_unverified'),
                ],
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, mixed>>
     */
    private function reviews(array $place): array
    {
        $category = (string) $place['primary_category'];
        $criterion = match ($category) {
            'park', 'route' => __('messages.quietness_and_path_condition_01e1845acd'),
            'dog-park' => __('messages.fence_and_entrance_safety_f46f6cf99a'),
            'vet', 'emergency-vet' => __('messages.communication_and_organization_ac850257e7'),
            'grooming' => __('messages.handling_and_communication_45af059174'),
            'pet-cafe' => __('messages.pet_rules_and_atmosphere_df7cbdd10b'),
            'shelter' => __('messages.visit_organization_7d9171f20e'),
            default => __('messages.accuracy_and_service_67e6859d0a'),
        };

        return [
            [
                'key' => $place['key'].'-review-one',
                'author' => __('messages.marta_k_f4f8689ab6'),
                'initials' => 'MK',
                'rating' => 5,
                'visited_with' => __('messages.scout_8a1db462be'),
                'verified' => true,
                'criterion' => $criterion,
                'body' => __('messages.the_description_matched_our_visit_and_the_practical_acce_4b9137ef6a'),
                'date' => __('messages.jul_26_2026_c42329f0e5'),
                'owner_response' => null,
            ],
            [
                'key' => $place['key'].'-review-two',
                'author' => __('messages.anonymous_visitor_47b92b1cef'),
                'initials' => 'AV',
                'rating' => 4,
                'visited_with' => __('messages.pet_profile_hidden_0017dbde01'),
                'verified' => false,
                'criterion' => $criterion,
                'body' => __('messages.helpful_place_overall_check_the_latest_hours_or_conditio_7aafb4574a'),
                'date' => __('messages.jul_19_2026_7a21bad083'),
                'owner_response' => $place['owner_managed']
                    ? __('messages.thank_you_we_have_updated_the_arrival_information_in_the_1916520933')
                    : null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function questions(array $place): array
    {
        return [
            [
                'key' => $place['key'].'-question-one',
                'question' => $this->questionFor($place),
                'author' => __('messages.pet_owner_bbca9a2b0c'),
                'answer' => $this->answerFor($place),
                'answer_author' => $place['owner_managed'] ? __('messages.official_place_response_971983fa5f') : __('messages.verified_visitor_f4b89fc97c'),
                'answered_at' => __('messages.updated_2_days_ago_2df23c8543'),
            ],
            [
                'key' => $place['key'].'-question-two',
                'question' => __('messages.how_current_is_the_information_on_this_page_ddf4d4a546'),
                'author' => __('messages.nori_s_owner_def2c7e2b1'),
                'answer' => (string) $place['data_freshness'],
                'answer_author' => __('messages.brand.data_note'),
                'answered_at' => __('messages.current_status_2f5d50add6'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function updates(array $place): array
    {
        $updates = [
            [
                'title' => __('messages.information_review_b25bfe321c'),
                'body' => (string) $place['data_freshness'],
                'time' => __('messages.latest_8730d3c202'),
                'icon' => 'history',
                'status' => __('messages.current_profile_data_a1d599dc20'),
            ],
        ];

        if ($place['key'] === 'old-town-pet-cafe') {
            $updates[] = [
                'title' => __('messages.pet_access_corrected_3cdf29111b'),
                'body' => __('messages.the_owner_confirmed_that_pets_are_now_welcome_on_the_ter_b9b3366a1a'),
                'time' => __('messages.today_2b065c7c9c'),
                'icon' => 'badge-check',
                'status' => __('messages.owner_confirmed_57fe730848'),
            ];
        }

        if ($place['key'] === 'zverynas-small-dog-run') {
            $updates[] = [
                'title' => __('messages.gate_latch_warning_e537b5aebe'),
                'body' => __('messages.four_visitors_confirmed_a_loose_latch_in_the_small_dog_e_ddde6c1477'),
                'time' => __('messages.3_hours_ago_92d0804055'),
                'icon' => 'triangle-alert',
                'status' => __('messages.temporary_warning_a5ea188731'),
            ];
        }

        return $updates;
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    private function social(array $place): array
    {
        return [
            'friends' => [
                ['name' => __('messages.ari_and_mochi_6ab978b432'), 'initials' => 'AM', 'detail' => __('messages.saved_this_place_58dff3e419')],
                ['name' => __('messages.priya_and_luna_641f4ef0c8'), 'initials' => 'PL', 'detail' => __('messages.visited_recently_65e1307070')],
            ],
            'summary' => __('messages.2_friends_have_a_privacy_permitted_connection_to_this_pl_ef3977e7ea'),
            'story' => $place['key'] === 'zverynas-small-dog-run'
                ? __('messages.a_few_dogs_are_here_the_small_dog_latch_needs_care_4ce50adb0f')
                : __('messages.latest_place_update_is_available_in_the_timeline_dfb19a04f0'),
            'story_expires' => __('messages.temporary_stories_expire_after_24_hours_749a28f1ac'),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, string>
     */
    private function weather(array $place): array
    {
        if (! in_array($place['primary_category'], ['park', 'dog-park', 'route'], true)) {
            return [
                'summary' => __('messages.indoor_or_service_location_b8281df895'),
                'temperature' => __('messages.weather_is_not_part_of_this_place_record_0e08759175'),
                'advisory' => __('places.presentation.live_weather_unavailable'),
                'source' => __('messages.integration_boundary_5d4679d0c3'),
            ];
        }

        return [
            'summary' => __('messages.warm_and_dry_demo_conditions_4c9904f80b'),
            'temperature' => __('messages.24_c_illustrative_7cfd48dad5'),
            'advisory' => $place['water']
                ? __('messages.shade_and_water_are_listed_but_bring_an_individual_bowl_1dc00c5a8e')
                : __('messages.bring_water_and_avoid_hot_surfaces_088e4b2c9e'),
            'source' => __('messages.illustrative_place_guidance_no_live_weather_provider_c62bff0ef8'),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, icon: string}>
     */
    private function nearby(array $place): array
    {
        return [
            ['title' => __('messages.public_transport_6dbd712a92'), 'detail' => __('messages.check_current_pet_rules_with_the_operator_bbfbc09c15'), 'icon' => 'bus-front'],
            ['title' => __('messages.emergency_help_ab9ccf99c5'), 'detail' => __('messages.suitable_clinics_appear_in_emergency_map_mode_607c960e21'), 'icon' => 'stethoscope'],
            ['title' => __('messages.entrance_guidance_fc54129517'), 'detail' => (string) $place['coordinate_accuracy'], 'icon' => 'door-open'],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string, detail: string}>
     */
    private function analytics(array $place): array
    {
        return [
            ['label' => __('messages.profile_views_63c2d118f7'), 'value' => '1.4k', 'detail' => __('messages.aggregate_demo_metric_649814c533')],
            ['label' => __('messages.route_opens_49edbf802d'), 'value' => '286', 'detail' => __('messages.no_individual_viewer_list_1f93d818d5')],
            ['label' => __('messages.saves_989716267f'), 'value' => '114', 'detail' => __('messages.private_collection_names_hidden_9798d5ca93')],
            ['label' => __('messages.data_freshness_64246b6cf0'), 'value' => __('messages.recent_690dbe9dc0'), 'detail' => (string) $place['data_freshness']],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string}>
     */
    private function verification(array $place): array
    {
        return [
            ['label' => __('messages.profile_label_4d9200a72e'), 'value' => (string) $place['verification']['label']],
            ['label' => __('messages.verified_scope_b2357ad523'), 'value' => (string) $place['verification']['scope']],
            ['label' => __('messages.last_checked_0c0c351a97'), 'value' => (string) $place['verification']['updated_at']],
            ['label' => __('messages.coordinate_accuracy_d3bc3a9c3f'), 'value' => (string) $place['coordinate_accuracy']],
            ['label' => __('messages.important_limitation_fd96ff71a0'), 'value' => __('messages.a_verification_label_applies_only_to_the_named_scope_615343dcb1')],
        ];
    }

    private function questionFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => __('messages.is_there_enough_lighting_and_room_to_keep_distance_a74fb03522'),
            'dog-park' => __('messages.is_the_small_dog_entrance_secure_today_32d238627d'),
            'vet', 'emergency-vet' => __('messages.do_you_accept_my_pet_species_without_an_appointment_074bbab4db'),
            'grooming' => __('messages.can_the_appointment_avoid_a_loud_dryer_da6fb70415'),
            'pet-cafe' => __('messages.are_pets_allowed_inside_or_only_on_the_terrace_940aa9a466'),
            'shelter' => __('messages.do_i_need_an_appointment_before_visiting_e3ba154e46'),
            default => __('messages.should_i_call_before_making_a_special_trip_73330a0c3f'),
        };
    }

    private function answerFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => $place['lighting']
                ? __('messages.main_paths_have_listed_lighting_but_quieter_outer_areas__b0345dec54')
                : __('messages.lighting_is_limited_daylight_visits_are_recommended_ceb802b9e3'),
            'dog-park' => $place['key'] === 'zverynas-small-dog-run'
                ? __('messages.the_zone_is_open_but_a_temporary_latch_warning_is_active_5c6c1fb594')
                : __('messages.the_latest_community_check_found_both_gates_working_e46fb19630'),
            'vet', 'emergency-vet' => __('messages.accepted_species_are_listed_here_but_call_first_to_confi_5ad8158962'),
            'grooming' => __('messages.yes_quiet_drying_and_breaks_can_be_requested_in_a_privat_aa71a80a51'),
            'pet-cafe' => __('messages.pets_are_currently_welcome_on_the_terrace_only_a92549ae8e'),
            'shelter' => __('messages.yes_timed_appointments_protect_animals_and_visitors_52ffd61cc0'),
            default => __('messages.calling_first_is_recommended_when_live_availability_is_n_f9648af378'),
        };
    }

    private function secondaryImage(string $category, int $width = 1600, int $height = 1000): string
    {
        $id = match ($category) {
            'park', 'route' => '1501854140801-50d01698950b',
            'dog-park' => '1530281700549-e82e7bf110d6',
            'vet', 'emergency-vet' => '1559190394-df5a28aab5c5',
            'grooming' => '1518791841217-8f162f1e1131',
            'pet-cafe' => '1554118811-1e0d58224f24',
            'shelter' => '1548767797-d8c844163c4c',
            default => '1586671267731-da2cf3ceeb80',
        };

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&h={$height}&q=82";
    }

    private function tertiaryImage(string $category, int $width = 1600, int $height = 1000): string
    {
        $id = match ($category) {
            'park', 'route' => '1472396961693-142e6e269027',
            'dog-park' => '1561037404-61cd46aa615b',
            'vet', 'emergency-vet' => '1517849845537-4d257902454a',
            'grooming' => '1533738363-b7f9aef128ce',
            'pet-cafe' => '1495474472287-4d71bcdd2085',
            'shelter' => '1592754862816-1a21a4ea2281',
            default => '1556228578-8c89e6adf883',
        };

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&h={$height}&q=82";
    }

    private function secondaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => __('messages.public_walking_entrance_with_a_broad_path_bb919f8c22'),
            'dog-park' => __('messages.dog_exercise_area_with_visible_fencing_3d75bb4af2'),
            'vet', 'emergency-vet' => __('messages.bright_veterinary_waiting_and_consultation_area_3969d2817c'),
            'grooming' => __('messages.quiet_pet_grooming_workspace_with_clean_equipment_0f581fa9e5'),
            'pet-cafe' => __('messages.pet_friendly_cafe_seating_near_an_outdoor_entrance_c5591ff761'),
            'shelter' => __('messages.calm_shelter_introduction_area_93319708c2'),
            default => __('messages.accessible_entrance_to_the_place_ec6c185db1'),
        };
    }

    private function tertiaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => __('messages.rest_area_and_natural_shade_beside_a_walking_path_f5fbf2d590'),
            'dog-park' => __('messages.water_and_seating_facilities_inside_a_dog_park_bf267caf0d'),
            'vet', 'emergency-vet' => __('messages.veterinary_professional_preparing_a_treatment_room_13f4508059'),
            'grooming' => __('messages.fresh_towels_and_quiet_grooming_tools_f67c67a65c'),
            'pet-cafe' => __('messages.water_bowl_placed_beside_a_cafe_terrace_table_d945ad3e6f'),
            'shelter' => __('messages.shelter_volunteer_area_with_pet_supplies_fdb1d3e801'),
            default => __('messages.services_and_facilities_available_at_the_place_a015611128'),
        };
    }
}
