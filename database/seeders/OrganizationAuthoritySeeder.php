<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ApplyOrganizationRestriction;
use App\Actions\CreateOrganization;
use App\Actions\InviteOrganizationMember;
use App\Actions\SuspendOrganization;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationInvitationData;
use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationStatus;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class OrganizationAuthoritySeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Organization demo data requires an explicitly allowed environment.');
        }

        $mia = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $lithuanian = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
        $administrator = User::query()->where('actor_key', 'demo-administrator')->firstOrFail();
        $create = app(CreateOrganization::class);

        $rescue = $create->handle($mia, new CreateOrganizationData(
            name: 'Vilnius Animal Welfare Network',
            type: OrganizationType::Rescue,
            defaultLocale: 'lt',
            idempotencyKey: 'demo-organization-rescue-create-0001',
            summary: 'A verified rescue network coordinating safe community events.',
            publicRegion: 'Vilnius',
        ));
        $rescue->forceFill([
            'stable_key' => 'demo-organization-vilnius-welfare',
            'slug' => 'vilnius-animal-welfare-network',
            'verification_status' => OrganizationVerificationStatus::Verified,
            'verification_source' => 'demo-independent-registry',
            'verified_at' => now(),
            'verification_expires_at' => now()->addYear(),
        ])->save();
        OrganizationMembership::query()->updateOrCreate(
            ['organization_id' => $rescue->id, 'user_id' => $lithuanian->id],
            [
                'invited_by_user_id' => $mia->id,
                'role' => OrganizationRole::EventManager,
                'status' => OrganizationMembershipStatus::Active,
                'joined_at' => now(),
                'removed_at' => null,
            ],
        );

        $community = $create->handle($lithuanian, new CreateOrganizationData(
            name: 'Neighbourhood Pet Community',
            type: OrganizationType::Community,
            defaultLocale: 'en',
            idempotencyKey: 'demo-organization-community-create-0001',
            summary: 'A local community organization with a pending member invitation.',
            publicRegion: 'Vilnius Old Town',
        ));
        $community->forceFill([
            'stable_key' => 'demo-organization-neighbourhood-community',
            'slug' => 'neighbourhood-pet-community',
        ])->save();
        app(InviteOrganizationMember::class)->handle(
            $lithuanian,
            $community,
            $administrator,
            new OrganizationInvitationData(
                role: OrganizationRole::Auditor,
                expiresAt: now()->addWeek()->toImmutable(),
                idempotencyKey: 'demo-organization-community-invite-0001',
            ),
        );

        app(ApplyOrganizationRestriction::class)->handle(
            $mia,
            $rescue,
            OrganizationRestrictionCapability::CreateInvitations,
            'demo-invitation-review',
            'demo-organization-rescue-restriction-0001',
        );

        $venue = $create->handle($administrator, new CreateOrganizationData(
            name: 'Demo Restricted Event Venue',
            type: OrganizationType::Venue,
            defaultLocale: 'ru',
            idempotencyKey: 'demo-organization-venue-create-0001',
            summary: 'A suspended venue retained for authorization and safety demonstrations.',
            publicRegion: 'Vilnius',
        ));
        $venue->forceFill([
            'stable_key' => 'demo-organization-restricted-venue',
            'slug' => 'demo-restricted-event-venue',
        ])->save();
        app(SuspendOrganization::class)->handle(
            $administrator,
            $venue,
            'demo-safety-review',
            'demo-organization-venue-suspension-0001',
        );
    }
}
