<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Models\AuditLog;
use App\Models\DomesticClassification;
use App\Models\PetProfile;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\SearchUpdate;
use App\Models\Taxon;
use App\Services\FindSearchCaseDuplicates;
use App\Services\ForumActor;
use App\Services\PortalMediaUrl;
use App\Services\SearchSafety;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateSearchCase
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly SearchSafety $safety,
        private readonly FindSearchCaseDuplicates $duplicates,
        private readonly StorePublicImage $storePublicImage,
        private readonly PortalMediaUrl $mediaUrl,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): SearchCase
    {
        return DB::transaction(function () use ($data): SearchCase {
            $user = $this->actor->requireUser();
            $identity = $this->actor->identity();
            $type = SearchCaseType::from((string) $data['type']);
            $petProfile = $this->resolvePetProfile($user->id, $data);
            $taxonId = $this->resolveTaxonId($data);
            $domesticClassificationId = $this->resolveDomesticClassificationId($data, $taxonId);
            $petProfileKey = $petProfile?->profile_key;

            if (
                in_array($type, [SearchCaseType::Lost, SearchCaseType::Stolen], true)
                && $petProfileKey === null
            ) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.choose_the_pet_profile_for_a_missing_pet_search'),
                ]);
            }

            $activeKey = in_array($type, [SearchCaseType::Lost, SearchCaseType::Stolen], true)
                ? $identity['key'].':'.$petProfileKey
                : null;

            if ($activeKey !== null && SearchCase::query()->where('active_key', $activeKey)->exists()) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.this_pet_already_has_an_active_search_update_that_search_instead'),
                ]);
            }

            $assessment = $this->safety->assessCase($data);
            $duplicateCandidates = $this->duplicates->handle($data);

            if ($duplicateCandidates->isNotEmpty()) {
                $assessment['flags'][] = 'possible-duplicate';
                $assessment['flags'] = array_values(array_unique($assessment['flags']));
            }

            $publish = $data['intent'] === 'publish' && ! $assessment['manual_review'];
            $photos = $this->storePhotos($data['photos'] ?? []);
            $status = $type === SearchCaseType::Found && (bool) ($data['animal_secured'] ?? false)
                ? SearchStatus::Safe
                : SearchStatus::Active;

            $searchCase = SearchCase::query()->create([
                'owner_id' => $user->id,
                'owner_key' => $identity['key'],
                'owner_name' => $identity['name'],
                'owner_initials' => $identity['initials'],
                'coordinator_key' => $identity['key'],
                'coordinator_name' => $identity['name'],
                'slug' => $this->uniqueSlug((string) $data['pet_name'], $type),
                'public_code' => $this->uniquePublicCode(),
                'active_key' => $activeKey,
                'type' => $type,
                'status' => $status,
                'moderation_status' => $publish
                    ? ModerationStatus::Approved
                    : ModerationStatus::Pending,
                'pet_profile_key' => $petProfileKey,
                'pet_profile_id' => $petProfile?->id,
                'taxon_id' => $taxonId,
                'domestic_classification_id' => $domesticClassificationId,
                'pet_name' => $data['pet_name'],
                'species' => $data['species'],
                'breed' => $data['breed'] ?? null,
                'sex' => $data['sex'] ?? null,
                'age_label' => $data['age_label'] ?? null,
                'size' => $data['size'] ?? null,
                'primary_color' => $data['primary_color'],
                'coat' => $data['coat'] ?? null,
                'distinctive_marks' => $data['distinctive_marks'] ?? null,
                'hidden_marks' => $data['hidden_marks'] ?? null,
                'description' => $data['description'],
                'health_notice' => $data['health_notice'] ?? null,
                'approach_instructions' => $data['approach_instructions'] ?? null,
                'avoid_instructions' => $data['avoid_instructions'] ?? null,
                'accessories' => array_values($data['accessories'] ?? []),
                'temperament' => $data['temperament'] ?? null,
                'microchip_status' => $data['microchip_status'],
                'last_seen_area' => $data['last_seen_area'],
                'city' => $data['city'],
                'country' => Str::upper((string) $data['country']),
                'public_latitude' => round((float) $data['latitude'], 3),
                'public_longitude' => round((float) $data['longitude'], 3),
                'exact_location' => [
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'note' => $data['location_note'] ?? null,
                ],
                'direction' => $data['direction'] ?? null,
                'last_seen_at' => $data['last_seen_at'],
                'reported_at' => now(),
                'notification_radius_km' => $data['notification_radius_km'],
                'visibility' => $data['visibility'],
                'alerts_active' => $publish,
                'volunteer_join_open' => $publish,
                'animal_secured' => (bool) ($data['animal_secured'] ?? false),
                'contact_protected' => true,
                'contact_details' => [
                    'channel' => $data['contact_channel'],
                    'value' => $data['contact_channel'] === 'platform'
                        ? $identity['key']
                        : ($data['contact_value'] ?? null),
                ],
                'contact_token' => hash('sha256', (string) Str::uuid()),
                'reward_offered' => (bool) ($data['reward_offered'] ?? false),
                'reward_summary' => $data['reward_offered'] ?? false
                    ? ($data['reward_summary'] ?? null)
                    : null,
                'cover_url' => $data['cover_url'] ?? ($photos[0] ?? null),
                'photos' => $photos,
                'risk_flags' => $assessment['flags'],
                'animal_snapshot' => $this->animalSnapshot(
                    $data,
                    $petProfile,
                    $taxonId,
                    $domesticClassificationId,
                ),
                'requires_taxonomy_review' => $taxonId === null
                    && in_array($type, [SearchCaseType::Sighted, SearchCaseType::Found], true),
                'latest_update' => $publish
                    ? __('messages.search_published_report_sightings_without_chasing_the_animal')
                    : __('messages.draft_saved_for_safety_review'),
            ]);

            SearchUpdate::query()->create([
                'search_case_id' => $searchCase->id,
                'author_key' => $identity['key'],
                'author_name' => $identity['name'],
                'type' => 'case-created',
                'visibility' => $publish ? 'public' : 'team',
                'title' => $type === SearchCaseType::Lost
                    ? __('messages.search.started')
                    : __('messages.search.found_report_created'),
                'body' => $searchCase->latest_update,
                'public_area' => $searchCase->last_seen_area,
                'occurred_at' => now(),
            ]);

            if ($publish) {
                SearchAlert::query()->create([
                    'search_case_id' => $searchCase->id,
                    'kind' => 'local-urgent',
                    'radius_km' => $searchCase->notification_radius_km,
                    'region' => collect([$searchCase->last_seen_area, $searchCase->city])->join(', '),
                    'channels' => ['in-app', 'push'],
                    'audiences' => ['nearby-users', 'local-groups', 'clinics', 'shelters'],
                    'status' => 'queued',
                    'recipient_count' => 0,
                    'message' => $searchCase->pet_name.' · '.$searchCase->last_seen_area,
                ]);
            }

            SearchCaseEvent::query()->create([
                'search_case_id' => $searchCase->id,
                'actor_user_id' => $user->id,
                'event_type' => 'case-created',
                'current_status' => $status->value,
                'reason_translation_key' => 'lost_found.events.case_created',
                'idempotency_key' => (string) Str::uuid(),
                'metadata' => [
                    'type' => $type->value,
                    'published' => $publish,
                    'duplicate_candidate_ids' => $duplicateCandidates->pluck('id')->all(),
                ],
            ]);

            $this->audit($searchCase, 'search-case.created', [
                'type' => $type->value,
                'status' => $status->value,
                'moderation_status' => $searchCase->moderation_status->value,
                'public_location_precision' => 3,
                'risk_flags' => $assessment['flags'],
            ]);

            return $searchCase;
        });
    }

    /** @param array<int, UploadedFile> $photos @return array<int, string> */
    private function storePhotos(array $photos): array
    {
        return collect($photos)
            ->filter(fn (mixed $photo): bool => $photo instanceof UploadedFile)
            ->map(fn (UploadedFile $photo): string => $this->mediaUrl->for(
                $this->storePublicImage->handle($photo, 'lost-found/cases'),
            ))
            ->values()
            ->all();
    }

    private function uniqueSlug(string $petName, SearchCaseType $type): string
    {
        $base = Str::slug($petName.' '.$type->value) ?: 'pet-search';
        $slug = $base;
        $suffix = 2;

        while (SearchCase::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniquePublicCode(): string
    {
        do {
            $code = 'LF-'.Str::upper(Str::random(7));
        } while (SearchCase::query()->where('public_code', $code)->exists());

        return $code;
    }

    /** @param array<string, mixed> $data */
    private function resolvePetProfile(int $userId, array $data): ?PetProfile
    {
        $petProfileId = $data['pet_profile_id'] ?? null;
        $petProfileKey = $data['pet_profile_key'] ?? null;

        if (blank($petProfileId) && blank($petProfileKey)) {
            return null;
        }

        $petProfile = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'name', 'species', 'breed', 'birth_date', 'status'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->when(
                filled($petProfileId),
                fn ($query) => $query->whereKey((int) $petProfileId),
                fn ($query) => $query->where(
                    fn ($profiles) => $profiles
                        ->where('profile_key', $petProfileKey)
                        ->orWhere('slug', $petProfileKey),
                ),
            )
            ->first();

        if (($petProfileId !== null || $petProfileKey !== null) && $petProfile === null) {
            throw ValidationException::withMessages([
                ($petProfileId !== null ? 'pet_profile_id' : 'pet_profile_key') => __('lost_found.validation.pet_ownership'),
            ]);
        }

        return $petProfile;
    }

    /** @param array<string, mixed> $data */
    private function resolveTaxonId(array $data): ?int
    {
        if (blank($data['taxon_id'] ?? null)) {
            return null;
        }

        $taxonId = Taxon::query()
            ->active()
            ->whereKey((int) $data['taxon_id'])
            ->value('id');

        if ($taxonId === null) {
            throw ValidationException::withMessages([
                'taxon_id' => __('lost_found.validation.taxonomy_relation'),
            ]);
        }

        return (int) $taxonId;
    }

    /** @param array<string, mixed> $data */
    private function resolveDomesticClassificationId(array $data, ?int $taxonId): ?int
    {
        if (blank($data['domestic_classification_id'] ?? null)) {
            return null;
        }

        $classificationId = DomesticClassification::query()
            ->whereKey((int) $data['domestic_classification_id'])
            ->where('is_active', true)
            ->when($taxonId !== null, fn ($query) => $query->where('taxon_id', $taxonId))
            ->value('id');

        if ($classificationId === null) {
            throw ValidationException::withMessages([
                'domestic_classification_id' => __('lost_found.validation.taxonomy_relation'),
            ]);
        }

        return (int) $classificationId;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function animalSnapshot(
        array $data,
        ?PetProfile $petProfile,
        ?int $taxonId,
        ?int $domesticClassificationId,
    ): array {
        return [
            'pet_profile_id' => $petProfile?->id,
            'pet_profile_key' => $petProfile === null
                ? ($data['pet_profile_key'] ?? null)
                : $petProfile->profile_key,
            'name' => $data['pet_name'],
            'species' => $data['species'],
            'breed' => $data['breed'] ?? null,
            'sex' => $data['sex'] ?? null,
            'age_label' => $data['age_label'] ?? null,
            'size' => $data['size'] ?? null,
            'primary_color' => $data['primary_color'],
            'temperament' => $data['temperament'] ?? null,
            'accessories' => array_values($data['accessories'] ?? []),
            'taxon_id' => $taxonId,
            'domestic_classification_id' => $domesticClassificationId,
            'captured_at' => now()->toIso8601String(),
        ];
    }

    /** @param array<string, mixed> $metadata */
    private function audit(SearchCase $searchCase, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'actor_key' => $this->actor->key(),
            'actor_role' => 'search-owner',
            'action' => $action,
            'target_type' => SearchCase::class,
            'target_id' => (string) $searchCase->id,
            'metadata' => $metadata,
        ]);
    }
}
