<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionSource;
use App\Enums\PlaceCorrectionStatus;
use App\Enums\PlaceQuestionStatus;
use App\Enums\PlaceReviewAnonymityMode;
use App\Enums\PlaceReviewEligibilityContext;
use App\Enums\PlaceReviewModerationStatus;
use App\Enums\PlaceWarningCategory;
use App\Enums\PlaceWarningSeverity;
use App\Enums\PlaceWarningSource;
use App\Enums\PlaceWarningStatus;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\Place;
use App\Models\PlaceCompatibilityBackfill;
use App\Models\PlaceCorrection;
use App\Models\PlaceCorrectionEvent;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionEvent;
use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\PlaceReviewVersion;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningEvent;
use App\Models\UserDomainState;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class PlaceCompatibilityBackfillService
{
    /**
     * @return array{scanned_state_rows: int, scanned: int, imported: int, skipped: int, failed: int, already_processed: int}
     */
    public function handle(bool $dryRun = false, int $chunkSize = 100): array
    {
        $counts = [
            'scanned_state_rows' => 0,
            'scanned' => 0,
            'imported' => 0,
            'skipped' => 0,
            'failed' => 0,
            'already_processed' => 0,
        ];

        UserDomainState::query()
            ->where('namespace', 'places.state.v1')
            ->with('user')
            ->chunkById(max(1, min(500, $chunkSize)), function ($states) use (&$counts, $dryRun): void {
                foreach ($states as $state) {
                    $counts['scanned_state_rows']++;
                    foreach ($this->contributions($state->payload) as $contribution) {
                        $counts['scanned']++;
                        if ($dryRun) {
                            continue;
                        }

                        $this->process($state, $contribution, $counts);
                    }
                }
            });

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{type: string, place_key: string, legacy_key: string, payload: array<string, mixed>}>
     */
    private function contributions(array $payload): array
    {
        $items = [];
        foreach (['corrections' => 'correction', 'warnings' => 'warning', 'reviews' => 'review', 'questions' => 'question'] as $key => $type) {
            foreach ((array) ($payload[$key] ?? []) as $placeKey => $records) {
                if (! is_string($placeKey)) {
                    continue;
                }
                foreach ((array) $records as $offset => $record) {
                    if (! is_array($record)) {
                        continue;
                    }
                    $items[] = $this->contribution($type, $placeKey, $record, (string) $offset);
                }
            }
        }
        foreach ((array) ($payload['reports'] ?? []) as $offset => $record) {
            if (! is_array($record) || ! is_string($record['place'] ?? null)) {
                continue;
            }
            $items[] = $this->contribution('report', $record['place'], $record, (string) $offset);
        }

        return $items;
    }

    /** @param array<string, mixed> $payload @return array{type: string, place_key: string, legacy_key: string, payload: array<string, mixed>} */
    private function contribution(string $type, string $placeKey, array $payload, string $offset): array
    {
        $identity = $payload['id'] ?? $payload['key'] ?? $offset;

        return [
            'type' => $type,
            'place_key' => $placeKey,
            'legacy_key' => Str::limit($placeKey.':'.(string) $identity, 190, ''),
            'payload' => $payload,
        ];
    }

    /**
     * @param  array{type: string, place_key: string, legacy_key: string, payload: array<string, mixed>}  $contribution
     * @param  array{scanned_state_rows: int, scanned: int, imported: int, skipped: int, failed: int, already_processed: int}  $counts
     */
    private function process(UserDomainState $state, array $contribution, array &$counts): void
    {
        if (PlaceCompatibilityBackfill::query()
            ->where('user_domain_state_id', $state->id)
            ->where('contribution_type', $contribution['type'])
            ->where('legacy_key', $contribution['legacy_key'])
            ->exists()) {
            $counts['already_processed']++;

            return;
        }

        $checksum = hash('sha256', json_encode($contribution['payload'], JSON_THROW_ON_ERROR));
        $place = Place::query()
            ->where('stable_key', $contribution['place_key'])
            ->orWhere('slug', $contribution['place_key'])
            ->first();
        if ($place === null) {
            $this->record($state, $contribution, $checksum, null, 'skipped', 'place_not_found');
            $counts['skipped']++;

            return;
        }

        try {
            DB::transaction(function () use ($state, $contribution, $checksum, $place): void {
                $target = match ($contribution['type']) {
                    'correction' => $this->importCorrection($state, $place, $contribution['payload'], $checksum),
                    'warning' => $this->importWarning($state, $place, $contribution['payload'], $checksum),
                    'review' => $this->importReview($state, $place, $contribution['payload'], $checksum),
                    'question' => $this->importQuestion($state, $place, $contribution['payload'], $checksum),
                    'report' => $this->importReport($state, $place, $contribution['payload'], $checksum),
                };
                $this->record($state, $contribution, $checksum, $target, 'imported', null);
            }, 3);
            $counts['imported']++;
        } catch (Throwable $exception) {
            $this->record($state, $contribution, $checksum, null, 'failed', Str::limit(Str::snake(class_basename($exception)), 120, ''));
            $counts['failed']++;
        }
    }

    /** @param array<string, mixed> $payload */
    private function importCorrection(UserDomainState $state, Place $place, array $payload, string $checksum): PlaceCorrection
    {
        $field = match ((string) ($payload['field'] ?? '')) {
            'pet-rules' => PlaceCorrectionField::PetRules,
            'address' => PlaceCorrectionField::PublicAddress,
            'name' => PlaceCorrectionField::Name,
            'summary' => PlaceCorrectionField::Summary,
            'phone' => PlaceCorrectionField::PublicPhone,
            'website' => PlaceCorrectionField::PublicWebsite,
            'email' => PlaceCorrectionField::PublicEmail,
            default => PlaceCorrectionField::Summary,
        };
        $createdAt = $this->timestamp($payload['created_at'] ?? null);
        $correction = PlaceCorrection::query()->create([
            'place_id' => $place->id,
            'submitter_user_id' => $state->user_id,
            'stable_key' => 'legacy-correction-'.substr($checksum, 0, 32),
            'idempotency_key' => 'legacy-'.$checksum,
            'correction_field' => $field,
            'original_value' => $payload['current_value'] ?? $place->getAttribute($field->placeColumn()),
            'original_version' => $place->lock_version,
            'proposed_value' => $payload['proposed_value'] ?? null,
            'explanation' => (string) ($payload['explanation'] ?? $payload['evidence'] ?? __('messages.legacy_compatibility_contribution_retained_for_review')),
            'evidence' => $payload['evidence'] ?? null,
            'source' => PlaceCorrectionSource::LegacyImport,
            'observed_at' => $this->timestamp($payload['visited_at'] ?? null),
            'moderation_status' => PlaceCorrectionStatus::Pending,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        PlaceCorrectionEvent::query()->create([
            'place_correction_id' => $correction->id,
            'actor_user_id' => $state->user_id,
            'idempotency_key' => 'legacy-'.$checksum,
            'event_type' => 'legacy_imported',
            'to_status' => PlaceCorrectionStatus::Pending,
            'public_summary_key' => 'places.corrections.history.legacy_imported',
            'metadata' => ['source_namespace' => $state->namespace, 'source_version' => $state->version],
            'created_at' => $createdAt,
        ]);

        return $correction;
    }

    /** @param array<string, mixed> $payload */
    private function importWarning(UserDomainState $state, Place $place, array $payload, string $checksum): PlaceWarning
    {
        $category = match ((string) ($payload['category'] ?? '')) {
            'road-closure' => PlaceWarningCategory::Closure,
            'poison', 'dangerous-food', 'chemicals', 'water' => PlaceWarningCategory::AnimalHealth,
            'broken-glass', 'damaged-fence', 'ice', 'fire', 'flood', 'lighting' => PlaceWarningCategory::Hazard,
            default => PlaceWarningCategory::Other,
        };
        $createdAt = $this->timestamp($payload['reported_at'] ?? $payload['created_at'] ?? null);
        $expiresAt = $this->timestamp($payload['expires_at'] ?? null) ?? now()->addDays(3);
        $warning = PlaceWarning::query()->create([
            'place_id' => $place->id,
            'author_user_id' => $state->user_id,
            'stable_key' => 'legacy-warning-'.substr($checksum, 0, 32),
            'idempotency_key' => 'legacy-'.$checksum,
            'category' => $category,
            'severity' => PlaceWarningSeverity::Medium,
            'affected_scope' => (string) ($payload['zone'] ?? 'general'),
            'source' => PlaceWarningSource::LegacyImport,
            'title' => (string) ($payload['title'] ?? 'Legacy place warning'),
            'detail' => (string) ($payload['detail'] ?? $payload['body'] ?? __('messages.legacy_warning_retained_for_moderation_review')),
            'evidence' => $payload['evidence'] ?? null,
            'status' => PlaceWarningStatus::NeedsReview,
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        PlaceWarningEvent::query()->create([
            'place_warning_id' => $warning->id,
            'actor_user_id' => $state->user_id,
            'idempotency_key' => 'legacy-'.$checksum,
            'event_type' => 'legacy_imported',
            'to_status' => PlaceWarningStatus::NeedsReview->value,
            'public_summary_key' => 'places.warnings.history.legacy_imported',
            'metadata' => ['source_namespace' => $state->namespace, 'source_version' => $state->version],
            'created_at' => $createdAt,
        ]);

        return $warning;
    }

    /** @param array<string, mixed> $payload */
    private function importReview(UserDomainState $state, Place $place, array $payload, string $checksum): PlaceReview
    {
        $rating = max(1, min(5, (int) ($payload['rating'] ?? 1)));
        $createdAt = $this->timestamp($payload['created_at'] ?? null);
        $review = PlaceReview::query()->create([
            'place_id' => $place->id,
            'author_user_id' => $state->user_id,
            'stable_key' => 'legacy-review-'.substr($checksum, 0, 32),
            'idempotency_key' => 'legacy-'.$checksum,
            'eligibility_context' => PlaceReviewEligibilityContext::Other,
            'verified_visit' => false,
            'rating_overall' => $rating,
            'rating_service' => ($payload['criterion'] ?? null) === 'service' ? $rating : null,
            'rating_accessibility' => ($payload['criterion'] ?? null) === 'accessibility' ? $rating : null,
            'rating_pet_friendliness' => null,
            'body' => (string) ($payload['body'] ?? __('messages.legacy_review_retained_for_moderation_review')),
            'anonymity_mode' => ($payload['anonymous'] ?? false) ? PlaceReviewAnonymityMode::Anonymous : PlaceReviewAnonymityMode::Named,
            'moderation_status' => PlaceReviewModerationStatus::Pending,
            'current_version' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        PlaceReviewVersion::query()->create([
            'place_review_id' => $review->id,
            'editor_user_id' => $state->user_id,
            'idempotency_key' => 'legacy-'.$checksum,
            'version' => 1,
            'rating_overall' => $rating,
            'rating_service' => $review->rating_service,
            'rating_accessibility' => $review->rating_accessibility,
            'rating_pet_friendliness' => null,
            'body' => $review->body,
            'anonymity_mode' => $review->anonymity_mode->value,
            'reason' => 'legacy_import',
            'created_at' => $createdAt,
        ]);
        PlaceReviewEvent::query()->create([
            'place_review_id' => $review->id,
            'actor_user_id' => $state->user_id,
            'idempotency_key' => 'legacy-'.$checksum,
            'event_type' => 'legacy_imported',
            'to_status' => PlaceReviewModerationStatus::Pending->value,
            'public_summary_key' => 'places.reviews.history.legacy_imported',
            'created_at' => $createdAt,
        ]);

        return $review;
    }

    /** @param array<string, mixed> $payload */
    private function importQuestion(UserDomainState $state, Place $place, array $payload, string $checksum): PlaceQuestion
    {
        $createdAt = $this->timestamp($payload['created_at'] ?? null);
        $question = PlaceQuestion::query()->create([
            'place_id' => $place->id,
            'author_user_id' => $state->user_id,
            'stable_key' => 'legacy-question-'.substr($checksum, 0, 32),
            'idempotency_key' => substr($checksum, 0, 8).'-'.substr($checksum, 8, 4).'-4'.substr($checksum, 13, 3).'-a'.substr($checksum, 17, 3).'-'.substr($checksum, 20, 12),
            'body' => (string) ($payload['body'] ?? $payload['question'] ?? __('messages.legacy_question_retained_for_moderation_review')),
            'status' => PlaceQuestionStatus::Open,
            'moderation_status' => 'pending',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        PlaceQuestionEvent::query()->create([
            'place_question_id' => $question->id,
            'actor_user_id' => $state->user_id,
            'idempotency_key' => substr($checksum, 0, 8).'-'.substr($checksum, 8, 4).'-4'.substr($checksum, 13, 3).'-a'.substr($checksum, 17, 3).'-'.substr($checksum, 20, 12),
            'event_type' => 'legacy_imported',
            'to_status' => PlaceQuestionStatus::Open->value,
            'public_summary_key' => 'places.questions.events.legacy_imported',
            'private_note' => is_string($payload['answer'] ?? null) ? 'legacy_answer_preserved_in_encrypted_source' : null,
            'created_at' => $createdAt,
        ]);

        return $question;
    }

    /** @param array<string, mixed> $payload */
    private function importReport(UserDomainState $state, Place $place, array $payload, string $checksum): ForumReport
    {
        $requestedReason = (string) ($payload['category'] ?? 'other');
        $reason = ForumReportReason::query()->where('stable_key', $requestedReason)->where('is_active', true)->first()
            ?? ForumReportReason::query()->where('stable_key', 'other')->where('is_active', true)->firstOrFail();
        $createdAt = $this->timestamp($payload['created_at'] ?? null);
        $report = ForumReport::query()->create([
            'subject_type' => Place::class,
            'subject_id' => (string) $place->id,
            'reporter_id' => $state->user_id,
            'reporter_key' => $state->user->actor_key,
            'reason' => $reason->stable_key,
            'forum_report_reason_id' => $reason->id,
            'details' => $payload['body'] ?? null,
            'priority' => $reason->default_priority,
            'status' => 'received',
            'affected_user_id' => $place->owner_user_id,
            'urgency' => 'standard',
            'contact_preference' => 'platform',
            'immediate_safety' => false,
            'truthfulness_confirmed' => false,
            'deduplication_key' => hash('sha256', Place::class.'|'.$place->id.'|'.$reason->stable_key),
            'idempotency_key' => 'legacy-'.$checksum,
            'metadata' => ['legacy_import' => true, 'evidence_reference' => $payload['evidence'] ?? null],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        ForumReportEvent::query()->create([
            'forum_report_id' => $report->id,
            'actor_user_id' => $state->user_id,
            'event_type' => 'legacy-imported',
            'to_status' => 'received',
            'user_message_translation_key' => 'forum_moderation.messages.report_submitted',
            'metadata' => ['truthfulness_confirmation_missing' => true],
            'created_at' => $createdAt,
        ]);

        return $report;
    }

    /**
     * @param  array{type: string, place_key: string, legacy_key: string, payload: array<string, mixed>}  $contribution
     */
    private function record(
        UserDomainState $state,
        array $contribution,
        string $checksum,
        ?Model $target,
        string $status,
        ?string $errorCode,
    ): void {
        PlaceCompatibilityBackfill::query()->create([
            'user_domain_state_id' => $state->id,
            'user_id' => $state->user_id,
            'contribution_type' => $contribution['type'],
            'legacy_key' => $contribution['legacy_key'],
            'payload_checksum' => $checksum,
            'target_type' => $target === null ? null : $target::class,
            'target_id' => $target?->getKey(),
            'status' => $status,
            'error_code' => $errorCode,
        ]);
    }

    private function timestamp(mixed $value): CarbonImmutable
    {
        if (is_string($value) && $value !== '') {
            try {
                return CarbonImmutable::parse($value);
            } catch (Throwable) {
            }
        }

        return CarbonImmutable::now();
    }
}
