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
                ['time' => __('messages.9_50_am_ffea28ee54'), 'title' => __('messages.quiet_arrival_3d115e5e3d'), 'description' => __('messages.meet_the_host_without_bringing_pets_into_a_tight_cluster_f1254af19b'), 'leader' => __('messages.mia_carter_0e5b29cc3b')],
                ['time' => __('messages.10_00_am_48ae3f036f'), 'title' => __('messages.parallel_start_d0268ce5c6'), 'description' => __('messages.pairs_leave_with_comfortable_spacing_and_no_direct_greet_b065be5aa9'), 'leader' => __('messages.mia_carter_0e5b29cc3b')],
                ['time' => __('messages.10_35_am_6e0be088d1'), 'title' => __('messages.water_and_reset_9de206e3ee'), 'description' => __('messages.short_pause_with_individual_bowls_in_a_shaded_area_e2fc21ad4f'), 'leader' => __('messages.noah_patel_147a9793ed')],
                ['time' => __('messages.11_30_am_2f66eb19b9'), 'title' => __('messages.flexible_finish_53aef2eacb'), 'description' => __('messages.leave_independently_or_join_the_optional_calm_closing_lo_456ae98ff2'), 'leader' => __('messages.mia_carter_0e5b29cc3b')],
            ],
            'training-course' => [
                ['time' => __('messages.10_50_am_c7ec7c18e9'), 'title' => __('messages.check_in_and_settle_877e689664'), 'description' => __('messages.enter_one_at_a_time_and_choose_a_marked_station_8a5b44f08c'), 'leader' => __('messages.ari_jensen_6c670df410')],
                ['time' => __('messages.11_00_am_2f212910a7'), 'title' => __('messages.attention_and_name_response_aaea43f17b'), 'description' => __('messages.short_repetitions_with_individual_rest_breaks_cd45edb93c'), 'leader' => __('messages.ari_jensen_6c670df410')],
                ['time' => __('messages.11_30_am_2f66eb19b9'), 'title' => __('messages.waiting_and_leash_movement_1218f4859c'), 'description' => __('messages.practical_patterns_with_adjustable_distance_3e8d288c36'), 'leader' => __('messages.ari_jensen_6c670df410')],
                ['time' => __('messages.12_05_pm_3db014af42'), 'title' => __('messages.home_routine_briefing_fc138c996e'), 'description' => __('messages.review_materials_and_next_session_goals_5a654f6f75'), 'leader' => __('messages.ari_jensen_6c670df410')],
            ],
            'pet-show' => [
                ['time' => __('messages.8_00_am_a123614e13'), 'title' => __('messages.exhibitor_check_in_44d0ece8ba'), 'description' => __('messages.qr_entry_and_private_document_review_at_hall_d_6a9d64faa7'), 'leader' => __('messages.registration_team_791d0ab289')],
                ['time' => __('messages.9_30_am_4d90d4ed3c'), 'title' => __('messages.morning_categories_999f5c70c5'), 'description' => __('messages.age_and_breed_categories_across_rings_1_3_c2de738a34'), 'leader' => __('messages.show_officials_c8655e762c')],
                ['time' => __('messages.1_00_pm_f82eb7519e'), 'title' => __('messages.companion_celebration_4a95930114'), 'description' => __('messages.inclusive_stories_adoption_journeys_and_friendly_parade_3b9dbd2583'), 'leader' => __('messages.community_hosts_edf3854270')],
                ['time' => __('messages.4_30_pm_b45f51d4de'), 'title' => __('messages.results_and_quiet_exit_c8c37e5df4'), 'description' => __('messages.results_publish_by_category_before_staggered_departure_626a3cf1b7'), 'leader' => __('messages.show_officials_c8655e762c')],
            ],
            'expert-webinar' => [
                ['time' => __('messages.5_45_pm_4730e652b0'), 'title' => __('messages.room_opens_40735d7f09'), 'description' => __('messages.test_audio_captions_and_question_submission_1f65175881'), 'leader' => __('messages.support_team_1afc0ab36f')],
                ['time' => __('messages.6_00_pm_6bc03202f6'), 'title' => __('messages.travel_preparation_73f97e35b5'), 'description' => __('messages.documents_carrier_routines_transport_and_planning_600434db2f'), 'leader' => __('messages.dr_elena_park_4db101e23c')],
                ['time' => __('messages.6_45_pm_da746f9b0c'), 'title' => __('messages.moderated_questions_997ca8a643'), 'description' => __('messages.general_education_without_individual_diagnosis_1847b7fca0'), 'leader' => __('messages.dr_elena_park_4db101e23c')],
                ['time' => __('messages.7_10_pm_ed5c4a72a5'), 'title' => __('messages.resources_and_recording_cee7c3b23b'), 'description' => __('messages.slides_citations_and_recording_access_explained_47f4343c01'), 'leader' => __('messages.support_team_1afc0ab36f')],
            ],
            'search-action' => [
                ['time' => __('messages.5_50_pm_9ce30d1280'), 'title' => __('messages.volunteer_check_in_258c88af37'), 'description' => __('messages.receive_a_zone_and_confirm_safe_sighting_instructions_422e47fc43'), 'leader' => __('messages.mia_carter_0e5b29cc3b')],
                ['time' => __('messages.6_00_pm_6bc03202f6'), 'title' => __('messages.search_starts_c3da39d17a'), 'description' => __('messages.teams_move_through_assigned_public_areas_167a00868d'), 'leader' => __('messages.zone_coordinators_6dfa3e2741')],
                ['time' => __('messages.7_15_pm_9b3ad047d3'), 'title' => __('messages.status_regroup_0e1cd741a4'), 'description' => __('messages.share_sightings_through_the_private_coordinator_channel_e7f400af2b'), 'leader' => __('messages.mia_carter_0e5b29cc3b')],
                ['time' => __('messages.9_00_pm_915881c885'), 'title' => __('messages.close_or_reassign_8575ac0a45'), 'description' => __('messages.checked_zones_are_recorded_before_volunteers_leave_69cd03bfa2'), 'leader' => __('messages.search_team_c81e115cc3')],
            ],
            default => [
                ['time' => __('messages.start_e4bb9f1ece'), 'title' => __('messages.arrival_and_check_in_51d44fbc8f'), 'description' => __('messages.confirm_registration_and_review_the_event_boundaries_b837a6a9c1'), 'leader' => $event['organizer']],
                ['time' => __('messages.main_eb814be3ca'), 'title' => __('messages.event_program_10c28155ae'), 'description' => __('messages.follow_the_published_event_plan_and_organizer_guidance_2dc1942638'), 'leader' => $event['organizer']],
                ['time' => __('messages.finish_a6c7a84baa'), 'title' => __('messages.closing_and_next_steps_9c66bdcec2'), 'description' => __('messages.collect_materials_feedback_and_any_follow_up_link_796d6fae59'), 'leader' => $event['organizer']],
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
                'badge' => $event['verification_label'] ?? __('messages.event_host_85e7071590'),
            ],
            [
                'name' => __('messages.noah_patel_147a9793ed'),
                'detail' => __('messages.registration_and_accessibility_66921b16a5'),
                'initials' => 'NP',
                'tone' => 'mint',
                'badge' => __('messages.co_organizer_6d5ba81786'),
            ],
            [
                'name' => __('messages.lena_brooks_ca42e74116'),
                'detail' => __('messages.chat_and_participant_support_133ece537e'),
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => __('messages.moderator_6748ec8b76'),
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
            ['name' => __('messages.ari_jensen_6c670df410'), 'detail' => __('messages.approved_arriving_with_mochi_bae16b2be5'), 'initials' => 'AJ', 'tone' => 'sun', 'badge' => __('messages.confirmed_fe00b67b6d')],
            ['name' => __('messages.noah_patel_147a9793ed'), 'detail' => __('messages.approved_accessibility_note_shared_privately_4a0ee2e6ae'), 'initials' => 'NP', 'tone' => 'mint', 'badge' => __('messages.confirmed_fe00b67b6d')],
            ['name' => __('messages.priya_shah_8925523814'), 'detail' => $event['format'] === 'online' ? __('messages.online_attendee_163063986e') : __('messages.first_time_at_this_event_036d6d82b0'), 'initials' => 'PS', 'tone' => 'paper', 'badge' => __('messages.confirmed_fe00b67b6d')],
            ['name' => __('messages.lena_brooks_ca42e74116'), 'detail' => __('messages.event_updates_enabled_b77b73c5ec'), 'initials' => 'LB', 'tone' => 'mint', 'badge' => __('messages.confirmed_fe00b67b6d')],
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
            ['name' => __('messages.mochi_95114c81f3'), 'detail' => __('messages.shiba_mix_calm_arrival_requested_5934b1095d'), 'initials' => 'MO', 'tone' => 'sun', 'badge' => __('messages.attending_a8efe09b47')],
            ['name' => __('messages.juniper_fe6a448ec9'), 'detail' => __('messages.golden_retriever_steady_pace_9336a95aaa'), 'initials' => 'JU', 'tone' => 'mint', 'badge' => __('messages.attending_a8efe09b47')],
            ['name' => __('messages.olive_3038ab334a'), 'detail' => __('messages.corgi_one_handler_8d2c5b6814'), 'initials' => 'OL', 'tone' => 'paper', 'badge' => __('messages.attending_a8efe09b47')],
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
                'title' => $event['format'] === 'online' ? __('messages.access_link_timing_41fc9bde17') : __('messages.meeting_point_stays_private_2b99d66bbd'),
                'body' => $event['format'] === 'online'
                    ? __('messages.the_webinar_room_link_appears_for_paid_attendees_fifteen_47dc683513')
                    : __('messages.approved_attendees_will_see_the_exact_entrance_public_ca_c06ebde237'),
                'time' => __('messages.today_9_20_am_ace23729ce'),
                'icon' => $event['format'] === 'online' ? 'video' : 'map-pin-check',
            ],
            [
                'title' => __('messages.bring_and_consent_checklist_updated_efcd46ef97'),
                'body' => __('messages.review_the_event_rules_photo_preference_and_item_checkli_42feb0004b'),
                'time' => __('messages.yesterday_4_15_pm_e73f66c348'),
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
                    ? __('messages.captions_and_the_text_transcript_will_be_available_in_th_38f80ce1c2')
                    : __('messages.please_arrive_with_enough_space_between_pets_the_host_wi_1a1b8ec8ba'),
                'time' => __('messages.9_28_am_76fb1e319f'),
            ],
            [
                'name' => __('messages.ari_jensen_6c670df410'),
                'initials' => 'AJ',
                'tone' => 'mint',
                'body' => __('messages.i_added_our_accessibility_note_privately_thanks_for_keep_8e96fb86d2'),
                'time' => __('messages.9_34_am_ebdf0a7a56'),
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
            'map_alt' => __('messages.text_map_showing_the_generalized_event_area_accessible_a_f7498fe8ef'),
            'details' => [
                ['label' => __('messages.arrival_794adbbc6c'), 'value' => $event['format'] === 'online' ? __('messages.join_from_a_modern_browser_with_audio_enabled_7048f35bd3') : __('messages.use_the_marked_public_entrance_after_registration_d1c6f64a6f')],
                ['label' => __('messages.accessibility_d3368cbffe'), 'value' => $event['format'] === 'online' ? __('messages.keyboard_navigation_and_captions_are_available_f4df230e6b') : __('messages.step_free_route_accessible_parking_seating_and_a_quiet_a_b5f1152c6a')],
                ['label' => __('messages.transport_aaead4abf5'), 'value' => $event['format'] === 'online' ? __('messages.no_travel_required_2bcb6a32ba') : __('messages.public_transit_and_parking_notes_appear_with_the_exact_l_6798a1cab3')],
                ['label' => __('messages.nearby_help_a50cd43945'), 'value' => $event['format'] === 'online' ? __('messages.technical_support_opens_fifteen_minutes_early_cd7ec8d5d5') : __('messages.24_hour_veterinary_clinic_details_are_available_to_atten_588f95fc15')],
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
            ['title' => __('messages.event_plan_e376e71284'), 'description' => __('messages.schedule_roles_and_important_contact_paths_e307382d36'), 'meta' => __('messages.pdf_updated_today_caa65d89f3'), 'icon' => 'file-text'],
            ['title' => __('messages.safety_and_accessibility_guide_306886884c'), 'description' => __('messages.arrival_quiet_area_emergency_and_consent_information_1d73d755b9'), 'meta' => __('messages.pdf_420_kb_c41450c5e2'), 'icon' => 'shield-check'],
            ['title' => $event['format'] === 'online' ? __('messages.slides_and_sources_4d3f1f06de') : __('messages.what_to_bring_3090fd37d1'), 'description' => $event['format'] === 'online' ? __('messages.available_after_the_webinar_02592350b0') : __('messages.compact_checklist_for_owners_and_pets_d19e340a5b'), 'meta' => __('messages.guide_current_version_8c86026eb8'), 'icon' => 'clipboard-list'],
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
                'caption' => __('messages.event_cover_selected_by_the_organizer_f28dcff8b3'),
            ],
            [
                'src' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=1200&h=900&q=85',
                'small' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=576&h=432&q=80',
                'medium' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=900&h=675&q=82',
                'alt' => __('messages.dog_resting_on_grass_during_a_calm_outdoor_gathering_0f9506bc5b'),
                'caption' => __('messages.a_quiet_zone_example_from_a_previous_community_event_4e75b38eda'),
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
            ? __('messages.keep_each_pet_supervised_and_follow_the_stated_leash_car_cfacaae7e1')
            : __('messages.attend_without_your_resident_pet_unless_the_organizer_ex_f19dd22a58');

        return [
            ['title' => __('messages.respect_the_participation_plan_9d315e0375'), 'description' => $petRule],
            ['title' => __('messages.protect_private_details_4c379e5475'), 'description' => __('messages.do_not_repost_exact_locations_online_links_participant_n_44cfa8ac3a')],
            ['title' => __('messages.use_calm_qualified_help_179b923e42'), 'description' => __('messages.end_an_activity_when_anyone_is_uncomfortable_and_contact_cbc4291a3b')],
            ['title' => __('messages.photography_is_opt_in_15bc83b1b7'), 'description' => __('messages.follow_photo_free_markers_and_request_approval_before_ta_8bb7d2f754')],
            ['title' => __('messages.commercial_activity_is_transparent_88694fe91f'), 'description' => __('messages.prices_sponsors_donations_refunds_and_organizer_responsi_7ffad2a432')],
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
                'title' => __('messages.public_first_contact_cdd20238a5'),
                'description' => $event['format'] === 'online'
                    ? __('messages.use_the_protected_event_room_and_do_not_share_private_co_2988ada495')
                    : __('messages.meet_at_the_published_public_entrance_and_keep_home_addr_6536ca6cdc'),
            ],
            [
                'icon' => 'stethoscope',
                'title' => __('messages.nearby_care_plan_7e3b4af2fb'),
                'description' => $event['format'] === 'online'
                    ? __('messages.the_webinar_is_educational_and_is_not_an_emergency_or_di_2565e285cb')
                    : __('messages.the_organizer_has_a_first_aid_location_and_the_nearest_2_b71d614374'),
            ],
            [
                'icon' => 'cloud-sun',
                'title' => __('messages.conditions_are_reviewed_a62b017696'),
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
            ['question' => __('messages.can_i_attend_without_a_pet_9474d77ebf'), 'answer' => $event['pets_allowed'] ? __('messages.yes_select_owner_only_attendance_during_registration_79ac4cec4e') : __('messages.yes_this_event_is_designed_for_owners_without_resident_p_71594e3067')],
            ['question' => __('messages.when_is_the_exact_location_available_e191b3c75f'), 'answer' => $event['format'] === 'online' ? __('messages.the_protected_link_appears_for_eligible_attendees_shortl_2faaa4654f') : __('messages.approved_or_confirmed_attendees_can_see_the_exact_entran_adba6fc6e5')],
            ['question' => __('messages.what_happens_if_plans_change_6e564d5bf2'), 'answer' => __('messages.material_date_time_place_price_or_organizer_changes_are__e8bdc0af94')],
            ['question' => __('messages.can_i_cancel_23d720c7ca'), 'answer' => $event['price_minor'] > 0 ? __('messages.yes_the_ticket_panel_shows_the_prototype_cancellation_an_43c7909cb6') : __('messages.yes_cancelling_releases_the_place_to_the_waitlist_8e485bf501')],
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
                'name' => __('messages.priya_shah_8925523814'),
                'initials' => 'PS',
                'tone' => 'mint',
                'rating' => '5',
                'title' => __('messages.the_description_matched_the_pace_11b81c7277'),
                'body' => __('messages.arrival_was_calm_the_organizer_stayed_reachable_and_the__d4751a8f07'),
                'meta' => __('messages.verified_attendee_previous_edition_559507dd43'),
            ],
            [
                'name' => __('messages.noah_patel_147a9793ed'),
                'initials' => 'NP',
                'tone' => 'paper',
                'rating' => '4',
                'title' => __('messages.clear_accessibility_information_181f69aac0'),
                'body' => __('messages.the_step_free_route_and_rest_area_were_easy_to_find_a_se_662b9f9967'),
                'meta' => __('messages.verified_attendee_previous_edition_559507dd43'),
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
                ['label' => __('messages.event_views_20aa9c80e6'), 'value' => (string) $views, 'detail' => __('messages.aggregate_views_c2a0cb7e32')],
                ['label' => __('messages.page_opens_4c6c9f0914'), 'value' => (string) $opened, 'detail' => __('messages.from_discovery_de91b5a064')],
                ['label' => __('messages.started_ecbc89cd37'), 'value' => (string) $started, 'detail' => __('messages.registration_attempts_cc751d305e')],
                ['label' => __('messages.confirmed_fe00b67b6d'), 'value' => (string) $completed, 'detail' => __('messages.current_places_117cbb5310')],
                ['label' => __('messages.attendance_4cecc70858'), 'value' => '86%', 'detail' => __('messages.previous_edition_2eaae57efc')],
                ['label' => __('messages.safety_726d11bd5b'), 'value' => '0', 'detail' => __('messages.open_incidents_73b28b13cc')],
            ],
            'funnel' => [
                ['label' => __('messages.saw_the_card_151635889a'), 'value' => $views, 'percent' => 100],
                ['label' => __('messages.opened_event_765835821c'), 'value' => $opened, 'percent' => (int) round(($opened / $views) * 100)],
                ['label' => __('messages.started_registration_b8e3d08d16'), 'value' => $started, 'percent' => (int) round(($started / $views) * 100)],
                ['label' => __('messages.confirmed_fe00b67b6d'), 'value' => $completed, 'percent' => (int) round(($completed / $views) * 100)],
            ],
            'privacy_note' => __('messages.only_aggregate_registration_attendance_and_feedback_data_c3c01e3783'),
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
                'name' => __('messages.ari_mochi_a7832e9cd0'),
                'detail' => __('messages.medium_dog_parallel_introduction_requested_c32bb2566f'),
                'initials' => 'AM',
                'tone' => 'sun',
                'status' => __('messages.pending_review_f1c45f3f13'),
            ],
            [
                'key' => 'noah-juniper',
                'name' => __('messages.noah_juniper_a398f9604e'),
                'detail' => __('messages.large_dog_calm_pace_ea25edace1'),
                'initials' => 'NJ',
                'tone' => 'mint',
                'status' => __('messages.pending_review_f1c45f3f13'),
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
                'name' => __('messages.lena_pip_75ea0610c8'),
                'detail' => __('messages.first_in_line_12_minutes_to_confirm_after_promotion_d9dc9344b3'),
                'initials' => 'LP',
                'tone' => 'paper',
                'status' => __('messages.waiting_6e293a8c00'),
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
                    'title' => __('messages.standard_place_7d66484f2d'),
                    'description' => $event['pets_allowed'] ? __('messages.one_owner_and_one_selected_pet_e5a89fb16e') : __('messages.one_registered_attendee_0fa066a857'),
                    'price_minor' => 0,
                    'currency' => $event['currency'],
                ],
            ];
        }

        return [
            [
                'key' => 'standard',
                'title' => $event['format'] === 'online' ? __('messages.live_webinar_be01c3db17') : __('messages.standard_ticket_3218abdc39'),
                'description' => $event['format'] === 'online' ? __('messages.live_access_recording_slides_and_cited_resources_3a4ba0d645') : __('messages.one_owner_and_one_selected_pet_e5a89fb16e'),
                'price_minor' => $event['price_minor'],
                'currency' => $event['currency'],
            ],
            [
                'key' => 'owner-only',
                'title' => __('messages.owner_only_55a834c80a'),
                'description' => __('messages.attend_without_a_pet_where_the_event_format_allows_it_ac43bfa81b'),
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
            ['label' => __('messages.review_the_event_rules_and_cancellation_terms_c34640c55b'), 'done' => true],
            ['label' => $event['pets_allowed'] ? __('messages.choose_the_attending_pet_profile_032a04eb62') : __('messages.confirm_owner_only_attendance_e6661a4972'), 'done' => false],
            ['label' => $event['format'] === 'online' ? __('messages.test_browser_audio_and_captions_560a87ebbf') : __('messages.save_the_arrival_and_accessibility_notes_9aa3451fb6'), 'done' => false],
            ['label' => $event['price_minor'] > 0 ? __('messages.complete_the_prototype_payment_reservation_d2edcc5151') : __('messages.confirm_the_free_place_2103d4f581'), 'done' => false],
        ];
    }
}
