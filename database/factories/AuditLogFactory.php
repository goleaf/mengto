<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\ExpertProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'actor_key' => 'mia-carter',
            'actor_role' => 'client',
            'action' => 'profile.viewed',
            'target_type' => ExpertProfile::class,
            'target_id' => '1',
            'metadata' => [],
        ];
    }
}
