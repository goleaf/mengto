<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\User;
use App\Notifications\PlaceSubmissionStatusChanged;
use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class PlaceSubmissionTransition
{
    public function __construct(private Gate $gate, private Request $request) {}

    /**
     * @param  list<PlaceSubmissionStatus>  $allowedFrom
     * @param  Closure(PlaceSubmission): void|null  $mutate
     * @param  Closure(PlaceSubmission, PlaceSubmissionEvent): void|null  $afterEvent
     */
    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $ability,
        array $allowedFrom,
        PlaceSubmissionStatus $toStatus,
        PlaceSubmissionAction $action,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
        ?string $reasonDetail = null,
        ?PlaceDuplicateCandidate $candidate = null,
        ?int $destinationPlaceId = null,
        ?PlaceSubmissionResolution $resolution = null,
        ?Closure $mutate = null,
        ?Closure $afterEvent = null,
        bool $recordsReview = true,
        bool $notifySubmitter = true,
        string $rateScope = 'place-moderation',
        int $rateLimit = 60,
    ): PlaceSubmission {
        $this->authorize($actor, $ability, $submission, $candidate);
        $this->validate($operationKey, $expectedLockVersion, $reasonCode, $reasonDetail);

        $eventKey = 'moderation:'.$actor->id.':'.$operationKey;
        $fingerprint = $this->fingerprint(
            $submission,
            $action,
            $expectedLockVersion,
            $reasonCode,
            $reasonDetail,
            $candidate,
            $destinationPlaceId,
        );
        $replay = PlaceSubmissionEvent::query()->where('idempotency_key', $eventKey)->first();

        if ($replay !== null) {
            return $this->replay($replay, $submission, $fingerprint);
        }

        $rateKey = $rateScope.':hour:'.hash('sha256', (string) $actor->id);

        if (RateLimiter::tooManyAttempts($rateKey, $rateLimit)) {
            throw ValidationException::withMessages([
                'moderation' => __('places.submissions.validation.rate_limited'),
            ]);
        }

        [$result, $created] = DB::transaction(function () use (
            $actor,
            $submission,
            $ability,
            $allowedFrom,
            $toStatus,
            $action,
            $eventKey,
            $fingerprint,
            $expectedLockVersion,
            $reasonCode,
            $reasonDetail,
            $candidate,
            $destinationPlaceId,
            $resolution,
            $mutate,
            $afterEvent,
            $recordsReview,
        ): array {
            $existingEvent = PlaceSubmissionEvent::query()
                ->where('idempotency_key', $eventKey)
                ->lockForUpdate()
                ->first();

            if ($existingEvent !== null) {
                return [$this->replay($existingEvent, $submission, $fingerprint), false];
            }

            $locked = PlaceSubmission::query()->lockForUpdate()->findOrFail($submission->id);
            $this->authorize($actor, $ability, $locked, $candidate);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('places.submissions.validation.stale'),
                ]);
            }

            if (! in_array($locked->status, $allowedFrom, true)) {
                throw ValidationException::withMessages([
                    'status' => __('places.submissions.validation.transition'),
                ]);
            }

            $fromStatus = $locked->status;
            $mutate?->__invoke($locked);
            $transitionedAt = now()->toImmutable();
            $locked->status = $toStatus;

            if ($recordsReview) {
                $locked->reviewed_by_user_id = $actor->id;
                $locked->reviewed_at = $transitionedAt;
            }

            $locked->lock_version = $expectedLockVersion + 1;

            if ($resolution !== null) {
                $locked->resolution = $resolution;
            }

            if ($toStatus === PlaceSubmissionStatus::Approved) {
                $locked->approved_at = $transitionedAt;
            }

            if ($toStatus === PlaceSubmissionStatus::Published) {
                $locked->published_at = $transitionedAt;
            }

            if ($toStatus === PlaceSubmissionStatus::Rejected) {
                $locked->rejected_at = $transitionedAt;
            }

            if ($toStatus === PlaceSubmissionStatus::Withdrawn) {
                $locked->withdrawn_at = $transitionedAt;
            }

            if ($toStatus === PlaceSubmissionStatus::Submitted) {
                $locked->approved_at = null;
                $locked->rejected_at = null;
                $locked->withdrawn_at = null;
            }

            $locked->save();

            $event = PlaceSubmissionEvent::query()->create([
                'place_submission_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'place_duplicate_candidate_id' => $candidate?->id,
                'candidate_place_id' => $candidate?->candidate_place_id,
                'destination_place_id' => $destinationPlaceId,
                'idempotency_key' => $eventKey,
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason_code' => $reasonCode,
                'reason_detail' => $reasonDetail,
                'payload_fingerprint' => $fingerprint,
                'expected_lock_version' => $expectedLockVersion,
                'result_lock_version' => $locked->lock_version,
                'audit_context' => $this->auditContext(),
                'created_at' => now(),
            ]);

            $afterEvent?->__invoke($locked, $event);

            return [$locked->refresh(), true];
        }, 3);

        if ($created) {
            RateLimiter::hit($rateKey, 3600);
        }

        if ($created && $notifySubmitter) {
            $recipient = $result->submitter;
            DB::afterCommit(static function () use ($recipient, $result): void {
                $recipient->notify(new PlaceSubmissionStatusChanged($result, $result->status));
            });
        }

        return $result;
    }

    private function validate(
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
        ?string $reasonDetail,
    ): void {
        Validator::make([
            'operation_key' => $operationKey,
            'expected_lock_version' => $expectedLockVersion,
            'reason_code' => $reasonCode,
            'reason_detail' => $reasonDetail,
        ], [
            'operation_key' => ['required', 'uuid'],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
            'reason_code' => ['required', 'string', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', 'max:80'],
            'reason_detail' => ['nullable', 'string', 'max:2000'],
        ])->validate();
    }

    private function fingerprint(
        PlaceSubmission $submission,
        PlaceSubmissionAction $action,
        int $expectedLockVersion,
        string $reasonCode,
        ?string $reasonDetail,
        ?PlaceDuplicateCandidate $candidate,
        ?int $destinationPlaceId,
    ): string {
        return hash_hmac('sha256', (string) json_encode([
            'submission' => $submission->stable_key,
            'action' => $action->value,
            'expected_lock_version' => $expectedLockVersion,
            'reason_code' => $reasonCode,
            'reason_detail' => $reasonDetail,
            'candidate' => $candidate?->candidate_key,
            'destination_place_id' => $destinationPlaceId,
        ], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    private function replay(
        PlaceSubmissionEvent $event,
        PlaceSubmission $submission,
        string $fingerprint,
    ): PlaceSubmission {
        if (! hash_equals((string) $event->payload_fingerprint, $fingerprint)
            || $event->place_submission_id !== $submission->id) {
            throw ValidationException::withMessages([
                'operation_key' => __('places.submissions.validation.idempotency_conflict'),
            ]);
        }

        return PlaceSubmission::query()->findOrFail($submission->id);
    }

    private function authorize(
        User $actor,
        string $ability,
        PlaceSubmission $submission,
        ?PlaceDuplicateCandidate $candidate,
    ): void {
        $arguments = $candidate === null ? $submission : [$submission, $candidate];
        $this->gate->forUser($actor)->authorize($ability, $arguments);
    }

    /** @return array<string, string> */
    private function auditContext(): array
    {
        return array_filter([
            'request_id' => $this->request->attributes->getString('request_id'),
            'channel' => $this->request->attributes->getString('operation_channel', 'application'),
        ], static fn (string $value): bool => $value !== '');
    }
}
