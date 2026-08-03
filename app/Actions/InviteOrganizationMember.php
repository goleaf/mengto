<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\OrganizationInvitationData;
use App\Enums\OrganizationInvitationStatus;
use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Services\OrganizationAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class InviteOrganizationMember
{
    public function __construct(
        private Gate $gate,
        private OrganizationAudit $audit,
    ) {}

    public function handle(
        User $actor,
        Organization $organization,
        User $recipient,
        OrganizationInvitationData $data,
    ): OrganizationInvitation {
        $this->gate->forUser($actor)->authorize('manageMembers', $organization);
        Validator::make([
            'role' => $data->role->value,
            'expires_at' => $data->expiresAt->toAtomString(),
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'role' => [
                'required',
                Rule::in(array_map(
                    static fn (OrganizationRole $role): string => $role->value,
                    OrganizationRole::assignableCases(),
                )),
            ],
            'expires_at' => ['required', 'date', 'after:now', 'before_or_equal:+30 days'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if (! $organization->allows(OrganizationRestrictionCapability::CreateInvitations)
            || ! $recipient->isActive()
            || ! $recipient->hasVerifiedEmail()
            || $recipient->id === $actor->id
        ) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $data, $organization, $recipient): OrganizationInvitation {
            $lockedOrganization = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $this->gate->forUser($actor)->authorize('manageMembers', $lockedOrganization);

            if (! $lockedOrganization->allows(OrganizationRestrictionCapability::CreateInvitations)) {
                throw new AuthorizationException;
            }

            if ($lockedOrganization->memberships()
                ->active()
                ->where('user_id', $recipient->id)
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invitee' => __('organizations.validation.already_member'),
                ]);
            }

            $existing = OrganizationInvitation::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->organization_id !== $organization->id
                    || $existing->invited_user_id !== $recipient->id
                ) {
                    throw new AuthorizationException;
                }

                return $existing;
            }

            $pending = OrganizationInvitation::query()
                ->where('organization_id', $organization->id)
                ->where('invited_user_id', $recipient->id)
                ->where('status', OrganizationInvitationStatus::Pending->value)
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if ($pending !== null) {
                throw ValidationException::withMessages([
                    'invitee' => __('organizations.validation.invitation_pending'),
                ]);
            }

            $plainTextToken = Str::random(64);
            $invitation = OrganizationInvitation::query()->create([
                'organization_id' => $organization->id,
                'invited_user_id' => $recipient->id,
                'invited_by_user_id' => $actor->id,
                'stable_key' => 'organization-invitation-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $data->idempotencyKey,
                'token_hash' => hash('sha256', $plainTextToken),
                'role' => $data->role,
                'status' => OrganizationInvitationStatus::Pending,
                'expires_at' => $data->expiresAt,
            ]);
            $invitation->plainTextToken = $plainTextToken;
            $this->audit->record(
                organization: $organization,
                actor: $actor,
                eventType: 'member-invited',
                reasonCode: 'member-invited',
                summaryTranslationKey: 'organizations.audit.member_invited',
                subject: $recipient,
                metadata: ['role' => $data->role->value, 'invitation_id' => $invitation->id],
                idempotencyKey: 'organization:invite:'.$data->idempotencyKey,
            );

            return $invitation;
        }, 3);
    }
}
