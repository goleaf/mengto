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
            'eyebrow' => __('messages.pinned_by_moderators'),
            'title' => $group['privacy'] === 'closed'
                ? __('messages.read_this_before_sharing_member_information')
                : __('messages.start_here_useful_context_makes_a_useful_community'),
            'description' => $group['privacy'] === 'closed'
                ? __('messages.keep_member_names_private_posts_precise_locations_and_application_answers_inside_the_group')
                : __('messages.choose_the_closest_topic_protect_exact_addresses_and_explain_what_you_have_already_tried'),
            'meta' => __('messages.updated_july_27').$group['organizer'],
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
                'title' => __('messages.separate_experience_from_medical_advice'),
                'description' => __('messages.personal_routines_can_help_a_conversation_but_diagnosis_and_medication_belong_with_qualified_professionals'),
            ]
            : [
                'icon' => 'paw-print',
                'title' => __('messages.treat_every_pet_as_an_individual'),
                'description' => __('messages.species_breed_age_and_size_provide_context_but_never_guarantee_behavior_or_compatibility'),
            ];

        return [
            [
                'icon' => 'message-circle-heart',
                'title' => __('messages.share_useful_context'),
                'description' => __('messages.describe_the_pet_environment_and_goal_so_advice_can_remain_specific_and_respectful'),
            ],
            $careGuidance,
            [
                'icon' => 'map-pin-off',
                'title' => __('messages.keep_private_locations_private'),
                'description' => __('messages.use_parks_districts_and_public_meeting_points_instead_of_home_addresses_or_routine_gps_history'),
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
                'format' => __('messages.guide'),
                'author' => $group['organizer'],
                'role' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
                'time' => __('messages.today_9_20_am'),
                'datetime' => '2026-07-29T09:20:00-07:00',
                'title' => __('messages.a_practical_starting_guide_for').$group['topic'],
                'body' => __('messages.we_collected_the_most_useful_recent_answers_into_one_short_guide_add_your_pet_s_context_before_trying_a_routine_and_flag_anything_that_needs_an_expert_review'),
                'tags' => [__('messages.moderator_post'), __('messages.start_here')],
                'stats' => ['reactions' => 84, 'comments' => 19, 'saves' => 31],
                'image' => $group['image_small'],
                'image_alt' => $group['image_alt'],
                'expert' => (bool) $group['official'],
            ],
            [
                'key' => $group['key'].'-question',
                'format' => __('messages.question'),
                'author' => __('messages.member'),
                'role' => __('messages.member'),
                'initials' => 'M',
                'tone' => 'mint',
                'time' => __('messages.yesterday_6_45_pm'),
                'datetime' => '2026-07-28T18:45:00-07:00',
                'title' => __('messages.what_made_the_first_week_easier_for_your_pet'),
                'body' => __('messages.i_am_comparing_calm_repeatable_routines_rather_than_quick_fixes_what_helped_what_did_you_change_and_how_long_did_you_give_it'),
                'tags' => [__('messages.needs_advice'), __('messages.lived_experience')],
                'stats' => ['reactions' => 42, 'comments' => 27, 'saves' => 12],
                'image' => null,
                'image_alt' => null,
                'expert' => false,
            ],
            [
                'key' => $group['key'].'-event',
                'format' => __('messages.event_update'),
                'author' => __('messages.jamie_cho'),
                'role' => __('messages.event_organizer'),
                'initials' => 'JC',
                'tone' => 'paper',
                'time' => __('messages.monday_11_10_am'),
                'datetime' => '2026-07-27T11:10:00-07:00',
                'title' => $group['next_event'],
                'body' => __('messages.the_event_page_now_includes_accessibility_arrival_and_weather_notes_exact_meeting_details_are_shared_only_with_confirmed_attendees'),
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
                'title' => __('messages.introductions_and_current_routines'),
                'description' => __('messages.meet_recent_members_and_learn_which_topics_they_want_to_explore'),
                'meta' => __('messages.18_participants_last_reply_12_min_ago'),
                'status' => __('messages.active'),
            ],
            [
                'icon' => 'circle-help',
                'title' => __('messages.questions_waiting_for_a_useful_answer'),
                'description' => __('messages.focused_questions_with_pet_context_and_no_accepted_answer_yet'),
                'meta' => __('messages.7_open_questions').$group['language'],
                'status' => __('messages.needs_replies'),
            ],
            [
                'icon' => 'badge-check',
                'title' => __('messages.moderator_reviewed_reference_thread'),
                'description' => __('messages.a_durable_summary_of_recurring_advice_sources_and_important_limitations'),
                'meta' => __('messages.updated_this_week_46_saves'),
                'status' => __('messages.resolved'),
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
                    : __('messages.online_room'),
                'access' => __('messages.exact_details_after_rsvp'),
                'attendees' => __('messages.18_going_6_spots_left'),
                'status' => __('messages.registration_open'),
            ],
            [
                'key' => $group['key'].'-qa',
                'month' => __('groups.detail.content.month_aug'),
                'day' => '08',
                'datetime' => '2026-08-08T18:00:00-07:00',
                'title' => __('messages.member_q_a_and_monthly_planning'),
                'place' => __('messages.online_captions_available'),
                'access' => __('messages.members_may_submit_questions_in_advance'),
                'attendees' => __('messages.34_interested'),
                'status' => __('messages.members_only'),
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
                'badge' => $group['official'] ? __('messages.verified_organizer') : __('messages.owner'),
            ],
            [
                'name' => __('messages.member'),
                'detail' => __('messages.member'),
                'initials' => 'M',
                'tone' => 'mint',
                'badge' => __('messages.active_member'),
            ],
            [
                'name' => __('messages.lena_brooks'),
                'detail' => __('messages.moderator_cat_enrichment'),
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => __('messages.moderator'),
            ],
            [
                'name' => __('messages.priya_shah'),
                'detail' => __('messages.volunteer_coordinator'),
                'initials' => 'PS',
                'tone' => 'mint',
                'badge' => __('messages.contributor'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, string>>
     */
    private function pets(array $group): array
    {
        return [];
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
                'title' => __('messages.new_member_guide'),
                'description' => __('messages.where_to_post_how_moderation_works_and_how_to_protect_private_information'),
                'meta' => __('messages.guide_reviewed_july_2026'),
            ],
            [
                'icon' => 'map',
                'title' => $group['local'] ? __('messages.public_meeting_place_checklist') : __('messages.online_event_accessibility_checklist'),
                'description' => __('messages.a_concise_planning_reference_for_organizers_and_first_time_attendees'),
                'meta' => __('messages.checklist_4_min_read'),
            ],
            [
                'icon' => 'stethoscope',
                'title' => __('messages.when_community_answers_are_not_enough'),
                'description' => __('messages.signs_that_a_question_belongs_with_a_veterinarian_or_qualified_behavior_professional'),
                'meta' => __('messages.safety_reference_expert_reviewed'),
            ],
            [
                'icon' => 'shield-check',
                'title' => __('messages.privacy_and_photo_consent'),
                'description' => __('messages.how_tags_event_photos_locations_and_closed_group_material_should_be_handled'),
                'meta' => __('messages.policy_summary_updated_this_month'),
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
                'title' => __('messages.be_useful_and_respectful'),
                'description' => __('messages.no_harassment_personal_attacks_repetitive_promotion_or_pressure_to_move_conversations_off_platform'),
            ],
            [
                'title' => __('messages.protect_people_and_locations'),
                'description' => __('messages.do_not_publish_home_addresses_private_event_details_documents_or_another_member_s_contact_information'),
            ],
            [
                'title' => __('messages.no_dangerous_medical_instructions'),
                'description' => __('messages.do_not_diagnose_prescribe_doses_recommend_stopping_treatment_or_promise_guaranteed_outcomes'),
            ],
            [
                'title' => __('messages.keep_commerce_transparent'),
                'description' => __('messages.no_animal_sales_or_unverified_fundraising_approved_services_must_use_the_correct_section_and_disclosure'),
            ],
            [
                'title' => __('presentation.respect_privacy_boundary', ['privacy' => $group['privacy']]),
                'description' => $group['privacy'] === 'closed'
                    ? __('messages.member_posts_names_and_screenshots_stay_inside_the_group_unless_every_affected_person_agrees')
                    : __('messages.public_posts_may_be_shared_but_authorship_context_and_photo_consent_must_be_preserved'),
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
                'name' => __('messages.jamie'),
                'initials' => 'JC',
                'tone' => 'sun',
                'body' => __('messages.i_added_the_shaded_arrival_point_and_accessibility_notes_to_the_event'),
                'time' => __('messages.9_18_am'),
            ],
            [
                'name' => __('messages.member'),
                'initials' => 'M',
                'tone' => 'mint',
                'body' => __('messages.shared_routines'),
                'time' => __('messages.9_24_am'),
            ],
            [
                'name' => $group['organizer'],
                'initials' => $group['organizer_initials'],
                'tone' => 'paper',
                'body' => __('messages.perfect_i_pinned_the_full_plan_so_it_does_not_disappear_in_chat'),
                'time' => __('messages.9_31_am'),
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
            'question' => __('messages.which_topic_should_the_group_prioritize_in_august'),
            'description' => __('messages.one_response_per_member_results_guide_the_next_resource_and_event'),
            'options' => [
                ['key' => 'routine', 'label' => __('messages.practical_daily_routines'), 'votes' => 48],
                ['key' => 'events', 'label' => $group['local'] ? __('messages.safer_local_meetups') : __('messages.accessible_online_events'), 'votes' => 35],
                ['key' => 'expert', 'label' => __('messages.expert_question_session'), 'votes' => 29],
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
                'name' => __('messages.lena_brooks'),
                'detail' => __('messages.moderator_community_care'),
                'initials' => 'LB',
                'tone' => 'mint',
            ],
            [
                'name' => __('messages.priya_shah'),
                'detail' => __('messages.moderator_safety_and_events'),
                'initials' => 'PS',
                'tone' => 'paper',
            ],
        ];
    }
}
