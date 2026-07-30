<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ResolveMedicalAccess
{
    public function handle(string $token, string $action = 'medical-access.opened'): MedicalAccessGrant
    {
        return DB::transaction(function () use ($token, $action): MedicalAccessGrant {
            $grant = MedicalAccessGrant::query()
                ->select([
                    'id', 'medical_record_id', 'granted_by_key', 'recipient_key',
                    'recipient_name', 'recipient_role', 'label', 'token_hash',
                    'sections', 'permissions', 'allow_download', 'allow_edit',
                    'max_views', 'views_used', 'expires_at', 'last_opened_at',
                    'revoked_at', 'created_at', 'updated_at',
                ])
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($grant === null || ! $grant->canBeOpened()) {
                throw (new ModelNotFoundException)->setModel(MedicalAccessGrant::class);
            }

            $grant->forceFill([
                'views_used' => $grant->views_used + 1,
                'last_opened_at' => now(),
            ])->save();

            AuditLog::query()->create([
                'actor_key' => $grant->recipient_key ?? 'temporary-link',
                'actor_role' => $grant->recipient_role,
                'action' => $action,
                'target_type' => MedicalAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'medical_record_id' => $grant->medical_record_id,
                    'sections' => $grant->sections,
                    'views_used' => $grant->views_used,
                    'max_views' => $grant->max_views,
                ],
            ]);

            return $grant->load([
                'medicalRecord' => fn ($records) => $records->select([
                    'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                    'species', 'breed', 'birth_date', 'birth_date_estimated',
                    'current_weight_grams', 'image_url', 'timezone',
                    'microchip_status', 'microchip_number', 'blood_group',
                    'critical_allergies', 'chronic_conditions',
                    'emergency_notes', 'primary_clinic_name',
                    'primary_clinic_contact', 'emergency_contact',
                    'last_visit_at', 'next_appointment_at',
                ]),
            ]);
        });
    }
}
