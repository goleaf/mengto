<?php

namespace App\Services;

final class MessageCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function conversations(): array
    {
        return [
            'ari' => [
                'key' => 'ari',
                'type' => 'personal',
                'category' => 'friends',
                'name' => __('messages.ari_jensen_6c670df410'),
                'handle' => '@ari-and-mochi',
                'pet' => __('messages.mochi_and_scout_4428e07716'),
                'pet_names' => [__('messages.mochi_95114c81f3'), __('messages.scout_8a1db462be')],
                'purpose' => __('messages.calm_first_walk_5054034ce4'),
                'preview' => __('messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_out_41eb85053a'),
                'time' => '09:42',
                'datetime' => '2026-07-30T09:42:00+03:00',
                'unread' => 2,
                'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => __('messages.ari_with_mochi_in_a_park_87a9c8273e'),
                'verified' => __('messages.email_verified_bdfb1e4f00'),
                'presence' => __('messages.online_for_friends_0b654f716f'),
                'response' => __('messages.usually_replies_within_an_hour_e644fc7020'),
                'members' => 2,
                'privacy' => __('messages.accepted_personal_dialog_3aee716486'),
                'role' => __('messages.pet_friend_e4475305b9'),
                'channel' => 'general',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'family-care' => [
                'key' => 'family-care',
                'type' => 'family',
                'category' => 'family',
                'name' => __('messages.scout_and_nori_care_63abb46290'),
                'handle' => __('messages.carter_family_55c7ad6954'),
                'pet' => __('messages.scout_and_nori_679c003ce7'),
                'pet_names' => [__('messages.scout_8a1db462be'), __('messages.nori_a64203ba20')],
                'purpose' => __('messages.shared_care_log_8f0247e4fc'),
                'preview' => __('messages.medication_was_logged_at_08_15_evening_walk_still_needs__8e8e34e1a2'),
                'time' => '08:18',
                'datetime' => '2026-07-30T08:18:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1601758174114-e711c0cbaa69?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.a_dog_and_cat_resting_together_at_home_72ce4556f4'),
                'verified' => __('messages.family_managed_26a3e3f160'),
                'presence' => __('messages.3_managers_4ca3a0824a'),
                'response' => __('messages.care_alerts_bypass_muted_summaries_fb56392d4b'),
                'members' => 3,
                'privacy' => __('messages.family_only_0cf98a6be6'),
                'role' => __('messages.owner_4b1b8aa360'),
                'channel' => 'care-log',
                'request' => false,
                'professional' => false,
                'sensitive' => true,
            ],
            'vingis-walk' => [
                'key' => 'vingis-walk',
                'type' => 'event',
                'category' => 'events',
                'name' => __('messages.quiet_vingis_walk_9e6bc2f567'),
                'handle' => __('messages.event_chat_9c298662a4'),
                'pet' => __('messages.8_registered_pets_c1642c5848'),
                'pet_names' => [__('messages.scout_8a1db462be'), __('messages.mochi_95114c81f3'), __('messages.juniper_fe6a448ec9')],
                'purpose' => __('messages.temporary_coordination_ad0da009a2'),
                'preview' => __('messages.meeting_point_updated_use_the_lit_riverside_gate_not_the_970e9f7e2e'),
                'time' => __('messaging.relative.yesterday'),
                'datetime' => '2026-07-29T18:20:00+03:00',
                'unread' => 4,
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.dogs_walking_together_on_a_green_park_path_c28abeadfd'),
                'verified' => __('messages.organizer_confirmed_94ca2a0d81'),
                'presence' => __('messages.7_of_8_online_recently_e7f8243e36'),
                'response' => __('messages.archives_three_days_after_the_walk_98061970a8'),
                'members' => 8,
                'privacy' => __('messages.confirmed_attendees_only_45ec336cdd'),
                'role' => __('messages.organizer_715a9cc0c3'),
                'channel' => 'announcements',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'paws-vet' => [
                'key' => 'paws-vet',
                'type' => 'professional',
                'category' => 'specialists',
                'name' => __('messages.paws_24_veterinary_center_8fd860644c'),
                'handle' => __('messages.case_pc_1048_33eb9cdff4'),
                'pet' => __('messages.nori_a64203ba20'),
                'pet_names' => [__('messages.nori_a64203ba20')],
                'purpose' => __('messages.follow_up_consultation_af29b371b6'),
                'preview' => __('messages.dr_emilia_added_a_visit_summary_and_requested_one_photo__c0274c25cc'),
                'time' => __('messaging.relative.monday'),
                'datetime' => '2026-07-27T14:05:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.veterinary_clinician_in_a_bright_examination_room_69c8f6a126'),
                'verified' => __('messages.clinic_identity_and_license_checked_a421610b09'),
                'presence' => __('messages.replies_08_00_20_00_13f27c248f'),
                'response' => __('messages.not_an_emergency_channel_bd4c5b1031'),
                'members' => 3,
                'privacy' => __('messages.client_and_assigned_staff_7ef735fde8'),
                'role' => __('messages.client_0c77fe09ab'),
                'channel' => 'case',
                'request' => false,
                'professional' => true,
                'sensitive' => true,
            ],
            'foster-adoption' => [
                'key' => 'foster-adoption',
                'type' => 'organization',
                'category' => 'organizations',
                'name' => __('messages.vilnius_animal_aid_b14cd5f39b'),
                'handle' => __('messages.adoption_application_va_218_4dca9c7e16'),
                'pet' => __('messages.luna_9d77a24d0f'),
                'pet_names' => [__('messages.luna_9d77a24d0f')],
                'purpose' => __('messages.structured_adoption_review_ac8725aa00'),
                'preview' => __('messages.your_introduction_visit_is_held_for_saturday_the_shelter_4bc9152282'),
                'time' => __('messaging.relative.sunday'),
                'datetime' => '2026-07-26T12:10:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.rescued_dog_looking_calmly_toward_the_camera_694b441e62'),
                'verified' => __('messages.verified_shelter_52247645ed'),
                'presence' => __('messages.application_team_c0c02d6d32'),
                'response' => __('messages.identity_details_unlock_by_application_stage_e4a459609d'),
                'members' => 4,
                'privacy' => __('messages.applicant_and_shelter_team_ac5c526f16'),
                'role' => __('messages.applicant_0a1ce11526'),
                'channel' => 'application',
                'request' => false,
                'professional' => true,
                'sensitive' => true,
            ],
            'lost-luna' => [
                'key' => 'lost-luna',
                'type' => 'search',
                'category' => 'groups',
                'name' => __('messages.search_for_luna_bbbc6563df'),
                'handle' => __('messages.temporary_coordination_ad0da009a2'),
                'pet' => __('messages.luna_9d77a24d0f'),
                'pet_names' => [__('messages.luna_9d77a24d0f')],
                'purpose' => __('messages.lost_pet_search_28ed6a9c20'),
                'preview' => __('messages.sector_c_is_checked_a_new_sighting_near_the_tram_stop_ne_edf4917fa0'),
                'time' => __('messaging.relative.saturday'),
                'datetime' => '2026-07-25T22:48:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.golden_dog_standing_outside_5394149cbb'),
                'verified' => __('messages.owner_coordinated_5d5b5bf415'),
                'presence' => __('messages.14_volunteers_acb5e1a09b'),
                'response' => __('messages.location_shares_expire_automatically_fe636a60c3'),
                'members' => 14,
                'privacy' => __('messages.approved_search_volunteers_78e54463b7'),
                'role' => __('messages.coordinator_f00e33d162'),
                'channel' => 'sightings',
                'request' => false,
                'professional' => false,
                'sensitive' => true,
            ],
            'trail-tails' => [
                'key' => 'trail-tails',
                'type' => 'group',
                'category' => 'groups',
                'name' => __('messages.trail_tails_8c13c56b9f'),
                'handle' => __('messages.community_chat_ab9832db31'),
                'pet' => __('messages.1_284_linked_pets_d547f22e8f'),
                'pet_names' => [__('messages.scout_8a1db462be')],
                'purpose' => __('messages.routes_and_outdoor_safety_90825102e0'),
                'preview' => __('messages.the_north_loop_is_muddy_after_rain_photos_are_in_the_rou_c7b9f20d3a'),
                'time' => __('messaging.relative.friday'),
                'datetime' => '2026-07-24T17:30:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messages.green_mountain_trail_under_daylight_582e38d01b'),
                'verified' => __('messages.community_moderated_7ce6d26f6f'),
                'presence' => __('messages.286_active_this_week_256c693854'),
                'response' => __('messages.slow_mode_for_new_members_2c2f6050dc'),
                'members' => 1284,
                'privacy' => __('messages.group_members_dd0fd917e7'),
                'role' => __('messages.member_7c968fb71f'),
                'channel' => 'routes',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'luna-request' => [
                'key' => 'luna-request',
                'type' => 'request',
                'category' => 'requests',
                'name' => __('messages.elena_and_luna_f0b13a786c'),
                'handle' => __('messages.new_message_request_fccaa57776'),
                'pet' => __('messages.luna_labrador_mix_71d927bc50'),
                'pet_names' => [__('messages.luna_9d77a24d0f'), __('messages.scout_8a1db462be')],
                'purpose' => __('messages.walk_invitation_d9327b13db'),
                'preview' => __('messages.hi_our_dogs_are_a_similar_age_would_a_calm_parallel_walk_edbf5e9fde'),
                'time' => __('messaging.relative.today'),
                'datetime' => '2026-07-30T07:55:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => __('messages.elena_smiling_outdoors_1a90d3e720'),
                'verified' => __('messages.email_verified_bdfb1e4f00'),
                'presence' => __('messages.read_status_hidden_for_requests_01e0a0b4b4'),
                'response' => __('messages.one_preview_message_allowed_b365d3f662'),
                'members' => 2,
                'privacy' => __('messages.request_preview_only_091ea165f6'),
                'role' => __('messages.recipient_51fac985e9'),
                'channel' => 'request',
                'request' => true,
                'professional' => false,
                'sensitive' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(string $conversation): array
    {
        return $this->messageSets()[$conversation] ?? [];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function messageSets(): array
    {
        return [
            'ari' => [
                $this->message('ari-1', __('messages.ari_jensen_6c670df410'), '09:18', __('messages.i_am_writing_as_mochi_s_person_a_parallel_walk_would_be__0bf36cc979'), false, 'text'),
                $this->message('ari-2', __('messages.mia_carter_0e5b29cc3b'), '09:25', __('messages.that_works_for_scout_let_s_use_a_public_park_and_keep_a__ee492efc08'), true, 'text', reply: __('messages.a_parallel_walk_would_be_easiest_728e96af56')),
                $this->message('ari-3', __('messages.ari_jensen_6c670df410'), '09:42', __('messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_out_41eb85053a'), false, 'place', meta: __('messages.vingis_quiet_loop_lit_riverside_gate_2_4_km_8d2c3fe87c')),
                $this->message('ari-4', __('messages.ari_jensen_6c670df410'), '09:43', __('messages.voice_note_calm_introduction_plan_6193ae70bc'), false, 'audio', meta: __('messages.0_32_transcript_available_c1ffa43eff')),
            ],
            'family-care' => [
                $this->message('family-1', __('messages.mia_carter_0e5b29cc3b'), '08:15', __('messages.scout_received_the_prescribed_morning_medication_c7655dbbf8'), true, 'task', meta: __('messages.medication_completed_by_mia_08_15_b7b43ad9f2')),
                $this->message('family-2', __('messages.alex_carter_805f38f620'), '08:16', __('messages.i_was_about_to_mark_this_too_the_duplicate_warning_worke_c57923e7e5'), false, 'text'),
                $this->message('family-3', __('messages.care_summary_02a35e092e'), '08:18', __('messages.today_two_feedings_one_walk_medication_completed_food_ne_c8cfcddae8'), false, 'system', meta: __('messages.private_family_digest_faa8483bbb')),
            ],
            'vingis-walk' => [
                $this->message('walk-1', __('messages.organizer_mia_ce26fa7776'), __('messages.yesterday_566181254b'), __('messages.meeting_point_updated_use_the_lit_riverside_gate_not_the_970e9f7e2e'), true, 'announcement', meta: __('messages.important_acknowledged_by_6_2935d4801f')),
                $this->message('walk-2', __('messages.noah_patel_147a9793ed'), __('messages.yesterday_566181254b'), __('messages.i_will_be_about_ten_minutes_late_6e71166010'), false, 'status', meta: __('messages.travel_status_running_late_67db55b74f')),
                $this->message('walk-3', __('messages.event_details_f9c7f3d828'), __('messages.yesterday_566181254b'), __('messages.quiet_vingis_walk_9e6bc2f567'), false, 'event', meta: __('messages.saturday_10_00_8_pets_leash_required_7ca5083190')),
            ],
            'paws-vet' => [
                $this->message('vet-1', __('messages.clinic_assistant_5e1dec1ed1'), __('messages.mon_f40d7f51f6'), __('messages.this_chat_is_monitored_during_working_hours_for_urgent_s_d3ce493f1d'), false, 'warning', meta: __('messages.not_an_emergency_service_4bfaa6bb36')),
                $this->message('vet-2', __('messages.mia_carter_0e5b29cc3b'), __('messages.mon_f40d7f51f6'), __('messages.nori_is_eating_normally_i_am_sharing_only_the_discharge__6500cc4c66'), true, 'file', meta: __('messages.nori_discharge_summary_pdf_access_until_aug_7_c8e74624f8')),
                $this->message('vet-3', __('messages.dr_emilia_vaitke_a0f21f8b96'), __('messages.mon_f40d7f51f6'), __('messages.please_add_one_clear_photo_before_friday_video_alone_may_956055971a'), false, 'professional', meta: __('messages.verified_veterinarian_lithuania_answered_jul_27_9109dc7aeb')),
                $this->message('vet-4', __('messages.consultation_537a956d9f'), __('messages.mon_f40d7f51f6'), __('messages.video_follow_up_18_minutes_recording_disabled_b50a08422b'), false, 'call', meta: __('messages.visit_summary_confirmed_by_specialist_93b8d57a42')),
            ],
            'foster-adoption' => [
                $this->message('adopt-1', __('messages.vilnius_animal_aid_b14cd5f39b'), __('messages.sun_db18f17fe5'), __('messages.your_application_passed_the_first_review_private_contact_95436018ef'), false, 'professional', meta: __('messages.application_va_218_stage_2_of_4_0ff55b6119')),
                $this->message('adopt-2', __('messages.mia_carter_0e5b29cc3b'), __('messages.sun_db18f17fe5'), __('messages.saturday_works_scout_will_stay_home_for_the_first_introd_a69592f65b'), true, 'text'),
                $this->message('adopt-3', __('messages.visit_request_1530f6fadc'), __('messages.sun_db18f17fe5'), __('messages.meet_luna_at_the_shelter_df29269f39'), false, 'event', meta: __('messages.saturday_11_30_exact_entrance_after_confirmation_968159a98b')),
            ],
            'lost-luna' => [
                $this->message('lost-1', __('messages.search_coordinator_22794c3cc9'), __('messages.sat_fdeb71b569'), __('messages.sector_c_is_checked_do_not_chase_luna_if_seen_add_a_sigh_2c9931cbc7'), false, 'announcement', meta: __('messages.emergency_channel_approved_volunteers_cea79dbeef')),
                $this->message('lost-2', __('messages.tomas_r_8fcf7ac3c7'), __('messages.sat_fdeb71b569'), __('messages.possible_sighting_by_the_tram_stop_at_22_41_photo_attach_50fb376779'), false, 'image', meta: __('messages.approximate_area_only_awaiting_verification_3a17da005c')),
                $this->message('lost-3', __('messages.search_map_c802781b2d'), __('messages.sat_fdeb71b569'), __('messages.4_of_7_sectors_checked_c3c466e04d'), false, 'task', meta: __('messages.temporary_locations_expire_when_search_closes_55f596b3ea')),
            ],
            'trail-tails' => [
                $this->message('trail-1', __('messages.moderator_noah_6cc539c1d8'), __('messages.fri_66dab40cea'), __('messages.north_loop_conditions_are_now_in_the_route_thread_new_me_c24fa13ee4'), false, 'announcement', meta: __('messages.routes_pinned_18eef7c4e1')),
                $this->message('trail-2', __('messages.lena_brooks_ca42e74116'), __('messages.fri_66dab40cea'), __('messages.the_first_kilometre_is_muddy_but_the_shorter_return_path_b890eac917'), false, 'text', reply: __('messages.north_loop_conditions_90dc899d8a')),
                $this->message('trail-3', __('messages.route_report_8b46a6ecaf'), __('messages.fri_66dab40cea'), __('messages.north_loop_after_rain_4779126911'), false, 'video', meta: __('messages.0_41_captions_available_sensitive_location_removed_cddf791e02')),
            ],
            'luna-request' => [
                $this->message('request-1', __('messages.elena_markova_3c7fcf61af'), '07:55', __('messages.hi_our_dogs_are_a_similar_age_would_a_calm_parallel_walk_edbf5e9fde'), false, 'text', meta: __('messages.reason_walk_invitation_da16337290')),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function message(
        string $id,
        string $sender,
        string $time,
        string $body,
        bool $mine,
        string $type,
        ?string $meta = null,
        ?string $reply = null,
    ): array {
        return [
            'id' => $id,
            'sender' => $sender,
            'time' => $time,
            'datetime' => '2026-07-30T09:00:00+03:00',
            'body' => $body,
            'mine' => $mine,
            'type' => $type,
            'meta' => $meta,
            'reply' => $reply,
            'edited' => false,
            'status' => $mine ? __('messages.read_9b9a8d05a7') : __('messages.delivered_9061156573'),
            'status_code' => $mine ? 'read' : 'delivered',
            'reactions' => [],
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function channels(): array
    {
        return [
            'vingis-walk' => [
                ['key' => 'announcements', 'label' => __('messages.announcements_fe02680f24'), 'icon' => 'megaphone', 'count' => 1],
                ['key' => 'general', 'label' => __('messages.general_c910d474dc'), 'icon' => 'messages-square', 'count' => 3],
                ['key' => 'transport', 'label' => __('messages.transport_aaead4abf5'), 'icon' => 'car-front', 'count' => 0],
                ['key' => 'photos', 'label' => __('messages.photos_5e3147ab51'), 'icon' => 'images', 'count' => 0],
            ],
            'lost-luna' => [
                ['key' => 'announcements', 'label' => __('messages.updates_22e2bada8f'), 'icon' => 'megaphone', 'count' => 2],
                ['key' => 'sightings', 'label' => __('messages.sightings_4906ba1ea4'), 'icon' => 'map-pin', 'count' => 1],
                ['key' => 'tasks', 'label' => __('messages.search_zones_e78a55f2a2'), 'icon' => 'list-checks', 'count' => 4],
            ],
            'trail-tails' => [
                ['key' => 'general', 'label' => __('messages.general_c910d474dc'), 'icon' => 'messages-square', 'count' => 6],
                ['key' => 'routes', 'label' => __('messages.routes_2c5b669bcd'), 'icon' => 'route', 'count' => 2],
                ['key' => 'safety', 'label' => __('messages.safety_726d11bd5b'), 'icon' => 'shield-alert', 'count' => 0],
                ['key' => 'photos', 'label' => __('messages.photos_5e3147ab51'), 'icon' => 'images', 'count' => 0],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function members(): array
    {
        return [
            'family-care' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.owner_4b1b8aa360'), 'pet' => __('messages.scout_and_nori_679c003ce7')],
                ['name' => __('messages.alex_carter_805f38f620'), 'role' => __('messages.co_owner_f3027e079c'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.sam_carter_0f1549ad7e'), 'role' => __('messages.family_member_98d2e3a335'), 'pet' => __('messages.nori_a64203ba20')],
            ],
            'vingis-walk' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.organizer_715a9cc0c3'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.ari_jensen_6c670df410'), 'role' => __('messages.participant_554f423511'), 'pet' => __('messages.mochi_95114c81f3')],
                ['name' => __('messages.noah_patel_147a9793ed'), 'role' => __('messages.participant_554f423511'), 'pet' => __('messages.juniper_fe6a448ec9')],
            ],
            'paws-vet' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.client_0c77fe09ab'), 'pet' => __('messages.nori_a64203ba20')],
                ['name' => __('messages.dr_emilia_vaitke_a0f21f8b96'), 'role' => __('messages.verified_veterinarian_30ade68387'), 'pet' => __('messages.assigned_specialist_405b062664')],
                ['name' => __('messages.clinic_assistant_5e1dec1ed1'), 'role' => __('messages.case_coordinator_b2a741e9b8'), 'pet' => __('messages.paws_24_28ba7c3aa0')],
            ],
            'lost-luna' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.coordinator_f00e33d162'), 'pet' => __('messages.luna_9d77a24d0f')],
                ['name' => __('messages.tomas_r_8fcf7ac3c7'), 'role' => __('messages.volunteer_ef4683c3dc'), 'pet' => __('messages.sector_c_dbbbc53070')],
                ['name' => __('messages.ari_jensen_6c670df410'), 'role' => __('messages.moderator_6748ec8b76'), 'pet' => __('messages.search_map_c802781b2d')],
            ],
            'trail-tails' => [
                ['name' => __('messages.noah_patel_147a9793ed'), 'role' => __('messages.moderator_6748ec8b76'), 'pet' => __('messages.juniper_fe6a448ec9')],
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.member_7c968fb71f'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.lena_brooks_ca42e74116'), 'role' => __('messages.member_7c968fb71f'), 'pet' => __('messages.pip_cf64881060')],
            ],
        ];
    }
}
