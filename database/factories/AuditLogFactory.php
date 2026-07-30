<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<AuditLog> */
class AuditLogFactory extends ApplicationFactory
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
