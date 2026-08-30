<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceWarningCategory;
use App\Enums\PlaceWarningResolution;
use App\Enums\PlaceWarningSeverity;
use App\Enums\PlaceWarningSource;
use App\Enums\PlaceWarningStatus;
use App\Models\Place;
use App\Models\PlaceWarning;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceWarning> */
final class PlaceWarningFactory extends ApplicationFactory
{
    protected $model = PlaceWarning::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'author_user_id' => User::factory(),
            'moderator_user_id' => null,
            'stable_key' => 'place-warning-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'category' => PlaceWarningCategory::Hazard,
            'severity' => PlaceWarningSeverity::Low,
            'affected_scope' => 'Public main entrance only.',
            'source' => PlaceWarningSource::PersonalObservation,
            'title' => 'Temporary entrance safety concern',
            'detail' => 'A temporary condition affects the public entrance and needs caution.',
            'evidence' => 'Observed from a public area.',
            'status' => PlaceWarningStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
            'disputed_at' => null,
            'resolved_at' => null,
            'resolution' => null,
            'moderation_reason' => null,
            'lock_version' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceWarningStatus::Published,
            'published_at' => now(),
            'resolution' => null,
            'resolved_at' => null,
        ]);
    }

    public function needsReview(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceWarningStatus::NeedsReview,
            'severity' => PlaceWarningSeverity::High,
            'published_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceWarningStatus::Resolved,
            'resolution' => PlaceWarningResolution::ConditionEnded,
            'resolved_at' => now(),
        ]);
    }
}
