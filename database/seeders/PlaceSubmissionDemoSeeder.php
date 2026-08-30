<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlaceDuplicateConfidence;
use App\Enums\PlaceFactScope;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVisibility;
use App\Models\Organization;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceFact;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\PlaceSubmissionIdentityLock;
use App\Models\PlaceSubmissionRevision;
use App\Models\User;
use App\Services\PlaceDuplicateDetector;
use App\Services\PlaceIdentityNormalizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PlaceSubmissionDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Place submission demo data may only be created in an explicitly allowed environment.');
        }

        $users = User::query()->orderBy('id')->limit(10)->get()
            ->sortBy(static fn (User $user): int => $user->email === 'user@example.com' ? 0 : $user->id)
            ->values();
        $destinations = Place::query()->publiclyDiscoverable()->orderBy('id')->limit(10)->get();

        if ($users->count() < 10 || $destinations->count() < 10) {
            throw new LogicException('Place submission demo data requires ten users and ten public places.');
        }

        $administrator = $users->first(static fn (User $user): bool => $user->isAdministrator());
        $organizationId = Organization::query()->orderBy('id')->value('id');

        if (! $administrator instanceof User || ! is_numeric($organizationId)) {
            throw new LogicException('Place submission demo data requires an administrator and canonical organization.');
        }

        $organizationId = (int) $organizationId;

        DB::transaction(function () use ($users, $destinations, $administrator, $organizationId): void {
            $normalizer = app(PlaceIdentityNormalizer::class);
            $states = [
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::NeedsInformation,
                PlaceSubmissionStatus::Approved,
                PlaceSubmissionStatus::Rejected,
                PlaceSubmissionStatus::Published,
                PlaceSubmissionStatus::Withdrawn,
                PlaceSubmissionStatus::Published,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Submitted,
            ];

            foreach ($states as $offset => $status) {
                $number = $offset + 1;
                $user = $users[$offset];
                $destination = $destinations[$offset];
                $resolution = match ($number) {
                    6 => PlaceSubmissionResolution::NewPlace,
                    8 => PlaceSubmissionResolution::ExistingLink,
                    default => PlaceSubmissionResolution::None,
                };
                $submission = $this->submission(
                    $normalizer,
                    $user,
                    $number,
                    $status,
                    $resolution,
                    $administrator,
                    $organizationId,
                    $number === 6 ? $destination : null,
                    $number === 8 ? $destination : null,
                );
                $eventAction = match (true) {
                    $status === PlaceSubmissionStatus::DuplicateReview => PlaceSubmissionAction::DuplicateReviewOpened,
                    $status === PlaceSubmissionStatus::NeedsInformation => PlaceSubmissionAction::InformationRequested,
                    $status === PlaceSubmissionStatus::Approved => PlaceSubmissionAction::NewPlaceApproved,
                    $status === PlaceSubmissionStatus::Rejected => PlaceSubmissionAction::Rejected,
                    $status === PlaceSubmissionStatus::Withdrawn => PlaceSubmissionAction::Withdrawn,
                    $status === PlaceSubmissionStatus::Published && $resolution === PlaceSubmissionResolution::ExistingLink => PlaceSubmissionAction::ExistingPlaceLinked,
                    $status === PlaceSubmissionStatus::Published => PlaceSubmissionAction::Published,
                    default => PlaceSubmissionAction::Submitted,
                };
                $this->evidence($submission, $user, $number, $status, $destination, $eventAction);
            }

            $candidateSubmission = PlaceSubmission::query()
                ->where('stable_key', 'demo-place-submission-10')
                ->firstOrFail();
            $duplicateSource = PlaceSubmission::query()
                ->where('stable_key', 'demo-place-submission-09')
                ->firstOrFail();
            PlaceDuplicateCandidate::query()->firstOrCreate(
                [
                    'place_submission_id' => $duplicateSource->id,
                    'candidate_submission_id' => $candidateSubmission->id,
                ],
                [
                    'candidate_key' => hash('sha256', $duplicateSource->stable_key.'|'.$candidateSubmission->stable_key),
                    'algorithm_version' => PlaceDuplicateDetector::ALGORITHM_VERSION,
                    'signals_fingerprint' => hash('sha256', 'name|address|pending-submission'),
                    'score' => 72,
                    'confidence' => PlaceDuplicateConfidence::Likely,
                    'matched_signals' => ['name', 'address'],
                    'distance_meters' => 18,
                    'presentation_scope' => 'review_only',
                    'created_at' => $duplicateSource->submitted_at,
                ],
            );

            foreach (range(1, 10) as $number) {
                $user = $users[$number - 1];
                $destination = $destinations[$number - 1];
                $source = Place::query()->firstOrCreate(
                    ['creation_idempotency_key' => "place-merge-demo-source-{$number}"],
                    [
                        'owner_user_id' => $user->id,
                        'created_by_user_id' => $user->id,
                        'last_edited_by_user_id' => $user->id,
                        'stable_key' => "place-merge-demo-source-{$number}",
                        'slug' => "place-merge-demo-source-{$number}",
                        'name' => "Merged demo place {$number}",
                        'normalized_name' => "merged demo place {$number}",
                        'summary' => 'A retained source identity for deterministic merge and restore review.',
                        'type' => PlaceType::PublicSpace,
                        'catalog_category' => 'park',
                        'visibility' => PlaceVisibility::Public,
                        'status' => $number === 1 ? PlaceStatus::Active : PlaceStatus::Merged,
                        'locale' => 'en',
                        'public_region' => 'Vilnius',
                        'merged_into_place_id' => $number === 1 ? null : $destination->id,
                    ],
                );
                $submissionNumber = 10 + $number;
                $submission = $this->submission(
                    $normalizer,
                    $user,
                    $submissionNumber,
                    PlaceSubmissionStatus::Published,
                    $number === 1 ? PlaceSubmissionResolution::NewPlace : PlaceSubmissionResolution::DuplicateMerge,
                    $administrator,
                    $organizationId,
                    $source,
                    $number === 1 ? null : $destination,
                );
                $event = $this->evidence(
                    $submission,
                    $user,
                    $submissionNumber,
                    PlaceSubmissionStatus::Published,
                    $destination,
                    $number === 1 ? PlaceSubmissionAction::MergeRestored : PlaceSubmissionAction::PlacesMerged,
                );
                PlaceMergeRedirect::query()->firstOrCreate(
                    ['source_identifier' => $source->slug],
                    [
                        'source_place_id' => $source->id,
                        'destination_place_id' => $destination->id,
                        'place_submission_event_id' => $event->id,
                        'created_by_user_id' => $administrator->id,
                        'restored_by_user_id' => $number === 1 ? $administrator->id : null,
                        'source_visibility' => $source->visibility,
                        'restored_at' => $number === 1 ? now() : null,
                        'created_at' => now(),
                    ],
                );
            }
        }, 3);
    }

    private function submission(
        PlaceIdentityNormalizer $normalizer,
        User $user,
        int $number,
        PlaceSubmissionStatus $status,
        PlaceSubmissionResolution $resolution,
        User $administrator,
        int $organizationId,
        ?Place $published,
        ?Place $linked,
    ): PlaceSubmission {
        $stableKey = sprintf('demo-place-submission-%02d', $number);
        $name = "Community place submission {$number}";
        $address = "Demo Street {$number}, Vilnius";
        $identityHash = hash_hmac('sha256', $stableKey, (string) config('app.key'));
        $reviewed = $status !== PlaceSubmissionStatus::Submitted
            && $status !== PlaceSubmissionStatus::DuplicateReview;

        $submission = PlaceSubmission::query()->updateOrCreate(
            ['stable_key' => $stableKey],
            [
                'submitter_user_id' => $user->id,
                'canonical_organization_id' => $organizationId,
                'published_place_id' => $published?->id,
                'linked_place_id' => $linked?->id,
                'reviewed_by_user_id' => $reviewed
                    ? $administrator->id
                    : null,
                'idempotency_key' => '00000000-0000-4000-8000-'.str_pad((string) $number, 12, '0', STR_PAD_LEFT),
                'payload_fingerprint' => hash_hmac('sha256', $stableKey.'-payload', (string) config('app.key')),
                'status' => $status,
                'resolution' => $resolution,
                'source_kind' => PlaceSubmissionSource::PersonalVisit,
                'source_reference' => 'Local deterministic demo fixture.',
                'relationship_to_place' => 'visitor',
                'location_precision' => $number === 10
                    ? PlaceLocationPrecision::PrivateExact
                    : PlaceLocationPrecision::PublicPoint,
                'locale' => $user->locale,
                'name' => $name,
                'normalized_name' => $normalizer->name($name),
                'catalog_category' => 'park',
                'place_type' => PlaceType::Park,
                'summary' => 'Representative community-submitted facts for the review workflow.',
                'public_region' => 'Vilnius',
                'public_address' => $number === 10 ? null : $address,
                'normalized_address' => $number === 10 ? null : $normalizer->address($address),
                'public_latitude' => $number === 10 ? null : number_format(54.680000 + ($number / 10_000), 6, '.', ''),
                'public_longitude' => $number === 10 ? null : number_format(25.270000 + ($number / 10_000), 6, '.', ''),
                'exact_address' => $number === 10 ? $address : null,
                'exact_latitude' => $number === 10 ? '54.681000' : null,
                'exact_longitude' => $number === 10 ? '25.271000' : null,
                'public_phone' => '+370600'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'normalized_phone' => '370600'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'public_email' => "place-submission-{$number}@example.test",
                'normalized_email' => "place-submission-{$number}@example.test",
                'public_website' => "https://example.test/places/{$number}",
                'normalized_website' => "example.test/places/{$number}",
                'identity_hash' => $identityHash,
                'submitted_facts' => ['hours' => ['description' => 'Daylight hours'], 'features' => ['water']],
                'consent_version' => 'places-submission-v1',
                'consented_at' => now()->subDays(20 - min($number, 19)),
                'observed_at' => now()->subDays(21 - min($number, 20)),
                'audit_context' => ['channel' => 'deterministic-seeder'],
                'continued_as_distinct' => false,
                'lock_version' => $reviewed ? 1 : 0,
                'submitted_at' => now()->subDays(20 - min($number, 19)),
                'reviewed_at' => $reviewed ? now()->subDay() : null,
                'approved_at' => in_array($status, [PlaceSubmissionStatus::Approved, PlaceSubmissionStatus::Published], true) ? now()->subDay() : null,
                'published_at' => $status === PlaceSubmissionStatus::Published ? now() : null,
                'rejected_at' => $status === PlaceSubmissionStatus::Rejected ? now() : null,
                'withdrawn_at' => $status === PlaceSubmissionStatus::Withdrawn ? now() : null,
            ],
        );

        PlaceSubmissionIdentityLock::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            ['first_submission_id' => $submission->id, 'lock_version' => 0],
        );

        return $submission;
    }

    private function evidence(
        PlaceSubmission $submission,
        User $user,
        int $number,
        PlaceSubmissionStatus $status,
        Place $candidatePlace,
        PlaceSubmissionAction $action = PlaceSubmissionAction::Submitted,
    ): PlaceSubmissionEvent {
        $revision = PlaceSubmissionRevision::query()->firstOrCreate(
            ['stable_key' => $submission->stable_key.'-revision-1'],
            [
                'place_submission_id' => $submission->id,
                'submitted_by_user_id' => $user->id,
                'revision_number' => 1,
                'kind' => 'initial',
                'summary' => 'Initial deterministic place evidence.',
                'created_at' => $submission->submitted_at,
            ],
        );

        $submittedFacts = [];

        foreach (['name' => $submission->name, 'public_region' => $submission->public_region] as $field => $value) {
            $submittedFacts[] = PlaceFact::query()->firstOrCreate(
                ['stable_key' => $submission->stable_key.'-fact-'.$field],
                [
                    'place_submission_id' => $submission->id,
                    'place_submission_revision_id' => $revision->id,
                    'submitted_by_user_id' => $user->id,
                    'field_key' => $field,
                    'field_value' => $value,
                    'value_hash' => hash_hmac('sha256', $value, (string) config('app.key')),
                    'source_kind' => PlaceSubmissionSource::PersonalVisit,
                    'source_reference' => 'Local deterministic demo fixture.',
                    'provenance_scope' => PlaceFactScope::Submitted,
                    'visibility_scope' => 'review_only',
                    'observed_at' => $submission->observed_at,
                    'created_at' => $submission->submitted_at,
                ],
            );
        }

        $candidate = PlaceDuplicateCandidate::query()->firstOrCreate(
            [
                'place_submission_id' => $submission->id,
                'candidate_place_id' => $candidatePlace->id,
            ],
            [
                'candidate_key' => hash('sha256', $submission->stable_key.'|'.$candidatePlace->stable_key),
                'algorithm_version' => PlaceDuplicateDetector::ALGORITHM_VERSION,
                'signals_fingerprint' => hash('sha256', 'name|region'),
                'score' => 55,
                'confidence' => PlaceDuplicateConfidence::Possible,
                'matched_signals' => ['name', 'region'],
                'distance_meters' => $number * 25,
                'presentation_scope' => $number === 1 ? 'review_only' : 'member_visible',
                'created_at' => $submission->submitted_at,
            ],
        );

        $canonicalPlaceId = $submission->linked_place_id ?? $submission->published_place_id;

        if ($canonicalPlaceId !== null) {
            foreach ($submittedFacts as $sourceFact) {
                PlaceFact::query()->firstOrCreate(
                    [
                        'place_id' => $canonicalPlaceId,
                        'copied_from_fact_id' => $sourceFact->id,
                    ],
                    [
                        'place_submission_id' => $submission->id,
                        'place_submission_revision_id' => $revision->id,
                        'origin_place_id' => $submission->resolution === PlaceSubmissionResolution::DuplicateMerge
                            ? $submission->published_place_id
                            : null,
                        'submitted_by_user_id' => $user->id,
                        'reviewed_by_user_id' => $submission->reviewed_by_user_id ?? $user->id,
                        'stable_key' => $sourceFact->stable_key.'-canonical-'.$canonicalPlaceId,
                        'field_key' => $sourceFact->field_key,
                        'field_value' => $sourceFact->field_value,
                        'value_hash' => $sourceFact->value_hash,
                        'source_kind' => $sourceFact->source_kind,
                        'source_reference' => $sourceFact->source_reference,
                        'provenance_scope' => $submission->resolution === PlaceSubmissionResolution::DuplicateMerge
                            ? PlaceFactScope::Merged
                            : PlaceFactScope::Published,
                        'visibility_scope' => 'public',
                        'observed_at' => $sourceFact->observed_at,
                        'verified_at' => $submission->reviewed_at ?? now(),
                        'created_at' => $submission->published_at ?? now(),
                    ],
                );
            }
        }

        $fromStatus = match ($action) {
            PlaceSubmissionAction::NewPlaceApproved,
            PlaceSubmissionAction::InformationRequested,
            PlaceSubmissionAction::Rejected => PlaceSubmissionStatus::Submitted,
            PlaceSubmissionAction::Published => PlaceSubmissionStatus::Approved,
            PlaceSubmissionAction::ExistingPlaceLinked,
            PlaceSubmissionAction::PlacesMerged => PlaceSubmissionStatus::DuplicateReview,
            PlaceSubmissionAction::MergeRestored => PlaceSubmissionStatus::Published,
            default => null,
        };

        return PlaceSubmissionEvent::query()->firstOrCreate(
            ['idempotency_key' => 'demo-place-event-'.$number],
            [
                'place_submission_id' => $submission->id,
                'actor_user_id' => $user->id,
                'place_duplicate_candidate_id' => $candidate->id,
                'candidate_place_id' => $candidatePlace->id,
                'destination_place_id' => $submission->linked_place_id,
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $status,
                'reason_code' => 'deterministic-demo',
                'reason_detail' => $fromStatus === null ? null : 'Deterministic reviewer context for this representative transition.',
                'payload_fingerprint' => hash_hmac('sha256', $submission->stable_key.'-event', (string) config('app.key')),
                'expected_lock_version' => $fromStatus === null ? null : 0,
                'result_lock_version' => $submission->lock_version,
                'audit_context' => ['channel' => 'deterministic-seeder'],
                'created_at' => $submission->submitted_at,
            ],
        );
    }
}
