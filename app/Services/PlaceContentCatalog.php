<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PlaceContentCatalog
{
    public function __construct(
        private readonly PlaceMediaCatalog $media,
    ) {}

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
        $gallery = $this->media->gallery($category);

        return [
            [
                ...$gallery[0],
                'alt' => (string) $place['image_alt'],
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.current_conditions') : __('messages.official_overview'),
                'date' => __('messages.july_2026'),
                'source' => $place['owner_managed'] ? __('messages.place_profile') : __('messages.brand.community'),
            ],
            [
                ...$gallery[1],
                'alt' => $this->secondaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.entrance_and_surface') : __('messages.arrival_and_access'),
                'date' => __('messages.june_2026'),
                'source' => __('messages.verified_visitor'),
            ],
            [
                ...$gallery[2],
                'alt' => $this->tertiaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? __('messages.facilities') : __('messages.service_area'),
                'date' => __('messages.may_2026'),
                'source' => __('messages.community_contributor'),
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
                ['day' => __('messages.every_day'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.call_before_travel_when_possible')],
                ['day' => __('messages.overnight'), 'hours' => __('messages.emergency_triage'), 'note' => (string) $place['special_hours']],
            ];
        }

        if (in_array($place['primary_category'], ['park', 'dog-park', 'route'], true)) {
            return [
                ['day' => __('messages.monday_friday'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.conditions_may_change_after_storms')],
                ['day' => __('messages.saturday_sunday'), 'hours' => (string) $place['hours_summary'], 'note' => (string) $place['special_hours']],
            ];
        }

        return [
            ['day' => __('messages.monday_friday'), 'hours' => (string) $place['hours_summary'], 'note' => __('messages.check_special_hours_before_travel')],
            ['day' => __('messages.weekend'), 'hours' => __('messages.see_current_place_schedule'), 'note' => (string) $place['special_hours']],
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
                'title' => __('messages.rule').($index + 1),
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
                'detail' => array_values($prices)[$index % max(1, count($prices))] ?? __('messages.ask_the_place_for_current_details'),
                'status' => in_array($place['primary_category'], ['pet-store', 'grooming', 'vet', 'emergency-vet'], true)
                    ? __('messages.availability_may_change')
                    : __('messages.available'),
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
                    'owner' => $place['owner_managed'] ? __('messages.the_place') : __('messages.the_community'),
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
                'value' => __('messages.available_according_to_the_latest_place_data'),
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
                    ? __('messages.confirm_current_conditions_before_relying_on_this_feature')
                    : __('messages.use_the_setting_that_matches_your_pet_and_leave_if_the_situation_feels_unsafe'),
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
                    'name' => __('messages.emilia_v'),
                    'initials' => 'EV',
                    'role' => __('messages.cat_and_low_stress_groomer'),
                    'experience' => __('messages.8_years_quiet_handling_and_senior_pets'),
                    'languages' => __('messages.lithuanian_english_russian'),
                    'verification' => __('messages.identity_and_demo_studio_role_checked'),
                ],
                [
                    'name' => __('messages.tomas_k'),
                    'initials' => 'TK',
                    'role' => __('messages.coat_care_specialist'),
                    'experience' => __('messages.6_years_de_shedding_and_show_preparation'),
                    'languages' => __('messages.lithuanian_english'),
                    'verification' => __('messages.demo_specialist_profile'),
                ],
            ],
            'vet', 'emergency-vet' => [
                [
                    'name' => __('messages.dr_lina_petrauskė'),
                    'initials' => 'LP',
                    'role' => $place['primary_category'] === 'emergency-vet' ? __('messages.emergency_and_avian_clinician') : __('messages.general_veterinary_clinician'),
                    'experience' => __('messages.demo_profile_on_site_availability_varies'),
                    'languages' => __('messages.lithuanian_english'),
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
            'park', 'route' => __('messages.quietness_and_path_condition'),
            'dog-park' => __('messages.fence_and_entrance_safety'),
            'vet', 'emergency-vet' => __('messages.communication_and_organization'),
            'grooming' => __('messages.handling_and_communication'),
            'pet-cafe' => __('messages.pet_rules_and_atmosphere'),
            'shelter' => __('messages.visit_organization'),
            default => __('messages.accuracy_and_service'),
        };

        return [
            [
                'key' => $place['key'].'-review-one',
                'author' => __('messages.marta_k'),
                'initials' => 'MK',
                'rating' => 5,
                'visited_with' => __('messages.scout'),
                'verified' => true,
                'criterion' => $criterion,
                'body' => __('messages.the_description_matched_our_visit_and_the_practical_access_notes_were_useful'),
                'date' => __('messages.jul_26_2026'),
                'owner_response' => null,
            ],
            [
                'key' => $place['key'].'-review-two',
                'author' => __('messages.anonymous_visitor'),
                'initials' => 'AV',
                'rating' => 4,
                'visited_with' => __('messages.pet_profile_hidden'),
                'verified' => false,
                'criterion' => $criterion,
                'body' => __('messages.helpful_place_overall_check_the_latest_hours_or_conditions_before_making_a_special_trip'),
                'date' => __('messages.jul_19_2026'),
                'owner_response' => $place['owner_managed']
                    ? __('messages.thank_you_we_have_updated_the_arrival_information_in_the_profile')
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
                'author' => __('messages.pet_owner'),
                'answer' => $this->answerFor($place),
                'answer_author' => $place['owner_managed'] ? __('messages.official_place_response') : __('messages.verified_visitor'),
                'answered_at' => __('messages.updated_2_days_ago'),
            ],
            [
                'key' => $place['key'].'-question-two',
                'question' => __('messages.how_current_is_the_information_on_this_page'),
                'author' => __('messages.nori_s_owner'),
                'answer' => (string) $place['data_freshness'],
                'answer_author' => __('messages.brand.data_note'),
                'answered_at' => __('messages.current_status'),
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
                'title' => __('messages.information_review'),
                'body' => (string) $place['data_freshness'],
                'time' => __('messages.latest'),
                'icon' => 'history',
                'status' => __('messages.current_profile_data'),
            ],
        ];

        if ($place['key'] === 'old-town-pet-cafe') {
            $updates[] = [
                'title' => __('messages.pet_access_corrected'),
                'body' => __('messages.the_owner_confirmed_that_pets_are_now_welcome_on_the_terrace_only'),
                'time' => __('messages.today'),
                'icon' => 'badge-check',
                'status' => __('messages.owner_confirmed'),
            ];
        }

        if ($place['key'] === 'zverynas-small-dog-run') {
            $updates[] = [
                'title' => __('messages.gate_latch_warning'),
                'body' => __('messages.four_visitors_confirmed_a_loose_latch_in_the_small_dog_entrance'),
                'time' => __('messages.3_hours_ago'),
                'icon' => 'triangle-alert',
                'status' => __('messages.temporary_warning'),
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
                ['name' => __('messages.ari_and_mochi'), 'initials' => 'AM', 'detail' => __('messages.saved_this_place')],
                ['name' => __('messages.priya_and_luna'), 'initials' => 'PL', 'detail' => __('messages.visited_recently')],
            ],
            'summary' => __('messages.2_friends_have_a_privacy_permitted_connection_to_this_place'),
            'story' => $place['key'] === 'zverynas-small-dog-run'
                ? __('messages.a_few_dogs_are_here_the_small_dog_latch_needs_care')
                : __('messages.latest_place_update_is_available_in_the_timeline'),
            'story_expires' => __('messages.temporary_stories_expire_after_24_hours'),
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
                'summary' => __('messages.indoor_or_service_location'),
                'temperature' => __('messages.weather_is_not_part_of_this_place_record'),
                'advisory' => __('places.presentation.live_weather_unavailable'),
                'source' => __('messages.integration_boundary'),
            ];
        }

        return [
            'summary' => __('messages.warm_and_dry_demo_conditions'),
            'temperature' => __('messages.24_c_illustrative'),
            'advisory' => $place['water']
                ? __('messages.shade_and_water_are_listed_but_bring_an_individual_bowl')
                : __('messages.bring_water_and_avoid_hot_surfaces'),
            'source' => __('messages.illustrative_place_guidance_no_live_weather_provider'),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, icon: string}>
     */
    private function nearby(array $place): array
    {
        return [
            ['title' => __('messages.public_transport'), 'detail' => __('messages.check_current_pet_rules_with_the_operator'), 'icon' => 'bus-front'],
            ['title' => __('messages.emergency_help'), 'detail' => __('messages.suitable_clinics_appear_in_emergency_map_mode'), 'icon' => 'stethoscope'],
            ['title' => __('messages.entrance_guidance'), 'detail' => (string) $place['coordinate_accuracy'], 'icon' => 'door-open'],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string, detail: string}>
     */
    private function analytics(array $place): array
    {
        return [
            ['label' => __('messages.profile_views'), 'value' => '1.4k', 'detail' => __('messages.aggregate_demo_metric')],
            ['label' => __('messages.route_opens'), 'value' => '286', 'detail' => __('messages.no_individual_viewer_list')],
            ['label' => __('messages.saves'), 'value' => '114', 'detail' => __('messages.private_collection_names_hidden')],
            ['label' => __('messages.data_freshness'), 'value' => __('messages.recent'), 'detail' => (string) $place['data_freshness']],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string}>
     */
    private function verification(array $place): array
    {
        return [
            ['label' => __('messages.profile_label'), 'value' => (string) $place['verification']['label']],
            ['label' => __('messages.verified_scope'), 'value' => (string) $place['verification']['scope']],
            ['label' => __('messages.last_checked'), 'value' => (string) $place['verification']['updated_at']],
            ['label' => __('messages.coordinate_accuracy'), 'value' => (string) $place['coordinate_accuracy']],
            ['label' => __('messages.important_limitation'), 'value' => __('messages.a_verification_label_applies_only_to_the_named_scope')],
        ];
    }

    private function questionFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => __('messages.is_there_enough_lighting_and_room_to_keep_distance'),
            'dog-park' => __('messages.is_the_small_dog_entrance_secure_today'),
            'vet', 'emergency-vet' => __('messages.do_you_accept_my_pet_species_without_an_appointment'),
            'grooming' => __('messages.can_the_appointment_avoid_a_loud_dryer'),
            'pet-cafe' => __('messages.are_pets_allowed_inside_or_only_on_the_terrace'),
            'shelter' => __('messages.do_i_need_an_appointment_before_visiting'),
            default => __('messages.should_i_call_before_making_a_special_trip'),
        };
    }

    private function answerFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => $place['lighting']
                ? __('messages.main_paths_have_listed_lighting_but_quieter_outer_areas_can_be_darker')
                : __('messages.lighting_is_limited_daylight_visits_are_recommended'),
            'dog-park' => $place['key'] === 'zverynas-small-dog-run'
                ? __('messages.the_zone_is_open_but_a_temporary_latch_warning_is_active')
                : __('messages.the_latest_community_check_found_both_gates_working'),
            'vet', 'emergency-vet' => __('messages.accepted_species_are_listed_here_but_call_first_to_confirm_the_current_clinician_and_intake'),
            'grooming' => __('messages.yes_quiet_drying_and_breaks_can_be_requested_in_a_private_care_note'),
            'pet-cafe' => __('messages.pets_are_currently_welcome_on_the_terrace_only'),
            'shelter' => __('messages.yes_timed_appointments_protect_animals_and_visitors'),
            default => __('messages.calling_first_is_recommended_when_live_availability_is_not_connected'),
        };
    }

    private function secondaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => __('messages.public_walking_entrance_with_a_broad_path'),
            'dog-park' => __('messages.dog_exercise_area_with_visible_fencing'),
            'vet', 'emergency-vet' => __('messages.bright_veterinary_waiting_and_consultation_area'),
            'grooming' => __('messages.quiet_pet_grooming_workspace_with_clean_equipment'),
            'pet-cafe' => __('messages.pet_friendly_cafe_seating_near_an_outdoor_entrance'),
            'shelter' => __('messages.calm_shelter_introduction_area'),
            default => __('messages.accessible_entrance_to_the_place'),
        };
    }

    private function tertiaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => __('messages.rest_area_and_natural_shade_beside_a_walking_path'),
            'dog-park' => __('messages.water_and_seating_facilities_inside_a_dog_park'),
            'vet', 'emergency-vet' => __('messages.veterinary_professional_preparing_a_treatment_room'),
            'grooming' => __('messages.fresh_towels_and_quiet_grooming_tools'),
            'pet-cafe' => __('messages.water_bowl_placed_beside_a_cafe_terrace_table'),
            'shelter' => __('messages.shelter_volunteer_area_with_pet_supplies'),
            default => __('messages.services_and_facilities_available_at_the_place'),
        };
    }
}
