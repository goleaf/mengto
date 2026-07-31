<?php

namespace App\Actions;

use App\Enums\SearchStatus;
use App\Enums\SightingStatus;
use App\Models\AuditLog;
use App\Models\SearchCase;
use App\Models\SearchUpdate;
use App\Models\Sighting;
use App\Services\ForumActor;
use App\Services\SearchSafety;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmitSighting
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly SearchSafety $safety,
        private readonly StorePublicImage $storePublicImage,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(SearchCase $searchCase, array $data): Sighting
    {
        return DB::transaction(function () use ($searchCase, $data): Sighting {
            $existing = Sighting::query()
                ->select(['id', 'search_case_id', 'reporter_key'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing !== null) {
                if ($existing->search_case_id !== $searchCase->id
                    || $existing->reporter_key !== $this->actor->key()) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('messages.this_observation_key_is_already_in_use_dbace23b87'),
                    ]);
                }

                return Sighting::query()->findOrFail($existing->id);
            }

            $lockedCase = SearchCase::query()
                ->select(['id', 'status', 'alerts_active', 'last_sighting_at'])
                ->lockForUpdate()
                ->findOrFail($searchCase->id);

            if ($lockedCase->status->isClosed() || ! $lockedCase->alerts_active) {
                throw ValidationException::withMessages([
                    'observed_at' => __('messages.this_urgent_search_is_closed_the_observation_was_not_sub_00f872c818'),
                ]);
            }

            $identity = $this->actor->identity();
            $assessment = $this->safety->assessSighting($data);
            $sighting = Sighting::query()->create([
                'search_case_id' => $searchCase->id,
                'reporter_key' => $identity['key'],
                'reporter_name' => $identity['name'],
                'idempotency_key' => $data['idempotency_key'],
                'status' => $assessment['manual_review']
                    ? SightingStatus::NeedsReview
                    : SightingStatus::Submitted,
                'observed_at' => $data['observed_at'],
                'submitted_at' => now(),
                'time_accuracy' => $data['time_accuracy'],
                'public_area' => $data['public_area'],
                'public_latitude' => round((float) $data['latitude'], 3),
                'public_longitude' => round((float) $data['longitude'], 3),
                'exact_location' => [
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'note' => $data['location_note'] ?? null,
                ],
                'direction' => $data['direction'] ?? null,
                'distance' => $data['distance'] ?? null,
                'confidence' => $data['confidence'],
                'contact_status' => $data['contact_status'],
                'animal_condition' => $data['animal_condition'] ?? null,
                'danger' => $data['danger'] ?? null,
                'notes' => $data['notes'] ?? null,
                'photo_url' => $this->storePhoto($data['photo'] ?? null),
                'video_url' => $this->storeVideo($data['video'] ?? null),
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                'exact_location_public' => false,
                'risk_flags' => $assessment['flags'],
            ]);

            $caseStatus = in_array($lockedCase->status, [
                SearchStatus::Active,
                SearchStatus::Paused,
                SearchStatus::LongTerm,
            ], true) ? SearchStatus::PossibleSighting : $lockedCase->status;

            $lockedCase->update([
                'status' => $caseStatus,
                'last_sighting_at' => $sighting->observed_at,
                'latest_update' => __('messages.a_new_sighting_is_awaiting_coordinator_verification_d2efe4cfe3'),
            ]);

            SearchUpdate::query()->create([
                'search_case_id' => $searchCase->id,
                'author_key' => $identity['key'],
                'author_name' => $identity['name'],
                'type' => 'sighting-submitted',
                'visibility' => 'team',
                'title' => __('messages.new_sighting_awaiting_verification'),
                'body' => $sighting->notes,
                'public_area' => $sighting->public_area,
                'occurred_at' => $sighting->observed_at,
            ]);

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'sighting-reporter',
                'action' => 'sighting.submitted',
                'target_type' => Sighting::class,
                'target_id' => (string) $sighting->id,
                'metadata' => [
                    'search_case_id' => $searchCase->id,
                    'confidence' => $sighting->confidence,
                    'status' => $sighting->status->value,
                    'observed_at' => $sighting->observed_at?->toAtomString(),
                    'submitted_at' => $sighting->submitted_at?->toAtomString(),
                ],
            ]);

            return $sighting;
        });
    }

    private function storePhoto(mixed $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return Storage::disk('public')->url(
            $this->storePublicImage->handle($file, 'lost-found/sightings/photos', 'photo'),
        );
    }

    private function storeVideo(mixed $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return Storage::disk('public')->url(
            $file->store('lost-found/sightings/videos', 'public'),
        );
    }
}
