<?php

namespace App\Services;

final class GroupContentCatalog
{
    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    public function content(array $group): array
    {
        return [
            'pinned' => $this->pinned($group),
            'principles' => $this->principles($group),
            'posts' => $this->posts($group),
            'discussions' => $this->discussions($group),
            'events' => $this->events($group),
            'members' => $this->members($group),
            'pets' => $this->pets($group),
            'resources' => $this->resources($group),
            'rules' => $this->rules($group),
            'chat' => $this->chat($group),
            'poll' => $this->poll($group),
            'moderators' => $this->moderators($group),
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array{icon: string, eyebrow: string, title: string, description: string, meta: string}
     */
    private function pinned(array $group): array
    {
        return [
            'icon' => $group['privacy'] === 'closed' ? 'shield-check' : 'pin',
            'eyebrow' => __('messages.pinned_by_moderators_fcee4c1b2d'),
            'title' => $group['privacy'] === 'closed'
                ? __('messages.read_this_before_sharing_member_information_ae0bfc1963')
                : __('messages.start_here_useful_context_makes_a_useful_community_e54fea01c0'),
            'description' => $group['privacy'] === 'closed'
                ? __('messages.keep_member_names_private_posts_precise_locations_and_ap_b200f12b87')
                : __('messages.choose_the_closest_topic_protect_exact_addresses_and_exp_cada95bbd6'),
            'meta' => __('messages.updated_july_27_8f28b67b11').$group['organizer'],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    private function principles(array $group): array
    {
        $careGuidance = in_array($group['group_type'], ['care', 'adoption'], true)
            ? [
                'icon' => 'stethoscope',
                'title' => __('messages.separate_experience_from_medical_advice_9503ac23b5'),
                'description' => __('messages.personal_routines_can_help_a_conversation_but_diagnosis__65b8fec5cd'),
            ]
            : [
                'icon' => 'paw-print',
                'title' => __('messages.treat_every_pet_as_an_individual_d13a36c339'),
                'description' => __('messages.species_breed_age_and_size_provide_context_but_never_gua_95d82593d3'),
            ];

        return [
            [
                'icon' => 'message-circle-heart',
                'title' => __('messages.share_useful_context_89397c310f'),
                'description' => __('messages.describe_the_pet_environment_and_goal_so_advice_can_rema_f3d200c6d2'),
            ],
            $careGuidance,
            [
                'icon' => 'map-pin-off',
                'title' => __('messages.keep_private_locations_private_83ebc3abd2'),
                'description' => __('messages.use_parks_districts_and_public_meeting_points_instead_of_0fb1793dcf'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function posts(array $group): array
    {
        return [
            [
                'key' => $group['key'].'-welcome',
                'format' => __('messages.guide_8dd65d0952'),
                'author' => $group['organizer'],
                'role' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
                'time' => __('messages.today_9_20_am_ace23729ce'),
                'datetime' => '2026-07-29T09:20:00-07:00',
                'title' => __('messages.a_practical_starting_guide_for_005053c744').$group['topic'],
                'body' => __('messages.we_collected_the_most_useful_recent_answers_into_one_sho_b5ff4c5e0e'),
                'tags' => [__('messages.moderator_post_223235a8ff'), __('messages.start_here_e8605bad77')],
                'stats' => ['reactions' => 84, 'comments' => 19, 'saves' => 31],
                'image' => $group['image_small'],
                'image_alt' => $group['image_alt'],
                'expert' => (bool) $group['official'],
            ],
            [
                'key' => $group['key'].'-question',
                'format' => __('messages.question_289aff12b0'),
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'role' => __('messages.member_with_scout_and_nori_b1bd58fd8e'),
                'initials' => 'MC',
                'tone' => 'mint',
                'time' => __('messages.yesterday_6_45_pm_9af780404f'),
                'datetime' => '2026-07-28T18:45:00-07:00',
                'title' => __('messages.what_made_the_first_week_easier_for_your_pet_0859a1d098'),
                'body' => __('messages.i_am_comparing_calm_repeatable_routines_rather_than_quic_ec6008c7ba'),
                'tags' => [__('messages.needs_advice_6847705abe'), __('messages.lived_experience_c7b3e9a390')],
                'stats' => ['reactions' => 42, 'comments' => 27, 'saves' => 12],
                'image' => null,
                'image_alt' => null,
                'expert' => false,
            ],
            [
                'key' => $group['key'].'-event',
                'format' => __('messages.event_update_3b5964ae5a'),
                'author' => __('messages.jamie_cho_5f313c129b'),
                'role' => __('messages.event_organizer_b50aa7d6a5'),
                'initials' => 'JC',
                'tone' => 'paper',
                'time' => __('messages.monday_11_10_am_7c153bb2a4'),
                'datetime' => '2026-07-27T11:10:00-07:00',
                'title' => $group['next_event'],
                'body' => __('messages.the_event_page_now_includes_accessibility_arrival_and_we_e0a499b14f'),
                'tags' => [__('groups.detail.content.tags.event'), __('groups.detail.content.tags.local')],
                'stats' => ['reactions' => 37, 'comments' => 8, 'saves' => 21],
                'image' => null,
                'image_alt' => null,
                'expert' => false,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string, meta: string, status: string}>
     */
    private function discussions(array $group): array
    {
        return [
            [
                'icon' => 'messages-square',
                'title' => __('messages.introductions_and_current_routines_a352d27726'),
                'description' => __('messages.meet_recent_members_and_learn_which_topics_they_want_to__62b66ad7c4'),
                'meta' => __('messages.18_participants_last_reply_12_min_ago_f46dc79b3e'),
                'status' => __('messages.active_9234069589'),
            ],
            [
                'icon' => 'circle-help',
                'title' => __('messages.questions_waiting_for_a_useful_answer_ac1dc9cc16'),
                'description' => __('messages.focused_questions_with_pet_context_and_no_accepted_answe_d257597dbb'),
                'meta' => __('messages.7_open_questions_a137dc7a15').$group['language'],
                'status' => __('messages.needs_replies_d14abf042e'),
            ],
            [
                'icon' => 'badge-check',
                'title' => __('messages.moderator_reviewed_reference_thread_8257a25770'),
                'description' => __('messages.a_durable_summary_of_recurring_advice_sources_and_import_e25926b800'),
                'meta' => __('messages.updated_this_week_46_saves_56e035bdca'),
                'status' => __('messages.resolved_5be3c2c835'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function events(array $group): array
    {
        return [
            [
                'key' => $group['key'].'-next',
                'month' => __('groups.detail.content.month_aug'),
                'day' => '02',
                'datetime' => '2026-08-02T09:30:00-07:00',
                'title' => $group['next_event'],
                'place' => $group['local']
                    ? __('presentation.public_meeting_area', ['location' => $group['location']])
                    : __('messages.online_room_27c37c1d40'),
                'access' => __('messages.exact_details_after_rsvp_6a23376343'),
                'attendees' => __('messages.18_going_6_spots_left_6962af8f1b'),
                'status' => __('messages.registration_open_86babcde8a'),
            ],
            [
                'key' => $group['key'].'-qa',
                'month' => __('groups.detail.content.month_aug'),
                'day' => '08',
                'datetime' => '2026-08-08T18:00:00-07:00',
                'title' => __('messages.member_q_a_and_monthly_planning_eedd381804'),
                'place' => __('messages.online_captions_available_920e9a5ce0'),
                'access' => __('messages.members_may_submit_questions_in_advance_d315b82793'),
                'attendees' => __('messages.34_interested_b297357e62'),
                'status' => __('messages.members_only_857e2a2909'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, detail: string, initials: string, tone: string, badge: string}>
     */
    private function members(array $group): array
    {
        return [
            [
                'name' => $group['organizer'],
                'detail' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
                'badge' => $group['official'] ? __('messages.verified_organizer_e2bea9d2b1') : __('messages.owner_4b1b8aa360'),
            ],
            [
                'name' => __('messages.mia_carter_0e5b29cc3b'),
                'detail' => __('messages.member_scout_and_nori_2e6b8eefa5'),
                'initials' => 'MC',
                'tone' => 'mint',
                'badge' => __('messages.active_member_8688d586e3'),
            ],
            [
                'name' => __('messages.lena_brooks_ca42e74116'),
                'detail' => __('messages.moderator_cat_enrichment_e430728130'),
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => __('messages.moderator_6748ec8b76'),
            ],
            [
                'name' => __('messages.priya_shah_8925523814'),
                'detail' => __('messages.volunteer_coordinator_ea4c1153dc'),
                'initials' => 'PS',
                'tone' => 'mint',
                'badge' => __('messages.contributor_d5535b9113'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, string>>
     */
    private function pets(array $group): array
    {
        return [
            [
                'name' => __('messages.scout_8a1db462be'),
                'detail' => __('messages.border_collie_active_learner_8070e8b176'),
                'image' => 'https://images.unsplash.com/photo-1553882809-a4f57e59501d?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_32fa7a972b'),
                'status' => __('messages.open_to_calm_group_activities_eeb8356418'),
            ],
            [
                'name' => __('messages.nori_a64203ba20'),
                'detail' => __('messages.tabby_cat_indoor_enrichment_a87f7dfe20'),
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.nori_a_tabby_cat_0501555ae5'),
                'status' => __('messages.participates_through_mia_6a5941b6ad'),
            ],
            [
                'name' => __('messages.mochi_95114c81f3'),
                'detail' => __('messages.shiba_mix_neighborhood_walks_af9f9423f4'),
                'image' => 'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.mochi_a_shiba_mix_b6e815b0f0'),
                'status' => __('messages.event_regular_fc9d79d47c'),
            ],
            [
                'name' => __('messages.olive_3038ab334a'),
                'detail' => __('messages.corgi_gentle_introductions_209a54b3fa'),
                'image' => 'https://images.unsplash.com/photo-1612195583950-b8fd34c87093?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('messages.olive_a_corgi_c44c73c350'),
                'status' => __('messages.new_member_38db545f1d'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string, meta: string}>
     */
    private function resources(array $group): array
    {
        return [
            [
                'icon' => 'book-open-text',
                'title' => __('messages.new_member_guide_8a08e3bc98'),
                'description' => __('messages.where_to_post_how_moderation_works_and_how_to_protect_pr_86b88f668b'),
                'meta' => __('messages.guide_reviewed_july_2026_152d429bab'),
            ],
            [
                'icon' => 'map',
                'title' => $group['local'] ? __('messages.public_meeting_place_checklist_0234d48f72') : __('messages.online_event_accessibility_checklist_93feac2d6d'),
                'description' => __('messages.a_concise_planning_reference_for_organizers_and_first_ti_e20bf57098'),
                'meta' => __('messages.checklist_4_min_read_3203b1080d'),
            ],
            [
                'icon' => 'stethoscope',
                'title' => __('messages.when_community_answers_are_not_enough_0e742366c1'),
                'description' => __('messages.signs_that_a_question_belongs_with_a_veterinarian_or_qua_d908ee04d0'),
                'meta' => __('messages.safety_reference_expert_reviewed_63733814ea'),
            ],
            [
                'icon' => 'shield-check',
                'title' => __('messages.privacy_and_photo_consent_04f66e1c36'),
                'description' => __('messages.how_tags_event_photos_locations_and_closed_group_materia_53f562f5fb'),
                'meta' => __('messages.policy_summary_updated_this_month_216256c20b'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{title: string, description: string}>
     */
    private function rules(array $group): array
    {
        return [
            [
                'title' => __('messages.be_useful_and_respectful_ec9ad819b7'),
                'description' => __('messages.no_harassment_personal_attacks_repetitive_promotion_or_p_047799d666'),
            ],
            [
                'title' => __('messages.protect_people_and_locations_16de3c3fef'),
                'description' => __('messages.do_not_publish_home_addresses_private_event_details_docu_822699393f'),
            ],
            [
                'title' => __('messages.no_dangerous_medical_instructions_46d7ef8b6d'),
                'description' => __('messages.do_not_diagnose_prescribe_doses_recommend_stopping_treat_ce303112dd'),
            ],
            [
                'title' => __('messages.keep_commerce_transparent_e32664f7e7'),
                'description' => __('messages.no_animal_sales_or_unverified_fundraising_approved_servi_b8add7f91d'),
            ],
            [
                'title' => __('presentation.respect_privacy_boundary', ['privacy' => $group['privacy']]),
                'description' => $group['privacy'] === 'closed'
                    ? __('messages.member_posts_names_and_screenshots_stay_inside_the_group_043e0d415c')
                    : __('messages.public_posts_may_be_shared_but_authorship_context_and_ph_7626807847'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, initials: string, tone: string, body: string, time: string}>
     */
    private function chat(array $group): array
    {
        return [
            [
                'name' => __('messages.jamie_eaea2ab372'),
                'initials' => 'JC',
                'tone' => 'sun',
                'body' => __('messages.i_added_the_shaded_arrival_point_and_accessibility_notes_73aac77a97'),
                'time' => __('messages.9_18_am_577d5c5ddb'),
            ],
            [
                'name' => __('messages.mia_4150950870'),
                'initials' => 'MC',
                'tone' => 'mint',
                'body' => __('messages.thank_you_i_will_bring_scout_s_own_water_bowl_and_start__e33240e6f2'),
                'time' => __('messages.9_24_am_c09d1143de'),
            ],
            [
                'name' => $group['organizer'],
                'initials' => $group['organizer_initials'],
                'tone' => 'paper',
                'body' => __('messages.perfect_i_pinned_the_full_plan_so_it_does_not_disappear__2ed3f8eae3'),
                'time' => __('messages.9_31_am_1c480d1ca6'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function poll(array $group): array
    {
        return [
            'key' => 'august-focus',
            'question' => __('messages.which_topic_should_the_group_prioritize_in_august_9397594256'),
            'description' => __('messages.one_response_per_member_results_guide_the_next_resource__a8f97f4840'),
            'options' => [
                ['key' => 'routine', 'label' => __('messages.practical_daily_routines_957e92e1dc'), 'votes' => 48],
                ['key' => 'events', 'label' => $group['local'] ? __('messages.safer_local_meetups_1cf9acf808') : __('messages.accessible_online_events_0d016c5a16'), 'votes' => 35],
                ['key' => 'expert', 'label' => __('messages.expert_question_session_7e17991b83'), 'votes' => 29],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, detail: string, initials: string, tone: string}>
     */
    private function moderators(array $group): array
    {
        return [
            [
                'name' => $group['organizer'],
                'detail' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
            ],
            [
                'name' => __('messages.lena_brooks_ca42e74116'),
                'detail' => __('messages.moderator_community_care_1257fea022'),
                'initials' => 'LB',
                'tone' => 'mint',
            ],
            [
                'name' => __('messages.priya_shah_8925523814'),
                'detail' => __('messages.moderator_safety_and_events_b72b93713e'),
                'initials' => 'PS',
                'tone' => 'paper',
            ],
        ];
    }
}
