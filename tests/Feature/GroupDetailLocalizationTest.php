<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

const GROUP_DETAIL_SYSTEM_COPY = [
    'detail.back_to_groups',
    'detail.details_label',
    'detail.no_public_details',
    'detail.summary_label',
    'detail.privacy.closed',
    'detail.privacy.public',
    'detail.actions.share',
    'detail.actions.report',
    'detail.stats.members.label',
    'detail.stats.members.detail',
    'detail.stats.pets.label',
    'detail.stats.pets.detail',
    'detail.stats.week.label',
    'detail.stats.week.detail',
    'detail.stats.since.label',
    'detail.stats.since.detail',
    'detail.access.pending_title',
    'detail.access.join_title',
    'detail.access.pending_description',
    'detail.access.join_description',
    'detail.tabs.overview',
    'detail.tabs.posts',
    'detail.tabs.discussions',
    'detail.tabs.events',
    'detail.tabs.members',
    'detail.tabs.pets',
    'detail.tabs.resources',
    'detail.tabs.rules',
    'detail.membership.member',
    'detail.membership.pending',
    'detail.membership.none',
    'detail.notifications.all',
    'detail.notifications.important',
    'detail.notifications.events',
    'detail.notifications.mentions',
    'detail.notifications.digest',
    'detail.notifications.off',
    'detail.notifications.title',
    'detail.notifications.meta',
    'detail.notifications.level_label',
    'detail.notifications.unavailable',
    'detail.sections.posts.eyebrow',
    'detail.sections.posts.title',
    'detail.sections.posts.empty',
    'detail.sections.discussions.eyebrow',
    'detail.sections.discussions.title',
    'detail.sections.discussions.empty',
    'detail.sections.events.eyebrow',
    'detail.sections.events.title',
    'detail.sections.events.empty',
    'detail.sections.members.eyebrow',
    'detail.sections.members.title',
    'detail.sections.members.empty',
    'detail.sections.pets.eyebrow',
    'detail.sections.pets.title',
    'detail.sections.pets.empty',
    'detail.sections.resources.eyebrow',
    'detail.sections.resources.title',
    'detail.sections.resources.empty',
    'detail.sections.rules.eyebrow',
    'detail.sections.rules.title',
    'detail.sections.rules.empty',
    'detail.sections.rules.requirements_title',
    'detail.sections.rules.requirement_description',
    'detail.sections.principles.eyebrow',
    'detail.sections.principles.title',
    'detail.sections.overview_posts.eyebrow',
    'detail.sections.overview_posts.title',
    'detail.sections.overview_posts.empty',
    'detail.sections.overview_events.eyebrow',
    'detail.sections.overview_events.title',
    'detail.sections.overview_events.empty',
    'detail.sections.team_title',
    'detail.chat.eyebrow',
    'detail.chat.title',
    'detail.chat.recent_label',
    'detail.chat.empty',
    'detail.chat.open',
    'detail.poll.eyebrow',
    'detail.poll.selected',
    'detail.poll.vote',
    'detail.poll.empty',
    'detail.event.view',
    'detail.post.verified_expert',
    'detail.post.no_labels',
    'detail.post.activity_label',
];

test('the group detail system contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/groups.php'));

    expect(array_keys($english))->toContain(...GROUP_DETAIL_SYSTEM_COPY);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/groups.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach (GROUP_DETAIL_SYSTEM_COPY as $key) {
            expect($localized[$key], "{$locale}.groups.{$key}")
                ->not->toBe($english[$key]);
        }
    }
});

test('the group detail renders localized chrome on every tab', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $responses = [];

    foreach (['overview', 'posts', 'discussions', 'events', 'members', 'pets', 'resources', 'rules'] as $tab) {
        $responses[$tab] = $this->get(route('groups.show', [
            'group' => 'trail-tails',
            'tab' => $tab,
        ]))->assertOk();
    }

    $overview = $responses['overview'];

    foreach ([
        'detail.back_to_groups',
        'detail.details_label',
        'detail.summary_label',
        'detail.privacy.public',
        'detail.actions.share',
        'detail.actions.report',
        'detail.sections.principles.eyebrow',
        'detail.sections.principles.title',
        'detail.sections.overview_posts.eyebrow',
        'detail.sections.overview_posts.title',
        'detail.sections.overview_events.eyebrow',
        'detail.sections.overview_events.title',
        'detail.sections.team_title',
        'detail.chat.eyebrow',
        'detail.chat.title',
        'detail.chat.recent_label',
        'detail.chat.open',
        'detail.poll.eyebrow',
        'detail.notifications.title',
        'detail.notifications.meta',
        'detail.notifications.level_label',
    ] as $key) {
        $overview->assertSee(trans("groups.{$key}", locale: $locale));
    }

    foreach (['overview', 'posts', 'discussions', 'events', 'members', 'pets', 'resources', 'rules'] as $tab) {
        $overview->assertSee(trans("groups.detail.tabs.{$tab}", locale: $locale));
    }

    foreach ([
        'posts' => ['posts.eyebrow', 'posts.title'],
        'discussions' => ['discussions.eyebrow', 'discussions.title'],
        'events' => ['events.eyebrow', 'events.title'],
        'members' => ['members.eyebrow', 'members.title'],
        'pets' => ['pets.eyebrow', 'pets.title'],
        'resources' => ['resources.eyebrow', 'resources.title'],
        'rules' => ['rules.eyebrow', 'rules.title', 'rules.requirements_title'],
    ] as $tab => $keys) {
        foreach ($keys as $key) {
            $responses[$tab]->assertSee(trans("groups.detail.sections.{$key}", locale: $locale));
        }
    }

    $xpath = responseXPath($overview);

    expect($xpath->query('//*[@data-group-detail-back]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-group-detail-hero]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-group-detail-dashboard]')->length)->toBe(1);
})->with(['lt', 'ru']);

test('group detail chrome uses only the group localization domain and browser ratchet', function (): void {
    $sources = [
        File::get(app_path('Services/GroupPresenter.php')),
        File::get(resource_path('views/groups/show.blade.php')),
        File::get(resource_path('views/components/group-hero.blade.php')),
        File::get(resource_path('views/components/group-dashboard.blade.php')),
        File::get(resource_path('views/components/group-chat-preview.blade.php')),
        File::get(resource_path('views/components/group-poll.blade.php')),
        File::get(resource_path('views/components/group-event-card.blade.php')),
        File::get(resource_path('views/components/group-post-card.blade.php')),
    ];

    foreach ($sources as $source) {
        expect($source)
            ->not->toContain("__('ui.", "__('messages.")
            ->not->toContain('meta="Applies only to this group"');
    }

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));

    expect($browser)->toContain(
        'englishGroupDetailCopy',
        'groupDetailCopy.length === 45',
        'English group detail chrome fallback remains.',
    );

    $contentStyles = File::get(resource_path('scss/_content.scss'));

    expect($contentStyles)
        ->toContain('&--paper {', 'background: $paper;', 'color: $ink;');
});
