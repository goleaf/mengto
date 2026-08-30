<?php

declare(strict_types=1);

use App\Models\CareJournal;
use Illuminate\Support\Facades\File;

const CARE_DIRECTORY_UI_KEYS = [
    'family_care_overview',
    'one_place_for_the_household',
    'each_pet_keeps_a_separate_journal_missing_records_stay_marked_as_unknown_never_silently_treated_as_missed_care',
    'your_pet_care_journals',
    'private',
    'today',
    'open_tasks',
    'last_feeding',
    'last_walk',
    'open_journal',
    'plan_care',
    'not_recorded',
    'no_private_care_journals_yet',
    'create_first_journal',
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
    $response->assertSee(trans('messages.breed_not_recorded', locale: $locale));

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
        ->assertSee(trans('ui.no_private_care_journals_yet', locale: $locale))
        ->assertSee(trans('ui.create_first_journal', locale: $locale));
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

        expect(trans('messages.breed_not_recorded', locale: $locale))
            ->not->toBe(trans('messages.breed_not_recorded', locale: 'en'));
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
