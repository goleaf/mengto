<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumEventLifecycleBackfillResult;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventTeamRole;
use App\Enums\ForumEventVerificationStatus;
use App\Models\ForumEvent;
use App\Services\ForumEventLifecycleSnapshot;
use Illuminate\Database\Eloquent\Builder;

final readonly class BackfillForumEventLifecycle
{
    public function __construct(
        private InitializeForumEventLifecycle $initialize,
        private ForumEventLifecycleSnapshot $snapshots,
    ) {}

    public function handle(): ForumEventLifecycleBackfillResult
    {
        $eventsInitialized = 0;
        $registrationsUpdated = 0;
        $petLinksCreated = 0;

        ForumEvent::query()
            ->select(['id', 'organizer_user_id', 'owner_user_id', 'current_version_number'])
            ->where(function (Builder $events): void {
                $events
                    ->where(function (Builder $owner): void {
                        $owner
                            ->whereNull('owner_user_id')
                            ->whereNotNull('organizer_user_id');
                    })
                    ->orWhereDoesntHave('versions', function (Builder $versions): void {
                        $versions->whereColumn(
                            'forum_event_versions.version_number',
                            'forum_events.current_version_number',
                        );
                    })
                    ->orWhereDoesntHave('occurrences')
                    ->orWhere(function (Builder $ownerTeam): void {
                        $ownerTeam
                            ->whereNotNull('owner_user_id')
                            ->whereDoesntHave('teamMemberships', function (Builder $memberships): void {
                                $memberships
                                    ->whereColumn(
                                        'forum_event_team_memberships.user_id',
                                        'forum_events.owner_user_id',
                                    )
                                    ->where('role', ForumEventTeamRole::Owner->value);
                            });
                    })
                    ->orWhereHas('registrations', function (Builder $registrations): void {
                        $registrations
                            ->whereNull('forum_event_occurrence_id')
                            ->orWhereNull('forum_event_version_id')
                            ->orWhereNull('accepted_snapshot')
                            ->orWhereNull('accepted_snapshot_checksum')
                            ->orWhereNull('submitted_at')
                            ->orWhereNull('locale')
                            ->orWhereNull('timezone')
                            ->orWhere(function (Builder $pets): void {
                                $pets
                                    ->whereNotNull('pet_profile_id')
                                    ->whereDoesntHave('registrationPets');
                            });
                    });
            })
            ->with('organizer:id')
            ->orderBy('id')
            ->chunkById(100, function ($events) use (
                &$eventsInitialized,
                &$petLinksCreated,
                &$registrationsUpdated,
            ): void {
                foreach ($events as $event) {
                    $lifecycle = $this->initialize->handle($event, $event->organizer, 'legacy-lifecycle-backfill');

                    if ($lifecycle->version->wasRecentlyCreated
                        || $lifecycle->occurrence->wasRecentlyCreated
                    ) {
                        $eventsInitialized++;
                    }

                    $event->registrations()
                        ->with([
                            'pets:id',
                            'user:id,locale,timezone',
                        ])
                        ->orderBy('id')
                        ->chunkById(100, function ($registrations) use (
                            $lifecycle,
                            &$petLinksCreated,
                            &$registrationsUpdated,
                        ): void {
                            foreach ($registrations as $registration) {
                                $changed = false;

                                if ($registration->forum_event_occurrence_id === null) {
                                    $registration->forum_event_occurrence_id = $lifecycle->occurrence->id;
                                    $changed = true;
                                }

                                if ($registration->forum_event_version_id === null) {
                                    $registration->forum_event_version_id = $lifecycle->version->id;
                                    $changed = true;
                                }

                                if ($registration->submitted_at === null) {
                                    $registration->submitted_at = $registration->created_at->toImmutable();
                                    $changed = true;
                                }

                                if ($registration->locale === null) {
                                    $registration->locale = $registration->user->locale;
                                    $changed = true;
                                }

                                if ($registration->timezone === null) {
                                    $registration->timezone = $registration->user->timezone;
                                    $changed = true;
                                }

                                if ($registration->confirmed_at === null && in_array(
                                    $registration->status,
                                    array_filter(
                                        ForumEventRegistrationStatus::cases(),
                                        static fn (ForumEventRegistrationStatus $status): bool => $status->consumesSeat(),
                                    ),
                                    true,
                                )) {
                                    $registration->confirmed_at = $registration->created_at->toImmutable();
                                    $changed = true;
                                }

                                $petIds = $registration->pet_profile_id === null
                                    ? []
                                    : [$registration->pet_profile_id];

                                if ($registration->accepted_snapshot === null) {
                                    $snapshot = $this->snapshots->registration(
                                        $registration,
                                        $lifecycle->version,
                                        $lifecycle->occurrence,
                                        $petIds,
                                    );
                                    $registration->accepted_snapshot = $snapshot;
                                    $registration->accepted_snapshot_checksum = $this->snapshots->checksum($snapshot);
                                    $changed = true;
                                }

                                if ($changed) {
                                    $registration->save();
                                    $registrationsUpdated++;
                                }

                                if ($registration->pet_profile_id !== null
                                    && ! $registration->pets->contains('id', $registration->pet_profile_id)
                                ) {
                                    $registration->pets()->attach($registration->pet_profile_id, [
                                        'eligibility_status' => ForumEventVerificationStatus::NotAssessed->value,
                                        'verification_source' => ForumEventVerificationStatus::Unknown->value,
                                    ]);
                                    $petLinksCreated++;
                                }
                            }
                        });
                }
            });

        return new ForumEventLifecycleBackfillResult(
            $eventsInitialized,
            $registrationsUpdated,
            $petLinksCreated,
        );
    }
}
