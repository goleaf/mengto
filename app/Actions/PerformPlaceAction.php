<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;
use App\Models\User;
use App\Services\PlaceCatalog;
use App\Services\PlaceState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PerformPlaceAction
{
    public function __construct(
        private readonly PlaceCatalog $catalog,
        private readonly PlaceState $state,
        private readonly ResolveAccessiblePlace $resolvePlace,
        private readonly SubmitPlaceQuestion $submitQuestion,
        private readonly AnswerPlaceQuestion $answerQuestion,
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
                'action' => __('messages.this_place_action_is_unavailable_beb1e64e42'),
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
            $active
                ? __('messages.place.saved', ['place' => $place['name']])
                : __('messages.place.unsaved', ['place' => $place['name']]),
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
            $active ? __('messages.place_updates_enabled_60cd691dfb') : __('messages.place_updates_paused_6c7fb2288f'),
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
            $active ? __('messages.place_added_to_the_selected_collection_9ae9bcfdff') : __('messages.place_removed_from_the_selected_collection_28a687124f'),
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

        return $this->placeResult($place, __('messages.visit_saved_privately_to_your_history_9c47cfe823'), $data);
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
            __('messages.check_in_active_for_two_hours_your_exact_position_is_not_a63cd5f631'),
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
            throw ValidationException::withMessages(['target' => __('messages.there_is_no_active_check_in_to_end_687d5a4687')]);
        }

        return $this->placeResult($place, __('messages.check_in_ended_3f3fb06d29'), $data);
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function clearHistory(): array
    {
        $this->state->clearRecent();

        return [
            'message' => __('messages.recent_place_history_cleared_abcac04962'),
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
            'message' => __('messages.approximate_current_area_enabled_no_home_point_or_locati_a48cf43587'),
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
            'message' => __('messages.location_access_removed_manual_city_search_remains_avail_4b3ba1cf71'),
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

        return $this->placeResult($place, __('messages.private_place_invitation_sent_10fea480d5'), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function confirmWarning(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->confirmWarning($place['key'], (string) $data['place_warning']);

        return $this->placeResult($place, __('messages.warning_confirmation_recorded_with_the_current_time_5a2d8d4ba4'), $data, 'updates');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function resolveWarning(array $data): array
    {
        $place = $this->requirePlace($data);
        $this->state->resolveWarning($place['key'], (string) $data['place_warning']);

        return $this->placeResult($place, __('messages.warning_marked_resolved_and_moved_to_history_aa0b43945e'), $data, 'updates');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function answerQuestion(array $data): array
    {
        $actor = $this->requireActor();
        $place = $this->requireCanonicalPlace($actor, $data);
        $this->answerQuestion->handle(
            $actor,
            $place,
            (string) $data['place_question'],
            $this->requiredText($data, 'body'),
            (string) $data['place_idempotency_key'],
        );

        return $this->placeResult(
            ['key' => $place->stable_key, 'name' => $place->name],
            __('places.questions.answer_published'),
            $data,
            'questions',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createPlace(array $data): array
    {
        return [
            'message' => __('places.submissions.create.description'),
            'route' => 'places.submissions.create',
            'parameters' => [],
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

        return $this->placeResult($place, __('messages.correction_submitted_with_evidence_7d964aa734'), $data, 'corrections');
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
            'zone' => trim((string) ($data['place_zone'] ?? __('messages.place.general_area'))),
            'evidence' => trim((string) ($data['place_evidence'] ?? '')),
        ]);

        return $this->placeResult(
            $place,
            __('messages.temporary_warning_published_for_review_and_automatic_exp_76764068a4'),
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

        return $this->placeResult($place, __('messages.review_published_with_its_visit_context_d0d6ca5e07'), $data, 'reviews');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createQuestion(array $data): array
    {
        $actor = $this->requireActor();
        $place = $this->requireCanonicalPlace($actor, $data);
        $this->submitQuestion->handle(
            $actor,
            $place,
            $this->requiredText($data, 'body'),
            (string) $data['place_idempotency_key'],
        );

        return $this->placeResult(
            ['key' => $place->stable_key, 'name' => $place->name],
            __('places.questions.sent'),
            $data,
            'questions',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createClaim(array $data): array
    {
        $place = $this->requirePlace($data);

        if ($place['owner_managed']) {
            throw ValidationException::withMessages(['target' => __('messages.this_demo_place_already_has_an_active_manager_808e171ba6')]);
        }

        $this->state->addClaim($place['key'], [
            'role' => (string) $data['place_relationship'],
            'organization' => $this->requiredText($data, 'title'),
            'contact' => $this->requiredText($data, 'place_contact'),
            'verification_method' => (string) $data['place_verification_method'],
            'evidence' => $this->requiredText($data, 'place_evidence'),
        ]);

        return $this->placeResult($place, __('messages.management_claim_sent_for_scoped_verification_6f89507342'), $data, 'updates');
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

        return $this->placeResult($place, __('messages.private_report_received_by_the_moderation_queue_6e7db0e9d9'), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requirePlace(array $data): array
    {
        $place = $this->catalog->find((string) ($data['target'] ?? ''));

        if ($place === null) {
            throw ValidationException::withMessages(['target' => __('messages.this_place_is_unavailable_61355a19b2')]);
        }

        return $place;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireCanonicalPlace(User $actor, array $data): Place
    {
        $place = $this->resolvePlace->handle($actor, (string) ($data['target'] ?? ''));

        if ($place === null) {
            throw ValidationException::withMessages([
                'target' => __('places.validation.unavailable'),
            ]);
        }

        return $place;
    }

    private function requireActor(): User
    {
        $actor = Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException;
        }

        return $actor;
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
            throw ValidationException::withMessages([$field => __('messages.this_field_is_required_68cadcee19')]);
        }

        return $value;
    }
}
