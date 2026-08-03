<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationRestrictionCapability;
use App\Models\Organization;
use App\Models\OrganizationRestriction;
use App\Models\User;
use App\Services\OrganizationAudit;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ApplyOrganizationRestriction
{
    public function __construct(
        private Gate $gate,
        private OrganizationAudit $audit,
    ) {}

    public function handle(
        User $actor,
        Organization $organization,
        OrganizationRestrictionCapability $capability,
        string $reasonCode,
        string $idempotencyKey,
        ?CarbonImmutable $endsAt = null,
    ): OrganizationRestriction {
        $this->gate->forUser($actor)->authorize('manageRestrictions', $organization);
        Validator::make([
            'capability' => $capability->value,
            'reason_code' => $reasonCode,
            'idempotency_key' => $idempotencyKey,
            'ends_at' => $endsAt?->toAtomString(),
        ], [
            'capability' => ['required', 'string'],
            'reason_code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
            'ends_at' => ['nullable', 'date', 'after:now'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $capability,
            $endsAt,
            $idempotencyKey,
            $organization,
            $reasonCode,
        ): OrganizationRestriction {
            $locked = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $this->gate->forUser($actor)->authorize('manageRestrictions', $locked);
            $existing = OrganizationRestriction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->organization_id !== $locked->id
                    || $existing->capability !== $capability
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('organizations.validation.idempotency_conflict'),
                    ]);
                }

                return $existing;
            }

            $restriction = OrganizationRestriction::query()->create([
                'organization_id' => $locked->id,
                'applied_by_user_id' => $actor->id,
                'capability' => $capability,
                'reason_code' => $reasonCode,
                'idempotency_key' => $idempotencyKey,
                'starts_at' => now(),
                'ends_at' => $endsAt,
            ]);
            $this->audit->record(
                organization: $locked,
                actor: $actor,
                eventType: 'restriction-applied',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'organizations.audit.restriction_applied',
                metadata: [
                    'capability' => $capability->value,
                    'restriction_id' => $restriction->id,
                ],
                idempotencyKey: 'organization:restriction:'.$idempotencyKey,
            );

            return $restriction;
        }, 3);
    }
}
