<?php

declare(strict_types=1);

use App\Actions\AcceptPetProfileManagerInvitation;
use App\Actions\CreatePetProfile;
use App\Actions\InvitePetProfileManager;
use App\Actions\RecordPetProfileFact;
use App\Actions\RevokePetProfileManager;
use App\Actions\TransitionPetProfileStatus;
use App\Actions\UpdatePetProfilePrivacy;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Livewire\Pets\CreatePetProfile as CreatePetProfileComponent;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use App\Models\User;
use App\Services\PetProfileAccess;
use App\Services\PetProfileFoundationBackfill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates one private canonical draft for repeated minimal creation requests', function (): void {
    $owner = User::factory()->create();
    $this->actingAs($owner);
    $action = app(CreatePetProfile::class);
    $payload = [
        'title' => 'Baks',
        'category' => 'dog',
        'detail' => 'Mixed breed',
        'body' => 'Quiet walks and patient introductions.',
        'relationship_role' => PetManagerRole::PrimaryOwner->value,
        'visibility' => 'private',
        'birth_date_precision' => 'estimated',
        'sex' => 'male',
        'reproductive_status' => 'unknown',
        'idempotency_key' => 'pet-create-request-001',
    ];

    $first = $action->handle($payload);
    $second = $action->handle($payload);

    expect($second->is($first))->toBeTrue()
        ->and(PetProfile::query()->count())->toBe(1)
        ->and($first->status)->toBe(PetProfileStatus::Draft)
        ->and($first->visibility)->toBe('private')
        ->and($first->is_discoverable)->toBeFalse()
        ->and($first->allow_external_indexing)->toBeFalse()
        ->and($first->profile_key)->toStartWith('created-pet-')
        ->and($first->managers()->count())->toBe(1)
        ->and($first->privacySetting()->count())->toBe(1)
        ->and($first->slugAliases()->count())->toBe(1)
        ->and($first->lifecycleEvents()->where('event_type', 'profile-created')->count())->toBe(1);
});

it('backfills legacy ownership privacy aliases and events idempotently', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'private',
        'is_discoverable' => true,
        'profile_data' => [
            'privacy' => [
                'posts' => 'friends',
                'location' => 'private',
                'unsupported' => 'public',
            ],
        ],
    ]);
    PetProfile::query()->whereKey($profile->id)->update(['status' => 'inactive']);
    $backfill = app(PetProfileFoundationBackfill::class);

    expect($profile->refresh()->status)->toBe(PetProfileStatus::Archived);

    $first = $backfill->run(1);
    $second = $backfill->run(1);

    expect($first)->toMatchArray([
        'processed' => 1,
        'managers' => 1,
        'privacy' => 1,
        'aliases' => 1,
        'profiles_normalized' => 1,
    ])->and($second)->toMatchArray([
        'processed' => 1,
        'managers' => 0,
        'privacy' => 0,
        'aliases' => 0,
        'profiles_normalized' => 0,
    ])->and(PetProfileManager::query()->where('pet_profile_id', $profile->id)->count())->toBe(1)
        ->and(PetProfilePrivacySetting::query()->where('pet_profile_id', $profile->id)->count())->toBe(1)
        ->and(PetProfileSlugAlias::query()->where('pet_profile_id', $profile->id)->count())->toBe(1)
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'foundation-backfilled')
            ->count())->toBe(1)
        ->and($profile->refresh()->getRawOriginal('status'))->toBe(PetProfileStatus::Archived->value)
        ->and($profile->fresh()->is_discoverable)->toBeFalse()
        ->and($profile->privacySetting()->firstOrFail()->section_rules)->toBe([
            'posts' => 'friends',
            'location' => 'private',
        ]);
});

it('honors active manager permissions and expires time-bound access', function (): void {
    $owner = User::factory()->create();
    $coOwner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    $membership = PetProfileManager::factory()->for($profile, 'profile')->for($coOwner)->create([
        'role' => PetManagerRole::CoOwner,
        'ends_at' => now()->addHour(),
    ]);
    $access = app(PetProfileAccess::class);

    expect($access->allows(
        $profile,
        $coOwner,
        PetProfilePermission::EditBasics,
    ))->toBeTrue();

    $membership->forceFill(['ends_at' => now()->subSecond()])->save();
    $profile->unsetRelation('managers');

    expect($access->allows(
        $profile,
        $coOwner,
        PetProfilePermission::EditBasics,
    ))->toBeFalse();
});

it('requires the invited account and keeps manager acceptance idempotent', function (): void {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $this->actingAs($owner);
    $invitation = app(InvitePetProfileManager::class)->handle(
        $profile,
        $invitee,
        PetManagerRole::Sitter,
        now()->addDay(),
        [],
        'manager-invite-001',
    );

    $this->actingAs($outsider);
    expect(fn () => app(AcceptPetProfileManagerInvitation::class)->handle(
        $invitation,
        'manager-accept-001',
    ))->toThrow(ValidationException::class);

    $this->actingAs($invitee);
    $accepted = app(AcceptPetProfileManagerInvitation::class)->handle(
        $invitation,
        'manager-accept-001',
    );
    $replayed = app(AcceptPetProfileManagerInvitation::class)->handle(
        $invitation,
        'manager-accept-001',
    );

    expect($accepted->status)->toBe(PetManagerStatus::Active)
        ->and($replayed->id)->toBe($accepted->id)
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'manager-accepted')
            ->count())->toBe(1);
});

it('records one transition and rejects stale lifecycle writes', function (): void {
    $owner = User::factory()->create();
    $this->actingAs($owner);
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => PetProfileStatus::Draft,
        'visibility' => 'private',
        'is_discoverable' => false,
    ]);
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $action = app(TransitionPetProfileStatus::class);

    $active = $action->handle(
        $profile,
        PetProfileStatus::Active,
        'owner-status-change',
        1,
        'status-transition-001',
    );
    $replayed = $action->handle(
        $profile,
        PetProfileStatus::Active,
        'owner-status-change',
        1,
        'status-transition-001',
    );

    expect($active->status)->toBe(PetProfileStatus::Active)
        ->and($active->lock_version)->toBe(2)
        ->and($replayed->id)->toBe($active->id)
        ->and($profile->lifecycleEvents()->where('event_type', 'status-changed')->count())->toBe(1);

    expect(fn () => $action->handle(
        $profile,
        PetProfileStatus::Hidden,
        'owner-status-change',
        1,
        'status-transition-002',
    ))->toThrow(ValidationException::class);
});

it('versions sourced identity facts and keeps prior values auditable', function (): void {
    $owner = User::factory()->create();
    $this->actingAs($owner);
    $profile = PetProfile::factory()->for($owner)->create([
        'lock_version' => 1,
    ]);
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $action = app(RecordPetProfileFact::class);
    $first = $action->handle(
        profile: $profile,
        factKey: 'birth-date',
        value: ['date' => '2020-05-01'],
        precision: 'estimated',
        sourceType: 'shelter',
        sourceReference: 'private-shelter-record-1',
        verificationStatus: PetEvidenceStatus::Unverified,
        visibility: PetProfileVisibility::Private,
        expectedLockVersion: 1,
        idempotencyKey: 'pet-fact-001',
    );
    $replayed = $action->handle(
        profile: $profile,
        factKey: 'birth-date',
        value: ['date' => '2020-05-01'],
        precision: 'estimated',
        sourceType: 'shelter',
        sourceReference: 'private-shelter-record-1',
        verificationStatus: PetEvidenceStatus::Unverified,
        visibility: PetProfileVisibility::Private,
        expectedLockVersion: 1,
        idempotencyKey: 'pet-fact-001',
    );
    $profile->refresh();
    $second = $action->handle(
        profile: $profile,
        factKey: 'birth-date',
        value: ['date' => '2020-06-01'],
        precision: 'exact',
        sourceType: 'document',
        sourceReference: 'private-passport-record-2',
        verificationStatus: PetEvidenceStatus::Verified,
        visibility: PetProfileVisibility::Private,
        expectedLockVersion: 2,
        idempotencyKey: 'pet-fact-002',
    );

    expect($replayed->id)->toBe($first->id)
        ->and($first->fresh()->is_current)->toBeFalse()
        ->and($first->fresh()->retired_at)->not->toBeNull()
        ->and($second->is_current)->toBeTrue()
        ->and($second->replaces_fact_id)->toBe($first->id)
        ->and($second->value)->toBe(['date' => '2020-06-01'])
        ->and($second->source_reference)->toBe('private-passport-record-2')
        ->and(PetProfileFact::query()
            ->where('pet_profile_id', $profile->id)
            ->where('fact_key', 'birth-date')
            ->count())->toBe(2)
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'fact-recorded')
            ->count())->toBe(2);

    $event = $profile->lifecycleEvents()->firstOrFail();
    expect(fn () => $event->forceFill(['reason_code' => 'rewritten'])->save())
        ->toThrow(LogicException::class);
});

it('revokes delegated access without allowing primary-owner revocation', function (): void {
    $owner = User::factory()->create();
    $sitter = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    $primary = PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $membership = PetProfileManager::factory()->for($profile, 'profile')->for($sitter)->create([
        'role' => PetManagerRole::Sitter,
    ]);
    $this->actingAs($owner);
    $action = app(RevokePetProfileManager::class);

    $revoked = $action->handle(
        $membership,
        'owner-revoked-access',
        'manager-revoke-001',
    );

    expect($revoked->status)->toBe(PetManagerStatus::Revoked)
        ->and($revoked->revoked_by_user_id)->toBe($owner->id);

    expect(fn () => $action->handle(
        $primary,
        'invalid-primary-revoke',
        'manager-revoke-002',
    ))->toThrow(ValidationException::class);
});

it('preserves the legacy owner fallback when another manager is only invited', function (): void {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    PetProfileManager::factory()->for($profile, 'profile')->for($invitee)->create([
        'status' => PetManagerStatus::Invited,
        'accepted_at' => null,
    ]);

    expect(app(PetProfileAccess::class)->allows(
        $profile,
        $owner,
        PetProfilePermission::ManageManagers,
    ))->toBeTrue();
});

it('creates a pet through the livewire form and rejects direct unauthorized management', function (): void {
    $owner = User::factory()->create();
    Livewire::actingAs($owner)
        ->test(CreatePetProfileComponent::class)
        ->set('form.name', 'Luna')
        ->set('form.species', 'cat')
        ->set('form.relationshipRole', PetManagerRole::PrimaryOwner->value)
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors();

    $profile = PetProfile::query()->where('name', 'Luna')->firstOrFail();

    expect($profile->status)->toBe(PetProfileStatus::Draft)
        ->and($profile->user_id)->toBe($owner->id)
        ->and($profile->species)->toBe('cat')
        ->and($profile->breed)->toBeNull();

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertOk()
        ->assertSee('Luna')
        ->set('form.species', 'other')
        ->set('form.breed', 'Mixed ancestry')
        ->set('form.bio', 'Calm at home and curious outdoors.')
        ->call('saveBasics')
        ->assertHasNoErrors()
        ->set('privacyForm.profileVisibility', 'public')
        ->set('privacyForm.isDiscoverable', true)
        ->set('privacyForm.allowExternalIndexing', true)
        ->call('savePrivacy')
        ->assertHasNoErrors()
        ->set('targetStatus', PetProfileStatus::Active->value)
        ->call('transitionStatus')
        ->assertHasNoErrors();

    $profile->refresh();
    expect($profile->status)->toBe(PetProfileStatus::Active)
        ->and($profile->species)->toBe('other')
        ->and($profile->breed)->toBe('Mixed ancestry')
        ->and($profile->visibility)->toBe('public')
        ->and($profile->is_discoverable)->toBeTrue()
        ->and($profile->allow_external_indexing)->toBeTrue();

    $outsider = User::factory()->create();
    Livewire::actingAs($outsider)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertForbidden();
});

it('protects every pet management route and renders it for an authorized owner', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);

    auth()->logout();
    $this->get(route('pets.manage.create'))->assertRedirect(route('login'));
    $this->get(route('pets.manage.invitations'))->assertRedirect(route('login'));
    $this->get(route('pets.manage.show', [
        'petProfile' => $profile->profile_key,
    ]))->assertRedirect(route('login'));

    $this->actingAs($owner);
    Model::preventAccessingMissingAttributes();

    try {
        $this->get(route('pets.manage.create'))->assertOk();
        $this->get(route('pets.manage.invitations'))->assertOk();
        $this->get(route('pets.manage.show', [
            'petProfile' => $profile->profile_key,
        ]))->assertOk()->assertSee($profile->name);
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('serves the stable profile key only through an authorized public projection', function (): void {
    $owner = User::factory()->create();
    $private = PetProfile::factory()->for($owner)->create([
        'profile_key' => 'pet-private-luna',
        'visibility' => 'private',
        'status' => PetProfileStatus::Draft,
        'is_discoverable' => false,
        'profile_data' => ['story' => 'Private story', 'location' => 'Exact home address'],
    ]);

    $this->get(route('pets.profile', ['petProfile' => $private->profile_key]))
        ->assertForbidden();

    $public = PetProfile::factory()->for($owner)->create([
        'profile_key' => 'pet-public-baks',
        'name' => 'Baks',
        'species' => 'dog',
        'visibility' => 'public',
        'status' => PetProfileStatus::Active,
        'is_discoverable' => true,
        'profile_data' => ['story' => 'Likes quiet walks', 'location' => 'Exact home address'],
    ]);
    PetProfilePrivacySetting::factory()->for($public, 'profile')->create([
        'profile_visibility' => 'public',
        'is_discoverable' => true,
        'owner_display_mode' => 'hidden',
        'public_location_precision' => 'hidden',
    ]);
    PetProfileManager::factory()->for($public, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $this->actingAs($owner);

    Model::preventAccessingMissingAttributes();

    try {
        $this->get(route('pets.profile', ['petProfile' => $public->profile_key]))
            ->assertOk()
            ->assertSee('Baks')
            ->assertSee('Likes quiet walks')
            ->assertDontSee('Exact home address');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('invalidates public projections immediately after a privacy change', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'public',
        'is_discoverable' => true,
    ]);
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    $this->actingAs($owner);

    foreach ([
        "pet-profile:{$profile->id}:public",
        "pet-profile:{$profile->profile_key}:canonical",
        'pet-profile:directory:public',
        'pet-profile:search:public',
        'pet-profile:recommendations:public',
    ] as $key) {
        Cache::forever($key, ['stale' => true]);
    }

    app(UpdatePetProfilePrivacy::class)->handle($profile->slug, [
        'profile_visibility' => 'private',
        'location_visibility' => 'private',
        'posts_visibility' => 'private',
        'friends_visibility' => 'private',
        'care_visibility' => 'private',
        'activity_visibility' => 'private',
        'is_discoverable' => false,
        'allow_external_indexing' => false,
        'lock_version' => 1,
        'idempotency_key' => 'privacy-cache-001',
    ]);

    expect(Cache::has("pet-profile:{$profile->id}:public"))->toBeFalse()
        ->and(Cache::has("pet-profile:{$profile->profile_key}:canonical"))->toBeFalse()
        ->and(Cache::has('pet-profile:directory:public'))->toBeFalse()
        ->and(Cache::has('pet-profile:search:public'))->toBeFalse()
        ->and(Cache::has('pet-profile:recommendations:public'))->toBeFalse()
        ->and(Cache::get("pet-profile:{$profile->id}:projection-version"))->toBe(2);
});

it('keeps pet profile translation keys and placeholders aligned across supported locales', function (): void {
    $catalogues = collect(['en', 'lt', 'ru'])->mapWithKeys(
        static fn (string $locale): array => [
            $locale => Arr::dot(require lang_path("{$locale}/pet_profiles.php")),
        ],
    );
    $english = $catalogues->get('en');

    expect($english)->toBeArray();

    foreach ($catalogues as $locale => $catalogue) {
        expect(array_keys($catalogue), $locale)->toBe(array_keys($english));

        foreach ($catalogue as $key => $value) {
            preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', (string) $english[$key], $englishMatches);
            preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', (string) $value, $matches);

            expect(array_values(array_unique($matches[0])), "{$locale}:{$key}")
                ->toBe(array_values(array_unique($englishMatches[0])))
                ->and((string) $value)->not->toBe("pet_profiles.{$key}");
        }
    }
});

it('keeps canonical public profile queries bounded as manager history grows', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'profile_key' => 'pet-query-count',
        'species' => 'dog',
        'visibility' => 'public',
        'status' => PetProfileStatus::Active,
        'is_discoverable' => true,
    ]);
    PetProfilePrivacySetting::factory()->for($profile, 'profile')->create([
        'profile_visibility' => 'public',
        'is_discoverable' => true,
    ]);
    User::factory()->count(30)->create()->each(
        fn (User $manager): PetProfileManager => PetProfileManager::factory()
            ->for($profile, 'profile')
            ->for($manager)
            ->create(),
    );

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = $this->get(route('pets.profile', ['petProfile' => $profile->profile_key]));
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();
    expect($queryCount)->toBeLessThanOrEqual(12);
});
