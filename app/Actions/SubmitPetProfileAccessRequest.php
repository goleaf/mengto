<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfileAccessRequestType;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\ForumActor;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class SubmitPetProfileAccessRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly ValidationFactory $validator,
        private readonly PetProfileEventRecorder $events,
    ) {}

    public function handle(
        PetProfile $profile,
        PetProfileAccessRequestType $type,
        ?PetManagerRole $requestedRole,
        string $evidenceSummary,
        ?string $temporaryAccessEndsAt,
        string $idempotencyKey,
    ): PetProfileAccessRequest {
        $requester = $this->actor->requireUser();
        $this->gate->authorize('requestAccess', $profile);
        $role = $this->resolveRole($type, $requestedRole);
        $evidenceSummary = trim($evidenceSummary);
        $validated = $this->validator->make([
            'evidence_summary' => $evidenceSummary,
            'temporary_access_ends_at' => $temporaryAccessEndsAt,
            'idempotency_key' => $idempotencyKey,
        ], [
            'evidence_summary' => ['required', 'string', 'min:20', 'max:2000'],
            'temporary_access_ends_at' => [
                Rule::requiredIf($type === PetProfileAccessRequestType::TemporaryAccess),
                'nullable',
                'date',
                'after:now',
                'before_or_equal:'.now()->addYear()->toDateTimeString(),
            ],
            'idempotency_key' => ['required', 'string', 'max:190'],
        ])->validate();
        $submissionKey = hash(
            'sha256',
            "pet-access-submit|{$requester->id}|{$idempotencyKey}",
        );
        $existing = PetProfileAccessRequest::query()
            ->where('submission_key', $submissionKey)
            ->first();

        if ($existing instanceof PetProfileAccessRequest) {
            if ($existing->pet_profile_id !== $profile->id
                || $existing->requester_user_id !== $requester->id
            ) {
                throw ValidationException::withMessages([
                    'idempotency_key' => __('pet_profiles.validation.access_request_idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $activeKey = hash('sha256', "pet-access-active|{$profile->id}|{$requester->id}");
        $temporaryEndsAt = filled($validated['temporary_access_ends_at'] ?? null)
            ? Carbon::parse(
                (string) $validated['temporary_access_ends_at'],
                $requester->timezone,
            )->utc()
            : null;

        try {
            return DB::transaction(function () use (
                $activeKey,
                $profile,
                $requester,
                $role,
                $submissionKey,
                $temporaryEndsAt,
                $type,
                $validated,
            ): PetProfileAccessRequest {
                $requester = User::query()
                    ->lockForUpdate()
                    ->findOrFail($requester->getKey());
                UserOnboarding::query()
                    ->whereBelongsTo($requester)
                    ->lockForUpdate()
                    ->first();
                $lockedProfile = PetProfile::query()
                    ->select([
                        'id',
                        'user_id',
                        'profile_key',
                        'status',
                        'visibility',
                        'is_discoverable',
                        'lock_version',
                    ])
                    ->whereKey($profile->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $membership = $lockedProfile->managers()
                    ->select([
                        'id',
                        'pet_profile_id',
                        'user_id',
                        'role',
                        'status',
                        'permission_overrides',
                        'starts_at',
                        'ends_at',
                        'revoked_at',
                    ])
                    ->where('user_id', $requester->id)
                    ->lockForUpdate()
                    ->first();
                $lockedProfile->setRelation(
                    'managers',
                    new Collection($membership instanceof PetProfileManager ? [$membership] : []),
                );
                $this->gate->forUser($requester)->authorize('requestAccess', $lockedProfile);

                $hasActiveMembership = $membership instanceof PetProfileManager
                    && $membership->isActiveAt(now());

                if ($type === PetProfileAccessRequestType::RelationshipCorrection
                    && ! $hasActiveMembership
                ) {
                    throw ValidationException::withMessages([
                        'request_type' => __('pet_profiles.validation.access_relationship_inactive'),
                    ]);
                }

                if ($hasActiveMembership
                    && $type !== PetProfileAccessRequestType::RelationshipCorrection
                ) {
                    throw ValidationException::withMessages([
                        'request_type' => __('pet_profiles.validation.access_already_active'),
                    ]);
                }

                $pending = PetProfileAccessRequest::query()
                    ->where('active_key', $activeKey)
                    ->where('status', PetProfileAccessRequestStatus::Pending)
                    ->lockForUpdate()
                    ->first();

                if (
                    $pending instanceof PetProfileAccessRequest
                    && $pending->temporary_access_ends_at !== null
                    && ! $pending->temporary_access_ends_at->isFuture()
                ) {
                    $pending->forceFill([
                        'status' => PetProfileAccessRequestStatus::Expired,
                        'active_key' => null,
                        'lock_version' => $pending->lock_version + 1,
                    ])->saveOrFail();
                    $pending = null;
                }

                if ($pending instanceof PetProfileAccessRequest) {
                    throw ValidationException::withMessages([
                        'request_type' => __('pet_profiles.validation.access_request_pending'),
                    ]);
                }

                $request = PetProfileAccessRequest::query()->create([
                    'request_key' => 'pet-access-'.Str::lower((string) Str::ulid()),
                    'pet_profile_id' => $profile->id,
                    'requester_user_id' => $requester->id,
                    'requester_actor_key_snapshot' => $requester->actor_key,
                    'request_type' => $type,
                    'requested_role' => $role,
                    'status' => PetProfileAccessRequestStatus::Pending,
                    'evidence_summary' => (string) $validated['evidence_summary'],
                    'temporary_access_ends_at' => $temporaryEndsAt,
                    'active_key' => $activeKey,
                    'submission_key' => $submissionKey,
                    'lock_version' => 1,
                ]);
                $this->events->record(
                    profile: $lockedProfile,
                    actor: $requester,
                    eventType: 'access-request-submitted',
                    reasonCode: 'access-request-submitted',
                    publicMetadata: [
                        'request_type' => $type->value,
                        'requested_role' => $role->value,
                    ],
                    privateMetadata: ['access_request_key' => $request->request_key],
                    idempotencyKey: "pet-access-submit:{$submissionKey}",
                    manager: $membership,
                );

                return $request;
            }, 3);
        } catch (QueryException $exception) {
            $replayed = PetProfileAccessRequest::query()
                ->where('submission_key', $submissionKey)
                ->where('pet_profile_id', $profile->id)
                ->where('requester_user_id', $requester->id)
                ->first();

            if ($replayed instanceof PetProfileAccessRequest) {
                return $replayed;
            }

            throw $exception;
        }
    }

    private function resolveRole(
        PetProfileAccessRequestType $type,
        ?PetManagerRole $requestedRole,
    ): PetManagerRole {
        if ($type !== PetProfileAccessRequestType::RelationshipCorrection) {
            return $type->defaultRole();
        }

        $allowed = [
            PetManagerRole::CoOwner,
            PetManagerRole::FamilyMember,
            PetManagerRole::FosterCarer,
            PetManagerRole::Sitter,
            PetManagerRole::Caregiver,
            PetManagerRole::ProfileAdministrator,
            PetManagerRole::Specialist,
            PetManagerRole::Finder,
            PetManagerRole::Volunteer,
            PetManagerRole::PreviousOwner,
            PetManagerRole::Other,
        ];

        if ($requestedRole === null || ! in_array($requestedRole, $allowed, true)) {
            throw ValidationException::withMessages([
                'requested_role' => __('pet_profiles.validation.access_request_role'),
            ]);
        }

        return $requestedRole;
    }
}
