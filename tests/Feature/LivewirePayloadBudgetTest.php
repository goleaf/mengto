<?php

declare(strict_types=1);

use App\Enums\PetManagerRole;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use Livewire\Livewire;

test('the pet profile editor stays within explicit snapshot and update budgets', function (): void {
    $profile = PetProfile::factory()->for($this->authenticatedUser)->draft()->create([
        'profile_data' => [
            'story' => str_repeat('s', 3000),
            'appearance_summary' => str_repeat('a', 1500),
            'identifying_marks' => str_repeat('i', 1500),
            'appearance' => [
                'color_details' => str_repeat('c', 1000),
                'feather_color_details' => str_repeat('f', 1000),
                'scale_color_details' => str_repeat('x', 1000),
                'seasonal_color_changes' => str_repeat('h', 1000),
            ],
            'body_covering' => ['skin_condition' => str_repeat('b', 1000)],
            'temperament_summary' => str_repeat('t', 1500),
            'social_preferences' => str_repeat('o', 1500),
            'meeting_preferences' => str_repeat('m', 1500),
            'location_label' => str_repeat('l', 120),
        ],
    ]);
    PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($this->authenticatedUser)
        ->create(['role' => PetManagerRole::PrimaryOwner]);
    PetProfilePrivacySetting::factory()->for($profile, 'profile')->create();

    $component = Livewire::actingAs($this->authenticatedUser)
        ->test(ManagePetProfile::class, ['petProfile' => $profile]);

    $initialSnapshotBytes = strlen(json_encode($component->snapshot, JSON_THROW_ON_ERROR));
    $initialHtmlBytes = strlen($component->html());

    $component->set('form.name', 'Measured profile name');

    $updateBytes = strlen(json_encode([
        'snapshot' => $component->snapshot,
        'effects' => $component->effects,
    ], JSON_THROW_ON_ERROR));

    $measurement = [
        'initial_html_bytes' => $initialHtmlBytes,
        'initial_snapshot_bytes' => $initialSnapshotBytes,
        'update_payload_bytes' => $updateBytes,
    ];

    if (getenv('PERFORMANCE_REPORT') === '1') {
        fwrite(STDERR, json_encode(['manage_pet_profile' => $measurement], JSON_THROW_ON_ERROR).PHP_EOL);
    }

    expect($initialSnapshotBytes)->toBeLessThanOrEqual(24_576)
        ->and($initialHtmlBytes)->toBeLessThanOrEqual(196_608)
        ->and($updateBytes)->toBeLessThanOrEqual(65_536);
});
