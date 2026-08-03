<?php

declare(strict_types=1);

use App\Actions\AddPetProfileName;
use App\Actions\RemovePetProfileName;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfileName;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function petNameIdentityProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->draft()->create([
        'name' => 'Luna',
        'visibility' => 'private',
        'is_discoverable' => false,
        ...$attributes,
    ]);
    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);

    return $profile;
}

it('creates an indexed additive pet name schema with protected ownership links', function (): void {
    expect(Schema::hasColumns('pet_profile_names', [
        'pet_profile_id',
        'name',
        'normalized_name',
        'type',
        'visibility',
        'locale',
        'is_searchable',
        'recorded_by_user_id',
        'recorded_at',
        'deleted_at',
    ]))->toBeTrue();

    $indexes = collect(Schema::getIndexes('pet_profile_names'))->keyBy('name');

    expect($indexes)->toHaveKeys([
        'pet_profile_names_profile_normalized_unique',
        'pet_profile_names_projection_idx',
        'pet_profile_names_recorder_idx',
        'pet_profile_names_search_idx',
    ])->and($indexes['pet_profile_names_profile_normalized_unique']['unique'])->toBeTrue();
});

it('adds multilingual typed names and rejects misleading or malformed values', function (): void {
    $owner = User::factory()->create();
    $profile = petNameIdentityProfile($owner);

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('nameForm.name', 'Mėta')
        ->set('nameForm.type', PetProfileNameType::Localized->value)
        ->set('nameForm.locale', 'lt')
        ->set('nameForm.visibility', PetProfileNameVisibility::Managers->value)
        ->call('addAlternativeName')
        ->assertHasNoErrors()
        ->assertSee('Mėta');

    $name = PetProfileName::query()->sole();

    expect($name->normalized_name)->toBe('mėta')
        ->and($name->type)->toBe(PetProfileNameType::Localized)
        ->and($name->visibility)->toBe(PetProfileNameVisibility::Managers)
        ->and($name->locale)->toBe('lt');

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('nameForm.name', 'SYSTEM')
        ->call('addAlternativeName')
        ->assertHasErrors(['nameForm.name'])
        ->set('nameForm.name', '🐾🐾🐾')
        ->call('addAlternativeName')
        ->assertHasErrors(['nameForm.name'])
        ->set('nameForm.name', 'Luna')
        ->call('addAlternativeName')
        ->assertHasErrors(['nameForm.name']);
});

it('preserves a private searchable previous name when the current name changes', function (): void {
    $owner = User::factory()->create();
    $profile = petNameIdentityProfile($owner, ['name' => 'Luna']);
    $profileKey = $profile->profile_key;
    $slug = $profile->slug;

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.name', 'Nova')
        ->set('form.species', $profile->species)
        ->call('saveBasics')
        ->assertHasNoErrors();

    $previous = PetProfileName::query()->sole();
    $profile->refresh();

    expect($profile->name)->toBe('Nova')
        ->and($profile->profile_key)->toBe($profileKey)
        ->and($profile->slug)->toBe($slug)
        ->and($previous->name)->toBe('Luna')
        ->and($previous->type)->toBe(PetProfileNameType::Previous)
        ->and($previous->visibility)->toBe(PetProfileNameVisibility::Private)
        ->and($previous->is_searchable)->toBeTrue()
        ->and($previous->recorded_by_user_id)->toBe($owner->id);

    actingAs($owner);
    get(route('pets.index', ['q' => 'luna']))
        ->assertOk()
        ->assertSee('Nova');
});

it('keeps repeated additions idempotent and restores a removed name', function (): void {
    $owner = User::factory()->create();
    $profile = petNameIdentityProfile($owner);
    $payload = [
        'name' => 'Moon',
        'type' => PetProfileNameType::Nickname->value,
        'visibility' => PetProfileNameVisibility::Private->value,
        'locale' => null,
    ];
    actingAs($owner);

    $first = app(AddPetProfileName::class)->handle($profile, $payload);
    $second = app(AddPetProfileName::class)->handle($profile, $payload);

    expect($second->is($first))->toBeTrue()
        ->and(PetProfileName::query()->count())->toBe(1);

    app(RemovePetProfileName::class)->handle($profile, $first->id);

    expect(PetProfileName::query()->count())->toBe(0)
        ->and(PetProfileName::withTrashed()->count())->toBe(1);

    $restored = app(AddPetProfileName::class)->handle($profile, [
        ...$payload,
        'visibility' => PetProfileNameVisibility::Public->value,
    ]);

    expect($restored->id)->toBe($first->id)
        ->and($restored->trashed())->toBeFalse()
        ->and($restored->visibility)->toBe(PetProfileNameVisibility::Public)
        ->and(PetProfileName::withTrashed()->count())->toBe(1);
});

it('never exposes private or manager-only names on the public pet profile', function (): void {
    $owner = User::factory()->create();
    $profile = petNameIdentityProfile($owner, [
        'name' => 'Nova',
        'status' => 'active',
        'visibility' => 'public',
        'is_discoverable' => true,
    ]);
    PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Public Moon',
        'normalized_name' => 'public moon',
        'visibility' => PetProfileNameVisibility::Public,
    ]);
    PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Manager Moon',
        'normalized_name' => 'manager moon',
        'visibility' => PetProfileNameVisibility::Managers,
    ]);
    PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Private Moon',
        'normalized_name' => 'private moon',
        'visibility' => PetProfileNameVisibility::Private,
    ]);

    get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee('Public Moon')
        ->assertDontSee('Manager Moon')
        ->assertDontSee('Private Moon');
});

it('limits private names to their recording manager while sharing manager names', function (): void {
    $owner = User::factory()->create();
    $coOwner = User::factory()->create();
    $profile = petNameIdentityProfile($owner, ['name' => 'Nova']);
    PetProfileManager::factory()->for($profile, 'profile')->for($coOwner)->create([
        'role' => PetManagerRole::CoOwner,
    ]);
    $privateName = PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Owner Secret',
        'normalized_name' => 'owner secret',
        'visibility' => PetProfileNameVisibility::Private,
        'recorded_by_user_id' => $owner->id,
    ]);
    PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Shared Moon',
        'normalized_name' => 'shared moon',
        'visibility' => PetProfileNameVisibility::Managers,
        'recorded_by_user_id' => $owner->id,
    ]);

    Livewire::actingAs($coOwner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertDontSee('Owner Secret')
        ->assertSee('Shared Moon');

    actingAs($coOwner);
    expect(fn () => app(RemovePetProfileName::class)->handle($profile, $privateName->id))
        ->toThrow(ModelNotFoundException::class);
    get(route('pets.index', ['q' => 'owner secret']))
        ->assertOk()
        ->assertDontSee('Nova');
    get(route('pets.index', ['q' => 'shared moon']))
        ->assertOk()
        ->assertSee('Nova');

    actingAs($owner);
    get(route('pets.index', ['q' => 'owner secret']))
        ->assertOk()
        ->assertSee('Nova');
});

it('rejects alternative-name mutations outside the managed profile boundary', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $profile = petNameIdentityProfile($owner);
    $name = PetProfileName::factory()->for($profile, 'profile')->create([
        'recorded_by_user_id' => $owner->id,
    ]);
    actingAs($stranger);

    expect(fn () => app(AddPetProfileName::class)->handle($profile, [
        'name' => 'Not allowed',
        'type' => PetProfileNameType::Nickname->value,
        'visibility' => PetProfileNameVisibility::Private->value,
        'locale' => null,
    ]))->toThrow(ValidationException::class)
        ->and(fn () => app(RemovePetProfileName::class)->handle($profile, $name->id))
        ->toThrow(ValidationException::class);
});

it('enforces normalized uniqueness at the database boundary', function (): void {
    $owner = User::factory()->create();
    $profile = petNameIdentityProfile($owner);
    PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'Moon',
        'normalized_name' => 'moon',
    ]);

    expect(fn () => PetProfileName::factory()->for($profile, 'profile')->create([
        'name' => 'MOON',
        'normalized_name' => 'moon',
    ]))->toThrow(QueryException::class);
});
