<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<string, string>> */
    private const INDEXES = [
        'adoption_applications' => [
            'reviewer_user_id' => 'adopt_apps_reviewer_idx',
        ],
        'adoption_cases' => [
            'pet_profile_id' => 'adopt_cases_pet_idx',
        ],
        'adoption_events' => [
            'actor_user_id' => 'adopt_events_actor_idx',
        ],
        'community_animal_group_taxon' => [
            'taxon_id' => 'community_group_taxon_taxon_idx',
        ],
        'credential_verification_appeals' => [
            'submitted_by_user_id' => 'credential_appeals_submitter_idx',
        ],
        'credentials' => [
            'replaces_credential_id' => 'credentials_replaces_idx',
            'reviewer_user_id' => 'credentials_reviewer_idx',
        ],
        'domestic_classifications' => [
            'parent_id' => 'domestic_class_parent_idx',
            'breed_registry_id' => 'domestic_class_registry_idx',
        ],
        'forum_category_redirects' => [
            'created_by_user_id' => 'category_redirects_creator_idx',
            'target_forum_category_id' => 'category_redirects_target_idx',
        ],
        'forum_category_relations' => [
            'related_forum_category_id' => 'category_relations_related_idx',
        ],
        'forum_confirmation_evidence' => [
            'submitted_by_user_id' => 'confirmation_evidence_submitter_idx',
            'forum_confirmation_id' => 'confirmation_evidence_subject_idx',
        ],
        'forum_confirmation_votes' => [
            'voter_user_id' => 'confirmation_votes_voter_idx',
        ],
        'forum_confirmations' => [
            'moderator_user_id' => 'confirmations_moderator_idx',
            'requester_user_id' => 'confirmations_requester_idx',
        ],
        'forum_moderation_actions' => [
            'reversal_of_action_id' => 'moderation_actions_reversal_idx',
            'actor_user_id' => 'moderation_actions_actor_idx',
            'forum_moderation_action_definition_id' => 'moderation_actions_definition_idx',
            'forum_moderation_case_id' => 'moderation_actions_case_idx',
        ],
        'forum_moderation_appeals' => [
            'reviewer_user_id' => 'moderation_appeals_reviewer_idx',
            'appellant_user_id' => 'moderation_appeals_appellant_idx',
        ],
        'forum_moderation_case_reports' => [
            'linked_by_user_id' => 'case_reports_linker_idx',
            'forum_report_id' => 'case_reports_report_idx',
        ],
        'forum_moderation_cases' => [
            'opened_by_user_id' => 'moderation_cases_opener_idx',
            'assigned_to_user_id' => 'moderation_cases_assignee_idx',
        ],
        'forum_moderator_recusals' => [
            'moderator_user_id' => 'moderator_recusals_moderator_idx',
        ],
        'forum_report_attachments' => [
            'uploaded_by_user_id' => 'report_attachments_uploader_idx',
            'forum_report_id' => 'report_attachments_report_idx',
        ],
        'forum_report_events' => [
            'actor_user_id' => 'report_events_actor_idx',
        ],
        'forum_reports' => [
            'duplicate_of_report_id' => 'forum_reports_duplicate_idx',
            'affected_pet_profile_id' => 'forum_reports_pet_idx',
            'affected_user_id' => 'forum_reports_affected_user_idx',
            'forum_report_reason_id' => 'forum_reports_reason_idx',
            'reporter_id' => 'forum_reports_reporter_idx',
        ],
        'forum_reputation_aggregates' => [
            'taxon_id' => 'reputation_aggregates_taxon_idx',
            'forum_category_id' => 'reputation_aggregates_category_idx',
            'forum_reputation_dimension_id' => 'reputation_aggregates_dimension_idx',
        ],
        'forum_reputation_events' => [
            'reversal_of_event_id' => 'reputation_events_reversal_idx',
            'taxon_id' => 'reputation_events_taxon_idx',
            'forum_category_id' => 'reputation_events_category_idx',
            'forum_reputation_dimension_id' => 'reputation_events_dimension_idx',
        ],
        'forum_topic_acceptances' => [
            'accepted_by_user_id' => 'topic_acceptances_actor_idx',
            'forum_answer_id' => 'topic_acceptances_answer_idx',
        ],
        'forum_topic_moves' => [
            'actor_user_id' => 'topic_moves_actor_idx',
            'to_forum_category_id' => 'topic_moves_target_category_idx',
            'from_forum_category_id' => 'topic_moves_source_category_idx',
        ],
        'forum_topic_taxon' => [
            'taxon_id' => 'forum_topic_taxon_taxon_idx',
        ],
        'forum_topics' => [
            'merged_into_topic_id' => 'forum_topics_merged_into_idx',
        ],
        'forum_trust_history' => [
            'actor_user_id' => 'trust_history_actor_idx',
            'to_forum_trust_level_id' => 'trust_history_to_level_idx',
            'from_forum_trust_level_id' => 'trust_history_from_level_idx',
        ],
        'forum_user_badges' => [
            'granted_by_user_id' => 'user_badges_granter_idx',
            'forum_badge_id' => 'user_badges_badge_idx',
        ],
        'forum_user_trust_levels' => [
            'granted_by_user_id' => 'user_trust_granter_idx',
            'forum_trust_level_id' => 'user_trust_level_idx',
        ],
        'forum_votes' => [
            'reputation_event_id' => 'forum_votes_reputation_event_idx',
            'user_id' => 'forum_votes_user_idx',
        ],
        'taxa' => [
            'original_taxon_id' => 'taxa_original_taxon_idx',
        ],
        'taxon_changes' => [
            'actor_user_id' => 'taxon_changes_actor_idx',
            'taxon_import_id' => 'taxon_changes_import_idx',
        ],
        'taxon_external_identifiers' => [
            'taxon_id' => 'taxon_external_ids_taxon_idx',
        ],
        'taxon_imports' => [
            'initiated_by_user_id' => 'taxon_imports_initiator_idx',
        ],
        'taxon_names' => [
            'taxon_source_id' => 'taxon_names_source_idx',
        ],
        'taxon_sources' => [
            'active_taxon_import_id' => 'taxon_sources_active_import_idx',
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            Schema::table($table, function (Blueprint $blueprint) use ($indexes): void {
                foreach ($indexes as $column => $name) {
                    $blueprint->index($column, $name);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::INDEXES, true) as $table => $indexes) {
            Schema::table($table, function (Blueprint $blueprint) use ($indexes): void {
                foreach (array_reverse($indexes, true) as $name) {
                    $blueprint->dropIndex($name);
                }
            });
        }
    }
};
