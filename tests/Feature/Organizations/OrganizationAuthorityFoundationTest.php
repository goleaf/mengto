<?php

declare(strict_types=1);

use App\Actions\ApplyOrganizationRestriction;
use App\Actions\CreateForumEvent;
use App\Actions\CreateOrganization;
use App\Actions\InviteOrganizationMember;
use App\Actions\InviteToForumEvent;
use App\Actions\RemoveOrganizationMember;
use App\Actions\RespondToOrganizationInvitation;
use App\Actions\SuspendOrganization;
use App\Actions\TransitionForumEventStatus;
use App\Data\CreateForumEventData;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationInvitationData;
use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\OrganizationInvitationStatus;
use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Livewire\Forum\ForumEventDirectory;
use App\Livewire\Organizations\OrganizationDirectory;
use App\Livewire\Organizations\OrganizationWorkspace;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventTeamMembership;
use App\Models\Organization;
use App\Models\OrganizationAuditEvent;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRestriction;
use App\Models\User;
use App\Services\ForumEventRegistrationService;
use Database\Seeders\OrganizationAuthoritySeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function organizationEventData(
    string $idempotencyKey,
    ?Organization $organization = null,
): CreateForumEventData {
    return new CreateForumEventData(
        title: 'Organization welfare workshop',
        summary: 'A practical event with explicit safety and animal welfare boundaries.',
        type: ForumEventType::Workshop,
        visibility: $organization === null
            ? ForumEventVisibility::Public
            : ForumEventVisibility::Organization,
        format: ForumEventFormat::Physical,
        startsAt: now()->addWeek()->toImmutable(),
        endsAt: now()->addWeek()->addHours(2)->toImmutable(),
        timezone: 'Europe/Vilnius',
        capacity: 20,
        registrationPolicy: ForumEventRegistrationPolicy::Open,
        waitlistEnabled: true,
        locationScope: 'Vilnius',
        exactLocation: 'Private organization room',
        onlineUrl: null,
        attendanceRequirements: null,
        vaccinationRequirements: null,
        vaccinationJurisdiction: null,
        minimumAnimalAgeMonths: null,
        maximumAnimalAgeMonths: null,
        accessibilityInformation: 'Step-free entrance confirmed by the organization.',
        costMinor: 0,
        currency: 'EUR',
        refundPolicy: null,
        photoConsentMode: ForumEventPhotoConsent::AskFirst,
        animalWelfareRules: 'Participants may leave immediately when an animal needs rest.',
        emergencyContactPlan: 'The safety lead coordinates urgent action and evacuation.',
        groupId: null,
        taxonIds: [],
        locale: 'en',
        idempotencyKey: $idempotencyKey,
        petParticipationMode: ForumEventPetParticipation::Optional,
        accessibilityStatus: ForumEventAccessibilityStatus::Confirmed,
        responsibleOrganizationId: $organization?->id,
    );
}

function organizationRegistrationData(string $idempotencyKey): RegisterForForumEventData
{
    return new RegisterForForumEventData(
        attendanceFormat: ForumEventFormat::Physical,
        guestCount: 0,
        petProfileId: null,
        requirementsNote: null,
        photoConsent: ForumEventPhotoConsent::Declined,
        requirementsAccepted: true,
        idempotencyKey: $idempotencyKey,
    );
}

test('organization creation is idempotent and creates authoritative owner membership and audit', function () {
    $owner = User::factory()->create();
    $data = new CreateOrganizationData(
        name: 'Vilnius Animal Welfare Network',
        type: OrganizationType::Rescue,
        defaultLocale: 'lt',
        idempotencyKey: 'organization-create-authority-0001',
    );

    $first = app(CreateOrganization::class)->handle($owner, $data);
    $second = app(CreateOrganization::class)->handle($owner, $data);

    expect($second->is($first))->toBeTrue()
        ->and($first->owner_user_id)->toBe($owner->id)
        ->and($first->status)->toBe(OrganizationStatus::Active)
        ->and($first->memberships()->count())->toBe(1)
        ->and($first->memberships()->firstOrFail()->role)->toBe(OrganizationRole::Owner)
        ->and($first->memberships()->firstOrFail()->status)
        ->toBe(OrganizationMembershipStatus::Active)
        ->and($first->auditEvents()->where('event_type', 'created')->count())->toBe(1);
});

test('organization invitations are tokenized expiring account-bound single-use and signed', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $wrongUser = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->create();
    $data = new OrganizationInvitationData(
        role: OrganizationRole::EventManager,
        expiresAt: now()->addDays(3)->toImmutable(),
        idempotencyKey: 'organization-invitation-authority-0001',
    );

    $invitation = app(InviteOrganizationMember::class)
        ->handle($owner, $organization, $invitee, $data);
    $token = $invitation->plainTextToken;

    expect($token)->toBeString()->not->toBe('')
        ->and($invitation->getRawOriginal('token_hash'))->not->toBe($token)
        ->and(URL::hasValidSignature(
            Request::create($invitation->signedResponseUrl($token)),
        ))->toBeTrue();

    expect(fn () => app(RespondToOrganizationInvitation::class)
        ->handle($wrongUser, $invitation, $token, true))
        ->toThrow(AuthorizationException::class);

    $accepted = app(RespondToOrganizationInvitation::class)
        ->handle($invitee, $invitation, $token, true);

    expect($accepted->status)->toBe(OrganizationInvitationStatus::Accepted)
        ->and($organization->memberships()->where('user_id', $invitee->id)->firstOrFail()->role)
        ->toBe(OrganizationRole::EventManager)
        ->and(fn () => app(RespondToOrganizationInvitation::class)
            ->handle($invitee, $accepted, $token, true))
        ->toThrow(ValidationException::class);
});

test('organization invitation tokens stay out of livewire public state', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $outsider = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->create();
    $invitation = app(InviteOrganizationMember::class)->handle(
        $owner,
        $organization,
        $invitee,
        new OrganizationInvitationData(
            role: OrganizationRole::Member,
            expiresAt: now()->addDay()->toImmutable(),
            idempotencyKey: 'organization-invitation-browser-state-0001',
        ),
    );
    $token = (string) $invitation->plainTextToken;
    $url = $invitation->signedResponseUrl($token);

    $this->actingAs($outsider)->get($url)->assertForbidden();
    $this->actingAs($invitee)
        ->get($url)
        ->assertOk()
        ->assertDontSee($token, false);
    $storedToken = session('organization_invitations.'.$invitation->id.'.token');

    expect($storedToken)->toBeString()->not->toBe($token)
        ->and(Crypt::decryptString($storedToken))->toBe($token);
});

test('organization tenant isolation revokes removed staff access without erasing attribution', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->create();
    $membership = OrganizationMembership::factory()
        ->for($organization)
        ->for($member)
        ->eventManager()
        ->active()
        ->create();

    expect(Gate::forUser($member)->allows('view', $organization))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('view', $organization))->toBeFalse();

    app(RemoveOrganizationMember::class)->handle(
        $owner,
        $membership,
        'staff-contract-ended',
    );

    expect($membership->refresh()->status)->toBe(OrganizationMembershipStatus::Removed)
        ->and(Gate::forUser($member)->allows('view', $organization))->toBeFalse()
        ->and(OrganizationAuditEvent::query()
            ->where('subject_user_id', $member->id)
            ->where('event_type', 'member-removed')
            ->exists())->toBeTrue();
});

test('organization restrictions are independent audited and suspension blocks selected capabilities', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->verified()->create();

    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $organization,
        OrganizationRestrictionCapability::CreateInvitations,
        'invitation-safety-review',
        'organization-restriction-invite-0001',
    );

    expect($organization->allows(OrganizationRestrictionCapability::CreateEvents))->toBeTrue()
        ->and($organization->allows(OrganizationRestrictionCapability::CreateInvitations))->toBeFalse();

    app(SuspendOrganization::class)->handle(
        $owner,
        $organization,
        'organization-safety-review',
        'organization-suspension-0001',
    );

    expect($organization->refresh()->status)->toBe(OrganizationStatus::Suspended)
        ->and($organization->allows(OrganizationRestrictionCapability::CreateEvents))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::PublishEvents))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::AcceptRegistrations))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::AcceptPayments))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::AccessParticipantData))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::RunCheckIn))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::EnterResults))->toBeFalse()
        ->and($organization->allows(OrganizationRestrictionCapability::CreateInvitations))->toBeFalse()
        ->and(OrganizationRestriction::query()->where('organization_id', $organization->id)->count())
        ->toBeGreaterThanOrEqual(8);
});

test('organization-bound events enforce membership restrictions and emergency safety access', function () {
    $owner = User::factory()->create();
    $participant = User::factory()->create();
    $outsider = User::factory()->create();
    $safetyLead = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->verified()->create();
    $event = app(CreateForumEvent::class)->handle(
        $owner,
        organizationEventData('organization-event-authority-0001', $organization),
    );
    OrganizationMembership::factory()
        ->for($organization)
        ->for($participant)
        ->active()
        ->create();
    OrganizationMembership::factory()
        ->for($organization)
        ->for($safetyLead)
        ->active()
        ->create();
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $safetyLead->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::SafetyLead,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);

    expect($event->responsible_organization_id)->toBe($organization->id)
        ->and(Gate::forUser($participant)->allows('view', $event))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('view', $event))->toBeFalse();

    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $organization,
        OrganizationRestrictionCapability::AccessParticipantData,
        'participant-data-review',
        'organization-restriction-data-0001',
    );
    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $organization,
        OrganizationRestrictionCapability::PublishEvents,
        'publication-review',
        'organization-restriction-publish-0001',
    );

    expect(Gate::forUser($owner)->allows('manageRegistrations', $event))->toBeFalse()
        ->and(Gate::forUser($safetyLead)->allows('manageRegistrations', $event))->toBeTrue()
        ->and(fn () => app(TransitionForumEventStatus::class)->handle(
            $owner,
            $event,
            ForumEventStatus::Published,
            'publication-requested',
            'organization-event-publish-0001',
        ))->toThrow(AuthorizationException::class);
});

test('organization capabilities isolate creation invitations registration check in and former staff', function () {
    $owner = User::factory()->create();
    $participant = User::factory()->create();
    $formerStaff = User::factory()->create();
    $outsider = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->verified()->create();
    $event = app(CreateForumEvent::class)->handle(
        $owner,
        organizationEventData('organization-event-capabilities-0001', $organization),
    );
    OrganizationMembership::factory()->for($organization)->for($participant)->active()->create();
    $formerMembership = OrganizationMembership::factory()
        ->for($organization)
        ->for($formerStaff)
        ->eventManager()
        ->create();
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $formerStaff->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::RegistrationManager,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);

    expect(fn () => app(InviteToForumEvent::class)->handle(
        $owner,
        $event,
        $outsider,
        now()->addWeek()->toImmutable(),
        'organization-event-outsider-invite-0001',
    ))->toThrow(ValidationException::class);
    expect(app(InviteToForumEvent::class)->handle(
        $owner,
        $event,
        $participant,
        now()->addWeek()->toImmutable(),
        'organization-event-member-invite-0001',
    )->invited_user_id)->toBe($participant->id);

    app(RemoveOrganizationMember::class)->handle(
        $owner,
        $formerMembership,
        'staff-access-ended',
    );
    expect(Gate::forUser($formerStaff)->allows('view', $event))->toBeFalse()
        ->and(Gate::forUser($formerStaff)->allows('manageRegistrations', $event))->toBeFalse();

    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $organization,
        OrganizationRestrictionCapability::AcceptRegistrations,
        'registration-safety-review',
        'organization-registration-restriction-0001',
    );
    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $participant,
        $event,
        organizationRegistrationData('organization-event-registration-0001'),
    ))->toThrow(AuthorizationException::class);

    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();
    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $organization,
        OrganizationRestrictionCapability::RunCheckIn,
        'check-in-safety-review',
        'organization-check-in-restriction-0001',
    );
    expect(fn () => app(ForumEventRegistrationService::class)->checkIn(
        $owner,
        $registration,
        'manual',
    ))->toThrow(AuthorizationException::class);

    $creationRestricted = Organization::factory()->forOwner($owner)->verified()->create();
    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $creationRestricted,
        OrganizationRestrictionCapability::CreateEvents,
        'event-creation-review',
        'organization-create-event-restriction-0001',
    );
    expect(fn () => app(CreateForumEvent::class)->handle(
        $owner,
        organizationEventData('organization-event-capabilities-0002', $creationRestricted),
    ))->toThrow(AuthorizationException::class);
});

test('organization specialist roles remain independently authorized', function () {
    $organization = Organization::factory()->create();
    $finance = User::factory()->create();
    $safety = User::factory()->create();
    $marketplace = User::factory()->create();
    $shelter = User::factory()->create();
    $auditor = User::factory()->create();

    OrganizationMembership::factory()->for($organization)->for($finance)->financeManager()->create();
    OrganizationMembership::factory()->for($organization)->for($safety)->safetyLead()->create();
    OrganizationMembership::factory()->for($organization)->for($marketplace)->marketplaceManager()->create();
    OrganizationMembership::factory()->for($organization)->for($shelter)->shelterCoordinator()->create();
    OrganizationMembership::factory()->for($organization)->for($auditor)->auditor()->create();

    expect(Gate::forUser($finance)->allows('manageFinance', $organization))->toBeTrue()
        ->and(Gate::forUser($finance)->allows('manageSafety', $organization))->toBeFalse()
        ->and(Gate::forUser($safety)->allows('manageSafety', $organization))->toBeTrue()
        ->and(Gate::forUser($safety)->allows('manageFinance', $organization))->toBeFalse()
        ->and(Gate::forUser($marketplace)->allows('manageMarketplace', $organization))->toBeTrue()
        ->and(Gate::forUser($marketplace)->allows('manageShelter', $organization))->toBeFalse()
        ->and(Gate::forUser($shelter)->allows('manageShelter', $organization))->toBeTrue()
        ->and(Gate::forUser($shelter)->allows('manageMarketplace', $organization))->toBeFalse()
        ->and(Gate::forUser($auditor)->allows('viewAudit', $organization))->toBeTrue()
        ->and(Gate::forUser($auditor)->allows('manageMembers', $organization))->toBeFalse();
});

test('organization routes and livewire mutations authorize independently in every supported locale', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->create();

    auth()->logout();
    $this->get(route('organizations.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('organizations.index'))
        ->assertRedirect(route('verification.notice'));
    $this->actingAs($owner)
        ->get(route('organizations.index'))
        ->assertOk()
        ->assertSee(__('organizations.pages.index.title'));
    $this->actingAs($outsider)
        ->get(route('organizations.show', $organization))
        ->assertForbidden();

    Livewire::actingAs($outsider)
        ->test(OrganizationWorkspace::class, ['organization' => $organization])
        ->assertForbidden();

    foreach (config('platform.supported_locales', ['en']) as $locale) {
        app()->setLocale($locale);

        expect(OrganizationType::Rescue->label())->not->toStartWith('organizations.')
            ->and(OrganizationRole::EventManager->label())->not->toStartWith('organizations.')
            ->and(OrganizationRestrictionCapability::CreateEvents->label())
            ->not->toStartWith('organizations.');
    }

    Livewire::actingAs($owner)
        ->test(OrganizationDirectory::class)
        ->assertSee(__('organizations.pages.index.title'));
});

test('event builder exposes only organizations the actor may use and creates organization events', function () {
    $owner = User::factory()->create();
    $otherOwner = User::factory()->create();
    $manageable = Organization::factory()->forOwner($owner)->create([
        'name' => 'Builder Manageable Organization',
    ]);
    $memberOnly = Organization::factory()->forOwner($otherOwner)->create([
        'name' => 'Builder Member Only Organization',
    ]);
    OrganizationMembership::factory()
        ->for($memberOnly)
        ->for($owner)
        ->active()
        ->create(['role' => OrganizationRole::Member]);
    $restricted = Organization::factory()->forOwner($owner)->create([
        'name' => 'Builder Restricted Organization',
    ]);
    app(ApplyOrganizationRestriction::class)->handle(
        $owner,
        $restricted,
        OrganizationRestrictionCapability::CreateEvents,
        'event-creation-review',
        'organization-builder-restriction-0001',
    );

    Livewire::actingAs($owner)
        ->test(ForumEventDirectory::class, ['createOnly' => true])
        ->assertSee(ForumEventVisibility::Organization->label())
        ->set('form.visibility', ForumEventVisibility::Organization->value)
        ->assertSee('Builder Manageable Organization')
        ->assertDontSee('Builder Member Only Organization')
        ->assertDontSee('Builder Restricted Organization')
        ->set('form.title', 'Organization builder workshop')
        ->set('form.summary', 'A complete organization event created through the canonical Livewire form.')
        ->set('form.responsibleOrganizationId', $manageable->id)
        ->set('form.locationScope', 'Vilnius')
        ->call('create')
        ->assertHasNoErrors();

    expect(ForumEvent::query()
        ->where('responsible_organization_id', $manageable->id)
        ->where('visibility', ForumEventVisibility::Organization->value)
        ->where('title', 'Organization builder workshop')
        ->exists())->toBeTrue();
});

test('organization directory query count stays bounded as organization volume grows', function () {
    $owner = User::factory()->create();
    Organization::factory()->forOwner($owner)->create();

    $renderQueryCount = function () use ($owner): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($owner)
            ->test(OrganizationDirectory::class)
            ->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queryCount;
    };
    $singleOrganizationQueries = $renderQueryCount();

    Organization::factory()->count(11)->forOwner($owner)->create();
    $twelveOrganizationQueries = $renderQueryCount();

    expect($singleOrganizationQueries)->toBeLessThanOrEqual(12)
        ->and($twelveOrganizationQueries)->toBeLessThanOrEqual($singleOrganizationQueries + 1);
});

test('organization workspace minimizes private member and restriction data', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->forOwner($owner)->create();
    OrganizationMembership::factory()
        ->for($organization)
        ->for($member)
        ->active()
        ->create(['role' => OrganizationRole::Member]);
    OrganizationRestriction::factory()
        ->for($organization)
        ->active()
        ->create(['reason_code' => 'internal-safety-review']);

    Livewire::actingAs($member)
        ->test(OrganizationWorkspace::class, ['organization' => $organization])
        ->assertSee($member->name)
        ->assertDontSee($member->email)
        ->assertDontSee('internal-safety-review');

    Livewire::actingAs($owner)
        ->test(OrganizationWorkspace::class, ['organization' => $organization])
        ->assertSee($member->email)
        ->assertSee('internal-safety-review');
});

test('organization models provide explicit factories and relationship helpers', function () {
    $organization = Organization::factory()->verified()->create();
    $membership = OrganizationMembership::factory()->for($organization)->active()->create();
    $invitation = OrganizationInvitation::factory()->for($organization)->pending()->create();
    $restriction = OrganizationRestriction::factory()->for($organization)->active()->create();
    $audit = OrganizationAuditEvent::factory()->for($organization)->create();
    $event = ForumEvent::factory()->forOrganization($organization)->create();

    expect($organization->exists)->toBeTrue()
        ->and($membership->organization->is($organization))->toBeTrue()
        ->and($invitation->organization->is($organization))->toBeTrue()
        ->and($restriction->organization->is($organization))->toBeTrue()
        ->and($audit->organization->is($organization))->toBeTrue()
        ->and($event->responsibleOrganization->is($organization))->toBeTrue();
});

test('organization demo seeding is production guarded coherent and idempotent', function () {
    User::factory()->lithuanian()->create(['actor_key' => 'demo-lithuanian']);
    User::factory()->administrator()->create(['actor_key' => 'demo-administrator']);
    config(['platform.demo_seed_environments' => ['testing']]);

    $seeder = app(OrganizationAuthoritySeeder::class);
    $seeder->run();
    $seeder->run();

    expect(Organization::query()->where('stable_key', 'like', 'demo-organization-%')->count())
        ->toBe(3)
        ->and(OrganizationMembership::query()->count())->toBe(4)
        ->and(OrganizationInvitation::query()->count())->toBe(1)
        ->and(OrganizationRestriction::query()->count())->toBe(9)
        ->and(OrganizationAuditEvent::query()->count())->toBe(6);

    config(['platform.demo_seed_environments' => []]);
    expect(fn () => $seeder->run())->toThrow(LogicException::class);
});
