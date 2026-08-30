<?php

declare(strict_types=1);

use App\Actions\CreateSearchCase;
use App\Models\PetProfile;
use App\Models\SearchCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('search case factories persist only a contact token digest', function (): void {
    Storage::fake('local');

    $case = SearchCase::factory()->create();
    $digest = $case->getRawOriginal('contact_token');

    expect($digest)
        ->toBeString()
        ->toMatch('/\A[a-f0-9]{64}\z/');
});

test('the search case action creates contact token digests at the persistence boundary', function (): void {
    PetProfile::factory()->for($this->authenticatedUser)->active()->create([
        'profile_key' => 'security-test-pet',
        'slug' => 'security-test-pet',
    ]);
    $case = null;
    $uuid = Str::freezeUuids(function ($uuid) use (&$case): void {
        $case = app(CreateSearchCase::class)->handle([
            'type' => 'lost',
            'intent' => 'draft',
            'pet_profile_key' => 'security-test-pet',
            'pet_name' => 'Scout',
            'species' => 'dog',
            'breed' => 'Mixed breed',
            'sex' => 'male',
            'age_label' => 'Adult',
            'size' => 'medium',
            'primary_color' => 'Black with a white chest',
            'coat' => 'short',
            'distinctive_marks' => 'White chest patch',
            'hidden_marks' => 'Private identifying mark',
            'description' => 'Scout slipped out of his harness and may keep distance from strangers.',
            'health_notice' => null,
            'approach_instructions' => 'Stay sideways and report the location.',
            'avoid_instructions' => 'Do not chase or surround.',
            'accessories' => ['blue collar'],
            'microchip_status' => 'present',
            'last_seen_area' => 'Vingis Park',
            'city' => 'Vilnius',
            'country' => 'LT',
            'latitude' => 54.683412,
            'longitude' => 25.237481,
            'location_note' => 'Southern gate',
            'direction' => 'East',
            'last_seen_at' => now()->subMinutes(30),
            'notification_radius_km' => 5,
            'visibility' => 'public',
            'contact_channel' => 'platform',
            'contact_value' => null,
            'reward_offered' => false,
            'photos' => [],
        ]);
    });

    expect($case)->toBeInstanceOf(SearchCase::class);
    $digest = $case->getRawOriginal('contact_token');

    expect($digest)->toBe(hash('sha256', $uuid->toString()))
        ->and($digest)->not->toContain($uuid->toString())
        ->and($case->toArray())->not->toHaveKey('contact_token');
});
