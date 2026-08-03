<?php

declare(strict_types=1);

use App\Actions\BackfillForumEventLifecycle;
use App\Models\ForumEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('an idempotent lifecycle backfill skips complete events with constant queries', function () {
    $organizer = User::factory()->create();
    ForumEvent::factory()
        ->count(6)
        ->forOrganizer($organizer)
        ->withLifecycle()
        ->create();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $result = app(BackfillForumEventLifecycle::class)->handle();
    $queryCount = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($result->eventsInitialized)->toBe(0)
        ->and($result->registrationsUpdated)->toBe(0)
        ->and($result->petLinksCreated)->toBe(0)
        ->and($queryCount)->toBeLessThanOrEqual(2);
});
