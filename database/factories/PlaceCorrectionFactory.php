<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionSource;
use App\Enums\PlaceCorrectionStatus;
use App\Models\Place;
use App\Models\PlaceCorrection;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceCorrection> */
final class PlaceCorrectionFactory extends ApplicationFactory
{
    protected $model = PlaceCorrection::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'submitter_user_id' => User::factory(),
            'reviewer_user_id' => null,
            'applied_by_user_id' => null,
            'stable_key' => 'place-correction-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'correction_field' => PlaceCorrectionField::Summary,
            'original_value' => $this->faker->sentence(),
            'original_version' => 0,
            'proposed_value' => $this->faker->sentence(),
            'explanation' => $this->faker->paragraph(),
            'evidence' => null,
            'source' => PlaceCorrectionSource::PersonalObservation,
            'observed_at' => null,
            'moderation_status' => PlaceCorrectionStatus::Pending,
            'resolution' => null,
            'decision_reason' => null,
            'applied_value' => null,
            'reviewed_at' => null,
            'applied_at' => null,
            'lock_version' => 0,
            'pending_fingerprint' => hash('sha256', (string) Str::uuid()),
        ];
    }
}
