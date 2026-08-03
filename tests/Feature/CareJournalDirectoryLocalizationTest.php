<?php

declare(strict_types=1);

use App\Models\CareJournal;
use Illuminate\Support\Facades\File;

const CARE_DIRECTORY_UI_KEYS = [
    'family_care_overview_b363d25aa9',
    'one_place_for_the_household_93084c6a2d',
    'each_pet_keeps_a_separate_journal_missing_records_e8f178c96b',
    'your_pet_care_journals_2cf8149a95',
    'private_c63eb6720c',
    'today_2b065c7c9c',
    'open_tasks_87cfa1a507',
    'last_feeding_5a15632589',
    'last_walk_2648a88ee0',
    'open_journal_1844ec9cd3',
    'plan_care_b0602a259f',
    'not_recorded_b37c7879f6',
    'no_private_care_journals_yet_6b892c7c93',
    'create_first_journal_be6634cd47',
];

test('the care journal directory renders its body in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    CareJournal::factory()->create([
        'owner_key' => $this->authenticatedUser->actor_key,
        'pet_name' => 'Scout Locale Test',
        'species' => 'dog',
        'breed' => null,
        'image_url' => '/images/pets/scout-locale-test.jpg',
    ]);

    $response = $this->get(route('care-journals.index'))->assertOk();

    foreach (CARE_DIRECTORY_UI_KEYS as $key) {
        if (str_starts_with($key, 'no_private_care_') || str_starts_with($key, 'create_first_')) {
            continue;
        }

        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }

    $response->assertSee(trans('pet_profiles.species.dog', locale: $locale));
    $response->assertSee(trans('messages.breed_not_recorded_ebcac0c0af', locale: $locale));

    $expectedMediaLabel = trans(
        'presentation.open_care_journal',
        ['pet' => 'Scout Locale Test'],
        $locale,
    );
    $media = responseXPath($response)->query(
        '//article[contains(concat(" ", normalize-space(@class), " "), " care-journal-card ")]'
        .'//a[contains(concat(" ", normalize-space(@class), " "), " care-journal-card__media ")]',
    )->item(0);

    expect($media)->not->toBeNull()
        ->and($media?->attributes?->getNamedItem('aria-label')?->nodeValue)
        ->toBe($expectedMediaLabel);
})->with(['lt', 'ru']);

test('the care journal empty state follows the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $this->get(route('care-journals.index'))
        ->assertOk()
        ->assertSee(trans('ui.no_private_care_journals_yet_6b892c7c93', locale: $locale))
        ->assertSee(trans('ui.create_first_journal_be6634cd47', locale: $locale));
})->with(['lt', 'ru']);

test('the care journal directory has complete non english copy and localized species', function (): void {
    foreach (['lt', 'ru'] as $locale) {
        foreach (CARE_DIRECTORY_UI_KEYS as $key) {
            expect(trans("ui.{$key}", locale: $locale))
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }

        foreach (['open_care_journal', 'care_week_status', 'recorded_count'] as $key) {
            expect(trans("presentation.{$key}", locale: $locale))
                ->not->toBe(trans("presentation.{$key}", locale: 'en'));
        }

        expect(trans('messages.breed_not_recorded_ebcac0c0af', locale: $locale))
            ->not->toBe(trans('messages.breed_not_recorded_ebcac0c0af', locale: 'en'));
    }

    expect(File::get(app_path('Services/CareJournalPresenter.php')))
        ->toContain("'species' => \$this->speciesLabels->for(\$journal->species)")
        ->not->toContain("'species' => Str::headline(\$journal->species)");
});

test('the browser matrix rejects care journal body fallbacks and split mobile cards', function (): void {
    expect(File::get(base_path('scripts/accessibility-browser-check.mjs')))
        ->toContain(
            'englishCareJournalCopy',
            "route.path === '/care-journals'",
            'English care body fallback remains.',
            'behavior.careJournalCopy.mediaLabel',
            'care journal media and body are not stacked.',
        );
});
