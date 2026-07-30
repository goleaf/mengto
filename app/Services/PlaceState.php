<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class PlaceState
{
    private const SESSION_KEY = 'paw-circle.places.v1';

    public function __construct(private readonly Session $session) {}

    public function isSaved(string $place): bool
    {
        return in_array($place, $this->state()['saved'], true);
    }

    public function toggleSaved(string $place): bool
    {
        $state = $this->state();
        $active = ! in_array($place, $state['saved'], true);
        $state['saved'] = $this->toggleListValue($state['saved'], $place, $active);
        $this->recordHistory($state, $place, $active ? 'Saved to favorites.' : 'Removed from favorites.');
        $this->store($state);

        return $active;
    }

    public function isFollowed(string $place): bool
    {
        return in_array($place, $this->state()['followed'], true);
    }

    public function toggleFollow(string $place): bool
    {
        $state = $this->state();
        $active = ! in_array($place, $state['followed'], true);
        $state['followed'] = $this->toggleListValue($state['followed'], $place, $active);
        $this->recordHistory($state, $place, $active ? 'Place updates enabled.' : 'Place updates paused.');
        $this->store($state);

        return $active;
    }

    public function hasVisited(string $place): bool
    {
        return isset($this->state()['visited'][$place]);
    }

    public function markVisited(string $place, string $pet = 'scout'): void
    {
        $state = $this->state();
        $state['visited'][$place] = [
            'pet' => $pet,
            'visited_at' => now()->toAtomString(),
        ];
        $this->recordHistory($state, $place, 'Marked as visited with '.Str::headline($pet).'.');
        $this->store($state);
    }

    /**
     * @return array<string, array{name: string, privacy: string, places: array<int, string>}>
     */
    public function collections(): array
    {
        return $this->state()['collections'];
    }

    public function isInCollection(string $place, string $collection): bool
    {
        return in_array($place, $this->state()['collections'][$collection]['places'] ?? [], true);
    }

    public function addToCollection(string $place, string $collection): bool
    {
        $state = $this->state();
        $state['collections'][$collection] ??= [
            'name' => Str::headline($collection),
            'privacy' => 'private',
            'places' => [],
        ];
        $active = ! in_array($place, $state['collections'][$collection]['places'], true);
        $state['collections'][$collection]['places'] = $this->toggleListValue(
            $state['collections'][$collection]['places'],
            $place,
            $active,
        );
        $this->recordHistory(
            $state,
            $place,
            $active
                ? 'Added to '.$state['collections'][$collection]['name'].'.'
                : 'Removed from '.$state['collections'][$collection]['name'].'.',
        );
        $this->store($state);

        return $active;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function currentCheckIn(string $place): ?array
    {
        $checkIn = $this->state()['check_ins'][$place] ?? null;

        if ($checkIn === null || now()->greaterThan($checkIn['expires_at'])) {
            if ($checkIn !== null) {
                $state = $this->state();
                unset($state['check_ins'][$place]);
                $this->store($state);
            }

            return null;
        }

        return $checkIn;
    }

    /**
     * @return array<string, mixed>
     */
    public function checkIn(string $place, string $pet, string $visibility): array
    {
        return Cache::lock('paw-circle:place-check-in:'.$place, 5)->block(
            2,
            function () use ($place, $pet, $visibility): array {
                $state = $this->state();
                $checkIn = [
                    'id' => (string) Str::uuid(),
                    'place' => $place,
                    'pet' => $pet,
                    'visibility' => $visibility,
                    'created_at' => now()->toAtomString(),
                    'expires_at' => now()->addHours(2)->toAtomString(),
                ];
                $state['check_ins'][$place] = $checkIn;
                $this->recordHistory($state, $place, 'Private check-in created with automatic expiry.');
                $this->store($state);

                return $checkIn;
            },
        );
    }

    public function clearCheckIn(string $place): bool
    {
        $state = $this->state();

        if (! isset($state['check_ins'][$place])) {
            return false;
        }

        unset($state['check_ins'][$place]);
        $this->recordHistory($state, $place, 'Check-in ended.');
        $this->store($state);

        return true;
    }

    public function recordViewed(string $place): void
    {
        $state = $this->state();
        $state['recent'] = array_values(array_unique([$place, ...$state['recent']]));
        $state['recent'] = array_slice($state['recent'], 0, 12);
        $this->store($state);
    }

    /**
     * @return array<int, string>
     */
    public function recent(): array
    {
        return $this->state()['recent'];
    }

    public function clearRecent(): void
    {
        $state = $this->state();
        $state['recent'] = [];
        $this->store($state);
    }

    /**
     * @param  array<string, mixed>  $invitation
     */
    public function addInvitation(string $place, array $invitation): void
    {
        $state = $this->state();
        $state['invitations'][$place] ??= [];
        array_unshift($state['invitations'][$place], [
            ...$invitation,
            'id' => (string) Str::uuid(),
            'created_at' => now()->toAtomString(),
            'status' => 'sent',
        ]);
        $state['invitations'][$place] = array_slice($state['invitations'][$place], 0, 20);
        $this->recordHistory($state, $place, 'Place invitation sent through PawCircle.');
        $this->store($state);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function invitations(string $place): array
    {
        return $this->state()['invitations'][$place] ?? [];
    }

    /**
     * @return array{enabled: bool, latitude: float|null, longitude: float|null, label: string}
     */
    public function generalizedLocation(): array
    {
        return $this->state()['generalized_location'];
    }

    public function setGeneralizedLocation(float $latitude, float $longitude): void
    {
        $state = $this->state();
        $state['generalized_location'] = [
            'enabled' => true,
            'latitude' => round($latitude, 3),
            'longitude' => round($longitude, 3),
            'label' => 'Approximate current area',
        ];
        $this->store($state);
    }

    public function clearGeneralizedLocation(): void
    {
        $state = $this->state();
        $state['generalized_location'] = [
            'enabled' => false,
            'latitude' => null,
            'longitude' => null,
            'label' => 'Vilnius selected manually',
        ];
        $this->store($state);
    }

    /**
     * @param  array<string, mixed>  $submission
     * @return array<string, mixed>
     */
    public function addSubmission(array $submission): array
    {
        return Cache::lock('paw-circle:place-submission:'.Str::slug((string) $submission['title']), 5)->block(
            2,
            function () use ($submission): array {
                $state = $this->state();
                $submission = [
                    ...$submission,
                    'id' => (string) Str::uuid(),
                    'key' => 'community-'.Str::slug((string) $submission['title']).'-'.Str::lower(Str::random(5)),
                    'status' => 'pending-review',
                    'created_at' => now()->toAtomString(),
                ];
                array_unshift($state['submissions'], $submission);
                $state['submissions'] = array_slice($state['submissions'], 0, 20);
                $this->recordHistory($state, (string) $submission['key'], 'Community place submitted for review.');
                $this->store($state);

                return $submission;
            },
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function submissions(): array
    {
        return $this->state()['submissions'];
    }

    /**
     * @param  array<string, mixed>  $correction
     */
    public function addCorrection(string $place, array $correction): void
    {
        Cache::lock('paw-circle:place-correction:'.$place, 5)->block(
            2,
            function () use ($place, $correction): void {
                $state = $this->state();
                $record = [
                    ...$correction,
                    'id' => (string) Str::uuid(),
                    'place' => $place,
                    'status' => 'submitted',
                    'created_at' => now()->toAtomString(),
                ];
                $state['corrections'][$place] ??= [];
                array_unshift($state['corrections'][$place], $record);
                $state['corrections'][$place] = array_slice($state['corrections'][$place], 0, 20);
                $this->recordHistory($state, $place, 'Correction submitted with evidence for review.');
                $this->store($state);
            },
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function corrections(string $place): array
    {
        return $this->state()['corrections'][$place] ?? [];
    }

    /**
     * @param  array<string, mixed>  $warning
     * @return array<string, mixed>
     */
    public function addWarning(string $place, array $warning): array
    {
        return Cache::lock('paw-circle:place-warning:'.$place, 5)->block(
            2,
            function () use ($place, $warning): array {
                $state = $this->state();
                $warning = [
                    ...$warning,
                    'key' => 'warning-'.Str::lower(Str::random(10)),
                    'place' => $place,
                    'status' => 'new',
                    'confirmations' => 1,
                    'reported_at' => now()->toAtomString(),
                    'expires_at' => now()->addDays(3)->toAtomString(),
                    'source' => 'Community report awaiting review',
                ];
                $state['warnings'][$place] ??= [];
                array_unshift($state['warnings'][$place], $warning);
                $state['warnings'][$place] = array_slice($state['warnings'][$place], 0, 20);
                $this->recordHistory($state, $place, 'Temporary warning submitted.');
                $this->store($state);

                return $warning;
            },
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $baseWarnings
     * @return array<int, array<string, mixed>>
     */
    public function warnings(string $place, array $baseWarnings = []): array
    {
        $state = $this->state();
        $warnings = [
            ...($state['warnings'][$place] ?? []),
            ...$baseWarnings,
        ];

        return array_values(array_map(
            static function (array $warning) use ($state, $place): array {
                $key = (string) $warning['key'];
                $confirmed = (int) ($state['warning_confirmations'][$place][$key] ?? 0);

                if (($state['resolved_warnings'][$place][$key] ?? false) === true) {
                    $warning['status'] = 'resolved';
                    $warning['resolved_at'] = $state['resolved_warnings_at'][$place][$key] ?? null;
                }

                $warning['confirmations'] = (int) $warning['confirmations'] + $confirmed;

                return $warning;
            },
            array_filter(
                $warnings,
                static fn (array $warning): bool => in_array($warning['status'], ['resolved', 'expired', 'false'], true)
                    || now()->lessThanOrEqualTo($warning['expires_at']),
            ),
        ));
    }

    public function confirmWarning(string $place, string $warning): bool
    {
        $state = $this->state();

        foreach ($state['warnings'][$place] ?? [] as $index => $record) {
            if (($record['key'] ?? '') !== $warning) {
                continue;
            }

            $state['warnings'][$place][$index]['confirmations'] = (int) $record['confirmations'] + 1;
            $state['warnings'][$place][$index]['status'] = 'confirmed';
            $this->recordHistory($state, $place, 'A visitor confirmed warning '.$warning.'.');
            $this->store($state);

            return true;
        }

        $state['warning_confirmations'][$place][$warning] = (int) ($state['warning_confirmations'][$place][$warning] ?? 0) + 1;
        $this->recordHistory($state, $place, 'A visitor confirmed catalog warning '.$warning.'.');
        $this->store($state);

        return true;
    }

    public function resolveWarning(string $place, string $warning): bool
    {
        $state = $this->state();

        foreach ($state['warnings'][$place] ?? [] as $index => $record) {
            if (($record['key'] ?? '') !== $warning) {
                continue;
            }

            $state['warnings'][$place][$index]['status'] = 'resolved';
            $state['warnings'][$place][$index]['resolved_at'] = now()->toAtomString();
            $this->recordHistory($state, $place, 'Warning '.$warning.' resolved and archived.');
            $this->store($state);

            return true;
        }

        $state['resolved_warnings'][$place][$warning] = true;
        $state['resolved_warnings_at'][$place][$warning] = now()->toAtomString();
        $this->recordHistory($state, $place, 'Catalog warning '.$warning.' resolved and archived.');
        $this->store($state);

        return true;
    }

    /**
     * @param  array<string, mixed>  $review
     */
    public function addReview(string $place, array $review): void
    {
        $state = $this->state();
        $review = [
            ...$review,
            'key' => 'review-'.Str::lower(Str::random(10)),
            'place' => $place,
            'author' => ($review['anonymous'] ?? false) ? 'Anonymous visitor' : 'Mia Carter',
            'initials' => ($review['anonymous'] ?? false) ? 'AV' : 'MC',
            'verified' => $this->hasVisited($place),
            'date' => now()->format('M j, Y'),
            'owner_response' => null,
        ];
        $state['reviews'][$place] ??= [];
        array_unshift($state['reviews'][$place], $review);
        $state['reviews'][$place] = array_slice($state['reviews'][$place], 0, 20);
        $this->recordHistory($state, $place, 'Review submitted.');
        $this->store($state);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reviews(string $place): array
    {
        return $this->state()['reviews'][$place] ?? [];
    }

    /**
     * @param  array<string, mixed>  $question
     */
    public function addQuestion(string $place, array $question): void
    {
        $state = $this->state();
        $question = [
            ...$question,
            'key' => 'question-'.Str::lower(Str::random(10)),
            'place' => $place,
            'author' => 'Mia Carter',
            'answer' => null,
            'answer_author' => null,
            'answered_at' => null,
            'created_at' => now()->toAtomString(),
        ];
        $state['questions'][$place] ??= [];
        array_unshift($state['questions'][$place], $question);
        $state['questions'][$place] = array_slice($state['questions'][$place], 0, 20);
        $this->recordHistory($state, $place, 'Question submitted to the place community.');
        $this->store($state);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function questions(string $place): array
    {
        return $this->state()['questions'][$place] ?? [];
    }

    public function answerQuestion(string $place, string $question, string $body): bool
    {
        $state = $this->state();

        foreach ($state['questions'][$place] ?? [] as $index => $record) {
            if (($record['key'] ?? '') !== $question) {
                continue;
            }

            $state['questions'][$place][$index]['answer'] = $body;
            $state['questions'][$place][$index]['answer_author'] = 'Official place response';
            $state['questions'][$place][$index]['answered_at'] = 'Answered now';
            $this->recordHistory($state, $place, 'Official answer added.');
            $this->store($state);

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $claim
     */
    public function addClaim(string $place, array $claim): void
    {
        $state = $this->state();
        $state['claims'][$place] ??= [];
        array_unshift($state['claims'][$place], [
            ...$claim,
            'id' => (string) Str::uuid(),
            'status' => 'pending-verification',
            'created_at' => now()->toAtomString(),
        ]);
        $state['claims'][$place] = array_slice($state['claims'][$place], 0, 10);
        $this->recordHistory($state, $place, 'Management claim submitted for verification.');
        $this->store($state);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function claims(string $place): array
    {
        return $this->state()['claims'][$place] ?? [];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function addReport(string $place, array $report): void
    {
        $state = $this->state();
        array_unshift($state['reports'], [
            ...$report,
            'id' => (string) Str::uuid(),
            'place' => $place,
            'status' => 'received',
            'created_at' => now()->toAtomString(),
        ]);
        $state['reports'] = array_slice($state['reports'], 0, 30);
        $this->recordHistory($state, $place, 'Private place report received.');
        $this->store($state);
    }

    /**
     * @return array<int, array{message: string, created_at: string}>
     */
    public function history(string $place): array
    {
        return $this->state()['history'][$place] ?? [];
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function toggleListValue(array $values, string $value, bool $active): array
    {
        if ($active) {
            return array_values(array_unique([...$values, $value]));
        }

        return array_values(array_filter(
            $values,
            static fn (string $candidate): bool => $candidate !== $value,
        ));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function recordHistory(array &$state, string $place, string $message): void
    {
        $state['history'][$place] ??= [];
        array_unshift($state['history'][$place], [
            'message' => $message,
            'created_at' => now()->toAtomString(),
        ]);
        $state['history'][$place] = array_slice($state['history'][$place], 0, 30);
    }

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return [
            'saved' => $stored['saved'] ?? [],
            'followed' => $stored['followed'] ?? [],
            'visited' => $stored['visited'] ?? [],
            'collections' => $stored['collections'] ?? [
                'evening-walks' => [
                    'name' => 'Evening walks',
                    'privacy' => 'private',
                    'places' => [],
                ],
                'vilnius-trip' => [
                    'name' => 'Vilnius trip',
                    'privacy' => 'shared with family',
                    'places' => ['vingis-quiet-loop', 'paws-24-veterinary-center', 'old-town-pet-cafe'],
                ],
                'scout-places' => [
                    'name' => 'Places for Scout',
                    'privacy' => 'private',
                    'places' => [],
                ],
            ],
            'check_ins' => $stored['check_ins'] ?? [],
            'recent' => $stored['recent'] ?? [],
            'invitations' => $stored['invitations'] ?? [],
            'generalized_location' => $stored['generalized_location'] ?? [
                'enabled' => false,
                'latitude' => null,
                'longitude' => null,
                'label' => 'Vilnius selected manually',
            ],
            'submissions' => $stored['submissions'] ?? [],
            'corrections' => $stored['corrections'] ?? [],
            'warnings' => $stored['warnings'] ?? [],
            'warning_confirmations' => $stored['warning_confirmations'] ?? [],
            'resolved_warnings' => $stored['resolved_warnings'] ?? [],
            'resolved_warnings_at' => $stored['resolved_warnings_at'] ?? [],
            'reviews' => $stored['reviews'] ?? [],
            'questions' => $stored['questions'] ?? [],
            'claims' => $stored['claims'] ?? [],
            'reports' => $stored['reports'] ?? [],
            'history' => $stored['history'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function store(array $state): void
    {
        $this->session->put(self::SESSION_KEY, $state);
    }
}
