<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;

/** @extends ApplicationFactory<PlaceAccessAudit> */
final class PlaceAccessAuditFactory extends ApplicationFactory
{
    protected $model = PlaceAccessAudit::class;

    public function definition(): array
    {
        return [
            'place_id' => null,
            'user_id' => null,
            'place_access_grant_id' => PlaceAccessGrant::factory(),
            'event_id' => null,
            'event_type' => 'exact-location-viewed',
            'purpose' => null,
            'channel' => 'test',
            'metadata' => null,
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (PlaceAccessAudit $audit): void {
            $grant = PlaceAccessGrant::query()->findOrFail($audit->place_access_grant_id);

            $audit->place_id = $grant->place_id;
            $audit->user_id = $grant->user_id;
            $audit->purpose = $grant->purpose->value;
        });
    }
}
