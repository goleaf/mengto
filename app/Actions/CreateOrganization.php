<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateOrganizationData;
use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\OrganizationAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final readonly class CreateOrganization
{
    public function __construct(
        private Gate $gate,
        private OrganizationAudit $audit,
    ) {}

    public function handle(User $owner, CreateOrganizationData $data): Organization
    {
        $this->gate->forUser($owner)->authorize('create', Organization::class);
        Validator::make([
            'name' => $data->name,
            'summary' => $data->summary,
            'type' => $data->type->value,
            'default_locale' => $data->defaultLocale,
            'public_region' => $data->publicRegion,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'type' => ['required', Rule::enum($data->type::class)],
            'default_locale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'public_region' => ['nullable', 'string', 'max:160'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use ($data, $owner): Organization {
            $existing = Organization::query()
                ->where('creation_idempotency_key', $data->idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->owner_user_id !== $owner->id) {
                    throw new AuthorizationException;
                }

                return $existing;
            }

            $stableSuffix = Str::lower((string) Str::ulid());
            $organization = Organization::query()->create([
                'owner_user_id' => $owner->id,
                'stable_key' => 'organization-'.$stableSuffix,
                'slug' => Str::slug($data->name).'-'.Str::substr($stableSuffix, -8),
                'creation_idempotency_key' => $data->idempotencyKey,
                'name' => trim($data->name),
                'summary' => filled($data->summary) ? trim((string) $data->summary) : null,
                'type' => $data->type,
                'status' => OrganizationStatus::Active,
                'default_locale' => $data->defaultLocale,
                'public_region' => filled($data->publicRegion)
                    ? trim((string) $data->publicRegion)
                    : null,
            ]);
            OrganizationMembership::query()->create([
                'organization_id' => $organization->id,
                'user_id' => $owner->id,
                'role' => OrganizationRole::Owner,
                'status' => OrganizationMembershipStatus::Active,
                'joined_at' => now(),
            ]);
            $this->audit->record(
                organization: $organization,
                actor: $owner,
                eventType: 'created',
                reasonCode: 'organization-created',
                summaryTranslationKey: 'organizations.audit.created',
                subject: $owner,
                idempotencyKey: 'organization:create:'.$data->idempotencyKey,
            );

            return $organization->load('memberships');
        }, 3);
    }
}
