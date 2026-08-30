<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceDuplicateConfidence;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use Illuminate\Database\Eloquent\Builder;

final readonly class PlaceDuplicateDetector
{
    public const ALGORITHM_VERSION = 'pla-p06-v1';

    public function __construct(private PlaceIdentityNormalizer $normalizer) {}

    /** @return list<PlaceDuplicateCandidate> */
    public function detect(PlaceSubmission $submission): array
    {
        $candidates = [];

        /**
         * @var array<int, array{
         *     place: Place,
         *     score: int,
         *     signals: list<string>,
         *     distance: int|null,
         *     presentation_scope: string
         * }> $placeAssessments
         */
        $placeAssessments = [];

        foreach ($this->candidatePlaces($submission) as $matchedPlace) {
            $place = $matchedPlace;
            $assessment = $this->scorePlace($submission, $matchedPlace);
            $isAlias = $matchedPlace->status === PlaceStatus::Merged
                && $matchedPlace->mergedInto instanceof Place
                && $matchedPlace->mergeRedirects->contains(
                    static fn (PlaceMergeRedirect $redirect): bool => $redirect->restored_at === null
                        && $redirect->destination_place_id === $matchedPlace->merged_into_place_id,
                );

            if ($matchedPlace->status === PlaceStatus::Merged && ! $isAlias) {
                continue;
            }

            if ($isAlias) {
                $place = $matchedPlace->mergedInto;
                $assessment['score'] = min(100, $assessment['score'] + 10);
                $assessment['signals'][] = 'alias';
            }

            if ($assessment['score'] < 40) {
                continue;
            }

            $presentationScope = $place->visibility === PlaceVisibility::Public
                && $place->status === PlaceStatus::Active
                && (! $isAlias || $matchedPlace->visibility === PlaceVisibility::Public)
                    ? 'member_visible'
                    : 'review_only';
            $previous = $placeAssessments[$place->id] ?? null;

            if ($previous !== null && $previous['score'] >= $assessment['score']) {
                continue;
            }

            $placeAssessments[$place->id] = [
                'place' => $place,
                'score' => $assessment['score'],
                'signals' => $assessment['signals'],
                'distance' => $assessment['distance'],
                'presentation_scope' => $presentationScope,
            ];
        }

        ksort($placeAssessments);

        foreach ($placeAssessments as $assessment) {
            $place = $assessment['place'];
            $candidates[] = PlaceDuplicateCandidate::query()->firstOrCreate(
                [
                    'place_submission_id' => $submission->id,
                    'candidate_place_id' => $place->id,
                ],
                [
                    'candidate_submission_id' => null,
                    'candidate_key' => $this->candidateKey($submission, 'place', $place->id),
                    'algorithm_version' => self::ALGORITHM_VERSION,
                    'signals_fingerprint' => hash('sha256', implode('|', $assessment['signals'])),
                    'score' => $assessment['score'],
                    'confidence' => $this->confidence($assessment['score']),
                    'matched_signals' => $assessment['signals'],
                    'distance_meters' => $assessment['distance'],
                    'presentation_scope' => $assessment['presentation_scope'],
                    'created_at' => now(),
                ],
            );
        }

        foreach ($this->candidateSubmissions($submission) as $candidateSubmission) {
            $assessment = $this->scoreSubmission($submission, $candidateSubmission);

            if ($assessment['score'] < 40) {
                continue;
            }

            $candidates[] = PlaceDuplicateCandidate::query()->firstOrCreate(
                [
                    'place_submission_id' => $submission->id,
                    'candidate_submission_id' => $candidateSubmission->id,
                ],
                [
                    'candidate_place_id' => null,
                    'candidate_key' => $this->candidateKey($submission, 'submission', $candidateSubmission->id),
                    'algorithm_version' => self::ALGORITHM_VERSION,
                    'signals_fingerprint' => hash('sha256', implode('|', $assessment['signals'])),
                    'score' => $assessment['score'],
                    'confidence' => $this->confidence($assessment['score']),
                    'matched_signals' => $assessment['signals'],
                    'distance_meters' => $assessment['distance'],
                    'presentation_scope' => 'review_only',
                    'created_at' => now(),
                ],
            );
        }

        usort($candidates, static fn (PlaceDuplicateCandidate $left, PlaceDuplicateCandidate $right): int => [
            -$left->score,
            $left->candidate_place_id === null ? 1 : 0,
            $left->id,
        ] <=> [
            -$right->score,
            $right->candidate_place_id === null ? 1 : 0,
            $right->id,
        ]);

        return $candidates;
    }

    /** @return iterable<int, Place> */
    private function candidatePlaces(PlaceSubmission $submission): iterable
    {
        return Place::query()
            ->with(['mergedInto', 'mergeRedirects'])
            ->select([
                'id', 'normalized_name', 'normalized_address', 'normalized_phone', 'normalized_email',
                'normalized_website', 'public_latitude', 'public_longitude', 'catalog_category',
                'organization_id', 'visibility', 'status', 'merged_into_place_id',
            ])
            ->where(function (Builder $query) use ($submission): void {
                $this->identityWhere($query, $submission);
            })
            ->orderBy('id')
            ->limit(200)
            ->get();
    }

    /** @return iterable<int, PlaceSubmission> */
    private function candidateSubmissions(PlaceSubmission $submission): iterable
    {
        return PlaceSubmission::query()
            ->select([
                'id', 'normalized_name', 'normalized_address', 'normalized_phone', 'normalized_email',
                'normalized_website', 'public_latitude', 'public_longitude', 'catalog_category',
                'canonical_organization_id', 'status',
            ])
            ->whereKeyNot($submission->id)
            ->whereIn('status', array_map(
                static fn (PlaceSubmissionStatus $status): string => $status->value,
                [
                    PlaceSubmissionStatus::Submitted,
                    PlaceSubmissionStatus::NeedsInformation,
                    PlaceSubmissionStatus::DuplicateReview,
                    PlaceSubmissionStatus::Approved,
                ],
            ))
            ->where(function (Builder $query) use ($submission): void {
                $this->identityWhere($query, $submission);
            })
            ->orderBy('id')
            ->limit(200)
            ->get();
    }

    /** @param Builder<Place>|Builder<PlaceSubmission> $query */
    private function identityWhere(Builder $query, PlaceSubmission $submission): void
    {
        $query->where('normalized_name', $submission->normalized_name);

        foreach (['normalized_address', 'normalized_phone', 'normalized_email', 'normalized_website'] as $column) {
            $value = $submission->getAttribute($column);

            if (is_string($value) && $value !== '') {
                $query->orWhere($column, $value);
            }
        }

        if ($submission->public_latitude !== null && $submission->public_longitude !== null) {
            $query->orWhere(function (Builder $coordinates) use ($submission): void {
                $coordinates
                    ->whereBetween('public_latitude', [
                        (float) $submission->public_latitude - 0.002,
                        (float) $submission->public_latitude + 0.002,
                    ])
                    ->whereBetween('public_longitude', [
                        (float) $submission->public_longitude - 0.003,
                        (float) $submission->public_longitude + 0.003,
                    ]);
            });
        }
    }

    /** @return array{score: int, signals: list<string>, distance: int|null} */
    private function scorePlace(PlaceSubmission $submission, Place $place): array
    {
        return $this->score(
            $submission,
            $place->normalized_name,
            $place->normalized_address,
            $place->normalized_phone,
            $place->normalized_email,
            $place->normalized_website,
            $place->public_latitude,
            $place->public_longitude,
            $place->catalog_category,
            $place->organization_id,
        );
    }

    /** @return array{score: int, signals: list<string>, distance: int|null} */
    private function scoreSubmission(PlaceSubmission $submission, PlaceSubmission $candidate): array
    {
        return $this->score(
            $submission,
            $candidate->normalized_name,
            $candidate->normalized_address,
            $candidate->normalized_phone,
            $candidate->normalized_email,
            $candidate->normalized_website,
            $candidate->public_latitude,
            $candidate->public_longitude,
            $candidate->catalog_category,
            $candidate->canonical_organization_id,
        );
    }

    /** @return array{score: int, signals: list<string>, distance: int|null} */
    private function score(
        PlaceSubmission $submission,
        ?string $name,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
        string|float|int|null $latitude,
        string|float|int|null $longitude,
        ?string $category,
        ?int $organizationId,
    ): array {
        $score = 0;
        $signals = [];

        foreach ([
            'name' => [$submission->normalized_name, $name, 30],
            'address' => [$submission->normalized_address, $address, 25],
            'phone' => [$submission->normalized_phone, $phone, 20],
            'email' => [$submission->normalized_email, $email, 20],
            'website' => [$submission->normalized_website, $website, 20],
        ] as $signal => [$left, $right, $weight]) {
            if (is_string($left) && $left !== '' && hash_equals($left, (string) $right)) {
                $score += $weight;
                $signals[] = $signal;
            }
        }

        $distance = $this->normalizer->distanceMeters(
            $submission->public_latitude,
            $submission->public_longitude,
            $latitude,
            $longitude,
        );

        if ($distance !== null && $distance <= 250) {
            $score += $distance <= 75 ? 20 : 10;
            $signals[] = 'coordinates';
        }

        if ($submission->catalog_category === $category) {
            $score += 5;
            $signals[] = 'category';
        }

        if ($submission->canonical_organization_id !== null
            && $submission->canonical_organization_id === $organizationId) {
            $score += 15;
            $signals[] = 'organization';
        }

        return [
            'score' => min($score, 100),
            'signals' => $signals,
            'distance' => $distance,
        ];
    }

    private function candidateKey(PlaceSubmission $submission, string $type, int $id): string
    {
        return hash('sha256', implode('|', [self::ALGORITHM_VERSION, $submission->id, $type, $id]));
    }

    private function confidence(int $score): PlaceDuplicateConfidence
    {
        return match (true) {
            $score >= 80 => PlaceDuplicateConfidence::Strong,
            $score >= 60 => PlaceDuplicateConfidence::Likely,
            default => PlaceDuplicateConfidence::Possible,
        };
    }
}
