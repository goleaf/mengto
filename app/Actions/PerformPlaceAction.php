<?php

namespace App\Actions;

use App\Services\PlaceCatalog;
use App\Services\PlaceState;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PerformPlaceAction
{
    public function __construct(
        private readonly PlaceCatalog $catalog,
        private readonly PlaceState $state,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, mixed>}
     */
    public function handle(array $data): array
    {
        return match ((string) $data['action']) {
            'toggle-place-save' => $this->toggleSave($data),
            'toggle-place-follow' => $this->toggleFollow($data),
            'toggle-place-collection' => $this->toggleCollection($data),
            'mark-place-visited' => $this->markVisited($data),
            'check-in-place' => $this->checkIn($data),
            'clear-place-check-in' => $this->clearCheckIn($data),
            'clear-place-history' => $this->clearHistory(),
            'set-place-location' => $this->setLocation($data),
            'clear-place-location' => $this->clearLocation(),
            'invite-to-place' => $this->invite($data),
            'confirm-place-warning' => $this->confirmWarning($data),
            'resolve-place-warning' => $this->resolveWarning($data),
            'answer-place-question' => $this->answerQuestion($data),
            'create-place' => $this->createPlace($data),
            'create-place-correction' => $this->createCorrection($data),
            'create-place-warning' => $this->createWarning($data),
            'create-place-review' => $this->createReview($data),
            'create-place-question' => $this->createQuestion($data),
            'create-place-claim' => $this->createClaim($data),
            'create-place-report' => $this->createReport($data),
            default => throw ValidationException::withMessages([
                'action' => 'This place action is unavailable.',
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleSave(array $data): array
    {
        $place = $this->requirePlace($data);
        $active = $this->state->toggleSaved($place['key']);

        return $this->placeResult(
            $place,
            $active ? $place['name'].' saved.' : $place['name'].' removed from favorites.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleFollow(array $data): array
    {
        $place = $this->requirePlace($data);
        $active = $this->state->toggleFollow($place['key']);

        return $this->placeResult(
            $place,
            $active ? 'Place updates enabled.' : 'Place updates paused.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleCollection(array $data): array
    {
        $place = $this->requirePlace($data);
        $collection = (string) $data['place_collection'];
        $active = $this->state->addToCollection($place['key'], $collection);

        return $this->placeResult(
            $place,
            $active ? 'Place added to the selected collection.' : 'Place removed from the selected collection.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function markVisited(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->markVisited($place['key'], (string) ($data['place_pet'] ?? 'scout'));

        return $this->placeResult($place, 'Visit saved privately to your history.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function checkIn(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->checkIn(
            $place['key'],
            (string) ($data['place_pet'] ?? 'scout'),
            (string) ($data['place_visibility'] ?? 'private'),
        );

        return $this->placeResult(
            $place,
            'Check-in active for two hours. Your exact position is not published.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function clearCheckIn(array $data): array
    {
        $place = $this->requirePlace($data);

        if (! $this->state->clearCheckIn($place['key'])) {
            throw ValidationException::withMessages(['target' => 'There is no active check-in to end.']);
        }

        return $this->placeResult($place, 'Check-in ended.', $data);
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function clearHistory(): array
    {
        $this->state->clearRecent();

        return [
            'message' => 'Recent place history cleared.',
            'route' => 'places.index',
            'parameters' => ['mode' => 'browse'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function setLocation(array $data): array
    {
        $this->state->setGeneralizedLocation(
            (float) $data['place_latitude'],
            (float) $data['place_longitude'],
        );

        return [
            'message' => 'Approximate current area enabled. No home point or location history was saved.',
            'route' => 'places.index',
            'parameters' => ['view' => 'split'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function clearLocation(): array
    {
        $this->state->clearGeneralizedLocation();

        return [
            'message' => 'Location access removed. Manual city search remains available.',
            'route' => 'places.index',
            'parameters' => ['area' => 'Vilnius'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function invite(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addInvitation($place['key'], [
            'recipient' => (string) $data['place_recipient'],
            'proposed_date' => (string) ($data['place_visit_date'] ?? ''),
            'body' => trim((string) ($data['body'] ?? '')),
        ]);

        return $this->placeResult($place, 'Private place invitation sent.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function confirmWarning(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->confirmWarning($place['key'], (string) $data['place_warning']);

        return $this->placeResult($place, 'Warning confirmation recorded with the current time.', $data, 'updates');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function resolveWarning(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->resolveWarning($place['key'], (string) $data['place_warning']);

        return $this->placeResult($place, 'Warning marked resolved and moved to history.', $data, 'updates');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function answerQuestion(array $data): array
    {
        $place = $this->requirePlace($data);

        if (! $place['owner_managed']) {
            throw ValidationException::withMessages(['target' => 'Only a verified place manager can add an official answer.']);
        }

        if (! $this->state->answerQuestion(
            $place['key'],
            (string) $data['place_question'],
            $this->requiredText($data, 'body'),
        )) {
            throw ValidationException::withMessages(['place_question' => 'This question is unavailable.']);
        }

        return $this->placeResult($place, 'Official answer published.', $data, 'questions');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createPlace(array $data): array
    {
        $title = $this->requiredText($data, 'title');
        $address = $this->requiredText($data, 'place_address');
        $duplicate = collect($this->catalog->all())->first(
            static fn (array $place): bool => Str::slug($place['name']) === Str::slug($title)
                || Str::lower($place['address']) === Str::lower($address),
        );

        if ($duplicate !== null) {
            throw ValidationException::withMessages([
                'title' => 'A similar place already exists: '.$duplicate['name'].'. Suggest a correction instead.',
            ]);
        }

        $submission = $this->state->addSubmission([
            'title' => $title,
            'category' => (string) $data['category'],
            'body' => $this->requiredText($data, 'body'),
            'city' => $this->requiredText($data, 'city'),
            'address' => $address,
            'coordinates' => trim((string) ($data['place_coordinates'] ?? '')),
            'hours' => trim((string) ($data['place_hours'] ?? '')),
            'pet_rules' => trim((string) ($data['rules'] ?? '')),
            'features' => trim((string) ($data['place_features'] ?? '')),
            'source' => trim((string) ($data['place_source'] ?? 'Community visit')),
            'evidence' => trim((string) ($data['place_evidence'] ?? '')),
            'relationship' => (string) ($data['place_relationship'] ?? 'visitor'),
        ]);

        return [
            'message' => 'Place submitted for duplicate and information review.',
            'route' => 'places.index',
            'parameters' => ['mode' => 'browse', 'q' => $submission['title']],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createCorrection(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addCorrection($place['key'], [
            'field' => (string) $data['place_field'],
            'current_value' => trim((string) ($data['place_current_value'] ?? '')),
            'proposed_value' => $this->requiredText($data, 'body'),
            'evidence' => $this->requiredText($data, 'place_evidence'),
            'visited_at' => (string) ($data['place_visit_date'] ?? ''),
            'source' => (string) ($data['place_source'] ?? 'personal-visit'),
        ]);

        return $this->placeResult($place, 'Correction submitted with evidence.', $data, 'corrections');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createWarning(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addWarning($place['key'], [
            'title' => $this->requiredText($data, 'title'),
            'category' => (string) $data['category'],
            'detail' => $this->requiredText($data, 'body'),
            'zone' => trim((string) ($data['place_zone'] ?? 'General place area')),
            'evidence' => trim((string) ($data['place_evidence'] ?? '')),
        ]);

        return $this->placeResult(
            $place,
            'Temporary warning published for review and automatic expiry.',
            $data,
            'updates',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createReview(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addReview($place['key'], [
            'rating' => (int) $data['place_rating'],
            'body' => $this->requiredText($data, 'body'),
            'visited_with' => Str::headline((string) ($data['place_pet'] ?? 'scout')),
            'criterion' => (string) ($data['place_review_criterion'] ?? 'overall'),
            'anonymous' => ($data['place_anonymous'] ?? 'no') === 'yes',
        ]);

        return $this->placeResult($place, 'Review published with its visit context.', $data, 'reviews');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createQuestion(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addQuestion($place['key'], [
            'question' => $this->requiredText($data, 'body'),
        ]);

        return $this->placeResult($place, 'Question sent to the place community.', $data, 'questions');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createClaim(array $data): array
    {
        $place = $this->requirePlace($data);

        if ($place['owner_managed']) {
            throw ValidationException::withMessages(['target' => 'This demo place already has an active manager.']);
        }

        $this->state->addClaim($place['key'], [
            'role' => (string) $data['place_relationship'],
            'organization' => $this->requiredText($data, 'title'),
            'contact' => $this->requiredText($data, 'place_contact'),
            'verification_method' => (string) $data['place_verification_method'],
            'evidence' => $this->requiredText($data, 'place_evidence'),
        ]);

        return $this->placeResult($place, 'Management claim sent for scoped verification.', $data, 'updates');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createReport(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->addReport($place['key'], [
            'category' => (string) $data['category'],
            'body' => $this->requiredText($data, 'body'),
            'evidence' => trim((string) ($data['place_evidence'] ?? '')),
        ]);

        return $this->placeResult($place, 'Private report received by the moderation queue.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requirePlace(array $data): array
    {
        $place = $this->catalog->find((string) ($data['target'] ?? ''));

        if ($place === null) {
            throw ValidationException::withMessages(['target' => 'This place is unavailable.']);
        }

        return $place;
    }

    /**
     * @param  array<string, mixed>  $place
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function placeResult(array $place, string $message, array $data, ?string $tab = null): array
    {
        $tab ??= trim((string) ($data['place_return_tab'] ?? 'overview'));

        return [
            'message' => $message,
            'route' => 'places.show',
            'parameters' => [
                'place' => $place['key'],
                'tab' => $tab === '' ? 'overview' : $tab,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requiredText(array $data, string $field): string
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([$field => 'This field is required.']);
        }

        return $value;
    }
}
