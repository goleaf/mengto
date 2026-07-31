<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Services\SocialRelationshipKey;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SocialRelationship> */
final class SocialRelationshipFactory extends ApplicationFactory
{
    protected $model = SocialRelationship::class;

    public function definition(): array
    {
        return [
            'relationship_key' => (string) Str::uuid(),
            'source_actor_id' => SocialActor::factory(),
            'target_actor_id' => SocialActor::factory(),
            'request_id' => null,
            'relationship_type' => SocialRelationshipType::Follow,
            'direction' => SocialRelationshipDirection::Directed,
            'status' => SocialRelationshipStatus::Active,
            'active_key' => null,
            'visibility' => 'private',
            'rights' => null,
            'created_by_user_id' => User::factory(),
            'accepted_by_user_id' => null,
            'context_type' => null,
            'context_key' => null,
            'reason_code' => null,
            'lock_version' => 1,
            'started_at' => now(),
            'paused_at' => null,
            'ends_at' => null,
            'ended_at' => null,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (SocialRelationship $relationship): void {
            if ($relationship->active_key !== null || $relationship->source_actor_id === null || $relationship->target_actor_id === null) {
                return;
            }

            $relationship->forceFill([
                'active_key' => SocialRelationshipKey::forRelationship(
                    $relationship->source_actor_id,
                    $relationship->target_actor_id,
                    $relationship->relationship_type,
                ),
            ])->saveQuietly();
        });
    }
}
