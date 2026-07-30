<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExpertProfile;
use App\Models\ExpertReport;

/** @extends ApplicationFactory<ExpertReport> */
class ExpertReportFactory extends ApplicationFactory
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
