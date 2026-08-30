<?php

declare(strict_types=1);

use App\Actions\CreateEventCompetition;
use App\Actions\CreateEventCompetitionEntry;
use App\Actions\AssignEventCompetitionJudge;
use App\Actions\SubmitEventCompetitionScore;
use App\Models\EventCompetitionCategory;
use App\Models\EventCompetitionCriterion;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEvent;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('organizer creates a versioned competition bound to its canonical event', function () {
    $organizer = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->withLifecycle()->create();

    $competition = app(CreateEventCompetition::class)->handle(
        $organizer,
        $event,
        'Agility finals',
        'Competition rules version one.',
        'competition-create-red-0001',
    );

    expect($competition->forum_event_id)->toBe($event->id)
        ->and($competition->ruleVersions()->count())->toBe(1);
});

test('eligible event registration enters a scoped category and a conflicted judge cannot score it', function () {
    $organizer = User::factory()->create();
    $entrant = User::factory()->create();
    $judge = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->withLifecycle()->create();
    $competition = app(CreateEventCompetition::class)->handle($organizer, $event, 'Agility finals', 'Competition rules version one.', 'competition-create-red-0002');
    $category = EventCompetitionCategory::query()->create(['competition_id' => $competition->id, 'stable_key' => 'agility-open', 'name' => 'Open']);
    $registration = ForumEventRegistration::factory()->for($event, 'event')->for($entrant)->create();
    $entry = app(CreateEventCompetitionEntry::class)->handle($entrant, $competition, $category, $registration, 'Nori and Ada', 'competition-entry-red-0001');
    $membership = ForumEventTeamMembership::factory()->for($event, 'event')->for($judge)->create(['role' => 'judge']);
    $assignment = app(AssignEventCompetitionJudge::class)->handle($organizer, $competition, $category, $judge, $membership, true);
    $assignment->conflicts()->create(['entry_id' => $entry->id, 'status' => 'confirmed', 'conflict_type' => 'household']);
    $criterion = EventCompetitionCriterion::query()->create(['category_id' => $category->id, 'rule_version_id' => 1, 'stable_key' => 'precision', 'name' => 'Precision', 'minimum_units' => 0, 'maximum_units' => 100000, 'scale_factor' => 1000]);

    expect(fn () => app(SubmitEventCompetitionScore::class)->handle($judge, $assignment, $entry, $criterion, 100001, null, 'competition-score-red-0001'))
        ->toThrow(ValidationException::class);
});
