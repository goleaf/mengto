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
                'name' => __('messages.ari_jensen'),
                'handle' => '@ari-and-mochi',
                'pet' => __('messages.mochi_and_scout'),
                'pet_names' => [__('messages.mochi'), __('messages.scout')],
                'purpose' => __('messaging.conversations.ari.purpose'),
                'preview' => __('messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_outside_lane'),
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
                'name' => __('messages.scout_and_nori_care'),
                'handle' => __('messages.carter_family'),
                'pet' => __('messages.scout_and_nori'),
                'pet_names' => [__('messages.scout'), __('messages.nori')],
                'purpose' => __('messaging.conversations.family_care.purpose'),
                'preview' => __('messages.medication_was_logged_at_08_15_evening_walk_still_needs_an_owner'),
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
                'name' => __('messages.quiet_vingis_walk'),
                'handle' => __('messages.event_chat'),
                'pet' => __('messages.8_registered_pets'),
                'pet_names' => [__('messages.scout'), __('messages.mochi'), __('messages.juniper')],
                'purpose' => __('messaging.conversations.vingis_walk.purpose'),
                'preview' => __('messages.meeting_point_updated_use_the_lit_riverside_gate_not_the_car_park'),
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
                'name' => __('messages.paws_24_veterinary_center'),
                'handle' => __('messages.case_pc_1048'),
                'pet' => __('messages.nori'),
                'pet_names' => [__('messages.nori')],
                'purpose' => __('messaging.conversations.paws_vet.purpose'),
                'preview' => __('messages.dr_emilia_added_a_visit_summary_and_requested_one_photo_before_friday'),
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
                'name' => __('messages.vilnius_animal_aid'),
                'handle' => __('messages.adoption_application_va_218'),
                'pet' => __('messages.luna'),
                'pet_names' => [__('messages.luna')],
                'purpose' => __('messaging.conversations.foster_adoption.purpose'),
                'preview' => __('messages.your_introduction_visit_is_held_for_saturday_the_shelter_address_stays_private_until_confirmation'),
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
                'name' => __('messages.search_for_luna'),
                'handle' => __('messages.temporary_coordination'),
                'pet' => __('messages.luna'),
                'pet_names' => [__('messages.luna')],
                'purpose' => __('messaging.conversations.lost_luna.purpose'),
                'preview' => __('messages.sector_c_is_checked_a_new_sighting_near_the_tram_stop_needs_verification'),
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
                'name' => __('messages.trail_tails'),
                'handle' => __('messages.community_chat'),
                'pet' => __('messages.1_284_linked_pets'),
                'pet_names' => [__('messages.scout')],
                'purpose' => __('messaging.conversations.trail_tails.purpose'),
                'preview' => __('messages.the_north_loop_is_muddy_after_rain_photos_are_in_the_route_thread'),
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
                'name' => __('messages.elena_and_luna'),
                'handle' => __('messages.new_message_request'),
                'pet' => __('messages.luna_labrador_mix'),
                'pet_names' => [__('messages.luna'), __('messages.scout')],
                'purpose' => __('messaging.conversations.luna_request.purpose'),
                'preview' => __('messages.hi_our_dogs_are_a_similar_age_would_a_calm_parallel_walk_suit_scout'),
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
                $this->message('ari-1', __('messages.ari_jensen'), '09:18', __('messages.i_am_writing_as_mochi_s_person_a_parallel_walk_would_be_easiest_for_our_first_hello'), false, 'text'),
                $this->message('ari-2', __('messages.mia_carter'), '09:25', __('messages.that_works_for_scout_let_s_use_a_public_park_and_keep_a_comfortable_distance'), true, 'text', reply: __('messages.a_parallel_walk_would_be_easiest')),
                $this->message('ari-3', __('messages.ari_jensen'), '09:42', __('messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_outside_lane'), false, 'place', meta: __('messages.vingis_quiet_loop_lit_riverside_gate_2_4_km')),
                $this->message('ari-4', __('messages.ari_jensen'), '09:43', __('messages.voice_note_calm_introduction_plan'), false, 'audio', meta: __('messages.0_32_transcript_available')),
            ],
            'family-care' => [
                $this->message('family-1', __('messages.mia_carter'), '08:15', __('messages.scout_received_the_prescribed_morning_medication'), true, 'task', meta: __('messages.medication_completed_by_mia_08_15')),
                $this->message('family-2', __('messages.alex_carter'), '08:16', __('messages.i_was_about_to_mark_this_too_the_duplicate_warning_worked'), false, 'text'),
                $this->message('family-3', __('messages.care_summary'), '08:18', __('messages.today_two_feedings_one_walk_medication_completed_food_needs_restocking'), false, 'system', meta: __('messages.private_family_digest')),
            ],
            'vingis-walk' => [
                $this->message('walk-1', __('messages.organizer_mia'), __('messages.yesterday'), __('messages.meeting_point_updated_use_the_lit_riverside_gate_not_the_car_park'), true, 'announcement', meta: __('messages.important_acknowledged_by_6')),
                $this->message('walk-2', __('messages.noah_patel'), __('messages.yesterday'), __('messages.i_will_be_about_ten_minutes_late'), false, 'status', meta: __('messages.travel_status_running_late')),
                $this->message('walk-3', __('messages.event_details'), __('messages.yesterday'), __('messages.quiet_vingis_walk'), false, 'event', meta: __('messages.saturday_10_00_8_pets_leash_required')),
            ],
            'paws-vet' => [
                $this->message('vet-1', __('messages.clinic_assistant'), __('messages.mon'), __('messages.this_chat_is_monitored_during_working_hours_for_urgent_symptoms_call_an_emergency_clinic'), false, 'warning', meta: __('messages.not_an_emergency_service')),
                $this->message('vet-2', __('messages.mia_carter'), __('messages.mon'), __('messages.nori_is_eating_normally_i_am_sharing_only_the_discharge_summary_for_this_follow_up'), true, 'file', meta: __('messages.nori_discharge_summary_pdf_access_until_aug_7')),
                $this->message('vet-3', __('messages.dr_emilia_vaitke'), __('messages.mon'), __('messages.please_add_one_clear_photo_before_friday_video_alone_may_not_be_enough_for_a_clinical_conclusion'), false, 'professional', meta: __('messages.verified_veterinarian_lithuania_answered_jul_27')),
                $this->message('vet-4', __('messages.consultation'), __('messages.mon'), __('messages.video_follow_up_18_minutes_recording_disabled'), false, 'call', meta: __('messages.visit_summary_confirmed_by_specialist')),
            ],
            'foster-adoption' => [
                $this->message('adopt-1', __('messages.vilnius_animal_aid'), __('messages.sun'), __('messages.your_application_passed_the_first_review_private_contact_details_remain_hidden_until_the_visit_is_confirmed'), false, 'professional', meta: __('messages.application_va_218_stage_2_of_4')),
                $this->message('adopt-2', __('messages.mia_carter'), __('messages.sun'), __('messages.saturday_works_scout_will_stay_home_for_the_first_introduction'), true, 'text'),
                $this->message('adopt-3', __('messages.visit_request'), __('messages.sun'), __('messages.meet_luna_at_the_shelter'), false, 'event', meta: __('messages.saturday_11_30_exact_entrance_after_confirmation')),
            ],
            'lost-luna' => [
                $this->message('lost-1', __('messages.search_coordinator'), __('messages.sat'), __('messages.sector_c_is_checked_do_not_chase_luna_if_seen_add_a_sighting_and_call_the_coordinator'), false, 'announcement', meta: __('messages.emergency_channel_approved_volunteers')),
                $this->message('lost-2', __('messages.tomas_r'), __('messages.sat'), __('messages.possible_sighting_by_the_tram_stop_at_22_41_photo_attached_for_verification'), false, 'image', meta: __('messages.approximate_area_only_awaiting_verification')),
                $this->message('lost-3', __('messages.search_map'), __('messages.sat'), __('messages.4_of_7_sectors_checked'), false, 'task', meta: __('messages.temporary_locations_expire_when_search_closes')),
            ],
            'trail-tails' => [
                $this->message('trail-1', __('messages.moderator_noah'), __('messages.fri'), __('messages.north_loop_conditions_are_now_in_the_route_thread_new_media_is_limited_during_slow_mode'), false, 'announcement', meta: __('messages.routes_pinned')),
                $this->message('trail-2', __('messages.lena_brooks'), __('messages.fri'), __('messages.the_first_kilometre_is_muddy_but_the_shorter_return_path_is_dry'), false, 'text', reply: __('messages.north_loop_conditions')),
                $this->message('trail-3', __('messages.route_report'), __('messages.fri'), __('messages.north_loop_after_rain'), false, 'video', meta: __('messages.0_41_captions_available_sensitive_location_removed')),
            ],
            'luna-request' => [
                $this->message('request-1', __('messages.elena_markova'), '07:55', __('messages.hi_our_dogs_are_a_similar_age_would_a_calm_parallel_walk_suit_scout'), false, 'text', meta: __('messages.reason_walk_invitation')),
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
            'status' => $mine ? __('messages.read') : __('messages.delivered'),
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
                ['key' => 'announcements', 'label' => __('messages.announcements'), 'icon' => 'megaphone', 'count' => 1],
                ['key' => 'general', 'label' => __('messages.general'), 'icon' => 'messages-square', 'count' => 3],
                ['key' => 'transport', 'label' => __('messages.transport'), 'icon' => 'car-front', 'count' => 0],
                ['key' => 'photos', 'label' => __('messages.photos'), 'icon' => 'images', 'count' => 0],
            ],
            'lost-luna' => [
                ['key' => 'announcements', 'label' => __('messages.updates'), 'icon' => 'megaphone', 'count' => 2],
                ['key' => 'sightings', 'label' => __('messages.sightings'), 'icon' => 'map-pin', 'count' => 1],
                ['key' => 'tasks', 'label' => __('messages.search_zones'), 'icon' => 'list-checks', 'count' => 4],
            ],
            'trail-tails' => [
                ['key' => 'general', 'label' => __('messages.general'), 'icon' => 'messages-square', 'count' => 6],
                ['key' => 'routes', 'label' => __('messages.routes'), 'icon' => 'route', 'count' => 2],
                ['key' => 'safety', 'label' => __('messages.safety'), 'icon' => 'shield-alert', 'count' => 0],
                ['key' => 'photos', 'label' => __('messages.photos'), 'icon' => 'images', 'count' => 0],
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
                ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.owner'), 'pet' => __('messages.scout_and_nori')],
                ['name' => __('messages.alex_carter'), 'role' => __('messaging.context.roles.co_owner'), 'pet' => __('messages.scout')],
                ['name' => __('messages.sam_carter'), 'role' => __('messaging.context.roles.family_member'), 'pet' => __('messages.nori')],
            ],
            'vingis-walk' => [
                ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.organizer'), 'pet' => __('messages.scout')],
                ['name' => __('messages.ari_jensen'), 'role' => __('messaging.context.roles.participant'), 'pet' => __('messages.mochi')],
                ['name' => __('messages.noah_patel'), 'role' => __('messaging.context.roles.participant'), 'pet' => __('messages.juniper')],
            ],
            'paws-vet' => [
                ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.client'), 'pet' => __('messages.nori')],
                ['name' => __('messages.dr_emilia_vaitke'), 'role' => __('messaging.context.roles.veterinarian'), 'pet' => __('messages.assigned_specialist')],
                ['name' => __('messages.clinic_assistant'), 'role' => __('messaging.context.roles.case_coordinator'), 'pet' => __('messages.paws_24')],
            ],
            'lost-luna' => [
                ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.coordinator'), 'pet' => __('messages.luna')],
                ['name' => __('messages.tomas_r'), 'role' => __('messaging.context.roles.volunteer'), 'pet' => __('messages.sector_c')],
                ['name' => __('messages.ari_jensen'), 'role' => __('messaging.context.roles.moderator'), 'pet' => __('messages.search_map')],
            ],
            'trail-tails' => [
                ['name' => __('messages.noah_patel'), 'role' => __('messaging.context.roles.moderator'), 'pet' => __('messages.juniper')],
                ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.member'), 'pet' => __('messages.scout')],
                ['name' => __('messages.lena_brooks'), 'role' => __('messaging.context.roles.member'), 'pet' => __('messages.pip')],
            ],
        ];
    }
}
