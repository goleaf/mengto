<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\SocialActor;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\SocialRelationshipKey;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SocialRelationshipRequest> */
final class SocialRelationshipRequestFactory extends ApplicationFactory
{
    protected $model = SocialRelationshipRequest::class;

    public function definition(): array
    {
        $source = SocialActor::factory();
        $target = SocialActor::factory();

        return [
            'request_key' => (string) Str::uuid(),
            'source_actor_id' => $source,
            'target_actor_id' => $target,
            'relationship_type' => SocialRelationshipType::OwnerFriendship,
            'direction' => SocialRelationshipDirection::Symmetric,
            'status' => SocialRequestStatus::Pending,
            'active_key' => null,
            'idempotency_key' => (string) Str::uuid(),
            'created_by_user_id' => User::factory(),
            'decided_by_user_id' => null,
            'context_type' => null,
            'context_key' => null,
            'message' => null,
            'reason_code' => null,
            'lock_version' => 1,
            'metadata' => null,
            'sent_at' => now(),
            'delivered_at' => now(),
            'decided_at' => null,
            'expires_at' => now()->addDays(30),
            'repeat_after' => null,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (SocialRelationshipRequest $request): void {
            if ($request->active_key !== null || $request->source_actor_id === null || $request->target_actor_id === null) {
                return;
            }

            $request->forceFill([
                'active_key' => SocialRelationshipKey::forRequest(
                    $request->source_actor_id,
                    $request->target_actor_id,
                    $request->relationship_type,
                ),
            ])->saveQuietly();
        });
    }
}
