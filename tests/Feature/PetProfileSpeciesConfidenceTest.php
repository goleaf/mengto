<?php

declare(strict_types=1);

use App\Enums\PetManagerRole;
use App\Enums\PetSpeciesConfidence;
use App\Livewire\Pets\CreatePetProfile;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\PetSpeciesLabel;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('creates possible cat and dog identities without inventing a confirmed species fact', function (
    string $species,
): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Temporary identity')
        ->set('form.species', $species)
        ->set('form.speciesConfidence', PetSpeciesConfidence::Possible->value)
        ->set('form.relationshipRole', PetManagerRole::Finder->value)
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    $profile = PetProfile::query()->sole();

    expect($profile->species)->toBe($species)
        ->and($profile->species_confidence)->toBe(PetSpeciesConfidence::Possible)
        ->and($profile->status->value)->toBe('found')
        ->and($profile->is_discoverable)->toBeFalse();
})->with(['cat', 'dog']);

it('stores an unidentified species explicitly and normalizes incompatible browser confidence', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Unknown animal')
        ->set('form.species', 'unknown')
        ->set('form.speciesConfidence', PetSpeciesConfidence::Confirmed->value)
        ->set('form.relationshipRole', PetManagerRole::Finder->value)
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors();

    $unknown = PetProfile::query()->sole();

    expect($unknown->species)->toBe('unknown')
        ->and($unknown->species_confidence)->toBe(PetSpeciesConfidence::Unidentified);

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Certain rabbit')
        ->set('form.species', 'rabbit')
        ->set('form.speciesConfidence', PetSpeciesConfidence::Possible->value)
        ->set('form.relationshipRole', PetManagerRole::Finder->value)
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors();

    expect(PetProfile::query()->where('name', 'Certain rabbit')->sole()->species_confidence)
        ->toBe(PetSpeciesConfidence::Confirmed);
});

it('lets an authorized manager correct a possible identification without changing identity', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()
        ->for($owner)
        ->possibleSpecies('dog')
        ->draft()
        ->create(['name' => 'Scout']);
    PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($owner)
        ->create(['role' => PetManagerRole::PrimaryOwner]);

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.speciesConfidence', PetSpeciesConfidence::Possible->value)
        ->set('form.speciesConfidence', PetSpeciesConfidence::Confirmed->value)
        ->call('saveBasics')
        ->assertHasNoErrors();

    expect($profile->refresh()->profile_key)->not->toBeEmpty()
        ->and($profile->species)->toBe('dog')
        ->and($profile->species_confidence)->toBe(PetSpeciesConfidence::Confirmed);

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.species', 'unknown')
        ->call('saveBasics')
        ->assertHasNoErrors();

    expect($profile->refresh()->species)->toBe('unknown')
        ->and($profile->species_confidence)->toBe(PetSpeciesConfidence::Unidentified);
});

it('labels possible and unidentified species honestly on public and workspace surfaces', function (): void {
    $owner = User::factory()->create();
    $possible = PetProfile::factory()
        ->for($owner)
        ->possibleSpecies('cat')
        ->discoverable()
        ->create(['name' => 'Misty']);
    PetProfileManager::factory()
        ->for($possible, 'profile')
        ->for($owner)
        ->create(['role' => PetManagerRole::PrimaryOwner]);

    actingAs($owner);

    get(route('pets.profile', $possible))
        ->assertOk()
        ->assertSee(__('pet_profiles.species_confidence.possible_label', [
            'species' => __('pet_profiles.species.cat'),
        ]))
        ->assertDontSee('>Cat<', false);

    get(route('pets.index'))
        ->assertOk()
        ->assertSee(__('pet_profiles.species_confidence.possible_label', [
            'species' => __('pet_profiles.species.cat'),
        ]));

    expect(app(PetSpeciesLabel::class)->for('unknown', PetSpeciesConfidence::Confirmed))
        ->toBe(__('pet_profiles.species.unknown'));
});

it('renders only confidence choices that are meaningful for the selected broad species', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->assertSeeHtml('id="pet-profile-species-confidence"')
        ->set('form.species', 'cat')
        ->assertSee(__('pet_profiles.species_confidence.confirmed'))
        ->assertSee(__('pet_profiles.species_confidence.possible'))
        ->set('form.species', 'rabbit')
        ->assertSee(__('pet_profiles.species_confidence.confirmed'))
        ->assertDontSee(__('pet_profiles.species_confidence.possible'))
        ->set('form.species', 'unknown')
        ->assertSee(__('pet_profiles.species_confidence.unidentified'))
        ->assertDontSee(__('pet_profiles.species_confidence.possible'));
});
