<?php

namespace App\Actions;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Models\AuditLog;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchUpdate;
use App\Services\ForumActor;
use App\Services\SearchSafety;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateSearchCase
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly SearchSafety $safety,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): SearchCase
    {
        return DB::transaction(function () use ($data): SearchCase {
            $identity = $this->actor->identity();
            $type = SearchCaseType::from((string) $data['type']);
            $activeKey = $type === SearchCaseType::Lost
                ? $identity['key'].':'.$data['pet_profile_key']
                : null;

            if ($activeKey !== null && SearchCase::query()->where('active_key', $activeKey)->exists()) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => 'This pet already has an active search. Update that search instead.',
                ]);
            }

            $assessment = $this->safety->assessCase($data);
            $publish = $data['intent'] === 'publish' && ! $assessment['manual_review'];
            $photos = $this->storePhotos($data['photos'] ?? []);
            $status = $type === SearchCaseType::Found && (bool) ($data['animal_secured'] ?? false)
                ? SearchStatus::Safe
                : SearchStatus::Active;

            $searchCase = SearchCase::query()->create([
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
                'pet_profile_key' => $data['pet_profile_key'] ?? null,
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
                'contact_token' => Str::random(48),
                'cover_url' => $data['cover_url'] ?? ($photos[0] ?? null),
                'photos' => $photos,
                'risk_flags' => $assessment['flags'],
                'latest_update' => $publish
                    ? 'Search published. Report sightings without chasing the animal.'
                    : 'Draft saved for safety review.',
            ]);

            SearchUpdate::query()->create([
                'search_case_id' => $searchCase->id,
                'author_key' => $identity['key'],
                'author_name' => $identity['name'],
                'type' => 'case-created',
                'visibility' => $publish ? 'public' : 'team',
                'title' => $type === SearchCaseType::Lost
                    ? 'Search started'
                    : 'Found animal report created',
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
            ->map(fn (UploadedFile $photo): string => Storage::disk('public')->url(
                $photo->store('lost-found/cases', 'public'),
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
