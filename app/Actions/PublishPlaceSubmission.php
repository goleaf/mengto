<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceFactScope;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\PlaceFact;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Support\Str;

final readonly class PublishPlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
    ): PlaceSubmission {
        return $this->transition->handle(
            $actor,
            $submission,
            'publish',
            [PlaceSubmissionStatus::Approved],
            PlaceSubmissionStatus::Published,
            PlaceSubmissionAction::Published,
            $operationKey,
            $expectedLockVersion,
            'approved-publication',
            resolution: PlaceSubmissionResolution::NewPlace,
            mutate: function (PlaceSubmission $locked) use ($actor): void {
                $place = Place::query()->firstOrCreate(
                    ['creation_idempotency_key' => 'place-publication:'.$locked->stable_key],
                    [
                        'owner_user_id' => null,
                        'organization_id' => $locked->canonical_organization_id,
                        'created_by_user_id' => $locked->submitter_user_id,
                        'last_edited_by_user_id' => $actor->id,
                        'stable_key' => 'place-'.Str::lower((string) Str::ulid()),
                        'slug' => Str::slug($locked->name).'-'.Str::lower((string) Str::ulid()),
                        'name' => $locked->name,
                        'normalized_name' => $locked->normalized_name,
                        'summary' => $locked->summary,
                        'type' => $locked->place_type,
                        'catalog_category' => $locked->catalog_category,
                        'visibility' => PlaceVisibility::Public,
                        'status' => PlaceStatus::Active,
                        'locale' => $locked->locale,
                        'public_region' => $locked->public_region,
                        'public_address' => $locked->public_address,
                        'normalized_address' => $locked->normalized_address,
                        'public_phone' => $locked->public_phone,
                        'normalized_phone' => $locked->normalized_phone,
                        'public_email' => $locked->public_email,
                        'normalized_email' => $locked->normalized_email,
                        'public_website' => $locked->public_website,
                        'normalized_website' => $locked->normalized_website,
                        'public_latitude' => $locked->public_latitude,
                        'public_longitude' => $locked->public_longitude,
                        'exact_address' => $locked->exact_address,
                        'exact_latitude' => $locked->exact_latitude,
                        'exact_longitude' => $locked->exact_longitude,
                        'is_indoor' => false,
                        'verification_status' => PlaceVerificationStatus::NotAssessed,
                        'verification_source' => 'community-review',
                        'accessibility_status' => PlaceAccessibilityStatus::NotAssessed,
                        'lock_version' => 0,
                        'metadata' => ['submission_key' => $locked->stable_key],
                    ],
                );

                $locked->published_place_id = $place->id;
                $locked->linked_place_id = null;
                $this->copyFacts($locked, $place, $actor);
            },
        );
    }

    private function copyFacts(PlaceSubmission $submission, Place $place, User $reviewer): void
    {
        $submission->facts()->orderBy('id')->each(function (PlaceFact $source) use ($place, $reviewer): void {
            PlaceFact::query()->firstOrCreate(
                ['place_id' => $place->id, 'copied_from_fact_id' => $source->id],
                [
                    'place_submission_id' => $source->place_submission_id,
                    'place_submission_revision_id' => $source->place_submission_revision_id,
                    'submitted_by_user_id' => $source->submitted_by_user_id,
                    'reviewed_by_user_id' => $reviewer->id,
                    'stable_key' => $source->stable_key.'-published-'.$place->id,
                    'field_key' => $source->field_key,
                    'field_value' => $source->field_value,
                    'value_hash' => $source->value_hash,
                    'source_kind' => $source->source_kind,
                    'source_reference' => $source->source_reference,
                    'provenance_scope' => PlaceFactScope::Published,
                    'visibility_scope' => $source->visibility_scope,
                    'observed_at' => $source->observed_at,
                    'verified_at' => now(),
                    'created_at' => now(),
                ],
            );
        });
    }
}
