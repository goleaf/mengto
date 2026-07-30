<?php

namespace Database\Factories;

use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExpertEngagement> */
class ExpertEngagementFactory extends Factory
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
