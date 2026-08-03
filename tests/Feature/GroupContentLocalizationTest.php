<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

const GROUP_CONTENT_IDENTITY_KEYS = [
    'jamie_cho_5f313c129b',
    'jamie_eaea2ab372',
    'lena_brooks_ca42e74116',
    'mia_4150950870',
    'mia_carter_0e5b29cc3b',
    'mochi_95114c81f3',
    'nori_a64203ba20',
    'olive_3038ab334a',
    'priya_shah_8925523814',
    'scout_8a1db462be',
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
            'pinned_by_moderators_fcee4c1b2d',
            'share_useful_context_89397c310f',
            'a_practical_starting_guide_for_005053c744',
            'registration_open_86babcde8a',
            'which_topic_should_the_group_prioritize_in_august_9397594256',
            'i_added_the_shaded_arrival_point_and_accessibility_notes_73aac77a97',
            'moderator_community_care_1257fea022',
        ],
        'posts' => [
            'guide_8dd65d0952',
            'question_289aff12b0',
            'event_update_3b5964ae5a',
        ],
        'discussions' => [
            'introductions_and_current_routines_a352d27726',
            'questions_waiting_for_a_useful_answer_ac1dc9cc16',
            'moderator_reviewed_reference_thread_8257a25770',
        ],
        'events' => [
            'exact_details_after_rsvp_6a23376343',
            'member_q_a_and_monthly_planning_eedd381804',
        ],
        'members' => [
            'active_member_8688d586e3',
            'moderator_cat_enrichment_e430728130',
            'volunteer_coordinator_ea4c1153dc',
        ],
        'pets' => [
            'border_collie_active_learner_8070e8b176',
            'tabby_cat_indoor_enrichment_a87f7dfe20',
            'shiba_mix_neighborhood_walks_af9f9423f4',
            'corgi_gentle_introductions_209a54b3fa',
        ],
        'resources' => [
            'new_member_guide_8a08e3bc98',
            'public_meeting_place_checklist_0234d48f72',
            'when_community_answers_are_not_enough_0e742366c1',
            'privacy_and_photo_consent_04f66e1c36',
        ],
        'rules' => [
            'be_useful_and_respectful_ec9ad819b7',
            'protect_people_and_locations_16de3c3fef',
            'no_dangerous_medical_instructions_46d7ef8b6d',
            'keep_commerce_transparent_e32664f7e7',
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
