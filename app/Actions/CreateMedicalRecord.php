<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use App\Services\PetProfileCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateMedicalRecord
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PetProfileCatalog $pets,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): MedicalRecord
    {
        return DB::transaction(function () use ($data): MedicalRecord {
            $pet = $this->pets->find((string) $data['pet_profile_key']);

            if ($pet === null) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => 'Choose a pet profile you manage.',
                ]);
            }

            if (MedicalRecord::query()
                ->where('owner_key', $this->actor->key())
                ->where('pet_profile_key', $pet['slug'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => 'This pet already has a medical record.',
                ]);
            }

            $record = MedicalRecord::query()->create([
                'owner_key' => $this->actor->key(),
                'slug' => $this->uniqueSlug($pet['slug']),
                'pet_profile_key' => $pet['slug'],
                'pet_name' => $pet['name'],
                'species' => Str::lower($pet['species']),
                'breed' => $pet['breed'],
                'birth_date' => $data['birth_date'] ?? null,
                'birth_date_estimated' => (bool) ($data['birth_date_estimated'] ?? false),
                'sex' => $data['sex'] ?? null,
                'reproductive_status' => $data['reproductive_status'],
                'current_weight_grams' => $this->grams($data['weight'] ?? null, $data['weight_unit']),
                'image_url' => $pet['profile_image'],
                'status' => 'active',
                'privacy' => 'private',
                'timezone' => $data['timezone'],
                'microchip_status' => $data['microchip_status'],
                'microchip_number' => $data['microchip_number'] ?? null,
                'microchip_checked_on' => $data['microchip_checked_on'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'critical_allergies' => $this->lines($data['critical_allergies'] ?? null),
                'chronic_conditions' => $this->lines($data['chronic_conditions'] ?? null),
                'emergency_notes' => $data['emergency_notes'] ?? null,
                'primary_clinic_name' => $data['primary_clinic_name'] ?? null,
                'primary_clinic_contact' => $data['primary_clinic_contact'] ?? null,
                'emergency_contact' => [
                    'name' => $data['emergency_contact_name'] ?? null,
                    'phone' => $data['emergency_contact_phone'] ?? null,
                    'relationship' => $data['emergency_contact_relationship'] ?? null,
                ],
            ]);

            if ($record->current_weight_grams !== null) {
                $record->weightEntries()->create([
                    'measured_at' => now(),
                    'timezone' => $record->timezone,
                    'weight_grams' => $record->current_weight_grams,
                    'source_type' => 'owner',
                    'source_name' => 'Initial medical record',
                    'measurement_context' => 'Initial value',
                    'verification_status' => 'owner-reported',
                    'created_by_key' => $this->actor->key(),
                ]);
            }

            $this->audit($record, 'medical-record.created', [
                'pet_profile_key' => $record->pet_profile_key,
                'privacy' => $record->privacy,
            ]);

            return $record;
        });
    }

    private function uniqueSlug(string $petKey): string
    {
        $base = Str::slug($petKey.' health');
        $slug = $base;
        $suffix = 2;

        while (MedicalRecord::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function grams(mixed $value, string $unit): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = (float) $value;

        return (int) round(match ($unit) {
            'g' => $number,
            'lb' => $number * 453.59237,
            'oz' => $number * 28.349523125,
            default => $number * 1000,
        });
    }

    /** @return array<int, string> */
    private function lines(?string $value): array
    {
        return collect(preg_split('/\R/', trim((string) $value)) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $metadata */
    private function audit(MedicalRecord $record, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'actor_key' => $this->actor->key(),
            'actor_role' => 'medical-record-owner',
            'action' => $action,
            'target_type' => MedicalRecord::class,
            'target_id' => (string) $record->id,
            'metadata' => $metadata,
        ]);
    }
}
