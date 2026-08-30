<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceWarningAppealStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningAppeal;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceWarningAppeal> */
final class PlaceWarningAppealFactory extends ApplicationFactory
{
    protected $model = PlaceWarningAppeal::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_warning_id' => PlaceWarning::factory(),
            'appellant_user_id' => User::factory(),
            'reviewer_user_id' => null,
            'idempotency_key' => (string) Str::uuid(),
            'reason' => 'New independently verifiable evidence is available for this decision.',
            'evidence' => null,
            'status' => PlaceWarningAppealStatus::Submitted,
            'decision_reason' => null,
            'decided_at' => null,
        ];
    }
}
