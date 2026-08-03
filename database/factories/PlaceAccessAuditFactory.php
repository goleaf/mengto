<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\User;

/** @extends ApplicationFactory<PlaceAccessAudit> */
final class PlaceAccessAuditFactory extends ApplicationFactory
{
    protected $model = PlaceAccessAudit::class;

    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'user_id' => User::factory(),
            'place_access_grant_id' => null,
            'event_id' => null,
            'event_type' => 'exact-location-viewed',
            'purpose' => null,
            'channel' => 'test',
            'metadata' => null,
            'created_at' => now(),
        ];
    }
}
