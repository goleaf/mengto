<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileStatus;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetProfileLifecycle;
use Illuminate\Contracts\Auth\Access\Gate;

final class TransitionPetProfileStatus
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileLifecycle $lifecycle,
    ) {}

    /** @param array<string, mixed> $privateMetadata */
    public function handle(
        PetProfile $profile,
        PetProfileStatus $target,
        string $reasonCode,
        int $expectedLockVersion,
        string $idempotencyKey,
        array $privateMetadata = [],
    ): PetProfile {
        $user = $this->actor->requireUser();
        $this->gate->authorize('transition', [$profile, $target]);

        return $this->lifecycle->transition(
            profile: $profile,
            target: $target,
            actor: $user,
            reasonCode: $reasonCode,
            expectedLockVersion: $expectedLockVersion,
            idempotencyKey: $idempotencyKey,
            privateMetadata: $privateMetadata,
        );
    }
}
