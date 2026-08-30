<?php

namespace App\Services;

final class EventContentCatalog
{
    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function content(array $event): array
    {
        return [
            'schedule' => $this->schedule($event),
            'organizers' => $this->organizers($event),
            'attendees' => $this->attendees($event),
            'pets' => $this->pets($event),
            'announcements' => $this->announcements($event),
            'chat' => $this->chat($event),
            'location' => $this->location($event),
            'files' => $this->files($event),
            'gallery' => $this->gallery($event),
            'rules' => $this->rules($event),
            'safety' => $this->safety($event),
            'faq' => $this->faq($event),
            'reviews' => $this->reviews($event),
            'analytics' => $this->analytics($event),
            'applications' => $this->applications($event),
            'waitlist' => $this->waitlist($event),
            'ticket_options' => $this->ticketOptions($event),
            'checklist' => $this->checklist($event),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function schedule(array $event): array
    {
        return match ($event['event_type']) {
            'group-walk' => [
                ['time' => __('messages.9_50_am'), 'title' => __('messages.quiet_arrival'), 'description' => __('messages.meet_the_host_without_bringing_pets_into_a_tight_cluster'), 'leader' => __('messages.mia_carter')],
                ['time' => __('messages.10_00_am'), 'title' => __('messages.parallel_start'), 'description' => __('messages.pairs_leave_with_comfortable_spacing_and_no_direct_greeting'), 'leader' => __('messages.mia_carter')],
                ['time' => __('messages.10_35_am'), 'title' => __('messages.water_and_reset'), 'description' => __('messages.short_pause_with_individual_bowls_in_a_shaded_area'), 'leader' => __('messages.noah_patel')],
                ['time' => __('messages.11_30_am'), 'title' => __('messages.flexible_finish'), 'description' => __('messages.leave_independently_or_join_the_optional_calm_closing_loop'), 'leader' => __('messages.mia_carter')],
            ],
            'training-course' => [
                ['time' => __('messages.10_50_am'), 'title' => __('messages.check_in_and_settle'), 'description' => __('messages.enter_one_at_a_time_and_choose_a_marked_station'), 'leader' => __('messages.ari_jensen')],
                ['time' => __('messages.11_00_am'), 'title' => __('messages.attention_and_name_response'), 'description' => __('messages.short_repetitions_with_individual_rest_breaks'), 'leader' => __('messages.ari_jensen')],
                ['time' => __('messages.11_30_am'), 'title' => __('messages.waiting_and_leash_movement'), 'description' => __('messages.practical_patterns_with_adjustable_distance'), 'leader' => __('messages.ari_jensen')],
                ['time' => __('messages.12_05_pm'), 'title' => __('messages.home_routine_briefing'), 'description' => __('messages.review_materials_and_next_session_goals'), 'leader' => __('messages.ari_jensen')],
            ],
            'pet-show' => [
                ['time' => __('messages.8_00_am'), 'title' => __('messages.exhibitor_check_in'), 'description' => __('messages.qr_entry_and_private_document_review_at_hall_d'), 'leader' => __('messages.registration_team')],
                ['time' => __('messages.9_30_am'), 'title' => __('messages.morning_categories'), 'description' => __('messages.age_and_breed_categories_across_rings_1_3'), 'leader' => __('messages.show_officials')],
                ['time' => __('messages.1_00_pm'), 'title' => __('messages.companion_celebration'), 'description' => __('messages.inclusive_stories_adoption_journeys_and_friendly_parade'), 'leader' => __('messages.community_hosts')],
                ['time' => __('messages.4_30_pm'), 'title' => __('messages.results_and_quiet_exit'), 'description' => __('messages.results_publish_by_category_before_staggered_departure'), 'leader' => __('messages.show_officials')],
            ],
            'expert-webinar' => [
                ['time' => __('messages.5_45_pm'), 'title' => __('messages.room_opens'), 'description' => __('messages.test_audio_captions_and_question_submission'), 'leader' => __('messages.support_team')],
                ['time' => __('messages.6_00_pm'), 'title' => __('messages.travel_preparation'), 'description' => __('messages.documents_carrier_routines_transport_and_planning'), 'leader' => __('messages.dr_elena_park')],
                ['time' => __('messages.6_45_pm'), 'title' => __('messages.moderated_questions'), 'description' => __('messages.general_education_without_individual_diagnosis'), 'leader' => __('messages.dr_elena_park')],
                ['time' => __('messages.7_10_pm'), 'title' => __('messages.resources_and_recording'), 'description' => __('messages.slides_citations_and_recording_access_explained'), 'leader' => __('messages.support_team')],
            ],
            'search-action' => [
                ['time' => __('messages.5_50_pm'), 'title' => __('messages.volunteer_check_in'), 'description' => __('messages.receive_a_zone_and_confirm_safe_sighting_instructions'), 'leader' => __('messages.mia_carter')],
                ['time' => __('messages.6_00_pm'), 'title' => __('messages.search_starts'), 'description' => __('messages.teams_move_through_assigned_public_areas'), 'leader' => __('messages.zone_coordinators')],
                ['time' => __('messages.7_15_pm'), 'title' => __('messages.status_regroup'), 'description' => __('messages.share_sightings_through_the_private_coordinator_channel'), 'leader' => __('messages.mia_carter')],
                ['time' => __('messages.9_00_pm'), 'title' => __('messages.close_or_reassign'), 'description' => __('messages.checked_zones_are_recorded_before_volunteers_leave'), 'leader' => __('messages.search_team')],
            ],
            default => [
                ['time' => __('messages.start'), 'title' => __('messages.arrival_and_check_in'), 'description' => __('messages.confirm_registration_and_review_the_event_boundaries'), 'leader' => $event['organizer']],
                ['time' => __('messages.main'), 'title' => __('messages.event_program'), 'description' => __('messages.follow_the_published_event_plan_and_organizer_guidance'), 'leader' => $event['organizer']],
                ['time' => __('messages.finish'), 'title' => __('messages.closing_and_next_steps'), 'description' => __('messages.collect_materials_feedback_and_any_follow_up_link'), 'leader' => $event['organizer']],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function organizers(array $event): array
    {
        return [
            [
                'name' => $event['organizer'],
                'detail' => __('presentation.lead_organizer', ['type' => $event['organizer_type']]),
                'initials' => $event['organizer_initials'],
                'tone' => 'sun',
                'badge' => $event['verification_label'] ?? __('messages.event_host'),
            ],
            [
                'name' => __('messages.noah_patel'),
                'detail' => __('messages.registration_and_accessibility'),
                'initials' => 'NP',
                'tone' => 'mint',
                'badge' => __('messages.co_organizer'),
            ],
            [
                'name' => __('messages.lena_brooks'),
                'detail' => __('messages.chat_and_participant_support'),
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => __('messages.moderator'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function attendees(array $event): array
    {
        if ($event['privacy'] === 'hidden') {
            return [];
        }

        return [
            ['name' => __('messages.ari_jensen'), 'detail' => __('messages.approved_arriving_with_mochi'), 'initials' => 'AJ', 'tone' => 'sun', 'badge' => __('messages.confirmed')],
            ['name' => __('messages.noah_patel'), 'detail' => __('messages.approved_accessibility_note_shared_privately'), 'initials' => 'NP', 'tone' => 'mint', 'badge' => __('messages.confirmed')],
            ['name' => __('messages.priya_shah'), 'detail' => $event['format'] === 'online' ? __('messages.online_attendee') : __('messages.first_time_at_this_event'), 'initials' => 'PS', 'tone' => 'paper', 'badge' => __('messages.confirmed')],
            ['name' => __('messages.lena_brooks'), 'detail' => __('messages.event_updates_enabled'), 'initials' => 'LB', 'tone' => 'mint', 'badge' => __('messages.confirmed')],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function pets(array $event): array
    {
        if (! $event['pets_allowed']) {
            return [];
        }

        return [
            ['name' => __('messages.mochi'), 'detail' => __('messages.shiba_mix_calm_arrival_requested'), 'initials' => 'MO', 'tone' => 'sun', 'badge' => __('messages.attending')],
            ['name' => __('messages.juniper'), 'detail' => __('messages.golden_retriever_steady_pace'), 'initials' => 'JU', 'tone' => 'mint', 'badge' => __('messages.attending')],
            ['name' => __('messages.olive'), 'detail' => __('messages.corgi_one_handler'), 'initials' => 'OL', 'tone' => 'paper', 'badge' => __('messages.attending')],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function announcements(array $event): array
    {
        return [
            [
                'title' => $event['format'] === 'online' ? __('messages.access_link_timing') : __('messages.meeting_point_stays_private'),
                'body' => $event['format'] === 'online'
                    ? __('messages.the_webinar_room_link_appears_for_paid_attendees_fifteen_minutes_before_the_start')
                    : __('messages.approved_attendees_will_see_the_exact_entrance_public_cards_continue_to_show_only_the_general_area'),
                'time' => __('messages.today_9_20_am'),
                'icon' => $event['format'] === 'online' ? 'video' : 'map-pin-check',
            ],
            [
                'title' => __('messages.bring_and_consent_checklist_updated'),
                'body' => __('messages.review_the_event_rules_photo_preference_and_item_checklist_before_arrival'),
                'time' => __('messages.yesterday_4_15_pm'),
                'icon' => 'clipboard-check',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function chat(array $event): array
    {
        return [
            [
                'name' => $event['organizer'],
                'initials' => $event['organizer_initials'],
                'tone' => 'sun',
                'body' => $event['format'] === 'online'
                    ? __('messages.captions_and_the_text_transcript_will_be_available_in_the_same_event_room')
                    : __('messages.please_arrive_with_enough_space_between_pets_the_host_will_direct_the_first_pairs'),
                'time' => __('messages.9_28_am'),
            ],
            [
                'name' => __('messages.ari_jensen'),
                'initials' => 'AJ',
                'tone' => 'mint',
                'body' => __('messages.i_added_our_accessibility_note_privately_thanks_for_keeping_those_details_out_of_the_participant_list'),
                'time' => __('messages.9_34_am'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function location(array $event): array
    {
        return [
            'general' => $event['general_location'],
            'exact' => $event['exact_location'],
            'online_link' => $event['online_link'],
            'map_alt' => __('messages.text_map_showing_the_generalized_event_area_accessible_arrival_route_parking_and_nearby_help'),
            'details' => [
                ['label' => __('messages.arrival'), 'value' => $event['format'] === 'online' ? __('messages.join_from_a_modern_browser_with_audio_enabled') : __('messages.use_the_marked_public_entrance_after_registration')],
                ['label' => __('messages.accessibility'), 'value' => $event['format'] === 'online' ? __('messages.keyboard_navigation_and_captions_are_available') : __('messages.step_free_route_accessible_parking_seating_and_a_quiet_area')],
                ['label' => __('messages.transport'), 'value' => $event['format'] === 'online' ? __('messages.no_travel_required') : __('messages.public_transit_and_parking_notes_appear_with_the_exact_location')],
                ['label' => __('messages.nearby_help'), 'value' => $event['format'] === 'online' ? __('messages.technical_support_opens_fifteen_minutes_early') : __('messages.24_hour_veterinary_clinic_details_are_available_to_attendees')],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function files(array $event): array
    {
        return [
            ['title' => __('messages.event_plan'), 'description' => __('messages.schedule_roles_and_important_contact_paths'), 'meta' => __('messages.pdf_updated_today'), 'icon' => 'file-text'],
            ['title' => __('messages.safety_and_accessibility_guide'), 'description' => __('messages.arrival_quiet_area_emergency_and_consent_information'), 'meta' => __('messages.pdf_420_kb'), 'icon' => 'shield-check'],
            ['title' => $event['format'] === 'online' ? __('messages.slides_and_sources') : __('messages.what_to_bring'), 'description' => $event['format'] === 'online' ? __('messages.available_after_the_webinar') : __('messages.compact_checklist_for_owners_and_pets'), 'meta' => __('messages.guide_current_version'), 'icon' => 'clipboard-list'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function gallery(array $event): array
    {
        return [
            [
                'src' => $event['image'],
                'small' => $event['image_small'],
                'medium' => $event['image_medium'],
                'alt' => $event['image_alt'],
                'caption' => __('messages.event_cover_selected_by_the_organizer'),
            ],
            [
                'src' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=1200&h=900&q=85',
                'small' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=576&h=432&q=80',
                'medium' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=900&h=675&q=82',
                'alt' => __('messages.dog_resting_on_grass_during_a_calm_outdoor_gathering'),
                'caption' => __('messages.a_quiet_zone_example_from_a_previous_community_event'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function rules(array $event): array
    {
        $petRule = $event['pets_allowed']
            ? __('messages.keep_each_pet_supervised_and_follow_the_stated_leash_carrier_distance_food_and_toy_boundaries')
            : __('messages.attend_without_your_resident_pet_unless_the_organizer_explicitly_approves_an_exception');

        return [
            ['title' => __('messages.respect_the_participation_plan'), 'description' => $petRule],
            ['title' => __('messages.protect_private_details'), 'description' => __('messages.do_not_repost_exact_locations_online_links_participant_notes_or_children_s_images')],
            ['title' => __('messages.use_calm_qualified_help'), 'description' => __('messages.end_an_activity_when_anyone_is_uncomfortable_and_contact_qualified_care_for_medical_or_complex_behavior_concerns')],
            ['title' => __('messages.photography_is_opt_in'), 'description' => __('messages.follow_photo_free_markers_and_request_approval_before_tagging_people_children_or_pets')],
            ['title' => __('messages.commercial_activity_is_transparent'), 'description' => __('messages.prices_sponsors_donations_refunds_and_organizer_responsibility_must_stay_visible')],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function safety(array $event): array
    {
        return [
            [
                'icon' => 'shield-check',
                'title' => __('messages.public_first_contact'),
                'description' => $event['format'] === 'online'
                    ? __('messages.use_the_protected_event_room_and_do_not_share_private_contact_details_in_the_public_questions')
                    : __('messages.meet_at_the_published_public_entrance_and_keep_home_addresses_private'),
            ],
            [
                'icon' => 'stethoscope',
                'title' => __('messages.nearby_care_plan'),
                'description' => $event['format'] === 'online'
                    ? __('messages.the_webinar_is_educational_and_is_not_an_emergency_or_diagnosis_service')
                    : __('messages.the_organizer_has_a_first_aid_location_and_the_nearest_24_hour_veterinary_clinic_details'),
            ],
            [
                'icon' => 'cloud-sun',
                'title' => __('messages.conditions_are_reviewed'),
                'description' => $event['weather']['advisory'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function faq(array $event): array
    {
        return [
            ['question' => __('messages.can_i_attend_without_a_pet'), 'answer' => $event['pets_allowed'] ? __('messages.yes_select_owner_only_attendance_during_registration') : __('messages.yes_this_event_is_designed_for_owners_without_resident_pets')],
            ['question' => __('messages.when_is_the_exact_location_available'), 'answer' => $event['format'] === 'online' ? __('messages.the_protected_link_appears_for_eligible_attendees_shortly_before_the_start') : __('messages.approved_or_confirmed_attendees_can_see_the_exact_entrance')],
            ['question' => __('messages.what_happens_if_plans_change'), 'answer' => __('messages.material_date_time_place_price_or_organizer_changes_are_logged_and_may_require_fresh_confirmation')],
            ['question' => __('messages.can_i_cancel'), 'answer' => $event['price_minor'] > 0 ? __('messages.yes_the_ticket_panel_shows_the_prototype_cancellation_and_refund_terms_before_payment') : __('messages.yes_cancelling_releases_the_place_to_the_waitlist')],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function reviews(array $event): array
    {
        return [
            [
                'name' => __('messages.priya_shah'),
                'initials' => 'PS',
                'tone' => 'mint',
                'rating' => '5',
                'title' => __('messages.the_description_matched_the_pace'),
                'body' => __('messages.arrival_was_calm_the_organizer_stayed_reachable_and_the_quiet_option_was_real'),
                'meta' => __('messages.verified_attendee_previous_edition'),
            ],
            [
                'name' => __('messages.noah_patel'),
                'initials' => 'NP',
                'tone' => 'paper',
                'rating' => '4',
                'title' => __('messages.clear_accessibility_information'),
                'body' => __('messages.the_step_free_route_and_rest_area_were_easy_to_find_a_second_sign_would_help_at_the_entrance'),
                'meta' => __('messages.verified_attendee_previous_edition'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function analytics(array $event): array
    {
        $views = max(184, $event['base_attendees'] * 18);
        $opened = (int) round($views * 0.42);
        $started = (int) round($opened * 0.38);
        $completed = $event['base_attendees'];

        return [
            'metrics' => [
                ['label' => __('messages.event_views'), 'value' => (string) $views, 'detail' => __('messages.aggregate_views')],
                ['label' => __('messages.page_opens'), 'value' => (string) $opened, 'detail' => __('messages.from_discovery')],
                ['label' => __('messages.started'), 'value' => (string) $started, 'detail' => __('messages.registration_attempts')],
                ['label' => __('messages.confirmed'), 'value' => (string) $completed, 'detail' => __('messages.current_places')],
                ['label' => __('messages.attendance'), 'value' => '86%', 'detail' => __('messages.previous_edition')],
                ['label' => __('messages.safety'), 'value' => '0', 'detail' => __('messages.open_incidents')],
            ],
            'funnel' => [
                ['label' => __('messages.saw_the_card'), 'value' => $views, 'percent' => 100],
                ['label' => __('messages.opened_event'), 'value' => $opened, 'percent' => (int) round(($opened / $views) * 100)],
                ['label' => __('messages.started_registration'), 'value' => $started, 'percent' => (int) round(($started / $views) * 100)],
                ['label' => __('messages.confirmed'), 'value' => $completed, 'percent' => (int) round(($completed / $views) * 100)],
            ],
            'privacy_note' => __('messages.only_aggregate_registration_attendance_and_feedback_data_is_shown_private_health_behavior_location_search_cancellation_and_guest_contact_details_are_excluded'),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function applications(array $event): array
    {
        if (! $event['managed_by_current_user']) {
            return [];
        }

        return [
            [
                'key' => 'ari-mochi',
                'name' => __('messages.ari_mochi'),
                'detail' => __('messages.medium_dog_parallel_introduction_requested'),
                'initials' => 'AM',
                'tone' => 'sun',
                'status' => __('messages.pending_review'),
            ],
            [
                'key' => 'noah-juniper',
                'name' => __('messages.noah_juniper'),
                'detail' => __('messages.large_dog_calm_pace'),
                'initials' => 'NJ',
                'tone' => 'mint',
                'status' => __('messages.pending_review'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function waitlist(array $event): array
    {
        if (! $event['managed_by_current_user']) {
            return [];
        }

        return [
            [
                'key' => 'lena-pip',
                'name' => __('messages.lena_pip'),
                'detail' => __('messages.first_in_line_12_minutes_to_confirm_after_promotion'),
                'initials' => 'LP',
                'tone' => 'paper',
                'status' => __('messages.waiting'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, mixed>>
     */
    private function ticketOptions(array $event): array
    {
        if ($event['ticket_model'] === 'free') {
            return [
                [
                    'key' => 'standard',
                    'title' => __('messages.standard_place'),
                    'description' => $event['pets_allowed'] ? __('messages.one_owner_and_one_selected_pet') : __('messages.one_registered_attendee'),
                    'price_minor' => 0,
                    'currency' => $event['currency'],
                ],
            ];
        }

        return [
            [
                'key' => 'standard',
                'title' => $event['format'] === 'online' ? __('messages.live_webinar') : __('messages.standard_ticket'),
                'description' => $event['format'] === 'online' ? __('messages.live_access_recording_slides_and_cited_resources') : __('messages.one_owner_and_one_selected_pet'),
                'price_minor' => $event['price_minor'],
                'currency' => $event['currency'],
            ],
            [
                'key' => 'owner-only',
                'title' => __('messages.owner_only'),
                'description' => __('messages.attend_without_a_pet_where_the_event_format_allows_it'),
                'price_minor' => max(0, $event['price_minor'] - 1000),
                'currency' => $event['currency'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array{label: string, done: bool}>
     */
    private function checklist(array $event): array
    {
        return [
            ['label' => __('messages.review_the_event_rules_and_cancellation_terms'), 'done' => true],
            ['label' => $event['pets_allowed'] ? __('messages.choose_the_attending_pet_profile') : __('messages.confirm_owner_only_attendance'), 'done' => false],
            ['label' => $event['format'] === 'online' ? __('messages.test_browser_audio_and_captions') : __('messages.save_the_arrival_and_accessibility_notes'), 'done' => false],
            ['label' => $event['price_minor'] > 0 ? __('messages.complete_the_prototype_payment_reservation') : __('messages.confirm_the_free_place'), 'done' => false],
        ];
    }
}
