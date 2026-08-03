<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\OrganizationAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final readonly class RemoveOrganizationMember
{
    public function __construct(
        private Gate $gate,
        private OrganizationAudit $audit,
    ) {}

    public function handle(
        User $actor,
        OrganizationMembership $membership,
        string $reasonCode,
    ): OrganizationMembership {
        $organization = $membership->organization;
        $this->gate->forUser($actor)->authorize('manageMembers', $organization);
        Validator::make(
            ['reason_code' => $reasonCode],
            ['reason_code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/']],
        )->validate();

        if ($membership->role === OrganizationRole::Owner) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $membership, $organization, $reasonCode): OrganizationMembership {
            $locked = OrganizationMembership::query()->lockForUpdate()->findOrFail($membership->id);
            $this->gate->forUser($actor)->authorize('manageMembers', $locked->organization);

            if ($locked->role === OrganizationRole::Owner) {
                throw new AuthorizationException;
            }

            if ($locked->status === OrganizationMembershipStatus::Removed) {
                return $locked;
            }

            $locked->forceFill([
                'status' => OrganizationMembershipStatus::Removed,
                'removed_by_user_id' => $actor->id,
                'removed_at' => now(),
                'removal_reason_code' => $reasonCode,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->audit->record(
                organization: $organization,
                actor: $actor,
                eventType: 'member-removed',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'organizations.audit.member_removed',
                subject: $locked->user,
                metadata: ['membership_id' => $locked->id, 'role' => $locked->role->value],
                idempotencyKey: 'organization:membership:'.$locked->id.':removed',
            );

            return $locked;
        }, 3);
    }
}
