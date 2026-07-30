<?php

namespace Database\Factories;

use App\Enums\MedicalReminderStatus;
use App\Models\MedicalRecord;
use App\Models\MedicalReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalReminder>
 */
class MedicalReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'type' => 'appointment',
            'title' => 'Follow-up visit',
            'due_at' => now()->addWeeks(2),
            'timezone' => 'Europe/Vilnius',
            'priority' => 'normal',
            'status' => MedicalReminderStatus::Scheduled,
            'recipients' => ['mia-carter'],
            'instructions' => 'Bring the latest medication list and symptom notes.',
            'related_type' => null,
            'related_id' => null,
            'created_by_key' => 'mia-carter',
        ];
    }
}
