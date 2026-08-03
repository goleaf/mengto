<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationRestriction;
use App\Models\User;
use App\Services\OrganizationAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final readonly class SuspendOrganization
{
    public function __construct(
        private Gate $gate,
        private OrganizationAudit $audit,
    ) {}

    public function handle(
        User $actor,
        Organization $organization,
        string $reasonCode,
        string $idempotencyKey,
    ): Organization {
        $this->gate->forUser($actor)->authorize('manageRestrictions', $organization);
        Validator::make([
            'reason_code' => $reasonCode,
            'idempotency_key' => $idempotencyKey,
        ], [
            'reason_code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $idempotencyKey,
            $organization,
            $reasonCode,
        ): Organization {
            $locked = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $this->gate->forUser($actor)->authorize('manageRestrictions', $locked);

            if ($locked->status !== OrganizationStatus::Suspended) {
                $locked->forceFill([
                    'status' => OrganizationStatus::Suspended,
                    'suspended_by_user_id' => $actor->id,
                    'suspended_at' => now(),
                    'suspension_reason_code' => $reasonCode,
                    'lock_version' => $locked->lock_version + 1,
                ])->save();
            }

            foreach (OrganizationRestrictionCapability::operationalEventCapabilities() as $capability) {
                OrganizationRestriction::query()->firstOrCreate(
                    ['idempotency_key' => $idempotencyKey.':'.$capability->value],
                    [
                        'organization_id' => $locked->id,
                        'applied_by_user_id' => $actor->id,
                        'capability' => $capability,
                        'reason_code' => $reasonCode,
                        'starts_at' => now(),
                    ],
                );
            }

            $this->audit->record(
                organization: $locked,
                actor: $actor,
                eventType: 'suspended',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'organizations.audit.suspended',
                metadata: [
                    'capabilities' => array_map(
                        static fn (OrganizationRestrictionCapability $capability): string => $capability->value,
                        OrganizationRestrictionCapability::operationalEventCapabilities(),
                    ),
                ],
                idempotencyKey: 'organization:suspension:'.$idempotencyKey,
            );

            return $locked->refresh();
        }, 3);
    }
}
