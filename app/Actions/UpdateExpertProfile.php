<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\ExpertProfile;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;

class UpdateExpertProfile
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(ExpertProfile $profile, array $data): ExpertProfile
    {
        return DB::transaction(function () use ($profile, $data): ExpertProfile {
            $profile->update([
                ...$data,
                'specializations' => array_values($data['specializations']),
                'species' => array_values($data['species']),
                'languages' => array_values($data['languages']),
                'formats' => array_values($data['formats']),
                'age_groups' => $data['age_groups'] ?? [],
                'methods' => array_values(array_filter($data['methods'] ?? [])),
                'accessibility' => $data['accessibility'] ?? [],
            ]);

            AuditLog::query()->create([
                'expert_profile_id' => $profile->id,
                'actor_key' => $this->actor->key(),
                'actor_role' => 'profile-owner',
                'action' => 'expert-profile.updated',
                'target_type' => ExpertProfile::class,
                'target_id' => (string) $profile->id,
                'metadata' => ['fields' => array_keys($data)],
            ]);

            return $profile->refresh();
        });
    }
}
