<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\DocumentGrant;
use App\Models\ExpertProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DocumentGrant> */
class DocumentGrantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'expert_profile_id' => ExpertProfile::factory(),
            'owner_key' => 'mia-carter',
            'label' => 'Selected laboratory result',
            'document_type' => 'laboratory-result',
            'file_path' => 'documents/private/'.fake()->uuid().'.pdf',
            'permissions' => ['view'],
            'expires_at' => now()->addWeek(),
        ];
    }
}
