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
                'purpose' => __('messaging.conversations.ari.purpose'),
                'preview' => __('messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_out_41eb85053a'),
                'time' => '09:42',
                'datetime' => '2026-07-30T09:42:00+03:00',
                'unread' => 2,
                'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.ari.avatar_alt'),
                'verified' => __('messaging.conversations.ari.verified'),
                'presence' => __('messaging.conversations.ari.presence'),
                'response' => __('messaging.conversations.ari.response'),
                'members' => 2,
                'privacy' => __('messaging.conversations.ari.privacy'),
                'role' => __('messaging.conversations.ari.role'),
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
                'purpose' => __('messaging.conversations.family_care.purpose'),
                'preview' => __('messages.medication_was_logged_at_08_15_evening_walk_still_needs__8e8e34e1a2'),
                'time' => '08:18',
                'datetime' => '2026-07-30T08:18:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1601758174114-e711c0cbaa69?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.family_care.avatar_alt'),
                'verified' => __('messaging.conversations.family_care.verified'),
                'presence' => __('messaging.conversations.family_care.presence'),
                'response' => __('messaging.conversations.family_care.response'),
                'members' => 3,
                'privacy' => __('messaging.conversations.family_care.privacy'),
                'role' => __('messaging.conversations.family_care.role'),
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
                'purpose' => __('messaging.conversations.vingis_walk.purpose'),
                'preview' => __('messages.meeting_point_updated_use_the_lit_riverside_gate_not_the_970e9f7e2e'),
                'time' => __('messaging.relative.yesterday'),
                'datetime' => '2026-07-29T18:20:00+03:00',
                'unread' => 4,
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.vingis_walk.avatar_alt'),
                'verified' => __('messaging.conversations.vingis_walk.verified'),
                'presence' => __('messaging.conversations.vingis_walk.presence'),
                'response' => __('messaging.conversations.vingis_walk.response'),
                'members' => 8,
                'privacy' => __('messaging.conversations.vingis_walk.privacy'),
                'role' => __('messaging.conversations.vingis_walk.role'),
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
                'purpose' => __('messaging.conversations.paws_vet.purpose'),
                'preview' => __('messages.dr_emilia_added_a_visit_summary_and_requested_one_photo__c0274c25cc'),
                'time' => __('messaging.relative.monday'),
                'datetime' => '2026-07-27T14:05:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.paws_vet.avatar_alt'),
                'verified' => __('messaging.conversations.paws_vet.verified'),
                'presence' => __('messaging.conversations.paws_vet.presence'),
                'response' => __('messaging.conversations.paws_vet.response'),
                'members' => 3,
                'privacy' => __('messaging.conversations.paws_vet.privacy'),
                'role' => __('messaging.conversations.paws_vet.role'),
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
                'purpose' => __('messaging.conversations.foster_adoption.purpose'),
                'preview' => __('messages.your_introduction_visit_is_held_for_saturday_the_shelter_4bc9152282'),
                'time' => __('messaging.relative.sunday'),
                'datetime' => '2026-07-26T12:10:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.foster_adoption.avatar_alt'),
                'verified' => __('messaging.conversations.foster_adoption.verified'),
                'presence' => __('messaging.conversations.foster_adoption.presence'),
                'response' => __('messaging.conversations.foster_adoption.response'),
                'members' => 4,
                'privacy' => __('messaging.conversations.foster_adoption.privacy'),
                'role' => __('messaging.conversations.foster_adoption.role'),
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
                'purpose' => __('messaging.conversations.lost_luna.purpose'),
                'preview' => __('messages.sector_c_is_checked_a_new_sighting_near_the_tram_stop_ne_edf4917fa0'),
                'time' => __('messaging.relative.saturday'),
                'datetime' => '2026-07-25T22:48:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.lost_luna.avatar_alt'),
                'verified' => __('messaging.conversations.lost_luna.verified'),
                'presence' => __('messaging.conversations.lost_luna.presence'),
                'response' => __('messaging.conversations.lost_luna.response'),
                'members' => 14,
                'privacy' => __('messaging.conversations.lost_luna.privacy'),
                'role' => __('messaging.conversations.lost_luna.role'),
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
                'purpose' => __('messaging.conversations.trail_tails.purpose'),
                'preview' => __('messages.the_north_loop_is_muddy_after_rain_photos_are_in_the_rou_c7b9f20d3a'),
                'time' => __('messaging.relative.friday'),
                'datetime' => '2026-07-24T17:30:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.trail_tails.avatar_alt'),
                'verified' => __('messaging.conversations.trail_tails.verified'),
                'presence' => __('messaging.conversations.trail_tails.presence'),
                'response' => __('messaging.conversations.trail_tails.response'),
                'members' => 1284,
                'privacy' => __('messaging.conversations.trail_tails.privacy'),
                'role' => __('messaging.conversations.trail_tails.role'),
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
                'purpose' => __('messaging.conversations.luna_request.purpose'),
                'preview' => __('messages.hi_our_dogs_are_a_similar_age_would_a_calm_parallel_walk_edbf5e9fde'),
                'time' => __('messaging.relative.today'),
                'datetime' => '2026-07-30T07:55:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => __('messaging.conversations.luna_request.avatar_alt'),
                'verified' => __('messaging.conversations.luna_request.verified'),
                'presence' => __('messaging.conversations.luna_request.presence'),
                'response' => __('messaging.conversations.luna_request.response'),
                'members' => 2,
                'privacy' => __('messaging.conversations.luna_request.privacy'),
                'role' => __('messaging.conversations.luna_request.role'),
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
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messaging.context.roles.owner'), 'pet' => __('messages.scout_and_nori_679c003ce7')],
                ['name' => __('messages.alex_carter_805f38f620'), 'role' => __('messaging.context.roles.co_owner'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.sam_carter_0f1549ad7e'), 'role' => __('messaging.context.roles.family_member'), 'pet' => __('messages.nori_a64203ba20')],
            ],
            'vingis-walk' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messaging.context.roles.organizer'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.ari_jensen_6c670df410'), 'role' => __('messaging.context.roles.participant'), 'pet' => __('messages.mochi_95114c81f3')],
                ['name' => __('messages.noah_patel_147a9793ed'), 'role' => __('messaging.context.roles.participant'), 'pet' => __('messages.juniper_fe6a448ec9')],
            ],
            'paws-vet' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messaging.context.roles.client'), 'pet' => __('messages.nori_a64203ba20')],
                ['name' => __('messages.dr_emilia_vaitke_a0f21f8b96'), 'role' => __('messaging.context.roles.veterinarian'), 'pet' => __('messages.assigned_specialist_405b062664')],
                ['name' => __('messages.clinic_assistant_5e1dec1ed1'), 'role' => __('messaging.context.roles.case_coordinator'), 'pet' => __('messages.paws_24_28ba7c3aa0')],
            ],
            'lost-luna' => [
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messaging.context.roles.coordinator'), 'pet' => __('messages.luna_9d77a24d0f')],
                ['name' => __('messages.tomas_r_8fcf7ac3c7'), 'role' => __('messaging.context.roles.volunteer'), 'pet' => __('messages.sector_c_dbbbc53070')],
                ['name' => __('messages.ari_jensen_6c670df410'), 'role' => __('messaging.context.roles.moderator'), 'pet' => __('messages.search_map_c802781b2d')],
            ],
            'trail-tails' => [
                ['name' => __('messages.noah_patel_147a9793ed'), 'role' => __('messaging.context.roles.moderator'), 'pet' => __('messages.juniper_fe6a448ec9')],
                ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messaging.context.roles.member'), 'pet' => __('messages.scout_8a1db462be')],
                ['name' => __('messages.lena_brooks_ca42e74116'), 'role' => __('messaging.context.roles.member'), 'pet' => __('messages.pip_cf64881060')],
            ],
        ];
    }
}
