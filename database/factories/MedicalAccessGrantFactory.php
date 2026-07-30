<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MedicalAccessGrant;
use App\Models\MedicalRecord;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<MedicalAccessGrant>
 */
class MedicalAccessGrantFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $token = Str::random(64);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'granted_by_key' => 'mia-carter',
            'recipient_key' => null,
            'recipient_name' => fake()->name(),
            'recipient_role' => 'veterinarian',
            'label' => 'Temporary veterinary review',
            'token_hash' => hash('sha256', $token),
            'sections' => ['summary', 'medications', 'vaccinations'],
            'permissions' => ['view'],
            'allow_download' => false,
            'allow_edit' => false,
            'max_views' => 5,
            'views_used' => 0,
            'expires_at' => now()->addDay(),
            'last_opened_at' => null,
            'revoked_at' => null,
        ];
    }
}
