<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CredentialStatus;
use App\Models\Credential;
use App\Models\CredentialVerificationAppeal;
use App\Models\CredentialVerificationEvent;
use App\Models\User;
use App\Policies\CredentialPolicy;
use App\Services\SynchronizeAdoptionProviderIdentity;
use App\Services\SynchronizeExpertVerificationStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ReviewCredentialVerificationAppeal
{
    public function __construct(
        private readonly CredentialPolicy $policy,
        private readonly SynchronizeExpertVerificationStatus $synchronizeProfile,
        private readonly SynchronizeAdoptionProviderIdentity $synchronizeAdoptionProvider,
    ) {}

    public function handle(
        User $reviewer,
        int $appealId,
        string $outcome,
        string $response,
        string $idempotencyKey,
    ): CredentialVerificationAppeal {
        Validator::make(
            ['outcome' => $outcome, 'response' => $response],
            [
                'outcome' => ['required', Rule::in(['upheld', 'reversed', 'information-requested'])],
                'response' => ['required', 'string', 'min:20', 'max:4000'],
            ],
        )->validate();

        return DB::transaction(function () use (
            $reviewer,
            $appealId,
            $outcome,
            $response,
            $idempotencyKey,
        ): CredentialVerificationAppeal {
            $existingEvent = CredentialVerificationEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return CredentialVerificationAppeal::query()->findOrFail(
                    (int) ($existingEvent->metadata['appeal_id'] ?? 0),
                );
            }

            $appeal = CredentialVerificationAppeal::query()
                ->lockForUpdate()
                ->findOrFail($appealId);
            $credential = Credential::query()
                ->with('expertProfile:id,owner_id,owner_key')
                ->lockForUpdate()
                ->findOrFail($appeal->credential_id);
            $this->authorizeReviewer($reviewer, $credential);

            if (! in_array($appeal->status, ['submitted', 'in-review'], true)) {
                throw ValidationException::withMessages([
                    'appeal' => __('credential_verification.validation.appeal_status'),
                ]);
            }

            $originalStatus = $credential->status;
            $restoredStatus = $credential->status;

            if ($outcome === 'reversed') {
                $restoredStatus = $this->restorableStatus($credential);
                $credential->forceFill([
                    'status' => $restoredStatus,
                    'suspended_at' => null,
                    'revoked_at' => null,
                    'appeal_status' => 'reversed',
                    'lock_version' => $credential->lock_version + 1,
                ])->save();
                $this->synchronizeProfile->handle($credential->expertProfile);
                $this->synchronizeAdoptionProvider->handleCredential($credential);
            } else {
                $credential->forceFill([
                    'appeal_status' => $outcome,
                    'lock_version' => $credential->lock_version + 1,
                ])->save();
            }

            $closed = $outcome !== 'information-requested';
            $appeal->forceFill([
                'reviewer_user_id' => $reviewer->id,
                'status' => $outcome,
                'reviewer_response' => trim($response),
                'reviewed_at' => now(),
                'closed_at' => $closed ? now() : null,
            ])->save();
            CredentialVerificationEvent::query()->create([
                'credential_id' => $credential->id,
                'actor_user_id' => $reviewer->id,
                'event_type' => 'appeal-'.$outcome,
                'from_status' => $originalStatus->value,
                'to_status' => $restoredStatus->value,
                'reason_translation_key' => $credential->public_summary_translation_key,
                'internal_reason' => trim($response),
                'idempotency_key' => $idempotencyKey,
                'metadata' => ['appeal_id' => $appeal->id],
            ]);

            return $appeal->refresh();
        }, 3);
    }

    private function authorizeReviewer(User $reviewer, Credential $credential): void
    {
        if (! $this->policy->review($reviewer, $credential)) {
            throw new AuthorizationException;
        }

        if ($credential->reviewer_user_id === $reviewer->id) {
            throw ValidationException::withMessages([
                'reviewer' => __('credential_verification.validation.original_reviewer'),
            ]);
        }
    }

    private function restorableStatus(Credential $credential): CredentialStatus
    {
        $previousStatus = CredentialVerificationEvent::query()
            ->where('credential_id', $credential->id)
            ->where('event_type', 'status-changed')
            ->where('to_status', $credential->status->value)
            ->latest('id')
            ->value('from_status');
        $status = is_string($previousStatus)
            ? CredentialStatus::tryFrom($previousStatus)
            : null;

        if (
            $status === CredentialStatus::Verified
            && $credential->expires_at?->isPast()
        ) {
            return CredentialStatus::Submitted;
        }

        return $status ?? CredentialStatus::Submitted;
    }
}
