<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceWarning;
use App\Models\PlaceWarningConfirmation;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceWarningConfirmation> */
final class PlaceWarningConfirmationFactory extends ApplicationFactory
{
    protected $model = PlaceWarningConfirmation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_warning_id' => PlaceWarning::factory(),
            'user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'confirmed_at' => now(),
        ];
    }
}
