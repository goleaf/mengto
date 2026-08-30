<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceFactScope;
use App\Enums\PlaceSubmissionSource;
use App\Models\PlaceFact;
use App\Models\PlaceSubmission;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceFact> */
final class PlaceFactFactory extends ApplicationFactory
{
    protected $model = PlaceFact::class;

    public function definition(): array
    {
        $value = fake()->sentence();

        return [
            'place_submission_id' => PlaceSubmission::factory(),
            'place_submission_revision_id' => null,
            'stable_key' => 'place-fact-'.Str::lower((string) Str::ulid()),
            'field_key' => 'summary',
            'field_value' => $value,
            'value_hash' => hash('sha256', $value),
            'source_kind' => PlaceSubmissionSource::PersonalVisit,
            'source_reference' => 'Recent personal observation.',
            'provenance_scope' => PlaceFactScope::Submitted,
            'visibility_scope' => 'review_only',
            'observed_at' => now()->subDay(),
            'created_at' => now(),
        ];
    }
}
