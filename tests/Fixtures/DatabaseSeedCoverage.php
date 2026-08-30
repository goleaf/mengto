<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Database\Seeders\RepresentativeModelManifest;

final class DatabaseSeedCoverage
{
    /** @return list<string> */
    public static function representativeNullableFields(): array
    {
        return [
            'adoption_applications.contract_metadata',
            'adoption_applications.private_references',
            'adoption_cases.domestic_classification_id',
            'adoption_cases.pet_profile_id',
            'adoption_cases.taxon_id',
            'content_media_assets.licence',
            'consultations.referral_summary',
            'consultations.started_at',
            'device_automation_runs.error',
            'device_commands.confirmed_at',
            'device_commands.expires_at',
            'device_commands.failure_reason',
            'expert_profiles.cover_url',
            'forum_events.responsible_organization_id',
            'forum_event_rooms.online_url',
            'forum_mentor_scopes.forum_category_id',
            'forum_mentor_scopes.taxon_id',
            'forum_reports.affected_pet_profile_id',
            'forum_reports.affected_user_id',
            'forum_event_registration_pets.checked_in_at',
            'forum_event_registration_pets.checked_out_at',
            'forum_event_registration_pets.conditions',
            'forum_journal_media.caption',
            'forum_journal_media.uploaded_by_user_id',
            'forum_mentorships.completed_at',
            'forum_mentorships.completion_validated_at',
            'forum_mentorships.declined_at',
            'forum_mentorships.ended_at',
            'forum_mentorships.ended_by_user_id',
            'forum_mentorships.end_reason',
            'forum_mentorships.validated_by_user_id',
            'listing_reports.reporter_id',
            'listing_reviews.replied_at',
            'listing_reviews.seller_reply',
            'medical_records.image_url',
            'orders.buyer_id',
            'orders.cancelled_at',
            'orders.completed_at',
            'orders.paid_at',
            'orders.seller_id',
            'pet_profiles.breed_origin_type',
            'pet_profiles.creation_key',
            'pet_profiles.creator_relationship',
            'pet_profiles.domestic_classification_id',
            'pet_profiles.size_category',
            'pet_profiles.taxon_id',
            'reservations.requester_id',
            'reviews.expert_reply',
            'reviews.replied_at',
            'search_cases.domestic_classification_id',
            'search_cases.health_notice',
            'search_cases.reward_summary',
            'search_cases.taxon_id',
            'search_reports.forum_report_id',
            'search_reports.reporter_id',
            'search_reports.sighting_id',
            'search_tasks.assignee_key',
            'search_tasks.assignee_name',
            'search_tasks.claimed_at',
            'search_tasks.completed_at',
            'search_tasks.result',
            'sightings.photo_url',
            'sightings.reporter_id',
            'social_relationship_requests.repeat_after',
            'smart_devices.image_url',
            'smart_devices.safety_state',
            'smart_devices.safety_state_recorded_at',
            'taxon_versions.authorship',
            'taxon_external_identifiers.external_url',
            'weight_entries.notes',
            'weight_entries.tare_grams',
        ];
    }

    /** @return list<string> */
    public static function representativeStructuredFields(): array
    {
        return [
            'bookings.documents',
            'breed_registries.metadata',
            'credential_verification_appeals.metadata',
            'domestic_classifications.aliases',
            'domestic_classifications.metadata',
            'expert_profiles.workplaces',
            'forum_answers.media',
            'forum_categories.metadata',
            'forum_community_note_versions.evidence',
            'forum_community_note_versions.metadata',
            'forum_confirmation_evidence.metadata',
            'forum_confirmations.metadata',
            'forum_confirmations.scope',
            'forum_confirmations.structured_claim',
            'forum_mentorship_events.metadata',
            'forum_moderation_actions.evidence',
            'forum_moderation_actions.metadata',
            'forum_moderation_appeals.evidence',
            'forum_moderation_cases.metadata',
            'forum_report_events.metadata',
            'forum_report_reasons.metadata',
            'forum_reports.metadata',
            'forum_reputation_events.metadata',
            'forum_review_panel_events.metadata',
            'forum_review_panels.metadata',
            'forum_topic_acceptances.metadata',
            'forum_topic_legal_holds.metadata',
            'forum_topic_update_requests.metadata',
            'forum_user_badges.metadata',
            'knowledge_versions.protected_sections',
            'listings.gallery',
            'listings.risk_flags',
            'order_disputes.evidence',
            'organizations.metadata',
            'pet_profile_privacy_settings.section_rules',
            'search_cases.photos',
            'search_cases.risk_flags',
            'search_tasks.attachments',
            'sightings.risk_flags',
            'taxon_changes.metadata',
            'taxon_imports.error_report',
        ];
    }

    /** @return array<string, string> */
    public static function structuredExemptions(): array
    {
        return [
            'forum_categories.permissions' => 'Source-managed category permissions are represented by lifecycle rule rows, not duplicated JSON.',
            'forum_categories.rules' => 'Source-managed category rules are represented by localized canonical definitions and lifecycle rows.',
            'forum_category_lifecycle_rules.metadata' => 'Canonical lifecycle rules need no supplemental metadata in the representative definition set.',
            'forum_event_sessions.conflict_snapshot' => 'Conflict snapshots exist only when an authorized schedule override is exercised.',
            'forum_event_team_memberships.permission_overrides' => 'The representative team uses role defaults and therefore has no per-member permission overrides.',
            'forum_expert_session_history.metadata' => 'The selected lifecycle events need no supplemental private metadata.',
            'forum_topics.redirect_path' => 'Redirect paths exist only for redirected topics; the representative topics retain canonical routes.',
            'forum_trust_levels.criteria' => 'Trust criteria are represented by the level threshold columns in the canonical reference rows.',
            'pet_profile_managers.permission_overrides' => 'The representative manager rows use the named role permission set without per-manager overrides.',
            'place_access_audits.metadata' => 'The representative audit variants carry their complete purpose in first-class columns.',
            'place_access_grants.metadata' => 'The representative grants use first-class scope and expiry columns without supplemental metadata.',
            'search_volunteers.temporary_location' => 'No representative volunteer opts into temporary precise-location sharing.',
            'social_relationship_events.private_metadata' => 'The selected relationship event variants contain no additional private payload.',
            'social_relationship_events.public_metadata' => 'The selected relationship event variants contain no additional public payload.',
            'social_relationships.rights' => 'The representative relationships use the default rights implied by relationship type.',
        ];
    }

    /** @return list<string> */
    public static function structuredFieldsRequiringRepresentativeValues(): array
    {
        $fields = [];

        foreach (RepresentativeModelManifest::classes() as $modelClass) {
            $model = new $modelClass;

            foreach ($model->getCasts() as $column => $cast) {
                $cast = (string) $cast;
                $base = explode(':', $cast, 2)[0];
                $suffix = explode(':', $cast, 2)[1] ?? null;
                $structured = in_array($base, ['array', 'json', 'object', 'collection'], true)
                    || ($base === 'encrypted'
                        && in_array($suffix, ['array', 'json', 'object', 'collection'], true))
                    || str_contains($base, 'ArrayObject')
                    || str_contains($base, 'Collection');

                if ($structured) {
                    $fields[] = $model->getTable().'.'.$column;
                }
            }
        }

        $fields = array_values(array_unique(array_diff(
            $fields,
            array_keys(self::structuredExemptions()),
        )));
        sort($fields);

        return $fields;
    }

    /**
     * Exact schema-qualified nullable fields where null is a meaningful
     * inactive lifecycle state, mutually exclusive variant, privacy boundary,
     * or optional association in the representative dataset.
     *
     * @return array<string, list<string>>
     */
    public static function nullableExemptions(): array
    {
        return [
            'adoption_applications' => ['reviewer_user_id', 'private_references', 'screening_notes', 'home_check_notes', 'contract_metadata', 'meeting_at', 'reserved_at', 'contracted_at', 'trial_started_at', 'trial_ends_at', 'follow_up_at', 'closed_at'],
            'adoption_cases' => ['closed_at', 'archived_at'],
            'audit_logs' => ['booking_id'],
            'bookings' => ['cancelled_at', 'cancellation_reason', 'reschedule_proposed_at'],
            'care_access_grants' => ['recipient_key', 'last_opened_at', 'revoked_at'],
            'care_entries' => ['care_task_id', 'cancelled_at', 'cancelled_by_key', 'source_recorded_at', 'source_timezone', 'synchronized_at'],
            'care_routines' => ['ends_on'],
            'care_tasks' => ['care_routine_id', 'repeat_rule', 'completed_at', 'completed_by_key', 'completed_by_name', 'completion_note'],
            'content_audience_rules' => ['context_actor_id', 'context_type', 'context_key', 'expires_at'],
            'content_media_assets' => ['retained_until'],
            'content_publications' => ['scheduled_at', 'expires_at'],
            'content_publication_events' => ['from_status'],
            'credentials' => ['rejection_reason', 'replaces_credential_id', 'suspended_at', 'revoked_at', 'appeal_status'],
            'credential_verification_appeals' => ['reviewer_user_id', 'reviewer_response', 'reviewed_at', 'closed_at'],
            'device_access_grants' => ['recipient_key', 'last_opened_at', 'revoked_at'],
            'device_automations' => ['last_run_at'],
            'device_automation_runs' => ['device_event_id'],
            'device_events' => ['acknowledged_at', 'acknowledged_by_key', 'care_entry_id', 'search_case_id'],
            'device_readings' => ['care_entry_id', 'medical_event_id', 'weight_entry_id'],
            'document_grants' => ['last_opened_at', 'downloaded_at', 'revoked_at'],
            'domestic_classifications' => ['breed_registry_id', 'parent_id', 'registry_identifier', 'archived_at'],
            'forum_blocks' => ['reason'],
            'forum_answers' => ['author_id'],
            'forum_categories' => ['archived_at'],
            'forum_category_lifecycle_rules' => ['updated_by_user_id'],
            'forum_category_redirects' => ['created_by_user_id'],
            'forum_category_translations' => ['notice', 'rules_summary'],
            'forum_community_notes' => ['subject_author_user_id', 'author_response', 'jurisdiction', 'species_context', 'forum_review_panel_id', 'moderator_user_id', 'moderator_decision', 'decision_reason', 'published_at', 'revalidation_due_at', 'archived_at'],
            'forum_community_note_versions' => ['author_response'],
            'forum_confirmations' => ['moderator_user_id', 'moderator_decision', 'revalidation_due_at', 'decided_at'],
            'forum_confirmation_evidence' => ['private_disk', 'private_path'],
            'forum_confirmation_votes' => ['conflict_type'],
            'forum_engagements' => ['remind_at'],
            'forum_events' => ['cancelled_by_user_id', 'cancelled_at', 'cancellation_reason_code', 'archived_at', 'safety_suspended_at'],
            'forum_event_history' => ['subject_user_id', 'from_status'],
            'forum_event_invitations' => ['responded_at'],
            'forum_event_occurrences' => ['cancelled_at', 'cancellation_reason_code', 'metadata'],
            'forum_event_registrations' => ['pet_profile_id', 'requirements_note', 'waitlist_position', 'cancelled_at', 'cancellation_reason_code', 'checked_out_at'],
            'forum_event_sessions' => ['conflict_override_reason', 'conflict_snapshot'],
            'forum_event_team_memberships' => ['permission_overrides', 'ends_at'],
            'forum_expert_sessions' => ['archived_by_user_id', 'archived_at', 'archive_reason_code'],
            'forum_expert_session_history' => ['forum_expert_session_question_id', 'forum_expert_session_answer_id', 'from_status', 'metadata'],
            'forum_expert_session_questions' => ['moderation_reason_code', 'moderation_reason', 'selected_at', 'declined_at', 'withdrawn_at', 'removed_at'],
            'forum_groups' => ['closed_at', 'archived_at'],
            'forum_group_activities' => ['archived_at'],
            'forum_group_announcements' => ['archived_at'],
            'forum_group_files' => ['archived_at'],
            'forum_group_invitations' => ['responded_at'],
            'forum_group_memberships' => ['reviewed_by_user_id', 'review_reason', 'restriction_reason', 'ended_at'],
            'forum_journals' => ['archived_by_user_id', 'archived_at', 'archive_reason_code'],
            'forum_journal_collaborators' => ['revoked_at'],
            'forum_journal_entry_versions' => ['edited_by_user_id'],
            'forum_mentorship_feedback' => ['private_note'],
            'forum_moderation_actions' => ['target_type', 'target_id', 'ends_at', 'review_at', 'reversal_of_action_id', 'reversed_at'],
            'forum_moderation_appeals' => ['reviewer_user_id', 'decision_reason', 'decided_at'],
            'forum_moderation_cases' => ['assigned_to_user_id', 'subject_type', 'subject_id', 'internal_summary', 'resolved_at', 'closed_at', 'retention_until', 'closure_idempotency_key'],
            'forum_notifications' => ['read_at'],
            'forum_polls' => ['archived_at'],
            'forum_reports' => ['answer_id', 'comment_id', 'duplicate_of_report_id', 'location_scope'],
            'forum_report_events' => ['from_status', 'internal_note'],
            'forum_reputation_aggregates' => ['forum_category_id', 'taxon_id', 'location_scope_key'],
            'forum_reputation_events' => ['forum_category_id', 'taxon_id', 'reversal_of_event_id', 'location_scope_key', 'expires_at', 'review_at'],
            'forum_review_assignments' => ['decision', 'reasoning', 'conflict_type', 'replacement_for_assignment_id', 'submitted_at', 'recused_at', 'idempotency_key'],
            'forum_review_panels' => ['decision', 'decision_reason', 'moderator_override_by_user_id', 'forum_moderation_case_id', 'forum_moderation_appeal_id', 'appealed_by_user_id', 'appeal_reason', 'appealed_at', 'decided_at', 'closed_at'],
            'forum_review_panel_events' => ['from_state', 'to_state', 'idempotency_key'],
            'forum_topics' => ['merged_into_topic_id', 'last_bumped_at', 'stale_review_requested_at', 'locked_at', 'removed_at', 'redirected_at', 'redirect_path'],
            'forum_topic_acceptances' => ['accepted_by_user_id', 'invalidated_at', 'invalidation_reason_code'],
            'forum_topic_legal_holds' => ['released_at', 'released_by_user_id', 'release_reason'],
            'forum_topic_lifecycle_events' => ['from_status'],
            'forum_topic_update_requests' => ['proposed_body', 'reviewed_by_user_id', 'reviewed_at', 'resolution_reason'],
            'forum_trust_history' => ['from_forum_trust_level_id'],
            'forum_user_badges' => ['expires_at', 'revoked_at'],
            'forum_user_trust_levels' => ['expires_at'],
            'forum_votes' => ['reason', 'reputation_event_id'],
            'knowledge_articles' => ['jurisdiction', 'taxon_id', 'replaced_by_article_id', 'protected_sections', 'editorial_locked_at', 'editorial_locked_by_user_id', 'editorial_lock_reason', 'translated_from_article_id', 'translated_by_user_id', 'translation_source'],
            'knowledge_article_collaborators' => ['revoked_at', 'revoked_by_user_id'],
            'knowledge_corrections' => ['reporter_user_id', 'reviewed_by_user_id', 'reviewed_at', 'decision_reason'],
            'knowledge_versions' => ['editor_user_id', 'jurisdiction', 'taxon_id'],
            'listings' => ['reserved_at', 'completed_at', 'video_url', 'expires_at'],
            'medical_access_grants' => ['recipient_key', 'last_opened_at', 'revoked_at'],
            'medical_documents' => ['medical_event_id', 'vaccination_id', 'expires_on'],
            'medical_reminders' => ['related_type', 'related_id', 'confirmed_at', 'confirmed_by_key'],
            'order_disputes' => ['resolution', 'resolved_at'],
            'organizations' => ['archived_at'],
            'organization_invitations' => ['responded_at', 'revoked_by_user_id', 'revoked_at'],
            'organization_memberships' => ['expires_at', 'removed_by_user_id', 'removed_at', 'removal_reason_code'],
            'organization_restrictions' => ['revoked_by_user_id', 'revoked_at'],
            'pet_profiles' => ['deleted_at', 'canonical_profile_id', 'hidden_at', 'archived_at', 'memorialized_at', 'deletion_requested_at', 'deletion_scheduled_for', 'merged_at', 'estimated_age_months', 'estimated_age_recorded_at', 'birthday_celebration_month', 'birthday_celebration_day', 'life_stage_override', 'life_stage_override_by_user_id', 'life_stage_override_at'],
            'pet_profile_access_requests' => ['temporary_access_ends_at', 'decision_key', 'reviewed_by_user_id', 'reviewed_at', 'granted_manager_id', 'resolution_note'],
            'pet_profile_breed_origins' => ['domestic_classification_id', 'approximate_share_percent'],
            'pet_profile_facts' => ['replaces_fact_id', 'retired_at'],
            'pet_profile_identifying_marks' => ['created_by_user_id', 'updated_by_user_id', 'retired_at'],
            'pet_profile_lifecycle_events' => ['from_status'],
            'pet_profile_managers' => ['permission_overrides', 'evidence_summary', 'revoked_at', 'revoked_by_user_id'],
            'pet_profile_media' => ['recoverable_until', 'replaced_at', 'removed_at', 'restored_at'],
            'pet_profile_names' => ['locale', 'deleted_at'],
            'pet_profile_slug_aliases' => ['retired_at'],
            'places' => ['archived_at'],
            'place_access_audits' => ['metadata'],
            'place_access_grants' => ['metadata'],
            'place_location_versions' => ['public_address'],
            'place_merge_redirects' => ['superseded_at'],
            'place_questions' => ['answered_at'],
            'reservations' => ['completed_at', 'offered_price'],
            'search_alerts' => ['stopped_at'],
            'search_cases' => ['found_at', 'returned_at', 'closed_at', 'closure_reason', 'duplicate_of_search_case_id', 'reunited_confirmed_by_user_id', 'reunited_at', 'archived_at'],
            'search_contact_relays' => ['read_at'],
            'search_sectors' => ['checked_by_key', 'checked_at'],
            'search_volunteers' => ['last_check_in_at', 'temporary_location', 'location_expires_at'],
            'sightings' => ['danger', 'video_url'],
            'social_account_blocks' => ['source_actor_id', 'target_actor_id', 'reason_code', 'revoked_by_user_id', 'revoked_at'],
            'social_actors' => ['detached_at'],
            'social_actor_settings' => ['updated_by_user_id'],
            'social_relationships' => ['request_id', 'rights', 'accepted_by_user_id', 'context_type', 'context_key', 'reason_code', 'paused_at', 'ends_at', 'ended_at'],
            'social_relationship_events' => ['social_relationship_id', 'social_relationship_request_id', 'represented_actor_id', 'from_status', 'reason_code', 'public_metadata', 'private_metadata', 'social_account_block_id'],
            'social_relationship_requests' => ['context_type', 'context_key', 'message', 'reason_code', 'metadata', 'message_fingerprint', 'risk_signals'],
            'taxa' => ['accepted_taxon_id', 'original_taxon_id', 'archived_at'],
            'taxon_changes' => ['actor_user_id'],
            'taxon_imports' => ['initiated_by_user_id', 'resume_token', 'cancelled_at'],
            'taxon_names' => ['geographic_scope', 'import_key'],
            'users' => ['remember_token'],
        ];
    }

    public static function nullableExemptionReason(string $table, string $column): ?string
    {
        if (in_array("{$table}.{$column}", self::representativeNullableFields(), true)) {
            return null;
        }

        if (in_array($column, self::nullableExemptions()[$table] ?? [], true)) {
            $qualified = "{$table}.{$column}";

            return match (true) {
                str_ends_with($column, '_at'),
                str_ends_with($column, '_on'),
                str_ends_with($column, '_until'),
                str_ends_with($column, '_for') => "{$qualified} is reserved for a lifecycle transition that is inactive in the selected representative states.",
                str_ends_with($column, '_id') => "{$qualified} is an optional or mutually exclusive association that is intentionally absent from the selected representative variant.",
                str_ends_with($column, '_key') => "{$qualified} is a conditional actor, scope, active-state, or idempotency key that does not apply to the selected representative variant.",
                default => "{$qualified} is an explicitly reviewed optional payload whose null value carries domain meaning in the selected representative variant.",
            };
        }

        return null;
    }
}
