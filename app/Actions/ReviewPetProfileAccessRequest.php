<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetProfileAccessRequestDecision;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfileAccessRequestType;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReviewPetProfileAccessRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly ValidationFactory $validator,
        private readonly PetProfileAccess $access,
        private readonly InvitePetProfileManager $inviteManager,
        private readonly PetProfileEventRecorder $events,
    ) {}

    public function handle(
        PetProfileAccessRequest $request,
        PetProfileAccessRequestDecision $decision,
        string $resolutionNote,
        string $idempotencyKey,
    ): PetProfileAccessRequest {
        $reviewer = $this->actor->requireUser();
        $profile = $request->profile()->firstOrFail();
        $this->gate->authorize('manageManagers', $profile);
        $validated = $this->validator->make([
            'resolution_note' => $resolutionNote,
            'idempotency_key' => $idempotencyKey,
        ], [
            'resolution_note' => $decision === PetProfileAccessRequestDecision::Reject
                ? ['required', 'string', 'min:10', 'max:1000']
                : ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'max:190'],
        ])->validate();
        $decisionKey = hash(
            'sha256',
            "pet-access-decision|{$reviewer->id}|{$idempotencyKey}",
        );

        return DB::transaction(function () use (
            $decision,
            $decisionKey,
            $profile,
            $request,
            $reviewer,
            $validated,
        ): PetProfileAccessRequest {
            $lockedProfile = PetProfile::query()
                ->select(['id', 'user_id', 'status', 'lock_version'])
                ->lockForUpdate()
                ->findOrFail($profile->id);
            $this->gate->authorize('manageManagers', $lockedProfile);
            $locked = PetProfileAccessRequest::query()
                ->with('requester:id,actor_key,name,status')
                ->lockForUpdate()
                ->findOrFail($request->id);

            if ($locked->decision_key === $decisionKey
                && $locked->status !== PetProfileAccessRequestStatus::Pending
            ) {
                return $locked;
            }

            if ($locked->status !== PetProfileAccessRequestStatus::Pending) {
                throw ValidationException::withMessages([
                    'access_request' => __('pet_profiles.validation.access_request_resolved'),
                ]);
            }

            if ($locked->requester_user_id === $reviewer->id) {
                throw ValidationException::withMessages([
                    'access_request' => __('pet_profiles.validation.access_request_self_review'),
                ]);
            }

            if ($decision === PetProfileAccessRequestDecision::Approve
                && $locked->request_type->requiresProtectedApproval()
            ) {
                throw ValidationException::withMessages([
                    'access_request' => __('pet_profiles.validation.access_request_protected'),
                ]);
            }

            $manager = $decision === PetProfileAccessRequestDecision::Approve
                ? $this->grantAccess($locked, $lockedProfile)
                : null;
            $status = $decision === PetProfileAccessRequestDecision::Approve
                ? PetProfileAccessRequestStatus::Approved
                : PetProfileAccessRequestStatus::Rejected;
            $locked->forceFill([
                'status' => $status,
                'active_key' => null,
                'decision_key' => $decisionKey,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'granted_manager_id' => $manager?->id,
                'resolution_note' => trim((string) ($validated['resolution_note'] ?? '')) ?: null,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $reviewerMembership = $this->access->membership($lockedProfile, $reviewer);
            $this->events->record(
                profile: $lockedProfile,
                actor: $reviewer,
                eventType: 'access-request-'.$status->value,
                reasonCode: 'access-request-'.$status->value,
                publicMetadata: [
                    'request_type' => $locked->request_type->value,
                    'requested_role' => $locked->requested_role->value,
                ],
                privateMetadata: ['access_request_key' => $locked->request_key],
                idempotencyKey: "pet-access-review:{$decisionKey}",
                manager: $reviewerMembership,
            );

            return $locked->refresh();
        }, 3);
    }

    private function grantAccess(
        PetProfileAccessRequest $request,
        PetProfile $profile,
    ): PetProfileManager {
        $requester = $request->requester;

        if (! $requester instanceof User || ! $requester->isActive()) {
            throw ValidationException::withMessages([
                'access_request' => __('pet_profiles.validation.access_request_requester_unavailable'),
            ]);
        }

        $existing = PetProfileManager::query()
            ->where('pet_profile_id', $profile->id)
            ->where('user_id', $requester->id)
            ->lockForUpdate()
            ->first();

        if ($request->request_type === PetProfileAccessRequestType::RelationshipCorrection) {
            if (! $existing instanceof PetProfileManager || ! $existing->isActiveAt(now())) {
                throw ValidationException::withMessages([
                    'access_request' => __('pet_profiles.validation.access_relationship_inactive'),
                ]);
            }

            $existing->forceFill([
                'role' => $request->requested_role,
                'evidence_status' => PetEvidenceStatus::Pending,
                'lock_version' => $existing->lock_version + 1,
            ])->save();

            return $existing->refresh();
        }

        $manager = $this->inviteManager->handle(
            $profile,
            $requester,
            $request->requested_role,
            $request->temporary_access_ends_at,
            [],
            "pet-access-request:{$request->request_key}:invite",
        );
        $manager->forceFill(['evidence_status' => PetEvidenceStatus::Pending])->save();

        return $manager->refresh();
    }
}
