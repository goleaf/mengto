<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ExpertEngagement> */
class ExpertEngagementFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'user_key' => 'factory-user-'.Str::lower((string) Str::ulid()),
            'is_saved' => true,
            'is_subscribed' => false,
            'last_viewed_at' => now(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => ['user_key' => $user->actor_key]);
    }
}
