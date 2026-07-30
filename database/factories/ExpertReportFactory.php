<?php

namespace Database\Factories;

use App\Models\ExpertProfile;
use App\Models\ExpertReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExpertReport> */
class ExpertReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'reporter_key' => 'mia-carter',
            'reason' => 'qualification-concern',
            'details' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'submitted',
        ];
    }
}
