<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceDuplicateConfidence;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceDuplicateCandidate> */
final class PlaceDuplicateCandidateFactory extends ApplicationFactory
{
    protected $model = PlaceDuplicateCandidate::class;

    public function definition(): array
    {
        return [
            'place_submission_id' => PlaceSubmission::factory(),
            'candidate_place_id' => Place::factory(),
            'candidate_key' => 'place-candidate-'.Str::lower((string) Str::ulid()),
            'algorithm_version' => 'pla-p06-v1',
            'signals_fingerprint' => hash('sha256', (string) Str::uuid()),
            'score' => 70,
            'confidence' => PlaceDuplicateConfidence::Likely,
            'matched_signals' => ['name', 'coordinates'],
            'distance_meters' => 45,
            'presentation_scope' => 'review_only',
            'created_at' => now(),
        ];
    }
}
