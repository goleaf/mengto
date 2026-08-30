<?php

declare(strict_types=1);

use App\Http\Middleware\ResolvePortalContext;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Services\PortalContextResolver;
use Livewire\Livewire;

test('an authenticated account can select only a manageable pet and active organization', function (): void {
    $pet = PetProfile::factory()->discoverable()->create([
        'user_id' => $this->authenticatedUser->id,
    ]);
    $organization = Organization::factory()->forOwner($this->authenticatedUser)->create();

    $this->post(route('portal.context.update'), [
        'pet_profile_id' => $pet->id,
        'organization_id' => $organization->id,
        'return_to' => route('content.index'),
    ])->assertRedirect(route('content.index'));

    expect(session('portal.context.pet_profile_id'))->toBe($pet->id)
        ->and(session('portal.context.organization_id'))->toBe($organization->id);
    $context = app(PortalContextResolver::class)->resolve($this->authenticatedUser);
    expect($context->petProfileId)->toBe($pet->id)
        ->and($context->organizationId)->toBe($organization->id);
});

test('forged context identifiers never replace the last authorized context', function (): void {
    $owned = PetProfile::factory()->discoverable()->create([
        'user_id' => $this->authenticatedUser->id,
    ]);
    $foreign = PetProfile::factory()->discoverable()->create();
    session(['portal.context.pet_profile_id' => $owned->id]);

    $this->from(route('content.index'))->post(route('portal.context.update'), [
        'pet_profile_id' => $foreign->id,
        'return_to' => route('content.index'),
    ])->assertNotFound();

    expect(session('portal.context.pet_profile_id'))->toBe($owned->id);
});

test('revoked pet and organization context is invalidated on the next request', function (): void {
    $pet = PetProfile::factory()->discoverable()->create();
    $manager = PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create();
    $organization = Organization::factory()->create();
    $membership = OrganizationMembership::factory()->active()
        ->for($organization)
        ->for($this->authenticatedUser)
        ->create();
    session([
        'portal.context.pet_profile_id' => $pet->id,
        'portal.context.organization_id' => $organization->id,
    ]);
    $manager->update(['revoked_at' => now()]);
    $membership->update(['removed_at' => now()]);

    $this->get(route('content.index'))->assertSuccessful();

    expect(session('portal.context.pet_profile_id'))->toBeNull()
        ->and(session('portal.context.organization_id'))->toBeNull()
        ->and(session('portal.context.invalidated'))->toBeTrue();
});

test('portal context middleware is persistent across Livewire hydration', function (): void {
    expect(Livewire::getPersistentMiddleware())->toContain(ResolvePortalContext::class);
});

test('context resolver output stays below the explicit serialized payload ceiling', function (): void {
    PetProfile::factory()->count(12)->discoverable()->create([
        'user_id' => $this->authenticatedUser->id,
    ]);
    Organization::factory()->count(8)->forOwner($this->authenticatedUser)->create();

    $payload = app(PortalContextResolver::class)->options($this->authenticatedUser);

    expect(strlen(json_encode($payload, JSON_THROW_ON_ERROR)))->toBeLessThanOrEqual(16_384)
        ->and($payload['pets'])->toHaveCount(12)
        ->and($payload['organizations'])->toHaveCount(8);
});
