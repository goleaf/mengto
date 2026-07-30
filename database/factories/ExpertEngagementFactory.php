<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<ExpertEngagement> */
class ExpertEngagementFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'user_key' => 'mia-carter',
            'is_saved' => true,
            'is_subscribed' => false,
            'last_viewed_at' => now(),
        ];
    }
}
