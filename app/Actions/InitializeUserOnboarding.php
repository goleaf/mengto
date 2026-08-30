<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;

final readonly class InitializeUserOnboarding
{
    public function __construct(private SocialActorResolver $actors) {}

    public function handle(User $user): UserOnboarding
    {
        $this->actors->provisionPrivateForUser($user);
        $startedAt = now();

        UserOnboarding::query()->insertOrIgnore([
            'user_id' => $user->id,
            'current_step' => OnboardingStep::Introduction->value,
            'started_at' => $startedAt,
            'lock_version' => 1,
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);

        return UserOnboarding::query()
            ->whereBelongsTo($user)
            ->firstOrFail();
    }
}
