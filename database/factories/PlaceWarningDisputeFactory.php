<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceWarningDisputeStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningDispute;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceWarningDispute> */
final class PlaceWarningDisputeFactory extends ApplicationFactory
{
    protected $model = PlaceWarningDispute::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_warning_id' => PlaceWarning::factory(),
            'disputant_user_id' => User::factory(),
            'reviewer_user_id' => null,
            'idempotency_key' => (string) Str::uuid(),
            'reason' => 'The public condition was corrected before this warning was recorded.',
            'evidence' => null,
            'status' => PlaceWarningDisputeStatus::Submitted,
            'decision_reason' => null,
            'decided_at' => null,
        ];
    }
}
