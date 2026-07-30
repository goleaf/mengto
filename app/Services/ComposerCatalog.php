<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class ComposerCatalog
{
    /**
     * @param  array<string, mixed>  $owner
     * @param  array<string, mixed>  $pet
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    public function form(
        string $kind,
        array $owner,
        array $pet,
        array $context = [],
        array $visibilityOptions = [],
    ): array {
        return match ($kind) {
            'post' => $this->post($context),
            'post-edit' => $this->post($context, true),
            'delete-post' => $this->deletePost($context),
            'group' => $this->group(),
            'meetup' => $this->meetup($context),
            'walk' => $this->walk(),
            'pet' => $this->pet(),
            'place' => $this->place(),
            'place-correction' => $this->placeCorrection($context),
            'place-warning' => $this->placeWarning($context),
            'place-review' => $this->placeReview($context),
            'place-question' => $this->placeQuestion($context),
            'place-claim' => $this->placeClaim($context),
            'message' => $this->message(),
            'profile' => $this->profile($owner),
            'pet-profile' => $this->petProfile($pet),
            'profile-privacy' => $this->profilePrivacy($context, $visibilityOptions),
            'pet-privacy' => $this->petPrivacy($pet, $context, $visibilityOptions),
            'report-profile' => $this->profileReport($context),
            'report-post' => $this->postReport($context),
            'report-group' => $this->groupReport($context),
            'report-event' => $this->eventReport($context),
            'report-place' => $this->placeReport($context),
            default => throw new InvalidArgumentException("Unknown PawCircle composer kind [{$kind}]."),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function post(array $context, bool $editing = false): array
    {
        $post = $editing ? ($context['post'] ?? null) : [];

        if ($editing && ! is_array($post)) {
            throw new InvalidArgumentException(__('messages.a_valid_editable_post_is_required_12a657e72d'));
        }

        $mediaOptions = ['none' => __('messages.no_media_0f040516f6')];

        foreach ($context['media_presets'] ?? [] as $key => $preset) {
            if ($key !== 'none') {
                $mediaOptions[$key] = (string) ($preset['label'] ?? ucfirst((string) $key));
            }
        }

        return $this->definition(
            eyebrow: __('messages.neighborhood_feed_3f7d71b76a'),
            title: $editing ? __('messages.edit_your_publication_693b86ab07') : __('messages.create_a_publication_08c3ee1c2e'),
            description: __('messages.choose_the_publishing_profile_a_safe_audience_and_only_t_aad2ab0a51'),
            action: $editing ? 'update-post' : 'create-post',
            submitLabel: $editing ? __('messages.save_changes_dd0ae7a5cb') : __('messages.publish_859390eb49'),
            submitIcon: $editing ? 'check' : 'send',
            cancelRoute: 'home',
            activeSection: 'feed',
            fields: [
                $this->field(
                    'identity',
                    __('messages.publish_as_608e00e5b3'),
                    'select',
                    (string) ($post['identity'] ?? 'mia'),
                    '',
                    required: true,
                    options: $context['identities'] ?? [],
                ),
                $this->field(
                    'format',
                    __('messages.format_2f343666aa'),
                    'select',
                    (string) ($post['format'] ?? 'photo'),
                    '',
                    required: true,
                    options: [
                        'text' => __('messages.text_update_02ad6964a6'),
                        'photo' => __('messages.photo_update_e9b85666f8'),
                        'video' => __('messages.video_d534be829e'),
                        'question' => __('messages.question_289aff12b0'),
                        'lost' => __('messages.lost_pet_alert_43b8776771'),
                        'adoption' => __('messages.adoption_profile_6c7cd4fe9a'),
                    ],
                ),
                $this->field('title', __('messages.headline_c5f5a4c815'), 'text', (string) ($post['title'] ?? ''), __('messages.optional_short_headline_641d599ba7')),
                $this->field('body', __('messages.post_a5554622c6'), 'textarea', (string) ($post['body'] ?? ''), __('messages.share_the_useful_part_of_the_story_2729ae1e99'), required: true),
                $this->field(
                    'topic',
                    __('messages.topic_7e61847d61'),
                    'select',
                    (string) ($post['topic'] ?? 'community'),
                    '',
                    required: true,
                    options: $context['topics'] ?? [],
                ),
                $this->field('tags', 'Tags', 'text', (string) ($post['tags'] ?? ''), __('messages.training_portland_rescue_852ebb99a3')),
                $this->field(
                    'media',
                    __('messages.media_preview_03c38ccad0'),
                    'select',
                    (string) ($post['media'] ?? 'park-carousel'),
                    '',
                    required: true,
                    options: $mediaOptions,
                ),
                $this->field('media_alt', __('messages.media_description_2e22cefe8c'), 'text', (string) ($post['media_alt'] ?? ''), __('messages.required_when_media_is_selected_34f601a284')),
                $this->field(
                    'location',
                    __('messages.safe_place_6b17cc6d85'),
                    'select',
                    (string) ($post['location'] ?? 'none'),
                    '',
                    required: true,
                    options: $context['safe_places'] ?? [],
                ),
                $this->field(
                    'audience',
                    __('messages.audience_545c023576'),
                    'select',
                    (string) ($post['audience'] ?? 'public'),
                    '',
                    required: true,
                    options: $context['audiences'] ?? [],
                ),
                $this->field(
                    'comment_policy',
                    __('messages.who_can_comment_d755959871'),
                    'select',
                    (string) ($post['comment_policy'] ?? 'all'),
                    '',
                    required: true,
                    options: $context['comment_policies'] ?? [],
                ),
                $this->field(
                    'sensitive',
                    __('messages.sensitive_media_e6a7a0b7b8'),
                    'select',
                    (string) ($post['sensitive'] ?? 'no'),
                    '',
                    required: true,
                    options: ['no' => __('messages.no_warning_needed_6e8077c495'), 'yes' => __('messages.hide_behind_a_content_warning_8cebd12521')],
                ),
            ],
            payload: $editing ? ['target' => $post['key']] : [],
            secondaryActions: [
                [
                    'label' => __('messages.save_draft_3de100106d'),
                    'icon' => 'file-pen-line',
                    'name' => 'intent',
                    'value' => 'draft',
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function group(): array
    {
        return $this->definition(
            eyebrow: __('messages.community_builder_4ba4379ff7'),
            title: __('messages.create_a_focused_group_0962f715aa'),
            description: __('messages.set_the_purpose_membership_boundary_posting_policy_and_f_4893727ed0'),
            action: 'create-group',
            submitLabel: __('messages.create_group_35be9c541d'),
            submitIcon: 'users-round',
            cancelRoute: 'groups.index',
            activeSection: 'groups',
            fields: [
                $this->field('title', __('messages.group_name_762ebb70ef'), 'text', '', __('messages.example_richmond_morning_walks_5e3dcadd6e'), required: true),
                $this->field(
                    'category',
                    __('messages.category_292c06f004'),
                    'select',
                    'local',
                    '',
                    required: true,
                    options: [
                        'breed' => __('messages.breed_community_83d88aef24'),
                        'species' => __('messages.animal_type_441252b48a'),
                        'local' => __('messages.local_community_7be3df84a5'),
                        'interest' => __('messages.shared_interest_14aee15576'),
                        'training' => __('messages.training_and_behavior_0c2f3aefdf'),
                        'care' => __('messages.care_and_health_support_2e080b2cee'),
                        'adoption' => __('messages.adoption_and_fostering_f6cc9a46cc'),
                        'volunteering' => __('messages.volunteering_a7097b70a5'),
                    ],
                ),
                $this->field(
                    'privacy',
                    __('messages.privacy_54a57c3147'),
                    'select',
                    'closed',
                    '',
                    required: true,
                    options: [
                        'public' => __('messages.public_anyone_can_read_and_join_f2be9fc5f9'),
                        'closed' => __('messages.closed_members_are_approved_fbccb15dfb'),
                    ],
                ),
                $this->field('city', __('messages.city_or_region_efb8875415'), 'text', '', __('messages.example_portland_oregon_01006a8156'), required: true),
                $this->field(
                    'language',
                    __('messages.primary_language_50a9d2b092'),
                    'select',
                    __('messages.english_ba118bf7fc'),
                    '',
                    required: true,
                    options: [
                        'English' => __('messages.english_ba118bf7fc'),
                        'English + Spanish' => __('messages.english_spanish_b50c1cd84d'),
                        'Russian' => __('messages.russian_5bcc40adf6'),
                        'Lithuanian' => __('messages.lithuanian_8625f6a206'),
                    ],
                ),
                $this->field(
                    'pet_identity',
                    __('messages.participating_profiles_51b5b8dd44'),
                    'select',
                    'all',
                    '',
                    required: true,
                    options: [
                        'mia' => __('messages.mia_only_4142ba6026'),
                        'scout' => __('messages.mia_with_scout_470cf57779'),
                        'nori' => __('messages.mia_with_nori_c7abec5bdf'),
                        'all' => __('messages.mia_with_scout_and_nori_856cd6f957'),
                    ],
                ),
                $this->field(
                    'posting_policy',
                    __('messages.who_can_publish_4feee5a2b4'),
                    'select',
                    'members',
                    '',
                    required: true,
                    options: [
                        'members' => __('messages.all_members_3d6fe3e703'),
                        'review' => __('messages.members_after_moderator_review_88c9058bb6'),
                        'staff' => __('messages.administrators_and_moderators_only_23a53de595'),
                    ],
                ),
                $this->field('body', __('messages.description_526e0087cc'), 'textarea', '', __('messages.who_is_this_group_for_and_what_belongs_here_8104670eb4'), required: true),
                $this->field('rules', __('messages.first_community_rules_140f742606'), 'textarea', '', __('messages.add_privacy_safety_promotion_and_respectful_conversation_5f60a82918'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function meetup(array $context): array
    {
        $place = is_array($context['place_context'] ?? null)
            ? $context['place_context']
            : [];

        return $this->definition(
            eyebrow: __('messages.event_studio_b83d54b8d9'),
            title: __('messages.create_a_pet_friendly_event_9b4f075ffa'),
            description: __('messages.set_the_format_audience_registration_boundary_tickets_an_022889198d'),
            action: 'create-meetup',
            submitLabel: __('messages.publish_event_175ef37539'),
            submitIcon: 'calendar-plus',
            cancelRoute: 'meetups.index',
            activeSection: 'meetups',
            fields: [
                $this->field('title', __('messages.event_name_25bc9efedf'), 'text', '', __('messages.example_quiet_sunday_park_loop_bb135f8b7f'), required: true),
                $this->field(
                    'category',
                    __('messages.category_292c06f004'),
                    'select',
                    'walk',
                    '',
                    required: true,
                    options: [
                        'walk' => __('messages.walk_or_first_meeting_7590ef5748'),
                        'training' => __('messages.training_36a798e3f3'),
                        'show' => __('messages.show_or_exhibition_2efcdb139b'),
                        'lecture' => __('messages.lecture_a757c6b403'),
                        'webinar' => __('messages.webinar_83584124b5'),
                        'adoption' => __('messages.adoption_day_f7aecb1fd3'),
                        'volunteering' => __('messages.volunteer_action_e20372cfb7'),
                        'charity' => __('messages.charity_event_b30f9ee3b4'),
                        'contest' => __('messages.contest_7da8d6db37'),
                        'photo-session' => __('messages.photo_session_5b0a24c2d6'),
                        'travel' => __('messages.pet_friendly_trip_01fe9a5588'),
                        'celebration' => __('messages.celebration_or_memorial_93d6c69d42'),
                        'search-action' => __('messages.urgent_search_action_7e47454493'),
                        'other' => __('messages.other_f97e9da0e3'),
                    ],
                ),
                $this->field(
                    'event_organizer',
                    __('messages.organize_as_1fa2d52f38'),
                    'select',
                    'mia',
                    '',
                    required: true,
                    options: [
                        'mia' => __('messages.mia_carter_0e5b29cc3b'),
                        'scout' => __('messages.scout_managed_by_mia_68a8dadfc0'),
                        'group' => __('messages.richmond_pet_circle_34411217bc'),
                        'organization' => __('messages.brand.community_team'),
                    ],
                ),
                $this->field(
                    'event_format',
                    __('messages.format_2f343666aa'),
                    'select',
                    'offline',
                    '',
                    required: true,
                    options: [
                        'offline' => __('messages.in_person_5cf02dbb1e'),
                        'online' => __('messages.online_0d21bd5202'),
                    ],
                ),
                $this->field('date', __('messages.date_99c40ab405'), 'date', '', '', required: true, min: today()->format('Y-m-d')),
                $this->field('time', __('messages.start_time_babe9dda85'), 'time', '10:00', '', required: true),
                $this->field(
                    'event_timezone',
                    __('messages.time_zone_b9fe146478'),
                    'select',
                    'America/Los_Angeles',
                    '',
                    required: true,
                    options: [
                        'America/Los_Angeles' => __('messages.pacific_time_292bbb9d60'),
                        'America/New_York' => __('messages.eastern_time_7c1e3f83d1'),
                        'Europe/Vilnius' => __('messages.vilnius_time_abe275522c'),
                        'Europe/London' => __('messages.london_time_2da158fbc8'),
                        'UTC' => 'UTC',
                    ],
                ),
                $this->field(
                    'location',
                    __('messages.meeting_place_46d1e79522'),
                    'text',
                    (string) ($place['address'] ?? ''),
                    __('messages.required_for_in_person_events_f5c920a937'),
                ),
                $this->field('event_online_url', __('messages.online_room_link_fb6fa2e93b'), 'url', '', __('messages.required_for_online_events_a27ee7bee8')),
                $this->field(
                    'privacy',
                    __('messages.privacy_54a57c3147'),
                    'select',
                    'public',
                    '',
                    required: true,
                    options: [
                        'public' => __('messages.public_and_discoverable_bd781ffa15'),
                        'closed' => __('messages.closed_with_limited_details_a4e6ac5b74'),
                        'hidden' => __('messages.invitation_only_700463f25d'),
                    ],
                ),
                $this->field(
                    'event_registration_policy',
                    __('messages.registration_c793e0d9a1'),
                    'select',
                    'approval',
                    '',
                    required: true,
                    options: [
                        'instant' => __('messages.instant_confirmation_e9af9a74f1'),
                        'approval' => __('messages.organizer_approval_08d724dd81'),
                        'invitation' => __('messages.invitation_only_700463f25d'),
                    ],
                ),
                $this->field('event_capacity', __('messages.capacity_ae65d09655'), 'number', '8', __('messages.people_and_pets_together_37ab91742e'), required: true, min: '2'),
                $this->field(
                    'event_ticket_model',
                    __('messages.tickets_3d131368b4'),
                    'select',
                    'free',
                    '',
                    required: true,
                    options: [
                        'free' => __('messages.free_registration_46424ced98'),
                        'paid' => __('messages.paid_ticket_80e85547b6'),
                    ],
                ),
                $this->field('event_ticket_price', __('messages.ticket_price_usd_df4146fc30'), 'number', '', __('messages.required_for_paid_events_f88e3aee0a'), min: '1'),
                $this->field(
                    'event_cover',
                    __('messages.cover_fa8d845666'),
                    'select',
                    'walk',
                    '',
                    required: true,
                    options: [
                        'walk' => __('messages.calm_park_walk_5121d71c0d'),
                        'training' => __('messages.training_session_86d6036c90'),
                        'community' => __('messages.community_gathering_c8b7eba0c1'),
                        'online' => __('messages.online_learning_548ab2a6ef'),
                    ],
                ),
                $this->field(
                    'body',
                    __('messages.description_526e0087cc'),
                    'textarea',
                    $place === []
                        ? ''
                        : __('presentation.meet_at_place_safely', ['place' => $place['name']]),
                    __('messages.describe_who_it_is_for_what_happens_and_what_to_bring_cb89971ddc'),
                    required: true,
                ),
                $this->field(
                    'rules',
                    __('messages.participation_rules_fc920a8994'),
                    'textarea',
                    $place === [] ? '' : implode("\n", $place['rules'] ?? []),
                    __('messages.add_leash_contact_photography_and_cancellation_rules_81ada16c6c'),
                    required: true,
                ),
                $this->field('event_safety_plan', __('messages.safety_plan_502d6dbea8'), 'textarea', '', __('messages.add_meeting_boundaries_emergency_contact_path_and_animal_04055f3f52'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function walk(): array
    {
        return $this->definition(
            eyebrow: __('messages.walk_planner_46c2829124'),
            title: __('messages.plan_a_neighborhood_walk_b49b993382'),
            description: __('messages.set_a_calm_route_clear_timing_and_an_easy_pace_before_se_de72ba82e9'),
            action: 'create-walk-plan',
            submitLabel: __('messages.save_walk_draft_28ee817770'),
            submitIcon: 'calendar-plus',
            cancelRoute: 'walks.index',
            activeSection: 'meetups',
            fields: [
                $this->field(
                    'target',
                    __('messages.walking_with_4c2026dfe7'),
                    'select',
                    'mochi',
                    '',
                    required: true,
                    options: [
                        'mochi' => __('messages.ari_and_mochi_6ab978b432'),
                        'juniper' => __('messages.noah_and_juniper_875732f92f'),
                        'scout' => __('messages.scout_and_mia_a4d8a1fd0f'),
                    ],
                ),
                $this->field('title', __('messages.plan_name_c0657726a5'), 'text', '', __('messages.example_early_fields_park_loop_e0b79c363f'), required: true),
                $this->field('date', __('messages.date_99c40ab405'), 'date', '', '', required: true, min: today()->format('Y-m-d')),
                $this->field('time', __('messages.start_time_babe9dda85'), 'time', '08:30', '', required: true),
                $this->field('location', __('messages.meeting_point_f08183059f'), 'text', '', __('messages.park_gate_quiet_corner_or_familiar_block_f08b497972'), required: true),
                $this->field(
                    'detail',
                    __('messages.pace_9582b504fe'),
                    'select',
                    __('messages.easy_pace_30_min_c2585b7d4e'),
                    '',
                    options: [
                        'Easy pace, 20 min' => __('messages.easy_pace_20_min_fcab90581c'),
                        'Easy pace, 30 min' => __('messages.easy_pace_30_min_c2585b7d4e'),
                        'Steady pace, 45 min' => __('messages.steady_pace_45_min_75cf4f560d'),
                        'Sniff-friendly, no time limit' => __('messages.sniff_friendly_no_time_limit_b9b373d610'),
                    ],
                ),
                $this->field('body', __('messages.routine_notes_6a0ff32a41'), 'textarea', '', __('messages.add_greetings_triggers_water_stops_or_a_quiet_finish_08dc11ae23'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function pet(): array
    {
        return $this->definition(
            eyebrow: __('messages.your_pack_7d1f961c38'),
            title: __('messages.add_a_pet_afb087ecee'),
            description: __('messages.create_a_simple_profile_that_helps_neighbors_understand__680bf2a4fe'),
            action: 'create-pet',
            submitLabel: __('messages.add_pet_7065b90594'),
            submitIcon: 'paw-print',
            cancelRoute: 'pets.index',
            activeSection: 'pets',
            fields: [
                $this->field('title', __('messages.pet_name_483f51db1f'), 'text', '', __('messages.pet_name_483f51db1f'), required: true),
                $this->field('category', __('messages.species_56205e12c2'), 'text', '', __('messages.dog_cat_rabbit_d05466fb5b'), required: true),
                $this->field('detail', __('messages.breed_or_type_fe0b9a5ca2'), 'text', '', __('messages.breed_or_companion_type_3643e58a92')),
                $this->field('body', __('messages.short_profile_3b4e1a72e7'), 'textarea', '', __('messages.share_a_favorite_routine_or_social_preference_ffdbedd273'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function message(): array
    {
        return $this->definition(
            eyebrow: __('messages.neighborhood_inbox_56469de0ab'),
            title: __('messages.start_a_new_message_bbf7f17e6e'),
            description: __('messages.write_a_clear_note_about_a_walk_care_question_or_local_p_654ee05b62'),
            action: 'send-message',
            submitLabel: __('messages.send_message_93a26b1eaf'),
            submitIcon: 'send',
            cancelRoute: 'messages.index',
            activeSection: 'messages',
            fields: [
                $this->field(
                    'target',
                    'To',
                    'select',
                    'ari',
                    '',
                    required: true,
                    options: [
                        'ari' => __('messages.ari_jensen_and_mochi_6ddcec1d58'),
                        'lena' => __('messages.lena_brooks_and_pip_09e49d7107'),
                        'noah' => __('messages.noah_patel_and_juniper_35030011c1'),
                        'priya' => __('messages.priya_shah_and_clover_3445bb5f24'),
                    ],
                ),
                $this->field('body', __('messages.message_2f77668a9d'), 'textarea', '', __('messages.write_your_message_aa220fb68c'), required: true),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $owner
     * @return array<string, mixed>
     */
    private function profile(array $owner): array
    {
        return $this->definition(
            eyebrow: __('messages.your_profile_528d89ad72'),
            title: __('messages.edit_your_pawcircle_profile_b14ac4f0a0'),
            description: __('messages.keep_the_details_neighbors_use_to_plan_walks_and_introdu_1e415b7eed'),
            action: 'update-profile',
            submitLabel: __('messages.save_profile_0c8209e72e'),
            submitIcon: 'check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('title', __('messages.name_dcd1d5223f'), 'text', $owner['name'], __('messages.your_name_2c6b2e253c'), required: true, autocomplete: 'name'),
                $this->field('location', __('messages.location_15b61974b2'), 'text', $owner['location'], __('messages.neighborhood_and_city_e10561bcb4'), required: true, autocomplete: 'address-level2'),
                $this->field('detail', __('messages.availability_12f67f8539'), 'text', $owner['status'], __('messages.when_are_you_open_to_meeting_b47a60dbb5')),
                $this->field('body', __('messages.about_you_428f4d00b7'), 'textarea', $owner['bio'], __('messages.share_your_routines_and_interests_de2eeed46c'), required: true),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    private function petProfile(array $pet): array
    {
        return $this->definition(
            eyebrow: __('messages.pet_profile_fc2c49bb42'),
            title: __('presentation.edit_profile_for', ['name' => $pet['name']]),
            description: __('messages.update_the_details_other_pet_people_use_before_planning__380010b90b'),
            action: 'update-pet',
            submitLabel: __('messages.save_pet_profile_839dbc815b'),
            submitIcon: 'check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('title', __('messages.name_dcd1d5223f'), 'text', $pet['name'], __('messages.pet_name_483f51db1f'), required: true),
                $this->field('category', __('messages.breed_d1ac8a8093'), 'text', $pet['breed'], __('messages.breed_or_companion_type_3643e58a92'), required: true),
                $this->field('detail', __('messages.availability_12f67f8539'), 'text', $pet['status'], __('messages.current_social_status_539e0514f4')),
                $this->field('body', __('messages.story_e9e509dcd3'), 'textarea', $pet['story'], __('messages.share_routines_preferences_and_personality_dc2232265c'), required: true),
            ],
            payload: ['target' => $pet['slug']],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    private function profilePrivacy(array $context, array $visibilityOptions): array
    {
        $privacy = $context['owner_privacy'] ?? [];

        return $this->definition(
            eyebrow: __('messages.owner_profile_privacy_233f194f47'),
            title: __('messages.choose_what_mia_shares_dbd67b8e47'),
            description: __('messages.each_profile_area_has_its_own_audience_exact_addresses_a_bb8dab3d35'),
            action: 'update-profile-privacy',
            submitLabel: __('messages.save_owner_privacy_aadcec4d5f'),
            submitIcon: 'shield-check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('location_visibility', __('messages.city_and_area_7c3055b4c7'), 'select', (string) ($privacy['location'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('pets_visibility', __('messages.pet_list_43504ec8c1'), 'select', (string) ($privacy['pets'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', __('messages.owner_posts_8986a90a06'), 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', __('messages.friend_list_e630797646'), 'select', (string) ($privacy['friends'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', __('messages.activity_status_67f774070a'), 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pet
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    private function petPrivacy(array $pet, array $context, array $visibilityOptions): array
    {
        $privacy = $context['pet_privacy'] ?? [];

        return $this->definition(
            eyebrow: __('messages.pet_profile_privacy_07b903d06f'),
            title: __('presentation.choose_sharing_for', ['name' => $pet['name']]),
            description: __('messages.pet_visibility_is_independent_from_mia_profile_visibilit_2273206a38'),
            action: 'update-pet-privacy',
            submitLabel: __('messages.save_pet_privacy_91d35b889b'),
            submitIcon: 'shield-check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('location_visibility', __('messages.city_and_area_7c3055b4c7'), 'select', (string) ($privacy['location'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', __('messages.pet_feed_e95604e8e0'), 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', __('messages.pet_friends_8866f0adbb'), 'select', (string) ($privacy['friends'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('care_visibility', __('messages.care_profile_a9c229194d'), 'select', (string) ($privacy['care'] ?? 'owners'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', __('messages.activity_status_67f774070a'), 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
            ],
            payload: ['target' => $pet['slug']],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function profileReport(array $context): array
    {
        $report = $context['report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException(__('messages.a_valid_profile_report_target_is_required_1e69818a0f'));
        }

        return $this->definition(
            eyebrow: __('messages.private_safety_report_46eda6ef34'),
            title: __('messages.report_83e2982ce4').$report['label'],
            description: __('messages.tell_the_moderation_team_what_happened_the_profile_owner_30a89ad40d'),
            action: 'create-profile-report',
            submitLabel: __('messages.submit_report_b41fd589ad'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: str_starts_with($report['target'], 'pet-') ? 'pets' : 'profile',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason_f81ab834de'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'fake-profile' => __('messages.fake_or_impersonating_profile_01ce6acc0f'),
                        'stolen-photos' => __('messages.stolen_animal_photos_8992ef9534'),
                        'animal-safety' => __('messages.animal_safety_concern_9907245780'),
                        'fraud' => __('messages.fraud_or_scam_556d825bf8'),
                        'spam' => __('messages.spam_or_unauthorized_advertising_0381fa0647'),
                        'harassment' => __('messages.harassment_or_abuse_6e9d8ee97c'),
                        'dangerous-advice' => __('messages.dangerous_medical_advice_4e602e83b0'),
                        'other' => __('messages.other_concern_910bb13965'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_c4dc542b51'), 'textarea', '', __('messages.add_context_or_evidence_for_the_moderation_team_ffdfdc1ec6'), required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function postReport(array $context): array
    {
        $report = $context['post_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException(__('messages.a_valid_publication_report_target_is_required_8a1f8aa358'));
        }

        return $this->definition(
            eyebrow: __('messages.private_safety_report_46eda6ef34'),
            title: __('messages.report_this_publication_4287864006'),
            description: __('messages.choose_the_closest_reason_and_give_moderators_enough_con_23b605f2a4'),
            action: 'create-post-report',
            submitLabel: __('messages.submit_report_b41fd589ad'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'feed',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason_f81ab834de'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: $context['post_report_reasons'] ?? [],
                ),
                $this->field('body', __('messages.what_happened_c4dc542b51'), 'textarea', '', __('messages.add_relevant_context_or_evidence_2041dead84'), required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function groupReport(array $context): array
    {
        $report = $context['group_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException(__('messages.a_valid_group_report_target_is_required_a9cb99398d'));
        }

        return $this->definition(
            eyebrow: __('messages.private_community_report_752d2f1d63'),
            title: __('messages.report_83e2982ce4').$report['label'],
            description: __('messages.choose_the_closest_reason_and_add_enough_context_for_the_abb3bc474b'),
            action: 'create-group-report',
            submitLabel: __('messages.submit_report_b41fd589ad'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'groups',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason_f81ab834de'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'spam' => __('messages.spam_or_unauthorized_advertising_0381fa0647'),
                        'harassment' => __('messages.harassment_or_abuse_6e9d8ee97c'),
                        'animal-safety' => __('messages.animal_safety_concern_9907245780'),
                        'dangerous-advice' => __('messages.dangerous_medical_advice_4e602e83b0'),
                        'fraud' => __('messages.fraud_or_unverified_fundraising_5047a1fa8f'),
                        'personal-data' => __('messages.private_information_was_exposed_a8f4163194'),
                        'illegal-sales' => __('messages.prohibited_animal_sales_3c69c95538'),
                        'stolen-media' => __('messages.stolen_photos_or_video_ac4a8e6108'),
                        'other' => __('messages.other_concern_910bb13965'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_c4dc542b51'), 'textarea', '', __('messages.add_relevant_context_dates_or_evidence_c1a2ca3b18'), required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function eventReport(array $context): array
    {
        $report = $context['event_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException(__('messages.a_valid_event_report_target_is_required_f7367e27e6'));
        }

        return $this->definition(
            eyebrow: __('messages.private_event_report_713316acc8'),
            title: __('messages.report_83e2982ce4').$report['label'],
            description: __('messages.tell_the_safety_team_what_happened_the_organizer_will_no_4edf5c2f06'),
            action: 'create-event-report',
            submitLabel: __('messages.submit_report_b41fd589ad'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'meetups',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason_f81ab834de'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'fraud' => __('messages.fraud_hidden_fees_or_a_fake_event_1bf69b8044'),
                        'animal-safety' => __('messages.animal_safety_or_cruel_treatment_26b23e270c'),
                        'harassment' => __('messages.threats_harassment_or_stalking_8bb72a2b01'),
                        'personal-data' => __('messages.private_information_was_exposed_a8f4163194'),
                        'illegal-sales' => __('messages.prohibited_animal_sale_5fbf80a1fb'),
                        'false-alert' => __('messages.false_emergency_or_search_alert_d711069c15'),
                        'dangerous-advice' => __('messages.dangerous_professional_advice_e332f6edb4'),
                        'other' => __('messages.other_concern_910bb13965'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_c4dc542b51'), 'textarea', '', __('messages.add_dates_messages_payment_context_or_other_evidence_3c6bb195e2'), required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function place(): array
    {
        return $this->definition(
            eyebrow: __('messages.community_map_0f11a85a32'),
            title: __('messages.add_a_place_ae05b5945b'),
            description: __('messages.share_enough_source_backed_information_for_moderators_to_b154c63cce'),
            action: 'create-place',
            submitLabel: __('messages.send_for_review_f7cb7531a9'),
            submitIcon: 'map-pin-plus',
            cancelRoute: 'places.index',
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.place_name_eed2561a03'), 'text', '', __('messages.use_the_name_shown_at_the_location_37ad60e4c5'), required: true),
                $this->field(
                    'category',
                    __('messages.primary_category_7637db1513'),
                    'select',
                    'park',
                    '',
                    required: true,
                    options: [
                        'park' => __('messages.park_6565cb0e22'),
                        'dog-park' => __('messages.dog_park_138fb35c3a'),
                        'route' => __('messages.walking_route_13e03decb5'),
                        'vet' => __('messages.veterinary_clinic_05e9176e96'),
                        'emergency-vet' => __('messages.24_hour_veterinary_clinic_e043a3c183'),
                        'pet-store' => __('messages.pet_store_15a6b4bd60'),
                        'grooming' => __('messages.grooming_5049c0bd16'),
                        'shelter' => __('messages.shelter_cfcd1f3d6a'),
                        'pet-cafe' => __('messages.pet_friendly_cafe_a146ead573'),
                    ],
                ),
                $this->field('city', __('messages.city_or_area_3d4dee46f8'), 'text', __('messages.vilnius_c283e0869a'), __('messages.city_district_or_region_c638fde56a'), required: true),
                $this->field('place_address', __('messages.public_address_or_entrance_dee01f92d0'), 'text', '', __('messages.do_not_enter_a_private_home_address_3d6af01a2b'), required: true),
                $this->field('place_coordinates', __('messages.approximate_coordinates_77d98c1bae'), 'text', '', __('messages.example_54_6892_25_2537_c44aa3bcda')),
                $this->field('body', __('messages.description_526e0087cc'), 'textarea', '', __('messages.what_is_here_who_is_it_useful_for_and_what_should_visito_f7f20d5ae5'), required: true),
                $this->field('place_hours', __('messages.hours_21e8492938'), 'textarea', '', __('messages.add_regular_seasonal_appointment_only_or_emergency_hours_b4d2055587')),
                $this->field('rules', __('messages.pet_rules_e0f08ad246'), 'textarea', '', __('messages.add_leash_species_size_access_and_event_rules_8eab0e2f7f'), required: true),
                $this->field('place_features', __('messages.facilities_and_accessibility_2f9fe78e4f'), 'textarea', '', __('messages.water_lighting_fencing_parking_ramps_quiet_zones_a414a3f49b')),
                $this->field('place_source', __('messages.information_source_cce9ae2a59'), 'url', '', __('messages.official_page_or_another_public_source_ec81e5616a')),
                $this->field('place_evidence', __('messages.evidence_note_257e942bb7'), 'textarea', '', __('messages.describe_a_sign_recent_visit_or_official_source_992471abe6')),
                $this->field(
                    'place_relationship',
                    __('messages.your_relationship_8376439493'),
                    'select',
                    'visitor',
                    '',
                    required: true,
                    options: [
                        'visitor' => __('messages.visitor_cf9f589632'),
                        'owner' => __('messages.owner_4b1b8aa360'),
                        'employee' => __('messages.employee_14014e6a57'),
                        'organization' => __('messages.organization_representative_a4ec5937ac'),
                        'city-representative' => __('messages.city_representative_ef47a8dfa5'),
                    ],
                ),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeCorrection(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_correction');

        return $this->definition(
            eyebrow: __('messages.community_correction_fa058012f5'),
            title: __('messages.correct_fd3c9f9f73').$place['label'],
            description: __('messages.propose_one_precise_change_and_include_a_recent_source_i_4eb90f4130'),
            action: 'create-place-correction',
            submitLabel: __('messages.submit_correction_b813cdf613'),
            submitIcon: 'file-check-2',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_field',
                    __('messages.what_changed_07f74744c6'),
                    'select',
                    'hours',
                    '',
                    required: true,
                    options: [
                        'hours' => __('messages.hours_21e8492938'),
                        'pet-rules' => __('messages.pet_rules_e0f08ad246'),
                        'address' => __('messages.address_or_map_point_1272080902'),
                        'contact' => __('messages.contact_details_9681395de4'),
                        'services' => __('messages.services_604dce445e'),
                        'accessibility' => __('messages.accessibility_d3368cbffe'),
                        'closure' => __('messages.temporary_or_permanent_closure_77ec4e1cb7'),
                    ],
                ),
                $this->field('place_current_value', __('messages.current_information_c49dcfd722'), 'textarea', '', __('messages.what_does_the_place_page_currently_say_046c6d06c5')),
                $this->field('body', __('messages.proposed_information_010e6730c8'), 'textarea', '', __('messages.write_the_corrected_information_clearly_eca90bd5be'), required: true),
                $this->field('place_visit_date', __('messages.date_checked_52e6675ddc'), 'date', today()->format('Y-m-d'), ''),
                $this->field('place_source', __('messages.public_source_9f1d704055'), 'url', '', __('messages.official_website_or_public_notice_ee5a77d976')),
                $this->field('place_evidence', __('messages.evidence_03867aea70'), 'textarea', '', __('messages.describe_the_sign_source_photo_or_visit_that_confirms_th_d20a02e30f'), required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeWarning(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: __('messages.temporary_safety_alert_fbac112f50'),
            title: __('messages.report_a_hazard_at_e03ed9bc52').$place['label'],
            description: __('messages.alerts_are_time_limited_and_reviewed_describe_the_exact__8f870bc2f3'),
            action: 'create-place-warning',
            submitLabel: __('messages.publish_alert_for_review_cc1be67f6c'),
            submitIcon: 'triangle-alert',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.short_warning_a45c73dc34'), 'text', '', __('messages.example_broken_glass_near_the_north_gate_1a929c8e41'), required: true),
                $this->field(
                    'category',
                    __('messages.hazard_5c5f3b2772'),
                    'select',
                    'broken-glass',
                    '',
                    required: true,
                    options: [
                        'broken-glass' => __('messages.broken_glass_or_sharp_debris_86a8e91b70'),
                        'poison' => __('messages.suspected_poison_7ef374a522'),
                        'dangerous-food' => __('messages.dangerous_food_9c3423efdb'),
                        'damaged-fence' => __('messages.damaged_fence_or_gate_295c519217'),
                        'ice' => __('messages.ice_or_slippery_surface_50d34f7bae'),
                        'road-closure' => __('messages.closed_route_or_entrance_16824695a2'),
                        'chemicals' => __('messages.chemical_treatment_af84664162'),
                        'water' => __('messages.unsafe_water_f0fe27b93d'),
                        'fire' => __('messages.fire_or_smoke_bdae6faef0'),
                        'flood' => __('messages.flooding_86468ff956'),
                        'lighting' => __('messages.lighting_failure_0094904818'),
                        'other' => __('messages.other_temporary_hazard_b99a2c0bf8'),
                    ],
                ),
                $this->field('place_zone', __('messages.area_inside_the_place_cacec89256'), 'text', '', __('messages.entrance_small_dog_zone_path_marker_be1380475f')),
                $this->field('body', __('messages.what_did_you_see_b97be28232'), 'textarea', '', __('messages.add_when_it_happened_and_what_visitors_should_avoid_868b3d9261'), required: true),
                $this->field('place_evidence', __('messages.evidence_note_257e942bb7'), 'textarea', '', __('messages.describe_a_current_photo_or_another_verifiable_source_032ae3583e')),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeReview(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: __('messages.verified_experience_eddd4ed430'),
            title: __('messages.review_70786ccffc').$place['label'],
            description: __('messages.review_the_place_and_its_published_information_do_not_in_b26f2d9ea7'),
            action: 'create-place-review',
            submitLabel: __('messages.publish_review_f795632a1e'),
            submitIcon: 'star',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_rating',
                    __('messages.overall_rating_ee62b83057'),
                    'select',
                    '5',
                    '',
                    required: true,
                    options: [
                        '5' => __('messages.5_excellent_02002b46a1'),
                        '4' => __('messages.4_good_295e8dabb3'),
                        '3' => __('messages.3_mixed_14cec79738'),
                        '2' => __('messages.2_poor_252214a24a'),
                        '1' => __('messages.1_very_poor_da85248424'),
                    ],
                ),
                $this->field(
                    'place_pet',
                    __('messages.visited_with_702d0076c2'),
                    'select',
                    'scout',
                    '',
                    required: true,
                    options: [
                        'scout' => __('messages.scout_8a1db462be'),
                        'nori' => __('messages.nori_a64203ba20'),
                    ],
                ),
                $this->field(
                    'place_review_criterion',
                    __('messages.main_topic_ab471789dd'),
                    'select',
                    'overall',
                    '',
                    options: [
                        'overall' => __('messages.overall_experience_be0e3d5ccd'),
                        'safety' => __('messages.safety_726d11bd5b'),
                        'accessibility' => __('messages.accessibility_d3368cbffe'),
                        'accuracy' => __('messages.information_accuracy_af9c84abf0'),
                        'communication' => __('messages.communication_3981a2b9c1'),
                        'cleanliness' => __('messages.cleanliness_ff35464a98'),
                        'price' => __('messages.price_clarity_4036c2a201'),
                    ],
                ),
                $this->field('place_visit_date', __('messages.visit_date_a00dd80c03'), 'date', today()->format('Y-m-d'), ''),
                $this->field('body', __('messages.review_aff0766a52'), 'textarea', '', __('messages.what_matched_the_listing_and_what_should_another_owner_k_41185b7c9f'), required: true),
                $this->field(
                    'place_anonymous',
                    __('messages.public_identity_284303e3ab'),
                    'select',
                    'no',
                    '',
                    options: [
                        'no' => __('messages.show_my_profile_48d1dda29e'),
                        'yes' => __('messages.hide_my_name_publicly_bb9089b2da'),
                    ],
                ),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeQuestion(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: __('messages.place_questions_2cf1d84855'),
            title: __('messages.ask_about_d73af78ba3').$place['label'],
            description: __('messages.ask_one_practical_question_answers_identify_whether_they_5f0a79d1a2'),
            action: 'create-place-question',
            submitLabel: __('messages.ask_question_8fa2965f2d'),
            submitIcon: 'message-circle-question',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('body', __('messages.question_289aff12b0'), 'textarea', '', __('messages.example_is_the_small_dog_gate_working_today_7e441f813f'), required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeClaim(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: __('messages.business_verification_f9a0e7ca49'),
            title: __('messages.claim_aa264a7118').$place['label'],
            description: __('messages.verification_grants_management_tools_not_control_over_co_822d2c1089'),
            action: 'create-place-claim',
            submitLabel: __('messages.request_access_b06f1662da'),
            submitIcon: 'badge-check',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.organization_or_business_name_a96a05eb15'), 'text', '', __('messages.legal_or_public_trading_name_d46aecce99'), required: true),
                $this->field(
                    'place_relationship',
                    __('messages.your_relationship_8376439493'),
                    'select',
                    'owner',
                    '',
                    required: true,
                    options: [
                        'owner' => __('messages.owner_4b1b8aa360'),
                        'employee' => __('messages.employee_14014e6a57'),
                        'organization' => __('messages.organization_representative_a4ec5937ac'),
                        'city-representative' => __('messages.city_representative_ef47a8dfa5'),
                        'visitor' => __('messages.other_relationship_ecf4dbd1c1'),
                    ],
                ),
                $this->field('place_contact', __('messages.official_contact_330466fd1a'), 'text', '', __('messages.domain_email_or_public_business_phone_2e3ccddea0'), required: true),
                $this->field(
                    'place_verification_method',
                    __('messages.verification_method_8e9144afb0'),
                    'select',
                    'domain-email',
                    '',
                    required: true,
                    options: [
                        'domain-email' => __('messages.email_on_the_official_domain_1d824ceaa9'),
                        'phone' => __('messages.call_the_published_business_number_038aff13ab'),
                        'address-code' => __('messages.code_delivered_to_the_place_950641c8fc'),
                        'organization-document' => __('messages.organization_document_90cd572159'),
                        'manual-review' => __('messages.manual_review_73159f6e47'),
                    ],
                ),
                $this->field('place_evidence', __('messages.verification_evidence_a9b1c08899'), 'textarea', '', __('messages.explain_how_the_moderation_team_can_verify_your_authorit_466a0dee32'), required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeReport(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: __('messages.private_place_report_79a04a8c18'),
            title: __('messages.report_83e2982ce4').$place['label'],
            description: __('messages.send_a_private_report_for_persistent_fraudulent_privacy__8144f95781'),
            action: 'create-place-report',
            submitLabel: __('messages.submit_report_b41fd589ad'),
            submitIcon: 'flag',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason_f81ab834de'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'does-not-exist' => __('messages.place_does_not_exist_d01d447429'),
                        'wrong-address' => __('messages.wrong_address_7960c95985'),
                        'closed' => __('messages.closed_permanently_f62671e55e'),
                        'fake-business' => __('messages.fake_or_impersonated_business_79a8f0287b'),
                        'dangerous-information' => __('messages.dangerous_or_misleading_information_399941c214'),
                        'animal-cruelty' => __('messages.animal_cruelty_concern_39d9153008'),
                        'fraud' => __('messages.fraud_or_payment_concern_2a695545f1'),
                        'hidden-fees' => __('messages.hidden_fees_e53dcef3c0'),
                        'privacy' => __('messages.private_information_exposed_d124a39414'),
                        'stolen-photos' => __('messages.stolen_photos_81f0aaaf4d'),
                        'false-professional-info' => __('messages.false_professional_information_80f18f1b76'),
                        'other' => __('messages.other_concern_910bb13965'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_c4dc542b51'), 'textarea', '', __('messages.add_dates_context_and_the_practical_risk_f0bb6600b6'), required: true),
                $this->field('place_evidence', __('messages.evidence_03867aea70'), 'textarea', '', __('messages.optional_public_source_photo_description_or_supporting_c_7e66946402')),
            ],
            payload: [
                'target' => $place['target'],
                'label' => $place['label'],
            ],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function requiredPlaceContext(array $context, string $key): array
    {
        $place = $context[$key] ?? null;

        if (! is_array($place)) {
            throw new InvalidArgumentException(__('messages.a_valid_place_target_is_required_99dd31d234'));
        }

        return $place;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function deletePost(array $context): array
    {
        $post = $context['post'] ?? null;

        if (! is_array($post)) {
            throw new InvalidArgumentException(__('messages.a_valid_managed_publication_is_required_59beeae2de'));
        }

        return $this->definition(
            eyebrow: __('messages.publication_lifecycle_b324ae2ec0'),
            title: __('messages.delete_this_publication_cac3d7c93b'),
            description: __('messages.this_removes_the_publication_from_the_prototype_feed_use_d2089fe1d4'),
            action: 'delete-post',
            submitLabel: __('messages.delete_publication_88d31ebb56'),
            submitIcon: 'trash-2',
            cancelRoute: 'posts.show',
            activeSection: 'feed',
            fields: [],
            payload: ['target' => $post['key']],
            cancelParameters: ['post' => $post['key']],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function definition(
        string $eyebrow,
        string $title,
        string $description,
        string $action,
        string $submitLabel,
        string $submitIcon,
        string $cancelRoute,
        string $activeSection,
        array $fields,
        array $payload = [],
        array $cancelParameters = [],
        array $secondaryActions = [],
    ): array {
        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'description' => $description,
            'action' => $action,
            'submit_label' => $submitLabel,
            'submit_icon' => $submitIcon,
            'cancel_route' => $cancelRoute,
            'cancel_parameters' => $cancelParameters,
            'active_section' => $activeSection,
            'fields' => $fields,
            'payload' => $payload,
            'secondary_actions' => $secondaryActions,
        ];
    }

    /**
     * @param  array<int|string, string>  $options
     * @return array{
     *     name: string,
     *     label: string,
     *     type: string,
     *     value: string,
     *     placeholder: string,
     *     required: bool,
     *     options: array<int|string, string>,
     *     min: string|null,
     *     autocomplete: string|null
     * }
     */
    private function field(
        string $name,
        string $label,
        string $type,
        string $value,
        string $placeholder,
        bool $required = false,
        array $options = [],
        ?string $min = null,
        ?string $autocomplete = null,
    ): array {
        return compact(
            'name',
            'label',
            'type',
            'value',
            'placeholder',
            'required',
            'options',
            'min',
            'autocomplete',
        );
    }
}
