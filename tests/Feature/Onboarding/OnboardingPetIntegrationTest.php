<?php

declare(strict_types=1);

use App\Actions\CreatePetProfile;
use App\Livewire\Pets\CreatePetProfile as CreatePetProfileComponent;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use App\Models\User;
use App\Services\PetProfileDuplicateReview;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('duplicate review finds an older exact match beyond newer same species rows', function (): void {
    $viewer = User::factory()->create();
    $olderMatch = PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    foreach (range(1, 50) as $number) {
        PetProfile::factory()->discoverable()->create([
            'name' => "Different dog {$number}",
            'species' => 'dog',
        ]);
    }

    $review = app(PetProfileDuplicateReview::class)->review($viewer, '  BAKS  ', 'dog');

    expect(collect($review['candidates'])->pluck('profile_key')->all())
        ->toBe([$olderMatch->profile_key]);
});

test('direct creation rejects browser-invalid canonical identity input', function (
    array $overrides,
    string $errorKey,
): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $data = [
        'title' => 'Baks',
        'species' => 'dog',
        'species_confidence' => 'confirmed',
        'relationship_role' => 'primary-owner',
        'visibility' => 'private',
        'idempotency_key' => 'invalid-direct-pet-create',
        ...$overrides,
    ];

    try {
        app(CreatePetProfile::class)->handle($data);
        $this->fail('Expected canonical creation validation to fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey($errorKey);
    }

    expect(PetProfile::query()->count())->toBe(0)
        ->and(PetProfileManager::query()->count())->toBe(0)
        ->and(PetProfilePrivacySetting::query()->count())->toBe(0)
        ->and(PetProfileSlugAlias::query()->count())->toBe(0)
        ->and(PetProfileLifecycleEvent::query()->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'pet-profile.created')->count())->toBe(0);
})->with([
    'missing name' => [['title' => ''], 'name'],
    'reserved name' => [['title' => 'PawCircle'], 'name'],
    'malformed name' => [['title' => '<script>'], 'name'],
    'non string name' => [['title' => []], 'name'],
    'overlong name' => [['title' => str_repeat('B', 121)], 'name'],
    'unsupported species' => [['species' => 'dragon'], 'species'],
    'unknown role' => [['relationship_role' => 'owner-by-claim'], 'relationship_role'],
    'unknown visibility' => [['visibility' => 'worldwide'], 'visibility'],
    'non string idempotency key' => [['idempotency_key' => []], 'idempotency_key'],
]);

test('duplicate decision tokens are bound to user candidates and expiry', function (): void {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    PetProfile::factory()->discoverable()->create(['name' => 'Baks', 'species' => 'dog']);
    $reviewer = app(PetProfileDuplicateReview::class);
    $review = $reviewer->review($creator, 'Baks', 'dog');
    $decision = $reviewer->confirmDifferentAnimal($creator, 'Baks', 'dog', $review['token']);
    $payload = [
        'title' => 'Baks',
        'species' => 'dog',
        'relationship_role' => 'primary-owner',
        'visibility' => 'private',
        'idempotency_key' => 'duplicate-token-binding',
        'duplicate_review_token' => $review['token'],
        'duplicate_review_decision_token' => $decision,
    ];

    $this->actingAs($otherUser);
    expect(fn () => app(CreatePetProfile::class)->handle($payload))
        ->toThrow(ValidationException::class);

    $this->actingAs($creator);
    expect(fn () => app(CreatePetProfile::class)->handle([...$payload,
        'duplicate_review_decision_token' => $decision.'tampered',
    ]))->toThrow(ValidationException::class);

    $this->travel(31)->minutes();
    expect(fn () => app(CreatePetProfile::class)->handle($payload))
        ->toThrow(ValidationException::class);

    expect(PetProfile::query()->whereIn('user_id', [$creator->id, $otherUser->id])->count())
        ->toBe(0);
});

test('direct creation requires a bound different animal confirmation when candidates exist', function (): void {
    $creator = User::factory()->create();
    PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);
    $this->actingAs($creator);
    $review = app(PetProfileDuplicateReview::class)->review($creator, 'Baks', 'dog');
    $data = [
        'title' => 'Baks',
        'species' => 'dog',
        'species_confidence' => 'confirmed',
        'relationship_role' => 'primary-owner',
        'visibility' => 'private',
        'idempotency_key' => 'confirmed-different-animal',
        'duplicate_review_token' => $review['token'],
    ];

    expect(fn () => app(CreatePetProfile::class)->handle($data))
        ->toThrow(ValidationException::class);

    $decisionToken = app(PetProfileDuplicateReview::class)->confirmDifferentAnimal(
        $creator,
        'Baks',
        'dog',
        $review['token'],
    );
    $created = app(CreatePetProfile::class)->handle($data + [
        'duplicate_review_decision_token' => $decisionToken,
    ]);

    expect($created->user_id)->toBe($creator->id)
        ->and(PetProfile::query()->count())->toBe(2);
});

test('canonical creation keeps a maximum length slug portable', function (): void {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $name = str_repeat('Ab', 60);

    $created = app(CreatePetProfile::class)->handle([
        'title' => $name,
        'species' => 'dog',
        'species_confidence' => 'confirmed',
        'relationship_role' => 'primary-owner',
        'visibility' => 'private',
        'idempotency_key' => 'portable-long-pet-slug',
    ]);

    expect(mb_strlen($created->slug))->toBeLessThanOrEqual(80);
});

test('onboarding pet create navigation returns directly without changing intended destination', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    session()->put('url.intended', route('devices.index'));

    $response = $this->actingAs($user)
        ->get(route('pets.manage.create'))
        ->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//a[@href="'.route('onboarding.show').'"]')->length)
        ->toBeGreaterThanOrEqual(2)
        ->and($xpath->query('//a[@href="'.route('pets.index').'"]'))->toHaveCount(0)
        ->and(session('url.intended'))->toBe(route('devices.index'));
});

test('normal pet create navigation keeps the pets destination', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('pets.manage.create'))
        ->assertOk();

    expect(responseXPath($response)->query('//a[@href="'.route('pets.index').'"]')->length)
        ->toBeGreaterThanOrEqual(2);
});

test('duplicate actions name the candidate and access errors remain associated', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $candidate = PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    Livewire::actingAs($user)
        ->test(CreatePetProfileComponent::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'co-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->call('startAccessRequest', $candidate->profile_key)
        ->set('accessRequestForm.evidenceSummary', '')
        ->call('submitSelectedAccessRequest')
        ->assertHasErrors(['accessRequestForm.evidenceSummary'])
        ->assertSeeHtml('aria-label="'.e(__('pet_profiles.duplicate_review.request_access_to', [
            'name' => 'Baks',
        ])).'"')
        ->assertSeeHtml('id="pet-access-request-evidence-error"')
        ->assertSeeHtml('aria-invalid="true"')
        ->assertSeeHtml('wire:loading.attr="aria-busy"');
});
