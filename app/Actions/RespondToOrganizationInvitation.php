<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationInvitationStatus;
use App\Enums\OrganizationMembershipStatus;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\OrganizationAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RespondToOrganizationInvitation
{
    public function __construct(private OrganizationAudit $audit) {}

    public function handle(
        User $actor,
        OrganizationInvitation $invitation,
        string $token,
        bool $accept,
    ): OrganizationInvitation {
        if (! $actor->isActive()
            || $invitation->invited_user_id !== $actor->id
            || ! $invitation->tokenMatches($token)
        ) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($accept, $actor, $invitation, $token): OrganizationInvitation {
            $locked = OrganizationInvitation::query()->lockForUpdate()->findOrFail($invitation->id);

            if ($locked->invited_user_id !== $actor->id || ! $locked->tokenMatches($token)) {
                throw new AuthorizationException;
            }

            if (! $locked->isCurrent()) {
                throw ValidationException::withMessages([
                    'invitation' => __('organizations.validation.invitation_unavailable'),
                ]);
            }

            $status = $accept
                ? OrganizationInvitationStatus::Accepted
                : OrganizationInvitationStatus::Declined;

            if ($accept) {
                OrganizationMembership::query()->updateOrCreate(
                    [
                        'organization_id' => $locked->organization_id,
                        'user_id' => $actor->id,
                    ],
                    [
                        'invited_by_user_id' => $locked->invited_by_user_id,
                        'role' => $locked->role,
                        'status' => OrganizationMembershipStatus::Active,
                        'joined_at' => now(),
                        'expires_at' => null,
                        'removed_by_user_id' => null,
                        'removed_at' => null,
                        'removal_reason_code' => null,
                    ],
                );
            }

            $locked->forceFill(['status' => $status, 'responded_at' => now()])->save();
            $this->audit->record(
                organization: $locked->organization,
                actor: $actor,
                eventType: $accept ? 'invitation-accepted' : 'invitation-declined',
                reasonCode: $status->value,
                summaryTranslationKey: $accept
                    ? 'organizations.audit.invitation_accepted'
                    : 'organizations.audit.invitation_declined',
                subject: $actor,
                metadata: ['invitation_id' => $locked->id],
                idempotencyKey: 'organization:invitation:'.$locked->id.':'.$status->value,
            );

            return $locked->refresh();
        }, 3);
    }
}
