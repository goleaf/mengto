<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

const GROUP_CONTENT_IDENTITY_KEYS = [
    'jamie_cho',
    'jamie',
    'lena_brooks',
    'mia',
    'mia_carter',
    'mochi',
    'nori',
    'olive',
    'priya_shah',
    'scout',
];

test('every first party group content fixture is translated in Lithuanian and Russian', function (): void {
    $source = File::get(app_path('Services/GroupContentCatalog.php'));

    preg_match_all("/__\\('messages\\.([^']+)'/", $source, $matches);

    $keys = array_values(array_unique($matches[1]));
    $english = require lang_path('en/messages.php');

    expect($matches[1])->toHaveCount(122)
        ->and($keys)->toHaveCount(119);

    foreach (['lt', 'ru'] as $locale) {
        $localized = require lang_path("{$locale}/messages.php");

        foreach ($keys as $key) {
            expect($localized)->toHaveKey($key);

            if (! in_array($key, GROUP_CONTENT_IDENTITY_KEYS, true)) {
                expect($localized[$key], "{$locale}.messages.{$key}")
                    ->not->toBe($english[$key]);
            }
        }
    }
});

test('group content localizes dates and tags instead of hardcoding English fixtures', function (): void {
    $source = File::get(app_path('Services/GroupContentCatalog.php'));

    expect($source)
        ->not->toContain("'month' => 'AUG'")
        ->not->toContain("'tags' => ['event', 'local']")
        ->toContain(
            "__('groups.detail.content.month_aug')",
            "__('groups.detail.content.tags.event')",
            "__('groups.detail.content.tags.local')",
        );

    $english = require lang_path('en/groups.php');

    foreach (['lt', 'ru'] as $locale) {
        $localized = require lang_path("{$locale}/groups.php");

        expect(data_get($localized, 'detail.content.month_aug'))
            ->not->toBe(data_get($english, 'detail.content.month_aug'))
            ->and(data_get($localized, 'detail.content.tags.event'))
            ->not->toBe(data_get($english, 'detail.content.tags.event'))
            ->and(data_get($localized, 'detail.content.tags.local'))
            ->not->toBe(data_get($english, 'detail.content.tags.local'));
    }

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));

    expect($browser)->toContain(
        'groupTabAudits',
        'englishGroupTabCopy',
        "for (const tab of ['overview', 'posts', 'discussions', 'events', 'members', 'pets', 'resources', 'rules'])",
        'English group tab content fallback remains.',
    );
});

test('all eight group tabs render localized fixture content', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $expectations = [
        'overview' => [
            'pinned_by_moderators',
            'share_useful_context',
            'a_practical_starting_guide_for',
            'registration_open',
            'which_topic_should_the_group_prioritize_in_august',
            'i_added_the_shaded_arrival_point_and_accessibility_notes_to_the_event',
            'moderator_community_care',
        ],
        'posts' => [
            'guide',
            'question',
            'event_update',
        ],
        'discussions' => [
            'introductions_and_current_routines',
            'questions_waiting_for_a_useful_answer',
            'moderator_reviewed_reference_thread',
        ],
        'events' => [
            'exact_details_after_rsvp',
            'member_q_a_and_monthly_planning',
        ],
        'members' => [
            'active_member',
            'moderator_cat_enrichment',
            'volunteer_coordinator',
        ],
        'pets' => [
            'border_collie_active_learner',
            'tabby_cat_indoor_enrichment',
            'shiba_mix_neighborhood_walks',
            'corgi_gentle_introductions',
        ],
        'resources' => [
            'new_member_guide',
            'public_meeting_place_checklist',
            'when_community_answers_are_not_enough',
            'privacy_and_photo_consent',
        ],
        'rules' => [
            'be_useful_and_respectful',
            'protect_people_and_locations',
            'no_dangerous_medical_instructions',
            'keep_commerce_transparent',
        ],
    ];

    $messages = require lang_path("{$locale}/messages.php");

    foreach ($expectations as $tab => $keys) {
        $response = $this->get(route('groups.show', [
            'group' => 'trail-tails',
            'tab' => $tab,
        ]))->assertOk();

        foreach ($keys as $key) {
            $response->assertSee($messages[$key]);
        }
    }
})->with(['lt', 'ru']);
