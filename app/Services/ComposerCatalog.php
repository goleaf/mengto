<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;
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
            throw new InvalidArgumentException(__('messages.a_valid_editable_post_is_required'));
        }

        $mediaOptions = ['none' => __('messages.no_media')];

        foreach ($context['media_presets'] ?? [] as $key => $preset) {
            if ($key !== 'none') {
                $mediaOptions[$key] = (string) ($preset['label'] ?? ucfirst((string) $key));
            }
        }

        return $this->definition(
            eyebrow: __('messages.neighborhood_feed'),
            title: $editing ? __('messages.edit_your_publication') : __('messages.create_a_publication'),
            description: __('messages.choose_the_publishing_profile_a_safe_audience_and_only_the_context_neighbors_need'),
            action: $editing ? 'update-post' : 'create-post',
            submitLabel: $editing ? __('messages.save_changes') : __('messages.publish'),
            submitIcon: $editing ? 'check' : 'send',
            cancelRoute: 'preview.feed',
            activeSection: 'feed',
            fields: [
                $this->field(
                    'identity',
                    __('messages.publish_as'),
                    'select',
                    (string) ($post['identity'] ?? 'mia'),
                    '',
                    required: true,
                    options: $context['identities'] ?? [],
                ),
                $this->field(
                    'format',
                    __('messages.format'),
                    'select',
                    (string) ($post['format'] ?? 'photo'),
                    '',
                    required: true,
                    options: [
                        'text' => __('messages.text_update'),
                        'photo' => __('messages.photo_update'),
                        'video' => __('messages.video'),
                        'question' => __('messages.question'),
                        'lost' => __('messages.lost_pet_alert'),
                        'adoption' => __('messages.adoption_profile'),
                    ],
                ),
                $this->field('title', __('messages.headline'), 'text', (string) ($post['title'] ?? ''), __('messages.optional_short_headline')),
                $this->field('body', __('messages.post'), 'textarea', (string) ($post['body'] ?? ''), __('messages.share_the_useful_part_of_the_story'), required: true),
                $this->field(
                    'topic',
                    __('messages.topic'),
                    'select',
                    (string) ($post['topic'] ?? 'community'),
                    '',
                    required: true,
                    options: $context['topics'] ?? [],
                ),
                $this->field('tags', 'Tags', 'text', (string) ($post['tags'] ?? ''), __('messages.training_portland_rescue')),
                $this->field(
                    'media',
                    __('messages.media_preview'),
                    'select',
                    (string) ($post['media'] ?? 'park-carousel'),
                    '',
                    required: true,
                    options: $mediaOptions,
                ),
                $this->field('media_alt', __('messages.media_description'), 'text', (string) ($post['media_alt'] ?? ''), __('messages.required_when_media_is_selected')),
                $this->field(
                    'location',
                    __('messages.safe_place'),
                    'select',
                    (string) ($post['location'] ?? 'none'),
                    '',
                    required: true,
                    options: $context['safe_places'] ?? [],
                ),
                $this->field(
                    'audience',
                    __('messages.audience'),
                    'select',
                    (string) ($post['audience'] ?? 'public'),
                    '',
                    required: true,
                    options: $context['audiences'] ?? [],
                ),
                $this->field(
                    'comment_policy',
                    __('messages.who_can_comment'),
                    'select',
                    (string) ($post['comment_policy'] ?? 'all'),
                    '',
                    required: true,
                    options: $context['comment_policies'] ?? [],
                ),
                $this->field(
                    'sensitive',
                    __('messages.sensitive_media'),
                    'select',
                    (string) ($post['sensitive'] ?? 'no'),
                    '',
                    required: true,
                    options: ['no' => __('messages.no_warning_needed'), 'yes' => __('messages.hide_behind_a_content_warning')],
                ),
            ],
            payload: $editing ? ['target' => $post['key']] : [],
            secondaryActions: [
                [
                    'label' => __('messages.save_draft'),
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
            eyebrow: __('messages.community_builder'),
            title: __('messages.create_a_focused_group'),
            description: __('messages.set_the_purpose_membership_boundary_posting_policy_and_first_rules_before_inviting_anyone'),
            action: 'create-group',
            submitLabel: __('messages.create_group'),
            submitIcon: 'users-round',
            cancelRoute: 'groups.index',
            activeSection: 'groups',
            fields: [
                $this->field('title', __('messages.group_name'), 'text', '', __('messages.example_richmond_morning_walks'), required: true),
                $this->field(
                    'category',
                    __('messages.category'),
                    'select',
                    'local',
                    '',
                    required: true,
                    options: [
                        'breed' => __('messages.breed_community'),
                        'species' => __('messages.animal_type'),
                        'local' => __('messages.local_community'),
                        'interest' => __('messages.shared_interest'),
                        'training' => __('messages.training_and_behavior'),
                        'care' => __('messages.care_and_health_support'),
                        'adoption' => __('messages.adoption_and_fostering'),
                        'volunteering' => __('messages.volunteering'),
                    ],
                ),
                $this->field(
                    'privacy',
                    __('messages.privacy'),
                    'select',
                    'closed',
                    '',
                    required: true,
                    options: [
                        'public' => __('messages.public_anyone_can_read_and_join'),
                        'closed' => __('messages.closed_members_are_approved'),
                    ],
                ),
                $this->field('city', __('messages.city_or_region'), 'text', '', __('messages.example_portland_oregon'), required: true),
                $this->field(
                    'language',
                    __('messages.primary_language'),
                    'select',
                    __('messages.english'),
                    '',
                    required: true,
                    options: [
                        'English' => __('messages.english'),
                        'English + Spanish' => __('messages.english_spanish'),
                        'Russian' => __('messages.russian'),
                        'Lithuanian' => __('messages.lithuanian'),
                    ],
                ),
                $this->field(
                    'pet_identity',
                    __('messages.participating_profiles'),
                    'select',
                    'all',
                    '',
                    required: true,
                    options: [
                        'mia' => __('messages.mia_only'),
                        'scout' => __('messages.mia_with_scout'),
                        'nori' => __('messages.mia_with_nori'),
                        'all' => __('messages.mia_with_scout_and_nori'),
                    ],
                ),
                $this->field(
                    'posting_policy',
                    __('messages.who_can_publish'),
                    'select',
                    'members',
                    '',
                    required: true,
                    options: [
                        'members' => __('messages.all_members'),
                        'review' => __('messages.members_after_moderator_review'),
                        'staff' => __('messages.administrators_and_moderators_only'),
                    ],
                ),
                $this->field('body', __('messages.description'), 'textarea', '', __('messages.who_is_this_group_for_and_what_belongs_here'), required: true),
                $this->field('rules', __('messages.first_community_rules'), 'textarea', '', __('messages.add_privacy_safety_promotion_and_respectful_conversation_boundaries'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function walk(): array
    {
        return $this->definition(
            eyebrow: __('messages.walk_planner'),
            title: __('messages.plan_a_neighborhood_walk'),
            description: __('messages.set_a_calm_route_clear_timing_and_an_easy_pace_before_sending_the_plan_to_a_neighbor'),
            action: 'create-walk-plan',
            submitLabel: __('messages.save_walk_draft'),
            submitIcon: 'calendar-plus',
            cancelRoute: 'walks.index',
            activeSection: 'meetups',
            fields: [
                $this->field(
                    'target',
                    __('messages.walking_with'),
                    'select',
                    'mochi',
                    '',
                    required: true,
                    options: [
                        'mochi' => __('messages.ari_and_mochi'),
                        'juniper' => __('messages.noah_and_juniper'),
                        'scout' => __('messages.scout_and_mia'),
                    ],
                ),
                $this->field('title', __('messages.plan_name'), 'text', '', __('messages.example_early_fields_park_loop'), required: true),
                $this->field('date', __('messages.date'), 'date', '', '', required: true, min: today()->format('Y-m-d')),
                $this->field('time', __('messages.start_time'), 'time', '08:30', '', required: true),
                $this->field('location', __('messages.meeting_point'), 'text', '', __('messages.park_gate_quiet_corner_or_familiar_block'), required: true),
                $this->field(
                    'detail',
                    __('messages.pace'),
                    'select',
                    __('messages.easy_pace_30_min'),
                    '',
                    options: [
                        'Easy pace, 20 min' => __('messages.easy_pace_20_min'),
                        'Easy pace, 30 min' => __('messages.easy_pace_30_min'),
                        'Steady pace, 45 min' => __('messages.steady_pace_45_min'),
                        'Sniff-friendly, no time limit' => __('messages.sniff_friendly_no_time_limit'),
                    ],
                ),
                $this->field('body', __('messages.routine_notes'), 'textarea', '', __('messages.add_greetings_triggers_water_stops_or_a_quiet_finish'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function pet(): array
    {
        return $this->definition(
            eyebrow: __('messages.your_pack'),
            title: __('messages.add_a_pet'),
            description: __('messages.create_a_simple_profile_that_helps_neighbors_understand_your_pet_s_routine_and_pace'),
            action: 'create-pet',
            submitLabel: __('messages.add_pet'),
            submitIcon: 'paw-print',
            cancelRoute: 'pets.index',
            activeSection: 'pets',
            fields: [
                $this->field('title', __('messages.pet_name'), 'text', '', __('messages.pet_name'), required: true),
                $this->field('category', __('messages.species'), 'text', '', __('messages.dog_cat_rabbit'), required: true),
                $this->field('detail', __('messages.breed_or_type'), 'text', '', __('messages.breed_or_companion_type')),
                $this->field('body', __('messages.short_profile'), 'textarea', '', __('messages.share_a_favorite_routine_or_social_preference'), required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function message(): array
    {
        return $this->definition(
            eyebrow: __('messages.neighborhood_inbox'),
            title: __('messages.start_a_new_message'),
            description: __('messages.write_a_clear_note_about_a_walk_care_question_or_local_plan'),
            action: 'send-message',
            submitLabel: __('messages.send_message'),
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
                        'ari' => __('messages.ari_jensen_and_mochi'),
                        'lena' => __('messages.lena_brooks_and_pip'),
                        'noah' => __('messages.noah_patel_and_juniper'),
                        'priya' => __('messages.priya_shah_and_clover'),
                    ],
                ),
                $this->field('body', __('messages.message'), 'textarea', '', __('messages.write_your_message'), required: true),
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
            eyebrow: __('messages.your_profile'),
            title: __('messages.edit_your_brand_profile'),
            description: __('messages.keep_the_details_neighbors_use_to_plan_walks_and_introductions_current'),
            action: 'update-profile',
            submitLabel: __('messages.save_profile'),
            submitIcon: 'check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('title', __('messages.name'), 'text', $owner['name'], __('messages.your_name'), required: true, autocomplete: 'name'),
                $this->field('location', __('messages.location'), 'text', $owner['location'], __('messages.neighborhood_and_city'), required: true, autocomplete: 'address-level2'),
                $this->field('detail', __('messages.availability'), 'text', $owner['status'], __('messages.when_are_you_open_to_meeting')),
                $this->field('body', __('messages.about_you'), 'textarea', $owner['bio'], __('messages.share_your_routines_and_interests'), required: true),
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
            eyebrow: __('messages.pet_profile'),
            title: __('presentation.edit_profile_for', ['name' => $pet['name']]),
            description: __('messages.update_the_details_other_pet_people_use_before_planning_time_together'),
            action: 'update-pet',
            submitLabel: __('messages.save_pet_profile'),
            submitIcon: 'check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('title', __('messages.name'), 'text', $pet['name'], __('messages.pet_name'), required: true),
                $this->field('category', __('messages.breed'), 'text', $pet['breed'], __('messages.breed_or_companion_type'), required: true),
                $this->field('detail', __('messages.availability'), 'text', $pet['status'], __('messages.current_social_status')),
                $this->field('body', __('messages.story'), 'textarea', $pet['story'], __('messages.share_routines_preferences_and_personality'), required: true),
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
            eyebrow: __('messages.owner_profile_privacy'),
            title: __('messages.choose_what_mia_shares'),
            description: __('messages.each_profile_area_has_its_own_audience_exact_addresses_and_private_contact_details_stay_unavailable'),
            action: 'update-profile-privacy',
            submitLabel: __('messages.save_owner_privacy'),
            submitIcon: 'shield-check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('location_visibility', __('messages.city_and_area'), 'select', (string) ($privacy['location'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('pets_visibility', __('messages.pet_list'), 'select', (string) ($privacy['pets'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', __('messages.owner_posts'), 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', __('messages.friend_list'), 'select', (string) ($privacy['friends'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', __('messages.activity_status'), 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
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
            eyebrow: __('messages.pet_profile_privacy'),
            title: __('presentation.choose_sharing_for', ['name' => $pet['name']]),
            description: __('messages.pet_visibility_is_independent_from_mia_profile_visibility_and_can_be_changed_at_any_time'),
            action: 'update-pet-privacy',
            submitLabel: __('messages.save_pet_privacy'),
            submitIcon: 'shield-check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('location_visibility', __('messages.city_and_area'), 'select', (string) ($privacy['location'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', __('messages.pet_feed'), 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', __('messages.pet_friends'), 'select', (string) ($privacy['friends'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('care_visibility', __('messages.care_profile'), 'select', (string) ($privacy['care'] ?? 'owners'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', __('messages.activity_status'), 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
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
            throw new InvalidArgumentException(__('messages.a_valid_profile_report_target_is_required'));
        }

        return $this->definition(
            eyebrow: __('messages.private_safety_report'),
            title: __('messages.report_prefix').$report['label'],
            description: __('messages.tell_the_moderation_team_what_happened_the_profile_owner_will_not_see_who_sent_this_report'),
            action: 'create-profile-report',
            submitLabel: __('messages.submit_report'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: str_starts_with($report['target'], 'pet-') ? 'pets' : 'profile',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'fake-profile' => __('messages.fake_or_impersonating_profile'),
                        'stolen-photos' => __('messages.stolen_animal_photos'),
                        'animal-safety' => __('messages.animal_safety_concern'),
                        'fraud' => __('messages.fraud_or_scam'),
                        'spam' => __('messages.spam_or_unauthorized_advertising'),
                        'harassment' => __('messages.harassment_or_abuse'),
                        'dangerous-advice' => __('messages.dangerous_medical_advice'),
                        'other' => __('messages.other_concern'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_question'), 'textarea', '', __('messages.add_context_or_evidence_for_the_moderation_team'), required: true),
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
            throw new InvalidArgumentException(__('messages.a_valid_publication_report_target_is_required'));
        }

        return $this->definition(
            eyebrow: __('messages.private_safety_report'),
            title: __('messages.report_this_publication'),
            description: __('messages.choose_the_closest_reason_and_give_moderators_enough_context_to_review_the_publication'),
            action: 'create-post-report',
            submitLabel: __('messages.submit_report'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'feed',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: $context['post_report_reasons'] ?? [],
                ),
                $this->field('body', __('messages.what_happened_question'), 'textarea', '', __('messages.add_relevant_context_or_evidence'), required: true),
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
            throw new InvalidArgumentException(__('messages.a_valid_group_report_target_is_required'));
        }

        return $this->definition(
            eyebrow: __('messages.private_community_report'),
            title: __('messages.report_prefix').$report['label'],
            description: __('messages.choose_the_closest_reason_and_add_enough_context_for_the_moderation_team_group_moderators_will_not_see_who_submitted_it'),
            action: 'create-group-report',
            submitLabel: __('messages.submit_report'),
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'groups',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'spam' => __('messages.spam_or_unauthorized_advertising'),
                        'harassment' => __('messages.harassment_or_abuse'),
                        'animal-safety' => __('messages.animal_safety_concern'),
                        'dangerous-advice' => __('messages.dangerous_medical_advice'),
                        'fraud' => __('messages.fraud_or_unverified_fundraising'),
                        'personal-data' => __('messages.private_information_was_exposed'),
                        'illegal-sales' => __('messages.prohibited_animal_sales'),
                        'stolen-media' => __('messages.stolen_photos_or_video'),
                        'other' => __('messages.other_concern'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_question'), 'textarea', '', __('messages.add_relevant_context_dates_or_evidence'), required: true),
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
            eyebrow: __('messages.community_map'),
            title: __('messages.add_a_place'),
            description: __('messages.share_enough_source_backed_information_for_moderators_to_check_the_place_without_exposing_a_private_home_address'),
            action: 'create-place',
            submitLabel: __('messages.send_for_review'),
            submitIcon: 'map-pin-plus',
            cancelRoute: 'places.index',
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.place_name'), 'text', '', __('messages.use_the_name_shown_at_the_location'), required: true),
                $this->field(
                    'category',
                    __('messages.primary_category'),
                    'select',
                    'park',
                    '',
                    required: true,
                    options: [
                        'park' => __('messages.park'),
                        'dog-park' => __('messages.dog_park'),
                        'route' => __('messages.walking_route'),
                        'vet' => __('messages.veterinary_clinic'),
                        'emergency-vet' => __('messages.24_hour_veterinary_clinic'),
                        'pet-store' => __('messages.pet_store'),
                        'grooming' => __('messages.grooming'),
                        'shelter' => __('messages.shelter'),
                        'pet-cafe' => __('messages.pet_friendly_cafe'),
                    ],
                ),
                $this->field('city', __('messages.city_or_area'), 'text', __('messages.vilnius'), __('messages.city_district_or_region'), required: true),
                $this->field('place_address', __('messages.public_address_or_entrance'), 'text', '', __('messages.do_not_enter_a_private_home_address'), required: true),
                $this->field('place_coordinates', __('messages.approximate_coordinates'), 'text', '', __('messages.example_54_6892_25_2537')),
                $this->field('body', __('messages.description'), 'textarea', '', __('messages.what_is_here_who_is_it_useful_for_and_what_should_visitors_know'), required: true),
                $this->field('place_hours', __('messages.hours'), 'textarea', '', __('messages.add_regular_seasonal_appointment_only_or_emergency_hours')),
                $this->field('rules', __('messages.pet_rules'), 'textarea', '', __('messages.add_leash_species_size_access_and_event_rules'), required: true),
                $this->field('place_features', __('messages.facilities_and_accessibility'), 'textarea', '', __('messages.water_lighting_fencing_parking_ramps_quiet_zones')),
                $this->field('place_source', __('messages.information_source'), 'url', '', __('messages.official_page_or_another_public_source')),
                $this->field('place_evidence', __('messages.evidence_note'), 'textarea', '', __('messages.describe_a_sign_recent_visit_or_official_source')),
                $this->field(
                    'place_relationship',
                    __('messages.your_relationship'),
                    'select',
                    'visitor',
                    '',
                    required: true,
                    options: [
                        'visitor' => __('messages.visitor'),
                        'owner' => __('messages.owner'),
                        'employee' => __('messages.employee'),
                        'organization' => __('messages.organization_representative'),
                        'city-representative' => __('messages.city_representative'),
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
            eyebrow: __('messages.community_correction'),
            title: __('messages.correct').$place['label'],
            description: __('messages.propose_one_precise_change_and_include_a_recent_source_important_details_stay_unchanged_until_reviewed'),
            action: 'create-place-correction',
            submitLabel: __('messages.submit_correction'),
            submitIcon: 'file-check-2',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_field',
                    __('messages.what_changed'),
                    'select',
                    'hours',
                    '',
                    required: true,
                    options: [
                        'hours' => __('messages.hours'),
                        'pet-rules' => __('messages.pet_rules'),
                        'address' => __('messages.address_or_map_point'),
                        'contact' => __('messages.contact_details'),
                        'services' => __('messages.services'),
                        'accessibility' => __('messages.accessibility'),
                        'closure' => __('messages.temporary_or_permanent_closure'),
                    ],
                ),
                $this->field('place_current_value', __('messages.current_information'), 'textarea', '', __('messages.what_does_the_place_page_currently_say')),
                $this->field('body', __('messages.proposed_information'), 'textarea', '', __('messages.write_the_corrected_information_clearly'), required: true),
                $this->field('place_visit_date', __('messages.date_checked'), 'date', today()->format('Y-m-d'), ''),
                $this->field('place_source', __('messages.public_source'), 'url', '', __('messages.official_website_or_public_notice')),
                $this->field('place_evidence', __('messages.evidence'), 'textarea', '', __('messages.describe_the_sign_source_photo_or_visit_that_confirms_this_change'), required: true),
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
            eyebrow: __('messages.temporary_safety_alert'),
            title: __('messages.report_a_hazard_at').$place['label'],
            description: __('messages.alerts_are_time_limited_and_reviewed_describe_the_exact_area_without_exposing_another_person_s_private_information'),
            action: 'create-place-warning',
            submitLabel: __('messages.publish_alert_for_review'),
            submitIcon: 'triangle-alert',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.short_warning'), 'text', '', __('messages.example_broken_glass_near_the_north_gate'), required: true),
                $this->field(
                    'category',
                    __('messages.hazard'),
                    'select',
                    'broken-glass',
                    '',
                    required: true,
                    options: [
                        'broken-glass' => __('messages.broken_glass_or_sharp_debris'),
                        'poison' => __('messages.suspected_poison'),
                        'dangerous-food' => __('messages.dangerous_food'),
                        'damaged-fence' => __('messages.damaged_fence_or_gate'),
                        'ice' => __('messages.ice_or_slippery_surface'),
                        'road-closure' => __('messages.closed_route_or_entrance'),
                        'chemicals' => __('messages.chemical_treatment'),
                        'water' => __('messages.unsafe_water'),
                        'fire' => __('messages.fire_or_smoke'),
                        'flood' => __('messages.flooding'),
                        'lighting' => __('messages.lighting_failure'),
                        'other' => __('messages.other_temporary_hazard'),
                    ],
                ),
                $this->field('place_zone', __('messages.area_inside_the_place'), 'text', '', __('messages.entrance_small_dog_zone_path_marker')),
                $this->field('body', __('messages.what_did_you_see'), 'textarea', '', __('messages.add_when_it_happened_and_what_visitors_should_avoid'), required: true),
                $this->field('place_evidence', __('messages.evidence_note'), 'textarea', '', __('messages.describe_a_current_photo_or_another_verifiable_source')),
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
            eyebrow: __('messages.verified_experience'),
            title: __('messages.review_prefix').$place['label'],
            description: __('messages.review_the_place_and_its_published_information_do_not_include_medical_records_or_another_person_s_private_details'),
            action: 'create-place-review',
            submitLabel: __('messages.publish_review'),
            submitIcon: 'star',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_rating',
                    __('messages.overall_rating'),
                    'select',
                    '5',
                    '',
                    required: true,
                    options: [
                        '5' => __('messages.5_excellent'),
                        '4' => __('messages.4_good'),
                        '3' => __('messages.3_mixed'),
                        '2' => __('messages.2_poor'),
                        '1' => __('messages.1_very_poor'),
                    ],
                ),
                $this->field(
                    'place_pet',
                    __('messages.visited_with'),
                    'select',
                    'scout',
                    '',
                    required: true,
                    options: [
                        'scout' => __('messages.scout'),
                        'nori' => __('messages.nori'),
                    ],
                ),
                $this->field(
                    'place_review_criterion',
                    __('messages.main_topic'),
                    'select',
                    'overall',
                    '',
                    options: [
                        'overall' => __('messages.overall_experience'),
                        'safety' => __('messages.safety'),
                        'accessibility' => __('messages.accessibility'),
                        'accuracy' => __('messages.information_accuracy'),
                        'communication' => __('messages.communication'),
                        'cleanliness' => __('messages.cleanliness'),
                        'price' => __('messages.price_clarity'),
                    ],
                ),
                $this->field('place_visit_date', __('messages.visit_date'), 'date', today()->format('Y-m-d'), ''),
                $this->field('body', __('messages.review'), 'textarea', '', __('messages.what_matched_the_listing_and_what_should_another_owner_know'), required: true),
                $this->field(
                    'place_anonymous',
                    __('messages.public_identity'),
                    'select',
                    'no',
                    '',
                    options: [
                        'no' => __('messages.show_my_profile'),
                        'yes' => __('messages.hide_my_name_publicly'),
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
            eyebrow: __('messages.place_questions'),
            title: __('messages.ask_about').$place['label'],
            description: __('messages.ask_one_practical_question_answers_identify_whether_they_came_from_an_owner_staff_member_specialist_moderator_or_visitor'),
            action: 'create-place-question',
            submitLabel: __('messages.ask_question'),
            submitIcon: 'message-circle-question',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('body', __('messages.question'), 'textarea', '', __('messages.example_is_the_small_dog_gate_working_today'), required: true),
            ],
            payload: [
                'target' => $place['target'],
                'place_idempotency_key' => (string) Str::uuid(),
            ],
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
            eyebrow: __('messages.business_verification'),
            title: __('messages.claim').$place['label'],
            description: __('messages.verification_grants_management_tools_not_control_over_community_reviews_or_moderation_decisions'),
            action: 'create-place-claim',
            submitLabel: __('messages.request_access'),
            submitIcon: 'badge-check',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', __('messages.organization_or_business_name'), 'text', '', __('messages.legal_or_public_trading_name'), required: true),
                $this->field(
                    'place_relationship',
                    __('messages.your_relationship'),
                    'select',
                    'owner',
                    '',
                    required: true,
                    options: [
                        'owner' => __('messages.owner'),
                        'employee' => __('messages.employee'),
                        'organization' => __('messages.organization_representative'),
                        'city-representative' => __('messages.city_representative'),
                        'visitor' => __('messages.other_relationship'),
                    ],
                ),
                $this->field('place_contact', __('messages.official_contact'), 'text', '', __('messages.domain_email_or_public_business_phone'), required: true),
                $this->field(
                    'place_verification_method',
                    __('messages.verification_method'),
                    'select',
                    'domain-email',
                    '',
                    required: true,
                    options: [
                        'domain-email' => __('messages.email_on_the_official_domain'),
                        'phone' => __('messages.call_the_published_business_number'),
                        'address-code' => __('messages.code_delivered_to_the_place'),
                        'organization-document' => __('messages.organization_document'),
                        'manual-review' => __('messages.manual_review'),
                    ],
                ),
                $this->field('place_evidence', __('messages.verification_evidence'), 'textarea', '', __('messages.explain_how_the_moderation_team_can_verify_your_authority'), required: true),
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
            eyebrow: __('messages.private_place_report'),
            title: __('messages.report_prefix').$place['label'],
            description: __('messages.send_a_private_report_for_persistent_fraudulent_privacy_or_serious_safety_concerns_use_a_temporary_alert_for_a_short_lived_local_hazard'),
            action: 'create-place-report',
            submitLabel: __('messages.submit_report'),
            submitIcon: 'flag',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'category',
                    __('messages.reason'),
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'does-not-exist' => __('messages.place_does_not_exist'),
                        'wrong-address' => __('messages.wrong_address'),
                        'closed' => __('messages.closed_permanently'),
                        'fake-business' => __('messages.fake_or_impersonated_business'),
                        'dangerous-information' => __('messages.dangerous_or_misleading_information'),
                        'animal-cruelty' => __('messages.animal_cruelty_concern'),
                        'fraud' => __('messages.fraud_or_payment_concern'),
                        'hidden-fees' => __('messages.hidden_fees'),
                        'privacy' => __('messages.private_information_exposed'),
                        'stolen-photos' => __('messages.stolen_photos'),
                        'false-professional-info' => __('messages.false_professional_information'),
                        'other' => __('messages.other_concern'),
                    ],
                ),
                $this->field('body', __('messages.what_happened_question'), 'textarea', '', __('messages.add_dates_context_and_the_practical_risk'), required: true),
                $this->field('place_evidence', __('messages.evidence'), 'textarea', '', __('messages.optional_public_source_photo_description_or_supporting_context')),
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
            throw new InvalidArgumentException(__('messages.a_valid_place_target_is_required'));
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
            throw new InvalidArgumentException(__('messages.a_valid_managed_publication_is_required'));
        }

        return $this->definition(
            eyebrow: __('messages.publication_lifecycle'),
            title: __('messages.delete_this_publication'),
            description: __('messages.this_removes_the_publication_from_the_prototype_feed_use_archive_from_the_publication_menu_when_you_may_want_to_restore_it_later'),
            action: 'delete-post',
            submitLabel: __('messages.delete_publication'),
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
