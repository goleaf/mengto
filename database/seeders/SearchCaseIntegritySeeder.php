<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PetProfile;
use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SearchCaseIntegritySeeder extends Seeder
{
    public function run(): void
    {
        SearchCase::query()
            ->select([
                'id', 'owner_id', 'owner_key', 'pet_profile_id', 'pet_profile_key',
                'pet_name', 'species', 'breed', 'sex', 'age_label', 'size',
                'primary_color', 'taxon_id', 'domestic_classification_id',
                'animal_snapshot', 'requires_taxonomy_review', 'type', 'status',
                'created_at',
            ])
            ->chunkById(200, function ($searchCases): void {
                foreach ($searchCases as $searchCase) {
                    $this->synchronizeCase($searchCase);
                }
            });
    }

    private function synchronizeCase(SearchCase $searchCase): void
    {
        $owner = $searchCase->owner_id !== null
            ? User::query()->select(['id', 'actor_key'])->find($searchCase->owner_id)
            : User::query()
                ->select(['id', 'actor_key'])
                ->where('actor_key', $searchCase->owner_key)
                ->first();
        $petProfile = $this->exactPetProfile($searchCase, $owner);
        $updates = [];

        if ($searchCase->owner_id === null && $owner !== null) {
            $updates['owner_id'] = $owner->id;
        }

        if ($searchCase->pet_profile_id === null && $petProfile !== null) {
            $updates['pet_profile_id'] = $petProfile->id;
        }

        if ($searchCase->animal_snapshot === null) {
            $updates['animal_snapshot'] = [
                'pet_profile_id' => $petProfile?->id,
                'pet_profile_key' => $petProfile === null
                    ? $searchCase->pet_profile_key
                    : $petProfile->profile_key,
                'name' => $searchCase->pet_name,
                'species' => $searchCase->species,
                'breed' => $searchCase->breed,
                'sex' => $searchCase->sex,
                'age_label' => $searchCase->age_label,
                'size' => $searchCase->size,
                'primary_color' => $searchCase->primary_color,
                'taxon_id' => $searchCase->taxon_id,
                'domestic_classification_id' => $searchCase->domestic_classification_id,
                'captured_at' => $searchCase->created_at?->toIso8601String(),
                'backfilled' => true,
            ];
        }

        if ($updates !== []) {
            $searchCase->forceFill($updates)->save();
        }

        SearchCaseEvent::query()->firstOrCreate(
            [
                'search_case_id' => $searchCase->id,
                'event_type' => 'case-created',
            ],
            [
                'actor_user_id' => $owner?->id,
                'current_status' => $searchCase->status->value,
                'reason_translation_key' => 'lost_found.events.case_created',
                'idempotency_key' => $this->stableUuid("search-case:{$searchCase->id}:created"),
                'metadata' => ['backfilled' => true],
                'created_at' => $searchCase->created_at ?? now(),
            ],
        );
    }

    private function exactPetProfile(SearchCase $searchCase, ?User $owner): ?PetProfile
    {
        if ($searchCase->pet_profile_id !== null) {
            return PetProfile::query()
                ->select(['id', 'user_id', 'profile_key', 'slug'])
                ->find($searchCase->pet_profile_id);
        }

        if ($owner === null || blank($searchCase->pet_profile_key)) {
            return null;
        }

        $matches = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'slug'])
            ->where('user_id', $owner->id)
            ->where('status', 'active')
            ->where(function ($profiles) use ($searchCase): void {
                $profiles
                    ->where('profile_key', $searchCase->pet_profile_key)
                    ->orWhere('slug', $searchCase->pet_profile_key);
            })
            ->limit(2)
            ->get();

        if ($matches->count() !== 1) {
            if ($matches->count() > 1 && ! $searchCase->requires_taxonomy_review) {
                $searchCase->forceFill(['requires_taxonomy_review' => true])->save();
            }

            return null;
        }

        return $matches->first();
    }

    private function stableUuid(string $value): string
    {
        $hash = md5($value);

        return sprintf(
            '%s-%s-5%s-a%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            substr($hash, 17, 3),
            substr($hash, 20, 12),
        );
    }
}
