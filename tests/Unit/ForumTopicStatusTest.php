<?php

declare(strict_types=1);

use App\Enums\ForumTopicStatus;

test('legacy topic statuses map to their canonical lifecycle states', function (
    ForumTopicStatus $legacy,
    ForumTopicStatus $canonical,
): void {
    expect($legacy->canonical())->toBe($canonical);
})->with([
    'review' => [ForumTopicStatus::Review, ForumTopicStatus::PendingModeration],
    'resolved' => [ForumTopicStatus::Resolved, ForumTopicStatus::Solved],
    'partially resolved' => [ForumTopicStatus::PartiallyResolved, ForumTopicStatus::PartiallySolved],
    'unanswered' => [ForumTopicStatus::Unanswered, ForumTopicStatus::Open],
    'closed' => [ForumTopicStatus::Closed, ForumTopicStatus::Locked],
]);

test('only safe lifecycle states appear in public topic queries', function (): void {
    $public = ForumTopicStatus::publicValues();

    expect($public)
        ->toContain(ForumTopicStatus::Open->value)
        ->toContain(ForumTopicStatus::Solved->value)
        ->toContain(ForumTopicStatus::Resolved->value)
        ->not->toContain(ForumTopicStatus::Draft->value)
        ->not->toContain(ForumTopicStatus::PendingModeration->value)
        ->not->toContain(ForumTopicStatus::Removed->value)
        ->not->toContain(ForumTopicStatus::Redirected->value);
});

test('solved topics accept corrections while terminal redirects do not', function (): void {
    expect(ForumTopicStatus::Solved->acceptsAnswers())->toBeTrue()
        ->and(ForumTopicStatus::Outdated->acceptsAnswers())->toBeTrue()
        ->and(ForumTopicStatus::Merged->acceptsAnswers())->toBeFalse()
        ->and(ForumTopicStatus::Redirected->redirectsToAnotherTopic())->toBeTrue();
});
