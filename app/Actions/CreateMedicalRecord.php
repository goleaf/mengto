<?php

namespace App\Actions;

use App\Enums\MedicalKnowledgeStatus;
use App\Enums\PetProfilePermission;
use App\Models\AuditLog;
use App\Models\MedicalRecord;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateMedicalRecord
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PetProfileAccess $petAccess,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): MedicalRecord
    {
        return DB::transaction(function () use ($data): MedicalRecord {
            $user = $this->actor->requireUser();
            $pet = PetProfile::query()
                ->select([
                    'id', 'user_id', 'profile_key', 'slug', 'name', 'species',
                    'breed', 'birth_date', 'sex', 'reproductive_status',
                    'status', 'profile_data',
                ])
                ->with('user:id,actor_key')
                ->managedBy($user)
                ->where('slug', (string) $data['pet_profile_key'])
                ->first();

            if ($pet === null || ! $this->petAccess->allows(
                $pet,
                $user,
                PetProfilePermission::ManageMedical,
            )) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.choose_a_pet_profile_you_manage_de4a79e7f0'),
                ]);
            }

            if (MedicalRecord::query()
                ->where('pet_profile_id', $pet->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => __('messages.this_pet_already_has_a_medical_record_d4c3459ec6'),
                ]);
            }

            $record = MedicalRecord::query()->create([
                'owner_id' => $pet->user_id,
                'pet_profile_id' => $pet->id,
                'owner_key' => $pet->user->actor_key,
                'slug' => $this->uniqueSlug($pet->slug),
                'pet_profile_key' => $pet->slug,
                'pet_name' => $pet->name,
                'species' => Str::lower($pet->species),
                'breed' => $pet->breed,
                'birth_date' => $data['birth_date'] ?? $pet->birth_date,
                'birth_date_estimated' => (bool) ($data['birth_date_estimated'] ?? false),
                'sex' => $data['sex'] ?? $pet->sex,
                'reproductive_status' => $data['reproductive_status'] ?? $pet->reproductive_status,
                'current_weight_grams' => $this->grams($data['weight'] ?? null, $data['weight_unit']),
                'image_url' => $pet->profile_data['profile_image'] ?? $pet->profile_data['avatar'] ?? null,
                'status' => 'active',
                'privacy' => 'private',
                'timezone' => $data['timezone'],
                'microchip_status' => $data['microchip_status'],
                'microchip_number' => $data['microchip_number'] ?? null,
                'microchip_checked_on' => $data['microchip_checked_on'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'allergy_knowledge_status' => MedicalKnowledgeStatus::from(
                    $data['allergy_knowledge_status'],
                ),
                'critical_allergies' => $this->lines($data['critical_allergies'] ?? null),
                'medication_knowledge_status' => MedicalKnowledgeStatus::from(
                    $data['medication_knowledge_status'],
                ),
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
                    'source_name' => __('messages.medical.initial_record'),
                    'measurement_context' => __('messages.medical.initial_value'),
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
