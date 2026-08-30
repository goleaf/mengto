<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareJournal;
use App\Services\ForumActor;
use App\Services\PetProfileCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCareJournal
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PetProfileCatalog $pets,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): CareJournal
    {
        return DB::transaction(function () use ($data): CareJournal {
            $pet = $this->pets->find((string) $data['pet_profile_key']);

            if ($pet === null) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.choose_a_pet_profile_you_manage'),
                ]);
            }

            if (CareJournal::query()
                ->where('owner_key', $this->actor->key())
                ->where('pet_profile_key', $pet['slug'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.this_pet_already_has_a_care_journal'),
                ]);
            }

            $identity = $this->actor->identity();
            $journal = CareJournal::query()->create([
                'owner_key' => $identity['key'],
                'slug' => $this->uniqueSlug($pet['slug']),
                'pet_profile_key' => $pet['slug'],
                'pet_name' => $pet['name'],
                'species' => Str::lower($pet['species']),
                'breed' => $pet['breed'],
                'image_url' => $pet['profile_image'],
                'privacy' => 'private',
                'timezone' => $data['timezone'],
                'current_caregiver_key' => $identity['key'],
                'current_caregiver_name' => ($data['current_caregiver_name'] ?? null) ?: $identity['name'],
                'status' => 'active',
            ]);

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'care-journal-owner',
                'action' => 'care-journal.created',
                'target_type' => CareJournal::class,
                'target_id' => (string) $journal->id,
                'metadata' => [
                    'pet_profile_key' => $journal->pet_profile_key,
                    'privacy' => $journal->privacy,
                ],
            ]);

            return $journal;
        });
    }

    private function uniqueSlug(string $petKey): string
    {
        $base = Str::slug($petKey.' care');
        $slug = $base;
        $suffix = 2;

        while (CareJournal::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
