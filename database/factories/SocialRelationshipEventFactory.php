<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationshipEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SocialRelationshipEvent> */
final class SocialRelationshipEventFactory extends ApplicationFactory
{
    protected $model = SocialRelationshipEvent::class;

    public function definition(): array
    {
        return [
            'social_relationship_id' => null,
            'social_relationship_request_id' => null,
            'social_account_block_id' => null,
            'source_actor_id' => SocialActor::factory(),
            'target_actor_id' => SocialActor::factory(),
            'represented_actor_id' => null,
            'actor_user_id' => User::factory(),
            'actor_key_snapshot' => (string) Str::uuid(),
            'event_type' => 'relationship-created',
            'relationship_type' => SocialRelationshipType::Follow,
            'from_status' => null,
            'to_status' => 'active',
            'reason_code' => null,
            'idempotency_key' => (string) Str::uuid(),
            'public_metadata' => null,
            'private_metadata' => null,
            'occurred_at' => now(),
        ];
    }
}
