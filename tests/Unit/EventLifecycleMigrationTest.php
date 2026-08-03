<?php

declare(strict_types=1);

use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Illuminate\Support\Facades\Schema;

class EventLifecycleMigrationTestCase extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
    }
}

uses(EventLifecycleMigrationTestCase::class);

test('point thirteen migrations rollback and reapply without losing legacy event rows', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant)
        ->create();
    $migrations = array_map(
        static fn (string $filename): object => require database_path('migrations/'.$filename),
        [
            '2026_08_03_082151_add_point_thirteen_fields_to_forum_events_table.php',
            '2026_08_03_082152_create_forum_event_series_and_occurrences_tables.php',
            '2026_08_03_082153_create_forum_event_versions_and_team_memberships_tables.php',
            '2026_08_03_082154_add_lifecycle_snapshot_to_forum_event_registrations_table.php',
            '2026_08_03_082155_create_forum_event_registration_pets_table.php',
        ],
    );

    foreach (array_reverse($migrations) as $migration) {
        $migration->down();
    }

    expect(Schema::hasTable('forum_event_occurrences'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_versions'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_team_memberships'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_registration_pets'))->toBeFalse()
        ->and(Schema::hasColumn('forum_events', 'owner_user_id'))->toBeFalse()
        ->and(Schema::hasColumn('forum_event_registrations', 'accepted_snapshot'))->toBeFalse()
        ->and(ForumEvent::query()->whereKey($event->id)->exists())->toBeTrue()
        ->and(ForumEventRegistration::query()->whereKey($registration->id)->exists())->toBeTrue();

    foreach ($migrations as $migration) {
        $migration->up();
    }

    expect(Schema::hasTable('forum_event_occurrences'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_versions'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_team_memberships'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_registration_pets'))->toBeTrue()
        ->and(Schema::hasColumn('forum_events', 'owner_user_id'))->toBeTrue()
        ->and(Schema::hasColumn('forum_event_registrations', 'accepted_snapshot'))->toBeTrue()
        ->and(ForumEvent::query()->whereKey($event->id)->exists())->toBeTrue()
        ->and(ForumEventRegistration::query()->whereKey($registration->id)->exists())->toBeTrue();
});
