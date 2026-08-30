<?php

declare(strict_types=1);

use App\Actions\AcceptPetProfileManagerInvitation;
use App\Actions\CreatePetProfile;
use App\Actions\ReviewPetProfileAccessRequest;
use App\Actions\SubmitPetProfileAccessRequest;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileAccessRequestDecision;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfileAccessRequestType;
use App\Enums\PetProfilePermission;
use App\Enums\UserStatus;
use App\Livewire\Pets\CreatePetProfile as CreatePetProfileComponent;
use App\Livewire\Pets\PetProfileAccessRequests;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\PetProfileDuplicateReview;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

it('creates the indexed encrypted access request boundary with valid factory defaults', function (): void {
    $request = PetProfileAccessRequest::factory()->create([
        'evidence_summary' => 'A private relationship statement for the current profile manager.',
        'resolution_note' => 'A private review note.',
    ]);

    expect(Schema::hasTable('pet_profile_access_requests'))->toBeTrue()
        ->and(Schema::hasColumn('pet_profiles', 'duplicate_name_hash'))->toBeTrue()
        ->and(Schema::hasIndex(
            'pet_profile_access_requests',
            'pet_access_requests_profile_status_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'pet_profile_access_requests',
            'pet_access_requests_requester_status_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'pet_profiles',
            'pet_profiles_duplicate_review_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'pet_profiles',
            'pet_profiles_duplicate_identity_idx',
        ))->toBeTrue()
        ->and($request->request_type)->toBe(PetProfileAccessRequestType::CoOwnership)
        ->and($request->requested_role)->toBe(PetManagerRole::CoOwner)
        ->and($request->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and($request->getRawOriginal('evidence_summary'))->not->toContain('private relationship')
        ->and($request->getRawOriginal('resolution_note'))->not->toContain('private review');
});

it('returns only bounded policy-visible exact identity matches in the safe duplicate review', function (): void {
    $viewer = User::factory()->create();
    $publicMatch = PetProfile::factory()->discoverable()->create([
        'name' => ' BAKS ',
        'species' => 'dog',
    ]);
    $privateMatch = PetProfile::factory()->privateProfile()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);
    PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'cat',
    ]);
    PetProfile::factory()->discoverable()->create([
        'name' => 'Milo',
        'species' => 'dog',
    ]);

    $review = app(PetProfileDuplicateReview::class)->review($viewer, 'baks', 'dog');

    expect($review['candidates'])->toHaveCount(1)
        ->and($review['candidates'][0]['profile_key'])->toBe($publicMatch->profile_key)
        ->and(collect($review['candidates'])->pluck('profile_key'))
        ->not->toContain($privateMatch->profile_key)
        ->and(array_keys($review['candidates'][0]))->toBe([
            'profile_key',
            'name',
            'species',
            'age',
            'photo',
            'photo_alt',
        ]);
});

it('caps duplicate cards even when many visible profiles share the same broad identity', function (): void {
    $viewer = User::factory()->create();
    PetProfile::factory()->discoverable()->count(8)->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $review = app(PetProfileDuplicateReview::class)->review($viewer, 'Baks', 'dog');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($review['candidates'])->toHaveCount(6)
        ->and($queryCount)->toBeLessThanOrEqual(3);
});

it('requires a current duplicate review before direct creation but replays idempotently', function (): void {
    $creator = User::factory()->create();
    PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);
    $this->actingAs($creator);
    $action = app(CreatePetProfile::class);
    $data = [
        'title' => 'Baks',
        'species' => 'dog',
        'category' => 'dog',
        'relationship_role' => 'primary-owner',
        'visibility' => 'private',
        'idempotency_key' => 'duplicate-create-boundary',
    ];

    expect(fn () => $action->handle($data))
        ->toThrow(ValidationException::class);

    $review = app(PetProfileDuplicateReview::class)->review($creator, 'Baks', 'dog');
    $decisionToken = app(PetProfileDuplicateReview::class)->confirmDifferentAnimal(
        $creator,
        'Baks',
        'dog',
        $review['token'],
    );
    $created = $action->handle($data + [
        'duplicate_review_token' => $review['token'],
        'duplicate_review_decision_token' => $decisionToken,
    ]);
    $replayed = $action->handle($data);

    expect($replayed->is($created))->toBeTrue()
        ->and(PetProfile::query()->where('user_id', $creator->id)->count())->toBe(1);
});

it('pauses Livewire creation for a safe match and creates only after an explicit different animal decision', function (): void {
    $creator = User::factory()->create();
    $existing = PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    $component = Livewire::actingAs($creator)
        ->test(CreatePetProfileComponent::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors()
        ->assertSee(__('pet_profiles.duplicate_review.title'))
        ->assertSee(__('pet_profiles.duplicate_review.this_is_my_pet'))
        ->assertSee($existing->name);

    expect(PetProfile::query()->count())->toBe(1);

    $component
        ->call('confirmDifferentAnimal')
        ->assertHasNoErrors();

    expect(PetProfile::query()->count())->toBe(2)
        ->and(PetProfile::query()->where('user_id', $creator->id)->count())->toBe(1);
});

it('submits one encrypted idempotent request without granting access', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);
    $this->actingAs($requester);
    $action = app(SubmitPetProfileAccessRequest::class);

    $first = $action->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'I share daily care and can provide the adoption agreement privately.',
        null,
        'claim-baks-once',
    );
    $replayed = $action->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'This replayed text must not replace the original evidence statement.',
        null,
        'claim-baks-once',
    );

    expect($replayed->is($first))->toBeTrue()
        ->and($first->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and($first->requested_role)->toBe(PetManagerRole::CoOwner)
        ->and($first->getRawOriginal('evidence_summary'))->not->toContain('adoption agreement')
        ->and(PetProfileAccessRequest::query()->count())->toBe(1)
        ->and(PetProfileManager::query()->where('user_id', $requester->id)->exists())->toBeFalse();
});

it('rechecks current profile visibility inside the locked submission transaction', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $staleProfile = PetProfile::factory()->for($owner)->discoverable()->create();
    PetProfile::query()
        ->whereKey($staleProfile->id)
        ->update([
            'visibility' => 'private',
            'is_discoverable' => false,
        ]);
    $this->actingAs($requester);

    expect(fn () => app(SubmitPetProfileAccessRequest::class)->handle(
        $staleProfile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'I can provide supporting relationship evidence through the private review.',
        null,
        'stale-public-profile-request',
    ))->toThrow(AuthorizationException::class)
        ->and(PetProfileAccessRequest::query()->count())->toBe(0);
});

it('requires a bounded future end time for temporary access', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);
    $action = app(SubmitPetProfileAccessRequest::class);

    expect(fn () => $action->handle(
        $profile,
        PetProfileAccessRequestType::TemporaryAccess,
        null,
        'I will provide temporary care while the current owner is recovering.',
        null,
        'temporary-without-end',
    ))->toThrow(ValidationException::class);

    expect(fn () => $action->handle(
        $profile,
        PetProfileAccessRequestType::TemporaryAccess,
        null,
        'I will provide temporary care while the current owner is recovering.',
        now()->addYears(2)->toDateTimeString(),
        'temporary-too-long',
    ))->toThrow(ValidationException::class)
        ->and(PetProfileAccessRequest::query()->count())->toBe(0);
});

it('lets the current manager review evidence then requires the requester to accept the invitation', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);
    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'I am a co-owner and can provide the shared veterinary registration.',
        null,
        'co-owner-review-flow',
    );

    $this->actingAs($owner);
    $reviewed = app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        'Evidence is sufficient for an invitation.',
        'approve-co-owner-review-flow',
    );
    $invitation = PetProfileManager::query()
        ->where('pet_profile_id', $profile->id)
        ->where('user_id', $requester->id)
        ->sole();

    expect($reviewed->status)->toBe(PetProfileAccessRequestStatus::Approved)
        ->and($reviewed->active_key)->toBeNull()
        ->and($reviewed->granted_manager_id)->toBe($invitation->id)
        ->and($invitation->status)->toBe(PetManagerStatus::Invited)
        ->and($invitation->role)->toBe(PetManagerRole::CoOwner);

    $this->actingAs($requester);
    $accepted = app(AcceptPetProfileManagerInvitation::class)->handle(
        $invitation,
        'accept-co-owner-review-flow',
    );

    expect($accepted->status)->toBe(PetManagerStatus::Active)
        ->and($accepted->allows(PetProfilePermission::ManageManagers))->toBeTrue();
});

it('recognizes a resolved access review stored with the legacy decision key', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $rawIdempotencyKey = 'legacy-access-review';
    $request = PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($requester, 'requester')
        ->create([
            'status' => PetProfileAccessRequestStatus::Rejected,
            'active_key' => null,
            'decision_key' => hash(
                'sha256',
                "pet-access-decision|{$owner->id}|{$rawIdempotencyKey}",
            ),
            'reviewed_by_user_id' => $owner->id,
            'reviewed_at' => now(),
        ]);
    $this->actingAs($owner);

    $replayed = app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Reject,
        'The legacy decision has already been recorded.',
        $rawIdempotencyKey,
    );

    expect($replayed->is($request))->toBeTrue()
        ->and($replayed->status)->toBe(PetProfileAccessRequestStatus::Rejected)
        ->and(PetProfileManager::query()
            ->where('pet_profile_id', $profile->id)
            ->where('user_id', $requester->id)
            ->exists())->toBeFalse();
});

it('corrects an existing non-critical relationship without duplicating manager records', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $membership = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($requester)
        ->create([
            'actor_key_snapshot' => $requester->actor_key,
            'role' => PetManagerRole::FamilyMember,
            'status' => PetManagerStatus::Active,
        ]);
    $this->actingAs($requester);
    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::RelationshipCorrection,
        PetManagerRole::Caregiver,
        'My role is temporary daily care rather than a permanent family relationship.',
        null,
        'relationship-correction-request',
    );

    $this->actingAs($owner);
    app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        'The requested relationship matches the current care arrangement.',
        'relationship-correction-approve',
    );

    expect($membership->refresh()->role)->toBe(PetManagerRole::Caregiver)
        ->and(PetProfileManager::query()->where('pet_profile_id', $profile->id)->count())->toBe(1)
        ->and($request->refresh()->status)->toBe(PetProfileAccessRequestStatus::Approved);
});

it('treats manager time bounds as authoritative for requests and relationship corrections', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $membership = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($requester)
        ->create([
            'actor_key_snapshot' => $requester->actor_key,
            'role' => PetManagerRole::FamilyMember,
            'status' => PetManagerStatus::Active,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
    $this->actingAs($requester);

    expect(fn () => app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::RelationshipCorrection,
        PetManagerRole::Caregiver,
        'My expired relationship should not be changed through the active correction flow.',
        null,
        'expired-relationship-correction',
    ))->toThrow(ValidationException::class);

    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'I can provide current co-ownership evidence after the previous access period ended.',
        null,
        'request-after-manager-expiry',
    );

    expect($request->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and($membership->refresh()->ends_at?->isPast())->toBeTrue();
});

it('fails closed when a correction relationship expires before manager review', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $membership = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($requester)
        ->create([
            'actor_key_snapshot' => $requester->actor_key,
            'role' => PetManagerRole::FamilyMember,
            'status' => PetManagerStatus::Active,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDay(),
        ]);
    $this->actingAs($requester);
    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::RelationshipCorrection,
        PetManagerRole::Caregiver,
        'My current role is daily care rather than a permanent family relationship.',
        null,
        'relationship-expires-before-review',
    );
    $membership->forceFill(['ends_at' => now()->subMinute()])->save();

    $this->actingAs($owner);

    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        'The evidence was reviewed after the relationship ended.',
        'expired-relationship-review',
    ))->toThrow(ValidationException::class)
        ->and($request->refresh()->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and($membership->refresh()->role)->toBe(PetManagerRole::FamilyMember);
});

it('blocks outsiders and standard approval of an ownership transfer request', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);
    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::OwnershipTransfer,
        null,
        'I completed the adoption and can provide the signed transfer documents.',
        null,
        'protected-transfer-request',
    );

    $this->actingAs($outsider);
    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        '',
        'outsider-transfer-review',
    ))->toThrow(AuthorizationException::class);

    $this->actingAs($owner);
    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        '',
        'owner-transfer-review',
    ))->toThrow(ValidationException::class);

    expect($request->refresh()->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and(PetProfileManager::query()->where('user_id', $requester->id)->exists())->toBeFalse();
});

it('shows requester role and private evidence only to an authorized manager', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create(['name' => 'Ani Requester']);
    $outsider = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create([
        'name' => 'Baks',
    ]);
    $this->actingAs($requester);
    app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::Management,
        null,
        'I manage the household care schedule and can verify the shared account.',
        null,
        'manager-review-screen',
    );

    Livewire::actingAs($owner)
        ->test(PetProfileAccessRequests::class, ['petProfile' => $profile])
        ->assertSee('Ani Requester')
        ->assertSee(__('pet_profiles.manager_roles.profile-administrator'))
        ->assertSee('I manage the household care schedule');

    Livewire::actingAs($outsider)
        ->test(PetProfileAccessRequests::class, ['petProfile' => $profile])
        ->assertForbidden();
});

it('submits a duplicate-card request from Livewire and never exposes a private match', function (): void {
    $requester = User::factory()->create();
    $public = PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);
    $private = PetProfile::factory()->privateProfile()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    Livewire::actingAs($requester)
        ->test(CreatePetProfileComponent::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'co-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertSee($public->profile_key)
        ->assertDontSee($private->profile_key)
        ->call('startAccessRequest', $public->profile_key)
        ->set('accessRequestForm.requestType', 'co-ownership')
        ->set(
            'accessRequestForm.evidenceSummary',
            'I share responsibility and can provide the registration privately.',
        )
        ->call('submitSelectedAccessRequest')
        ->assertHasNoErrors()
        ->assertSee(__('pet_profiles.feedback.access_request_submitted'));

    expect(PetProfileAccessRequest::query()->count())->toBe(1)
        ->and(PetProfile::query()->count())->toBe(2);
});

it('binds a locked access request to its canonical profile before review authorization', function (): void {
    $reviewer = User::factory()->create();
    $foreignOwner = User::factory()->create();
    $requester = User::factory()->create();
    $authorizedProfile = PetProfile::factory()->for($reviewer)->discoverable()->create();
    $foreignProfile = PetProfile::factory()->for($foreignOwner)->discoverable()->create();
    $request = PetProfileAccessRequest::factory()
        ->for($foreignProfile, 'profile')
        ->for($requester, 'requester')
        ->create();
    $request->setAttribute('pet_profile_id', $authorizedProfile->id);
    $request->setRelation('profile', $authorizedProfile);
    $this->actingAs($reviewer);

    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Reject,
        'This request does not belong to a profile I may review.',
        'cross-profile-review-binding',
    ))->toThrow(AuthorizationException::class);

    expect(PetProfileAccessRequest::query()->findOrFail($request->id)->status)
        ->toBe(PetProfileAccessRequestStatus::Pending)
        ->and(PetProfileManager::query()->where('pet_profile_id', $foreignProfile->id)->exists())
        ->toBeFalse();
});

it('expires a stale temporary request and permits a new request', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);
    $action = app(SubmitPetProfileAccessRequest::class);
    $expired = $action->handle(
        $profile,
        PetProfileAccessRequestType::TemporaryAccess,
        null,
        'I will provide short-term care and can verify the arrangement privately.',
        now()->addMinute()->toDateTimeString(),
        'temporary-request-to-expire',
    );
    $this->travel(2)->minutes();

    $replacement = $action->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        'I can now provide current co-ownership evidence for private review.',
        null,
        'replacement-after-temporary-expiry',
    );

    expect($expired->refresh()->status)->toBe(PetProfileAccessRequestStatus::Expired)
        ->and($expired->active_key)->toBeNull()
        ->and($replacement->status)->toBe(PetProfileAccessRequestStatus::Pending)
        ->and($replacement->active_key)->not->toBeNull()
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'access-request-expired')
            ->where('actor_user_id', $requester->id)
            ->count())->toBe(1);
});

it('expires a stale temporary request instead of approving access', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);
    $request = app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::TemporaryAccess,
        null,
        'I will provide short-term care and can verify the arrangement privately.',
        now()->addMinute()->toDateTimeString(),
        'expired-before-review',
    );
    $this->travel(2)->minutes();
    $this->actingAs($owner);

    $reviewed = app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        'The review occurred after the requested care period ended.',
        'review-expired-temporary-request',
    );
    $replayed = app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Approve,
        'The review occurred after the requested care period ended.',
        'review-expired-temporary-request',
    );

    expect($reviewed->status)->toBe(PetProfileAccessRequestStatus::Expired)
        ->and($replayed->status)->toBe(PetProfileAccessRequestStatus::Expired)
        ->and($reviewed->active_key)->toBeNull()
        ->and($reviewed->granted_manager_id)->toBeNull()
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'access-request-expired')
            ->where('actor_user_id', $owner->id)
            ->count())->toBe(1)
        ->and(PetProfileManager::query()
            ->where('pet_profile_id', $profile->id)
            ->where('user_id', $requester->id)
            ->exists())->toBeFalse();
});

it('prevents unavailable invited accounts from activating pet management', function (
    UserStatus $status,
): void {
    $owner = User::factory()->create();
    $invitee = User::factory()->create(['status' => $status]);
    $profile = PetProfile::factory()->for($owner)->privateProfile()->create();
    $invitation = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($invitee)
        ->invited()
        ->create();
    $this->actingAs($invitee);

    expect(fn () => app(AcceptPetProfileManagerInvitation::class)->handle(
        $invitation,
        'unavailable-invitation-acceptance',
    ))->toThrow(HttpException::class);

    expect($invitation->refresh()->status)->toBe(PetManagerStatus::Invited)
        ->and($invitation->accepted_at)->toBeNull();
})->with([
    'blocked' => [UserStatus::Blocked],
    'suspended' => [UserStatus::Suspended],
]);

it('reloads reviewer availability at the locked decision boundary', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $request = PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($requester, 'requester')
        ->create();
    $this->actingAs($owner);
    User::query()->whereKey($owner)->update(['status' => UserStatus::Blocked]);

    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Reject,
        'This request must not be reviewed by an unavailable account.',
        'stale-reviewer-status',
    ))->toThrow(AuthorizationException::class);

    expect($request->refresh()->status)->toBe(PetProfileAccessRequestStatus::Pending);
});

it('rejects whitespace-only access evidence and rejection rationale', function (): void {
    $owner = User::factory()->create();
    $requester = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->discoverable()->create();
    $this->actingAs($requester);

    expect(fn () => app(SubmitPetProfileAccessRequest::class)->handle(
        $profile,
        PetProfileAccessRequestType::CoOwnership,
        null,
        str_repeat(' ', 20),
        null,
        'whitespace-access-evidence',
    ))->toThrow(ValidationException::class);

    $request = PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($requester, 'requester')
        ->create();
    $this->actingAs($owner);

    expect(fn () => app(ReviewPetProfileAccessRequest::class)->handle(
        $request,
        PetProfileAccessRequestDecision::Reject,
        str_repeat(' ', 10),
        'whitespace-rejection-rationale',
    ))->toThrow(ValidationException::class);

    expect($request->refresh()->status)->toBe(PetProfileAccessRequestStatus::Pending);
});
