<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\DocumentGrant;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<DocumentGrant> */
class DocumentGrantFactory extends ApplicationFactory
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
