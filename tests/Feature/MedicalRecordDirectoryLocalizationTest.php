<?php

declare(strict_types=1);

use App\Models\MedicalRecord;
use App\Models\PetProfile;
use Illuminate\Support\Facades\File;

const MEDICAL_DIRECTORY_UI_KEYS = [
    'medical_privacy_status',
    'medical_data_is_private_by_default',
    'pet_followers_social_groups_marketplace_sellers_and_unrelated_specialists_cannot_open_these_records',
    'managed_records',
    'private_health_record',
    'private',
    'weight',
    'medications',
    'tasks',
    'open',
    'no_health_records_yet',
    'create_the_first_record',
];

test('the medical record directory renders its body in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'name' => 'Birch Locale Test',
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
        ['pet' => 'Birch Locale Test'],
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
        ->assertSee(trans('ui.no_health_records_yet', locale: $locale))
        ->assertSee(trans('ui.create_the_first_record', locale: $locale));
})->with(['lt', 'ru']);

test('the medical record directory has complete non english copy and no hardcoded image alt', function (): void {
    foreach (['lt', 'ru'] as $locale) {
        foreach (MEDICAL_DIRECTORY_UI_KEYS as $key) {
            expect(trans("ui.{$key}", locale: $locale))
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }

        expect(trans('presentation.medical_record_image_alt', ['pet' => 'Birch'], $locale))
            ->not->toBe(trans('presentation.medical_record_image_alt', ['pet' => 'Birch'], 'en'))
            ->toContain('Birch');
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
