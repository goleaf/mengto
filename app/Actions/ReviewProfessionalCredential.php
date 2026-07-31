<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CredentialStatus;
use App\Models\Credential;
use App\Models\CredentialVerificationEvent;
use App\Models\User;
use App\Policies\CredentialPolicy;
use App\Services\SynchronizeAdoptionProviderIdentity;
use App\Services\SynchronizeExpertVerificationStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

final class ReviewProfessionalCredential
{
    public function __construct(
        private readonly CredentialPolicy $policy,
        private readonly SynchronizeExpertVerificationStatus $synchronizeProfile,
        private readonly SynchronizeAdoptionProviderIdentity $synchronizeAdoptionProvider,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        User $reviewer,
        int $credentialId,
        CredentialStatus $targetStatus,
        string $reasonTranslationKey,
        string $internalReason,
        string $idempotencyKey,
        array $metadata = [],
    ): Credential {
        return DB::transaction(function () use (
            $reviewer,
            $credentialId,
            $targetStatus,
            $reasonTranslationKey,
            $internalReason,
            $idempotencyKey,
            $metadata,
        ): Credential {
            $existingEvent = CredentialVerificationEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return Credential::query()->findOrFail($existingEvent->credential_id);
            }

            $credential = Credential::query()
                ->with('expertProfile:id,owner_id,owner_key')
                ->lockForUpdate()
                ->findOrFail($credentialId);
            $this->authorizeReviewer($reviewer, $credential);
            $this->validateTransition($credential, $targetStatus);
            $this->validateReason($reasonTranslationKey, $internalReason);

            $fromStatus = $credential->effectiveStatus();
            $credential->forceFill([
                'status' => $targetStatus,
                'reviewed_by' => $reviewer->name,
                'reviewer_user_id' => $reviewer->id,
                'verified_at' => $targetStatus === CredentialStatus::Verified ? now() : $credential->verified_at,
                'suspended_at' => $targetStatus === CredentialStatus::Suspended ? now() : null,
                'revoked_at' => $targetStatus === CredentialStatus::Revoked ? now() : null,
                'public_summary_translation_key' => $reasonTranslationKey,
                'rejection_reason' => $targetStatus === CredentialStatus::Rejected
                    ? $reasonTranslationKey
                    : null,
                'appeal_status' => null,
                'lock_version' => $credential->lock_version + 1,
            ])->save();

            CredentialVerificationEvent::query()->create([
                'credential_id' => $credential->id,
                'actor_user_id' => $reviewer->id,
                'event_type' => 'status-changed',
                'from_status' => $fromStatus->value,
                'to_status' => $targetStatus->value,
                'reason_translation_key' => $reasonTranslationKey,
                'internal_reason' => $internalReason,
                'idempotency_key' => $idempotencyKey,
                'metadata' => $metadata,
            ]);
            $this->synchronizeProfile->handle($credential->expertProfile);
            $this->synchronizeAdoptionProvider->handleCredential($credential);

            return $credential->refresh();
        }, 3);
    }

    private function authorizeReviewer(User $reviewer, Credential $credential): void
    {
        if (! $this->policy->review($reviewer, $credential)) {
            throw new AuthorizationException;
        }
    }

    private function validateTransition(
        Credential $credential,
        CredentialStatus $targetStatus,
    ): void {
        $allowed = match ($credential->effectiveStatus()) {
            CredentialStatus::Submitted => [
                CredentialStatus::InReview,
                CredentialStatus::Rejected,
            ],
            CredentialStatus::InReview => [
                CredentialStatus::Verified,
                CredentialStatus::Rejected,
            ],
            CredentialStatus::Verified => [
                CredentialStatus::Expiring,
                CredentialStatus::Suspended,
                CredentialStatus::Revoked,
            ],
            CredentialStatus::Expiring => [
                CredentialStatus::Verified,
                CredentialStatus::Expired,
                CredentialStatus::Suspended,
                CredentialStatus::Revoked,
            ],
            CredentialStatus::Expired, CredentialStatus::Rejected => [
                CredentialStatus::Submitted,
            ],
            CredentialStatus::Suspended => [
                CredentialStatus::Verified,
                CredentialStatus::Revoked,
            ],
            CredentialStatus::Revoked => [],
        };

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => __('credential_verification.validation.transition'),
            ]);
        }

        if (
            $targetStatus === CredentialStatus::Verified
            && $credential->expires_at?->isPast()
        ) {
            throw ValidationException::withMessages([
                'expires_at' => __('credential_verification.validation.expiry'),
            ]);
        }
    }

    private function validateReason(string $translationKey, string $internalReason): void
    {
        if (! Lang::has($translationKey) || mb_strlen(trim($internalReason)) < 20) {
            throw ValidationException::withMessages([
                'reason' => __('credential_verification.validation.transition'),
            ]);
        }
    }
}
