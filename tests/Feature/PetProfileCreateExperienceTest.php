<?php

declare(strict_types=1);

use App\Livewire\Pets\CreatePetProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects the legacy pet composer to the canonical creation flow', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('compose', 'pet'))
        ->assertRedirect(route('pets.manage.create'));
});

it('renders one concise private draft form without advanced profile fields', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pets.manage.create'))
        ->assertOk()
        ->assertSee('data-section="pet-profile-create"', false)
        ->assertSee('id="pet-profile-name"', false)
        ->assertSee('id="pet-profile-photo"', false)
        ->assertSee('id="pet-profile-photo-alt"', false)
        ->assertSee('id="pet-profile-species"', false)
        ->assertSee('id="pet-profile-relationship"', false)
        ->assertSee('id="pet-profile-visibility"', false)
        ->assertDontSee('id="pet-profile-breed"', false)
        ->assertDontSee('id="pet-profile-birth-date"', false)
        ->assertDontSee('id="pet-profile-sex"', false)
        ->assertDontSee('id="pet-profile-reproductive-status"', false)
        ->assertDontSee('id="pet-profile-bio"', false)
        ->assertDontSee('taxonomy-selector', false);
});

it('requires an accessible description only when the optional photo is selected', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->set('mediaForm.upload', UploadedFile::fake()->image(
            'baks.jpg',
            100,
            100,
        ))
        ->call('create')
        ->assertHasErrors(['mediaForm.altText' => 'required']);
});

it('validates every browser-controlled field in the minimal creation form', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', '')
        ->set('form.species', 'not-a-species')
        ->set('form.relationshipRole', 'not-a-role')
        ->set('form.visibility', 'not-a-visibility')
        ->call('create')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.species' => 'in',
            'form.relationshipRole',
            'form.visibility',
        ]);
});

it('links the pet directory to the canonical creation flow', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pets.index'))
        ->assertOk()
        ->assertSee(route('pets.manage.create'), false)
        ->assertDontSee(route('compose', 'pet'), false);
});
