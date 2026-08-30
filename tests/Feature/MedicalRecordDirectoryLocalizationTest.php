<?php

declare(strict_types=1);

use App\Models\MedicalRecord;
use App\Models\PetProfile;
use Illuminate\Support\Facades\File;

const MEDICAL_DIRECTORY_UI_KEYS = [
    'medical_privacy_status_a8a40a4bd3',
    'medical_data_is_private_by_default_2829377998',
    'pet_followers_social_groups_marketplace_sellers_and_unrelated_62afc5fdb8',
    'managed_records_663bb02e0d',
    'private_health_record_f1b39f16e5',
    'private_c63eb6720c',
    'weight_81d27ef6d5',
    'medications_6b5763afa6',
    'tasks_b3a60e61a5',
    'open_ed077f3d81',
    'no_health_records_yet_8cc935a87c',
    'create_the_first_record_381606f2e5',
];

test('the medical record directory renders its body in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'name' => 'Scout Locale Test',
        'profile_data' => [
            'profile_image' => '/images/pets/scout-locale-test.jpg',
        ],
    ]);

    MedicalRecord::factory()->forPetProfile($pet)->create();

    $response = $this->get(route('medical-records.index'))->assertOk();

    foreach (MEDICAL_DIRECTORY_UI_KEYS as $key) {
        if (str_starts_with($key, 'no_health_records_') || str_starts_with($key, 'create_the_first_')) {
            continue;
        }

        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }

    $expectedAlt = trans(
        'presentation.medical_record_image_alt',
        ['pet' => 'Scout Locale Test'],
        $locale,
    );
    $image = responseXPath($response)->query(
        '//article[contains(concat(" ", normalize-space(@class), " "), " medical-record-card ")]//img',
    )->item(0);

    expect($image)->not->toBeNull()
        ->and($image?->attributes?->getNamedItem('alt')?->nodeValue)->toBe($expectedAlt);
})->with(['lt', 'ru']);

test('the medical record empty state follows the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $this->get(route('medical-records.index'))
        ->assertOk()
        ->assertSee(trans('ui.no_health_records_yet_8cc935a87c', locale: $locale))
        ->assertSee(trans('ui.create_the_first_record_381606f2e5', locale: $locale));
})->with(['lt', 'ru']);

test('the medical record directory has complete non english copy and no hardcoded image alt', function (): void {
    foreach (['lt', 'ru'] as $locale) {
        foreach (MEDICAL_DIRECTORY_UI_KEYS as $key) {
            expect(trans("ui.{$key}", locale: $locale))
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }

        expect(trans('presentation.medical_record_image_alt', ['pet' => 'Scout'], $locale))
            ->not->toBe(trans('presentation.medical_record_image_alt', ['pet' => 'Scout'], 'en'))
            ->toContain('Scout');
    }

    expect(File::get(resource_path('views/components/medical-record-card.blade.php')))
        ->toContain("__('presentation.medical_record_image_alt'")
        ->not->toContain('health record"');
});

test('the browser matrix rejects medical directory body fallbacks', function (): void {
    expect(File::get(base_path('scripts/accessibility-browser-check.mjs')))
        ->toContain(
            'englishMedicalRecordCopy',
            "route.path === '/medical-records'",
            'English medical body fallback remains.',
            'behavior.medicalRecordCopy.imageAlt',
            'medical card media and body are not stacked.',
        );
});
