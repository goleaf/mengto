<?php

namespace App\Actions;

use App\Enums\SearchCaseType;
use App\Enums\SearchSectorStatus;
use App\Enums\SearchStatus;
use App\Enums\SearchTaskStatus;
use App\Enums\SearchVolunteerStatus;
use App\Enums\SightingStatus;
use App\Models\AuditLog;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchSector;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformSearchAction
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, search_case: SearchCase}
     */
    public function handle(SearchCase $searchCase, array $data): array
    {
        return match ($data['action']) {
            'join-search' => $this->joinSearch($searchCase, $data),
            'create-sector' => $this->createSector($searchCase, $data),
            'create-task' => $this->createTask($searchCase, $data),
            'claim-task' => $this->claimTask($searchCase, $data),
            'start-task' => $this->startTask($searchCase, $data),
            'complete-task' => $this->completeTask($searchCase, $data),
            'confirm-sighting' => $this->reviewSighting($searchCase, $data, true),
            'reject-sighting' => $this->reviewSighting($searchCase, $data, false),
            'publish-update' => $this->publishUpdate($searchCase, $data),
            'update-status' => $this->updateStatus($searchCase, $data),
        };
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function joinSearch(SearchCase $searchCase, array $data): array
    {
        if (! $searchCase->volunteer_join_open || $searchCase->status->isClosed()) {
            throw ValidationException::withMessages([
                'action' => 'This search is not accepting new volunteers.',
            ]);
        }

        $identity = $this->actor->identity();
        $volunteer = SearchVolunteer::query()->updateOrCreate(
            [
                'search_case_id' => $searchCase->id,
                'actor_key' => $identity['key'],
            ],
            [
                'display_name' => $identity['name'],
                'capabilities' => array_values($data['capabilities']),
                'status' => SearchVolunteerStatus::Active,
                'privacy_level' => 'team-only',
                'joined_at' => now(),
            ],
        );

        $this->audit('search-volunteer.joined', $searchCase, [
            'volunteer_id' => $volunteer->id,
            'capabilities' => $volunteer->capabilities,
        ]);

        return [
            'message' => 'You joined the search. Choose a safe task and keep exact locations inside the team.',
            'search_case' => $searchCase,
        ];
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function createSector(SearchCase $searchCase, array $data): array
    {
        $exists = SearchSector::query()
            ->where('search_case_id', $searchCase->id)
            ->where('code', $data['sector_code'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['sector_code' => 'This sector code already exists.']);
        }

        $sector = SearchSector::query()->create([
            'search_case_id' => $searchCase->id,
            'code' => $data['sector_code'],
            'label' => $data['sector_label'],
            'status' => SearchSectorStatus::Unchecked,
            'priority' => $data['sector_priority'] ?? 2,
            'risk_notes' => $data['sector_risk_notes'] ?? null,
            'access_notes' => $data['sector_access_notes'] ?? null,
        ]);

        $this->audit('search-sector.created', $searchCase, ['sector_id' => $sector->id]);

        return ['message' => 'Search sector created.', 'search_case' => $searchCase];
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function createTask(SearchCase $searchCase, array $data): array
    {
        $sectorId = $this->validatedSectorId($searchCase, $data['sector_id'] ?? null);
        $task = SearchTask::query()->create([
            'search_case_id' => $searchCase->id,
            'search_sector_id' => $sectorId,
            'created_by_key' => $this->actor->key(),
            'type' => $data['task_type'],
            'title' => $data['task_title'],
            'description' => $data['task_description'],
            'status' => SearchTaskStatus::Open,
            'safety_level' => $data['safety_level'] ?? 'standard',
            'starts_at' => $data['starts_at'] ?? null,
            'due_at' => $data['due_at'] ?? null,
        ]);

        $this->audit('search-task.created', $searchCase, [
            'task_id' => $task->id,
            'safety_level' => $task->safety_level,
        ]);

        return ['message' => 'Volunteer task created.', 'search_case' => $searchCase];
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function claimTask(SearchCase $searchCase, array $data): array
    {
        return DB::transaction(function () use ($searchCase, $data): array {
            $task = $this->lockedTask($searchCase, (int) $data['task_id']);

            if (! $task->status->canBeClaimed() || $task->assignee_key !== null) {
                throw ValidationException::withMessages(['task_id' => 'This task has already been claimed.']);
            }

            if (in_array($task->safety_level, ['specialist-only', 'dangerous'], true)) {
                throw ValidationException::withMessages([
                    'task_id' => 'This task can only be assigned by the coordinator to a qualified team.',
                ]);
            }

            $identity = $this->actor->identity();
            SearchVolunteer::query()->firstOrCreate(
                [
                    'search_case_id' => $searchCase->id,
                    'actor_key' => $identity['key'],
                ],
                [
                    'display_name' => $identity['name'],
                    'capabilities' => [],
                    'status' => SearchVolunteerStatus::Active,
                    'joined_at' => now(),
                ],
            );

            $task->update([
                'assignee_key' => $identity['key'],
                'assignee_name' => $identity['name'],
                'status' => SearchTaskStatus::Claimed,
                'claimed_at' => now(),
                'version' => $task->version + 1,
            ]);

            $this->audit('search-task.claimed', $searchCase, ['task_id' => $task->id]);

            return ['message' => 'Task claimed. Check the safety note before starting.', 'search_case' => $searchCase];
        });
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function startTask(SearchCase $searchCase, array $data): array
    {
        return DB::transaction(function () use ($searchCase, $data): array {
            $task = $this->lockedTask($searchCase, (int) $data['task_id']);
            $this->authorizeTaskAssignee($searchCase, $task);

            if ($task->status !== SearchTaskStatus::Claimed) {
                throw ValidationException::withMessages(['task_id' => 'Claim the task before starting it.']);
            }

            $task->update([
                'status' => SearchTaskStatus::InProgress,
                'starts_at' => $task->starts_at ?? now(),
                'version' => $task->version + 1,
            ]);

            $this->audit('search-task.started', $searchCase, ['task_id' => $task->id]);

            return ['message' => 'Task started. Check in with the coordinator if conditions change.', 'search_case' => $searchCase];
        });
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function completeTask(SearchCase $searchCase, array $data): array
    {
        return DB::transaction(function () use ($searchCase, $data): array {
            $task = $this->lockedTask($searchCase, (int) $data['task_id']);
            $this->authorizeTaskAssignee($searchCase, $task);

            if (! in_array($task->status, [
                SearchTaskStatus::Claimed,
                SearchTaskStatus::InProgress,
            ], true)) {
                throw ValidationException::withMessages(['task_id' => 'This task cannot be completed now.']);
            }

            $task->update([
                'status' => SearchTaskStatus::Completed,
                'result' => $data['task_result'],
                'completed_at' => now(),
                'version' => $task->version + 1,
            ]);

            if ($task->search_sector_id !== null) {
                SearchSector::query()
                    ->whereKey($task->search_sector_id)
                    ->where('search_case_id', $searchCase->id)
                    ->update([
                        'status' => SearchSectorStatus::Checked->value,
                        'checked_by_key' => $this->actor->key(),
                        'checked_at' => now(),
                    ]);
            }

            $this->audit('search-task.completed', $searchCase, [
                'task_id' => $task->id,
                'result' => $task->result,
            ]);

            return ['message' => 'Task completed and recorded on the search timeline.', 'search_case' => $searchCase];
        });
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function reviewSighting(SearchCase $searchCase, array $data, bool $confirmed): array
    {
        return DB::transaction(function () use ($searchCase, $data, $confirmed): array {
            $sighting = Sighting::query()
                ->select([
                    'id', 'search_case_id', 'status', 'observed_at', 'public_area',
                    'public_latitude', 'public_longitude', 'direction', 'notes',
                    'verified_by_key', 'verified_at',
                ])
                ->where('search_case_id', $searchCase->id)
                ->lockForUpdate()
                ->findOrFail((int) $data['sighting_id']);

            if (in_array($sighting->status, [SightingStatus::Confirmed, SightingStatus::Rejected], true)) {
                throw ValidationException::withMessages([
                    'sighting_id' => 'This sighting has already been reviewed.',
                ]);
            }

            $status = $confirmed ? SightingStatus::Confirmed : SightingStatus::Rejected;
            $sighting->update([
                'status' => $status,
                'verified_by_key' => $this->actor->key(),
                'verified_at' => now(),
            ]);

            if ($confirmed) {
                $searchCase->update([
                    'status' => SearchStatus::PossibleSighting,
                    'last_seen_area' => $sighting->public_area,
                    'public_latitude' => $sighting->public_latitude,
                    'public_longitude' => $sighting->public_longitude,
                    'direction' => $sighting->direction,
                    'last_sighting_at' => $sighting->observed_at,
                    'latest_update' => 'A sighting was confirmed by the search coordinator.',
                    'alerts_active' => true,
                ]);

                SearchUpdate::query()->create([
                    'search_case_id' => $searchCase->id,
                    'author_key' => $this->actor->key(),
                    'author_name' => $this->actor->identity()['name'],
                    'type' => 'sighting-confirmed',
                    'visibility' => 'public',
                    'title' => 'New confirmed sighting',
                    'body' => 'Report new observations and do not chase the animal.',
                    'public_area' => $sighting->public_area,
                    'occurred_at' => $sighting->observed_at,
                ]);

                SearchAlert::query()->create([
                    'search_case_id' => $searchCase->id,
                    'kind' => 'confirmed-sighting',
                    'radius_km' => $searchCase->notification_radius_km,
                    'region' => $sighting->public_area,
                    'channels' => ['in-app', 'push'],
                    'audiences' => ['nearby-users', 'active-volunteers'],
                    'status' => 'queued',
                    'recipient_count' => 0,
                    'message' => $searchCase->pet_name.' · confirmed sighting in '.$sighting->public_area,
                ]);
            }

            $this->audit(
                $confirmed ? 'sighting.confirmed' : 'sighting.rejected',
                $searchCase,
                ['sighting_id' => $sighting->id],
            );

            return [
                'message' => $confirmed ? 'Sighting confirmed and the priority area updated.' : 'Sighting marked as not a match.',
                'search_case' => $searchCase,
            ];
        });
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function publishUpdate(SearchCase $searchCase, array $data): array
    {
        SearchUpdate::query()->create([
            'search_case_id' => $searchCase->id,
            'author_key' => $this->actor->key(),
            'author_name' => $this->actor->identity()['name'],
            'type' => 'case-update',
            'visibility' => 'public',
            'title' => $data['update_title'],
            'body' => $data['update_body'] ?? null,
            'public_area' => $data['update_area'] ?? null,
            'occurred_at' => now(),
        ]);

        $searchCase->update(['latest_update' => $data['update_title']]);
        $this->audit('search-case.updated', $searchCase, ['title' => $data['update_title']]);

        return ['message' => 'Public search update published.', 'search_case' => $searchCase];
    }

    /** @param array<string, mixed> $data @return array{message: string, search_case: SearchCase} */
    private function updateStatus(SearchCase $searchCase, array $data): array
    {
        return DB::transaction(function () use ($searchCase, $data): array {
            $lockedCase = SearchCase::query()
                ->select([
                    'id', 'owner_key', 'coordinator_key', 'pet_name', 'active_key',
                    'type', 'pet_profile_key', 'status', 'alerts_active', 'volunteer_join_open',
                    'notification_radius_km', 'last_seen_area', 'city',
                ])
                ->lockForUpdate()
                ->findOrFail($searchCase->id);
            $status = SearchStatus::from((string) $data['status']);
            $now = now();
            $isClosing = $status->isClosed() || $status === SearchStatus::LongTerm;
            $isReactivated = $status === SearchStatus::Active;
            $reactivatedKey = $isReactivated
                && $lockedCase->type === SearchCaseType::Lost
                && filled($lockedCase->pet_profile_key)
                    ? $lockedCase->owner_key.':'.$lockedCase->pet_profile_key
                    : null;

            if ($reactivatedKey !== null && SearchCase::query()
                ->where('active_key', $reactivatedKey)
                ->whereKeyNot($lockedCase->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'This pet already has another active search.',
                ]);
            }

            $lockedCase->update([
                'status' => $status,
                'active_key' => $status->isClosed()
                    ? null
                    : ($isReactivated ? $reactivatedKey : $lockedCase->active_key),
                'alerts_active' => $isReactivated ? true : ($isClosing || $status === SearchStatus::Paused ? false : $lockedCase->alerts_active),
                'volunteer_join_open' => $isReactivated ? true : ($isClosing ? false : $lockedCase->volunteer_join_open),
                'animal_secured' => in_array($status, [
                    SearchStatus::Safe,
                    SearchStatus::IdentityConfirmed,
                    SearchStatus::Returned,
                    SearchStatus::SelfReturned,
                ], true),
                'found_at' => in_array($status, [
                    SearchStatus::PossibleFound,
                    SearchStatus::Safe,
                    SearchStatus::IdentityConfirmed,
                ], true) ? ($searchCase->found_at ?? $now) : $searchCase->found_at,
                'returned_at' => in_array($status, [
                    SearchStatus::Returned,
                    SearchStatus::SelfReturned,
                ], true) ? $now : $searchCase->returned_at,
                'closed_at' => $status->isClosed() ? $now : null,
                'closure_reason' => $status->isClosed() ? $status->value : null,
                'latest_update' => $data['status_note'] ?? $status->label(),
            ]);

            if ($isClosing) {
                SearchAlert::query()
                    ->where('search_case_id', $searchCase->id)
                    ->whereIn('status', ['queued', 'sent'])
                    ->update(['status' => 'stopped', 'stopped_at' => $now]);
                SearchTask::query()
                    ->where('search_case_id', $searchCase->id)
                    ->whereNotIn('status', [
                        SearchTaskStatus::Completed->value,
                        SearchTaskStatus::Cancelled->value,
                    ])
                    ->update(['status' => SearchTaskStatus::Cancelled->value]);
                SearchVolunteer::query()
                    ->where('search_case_id', $searchCase->id)
                    ->where('status', SearchVolunteerStatus::Active->value)
                    ->update(['status' => SearchVolunteerStatus::Left->value]);
            } elseif ($isReactivated) {
                SearchAlert::query()->create([
                    'search_case_id' => $searchCase->id,
                    'kind' => 'search-reactivated',
                    'radius_km' => $lockedCase->notification_radius_km,
                    'region' => collect([$lockedCase->last_seen_area, $lockedCase->city])->join(', '),
                    'channels' => ['in-app', 'push'],
                    'audiences' => ['nearby-users', 'local-groups'],
                    'status' => 'queued',
                    'recipient_count' => 0,
                    'message' => $lockedCase->pet_name.' · search reactivated',
                ]);
            }

            SearchUpdate::query()->create([
                'search_case_id' => $searchCase->id,
                'author_key' => $this->actor->key(),
                'author_name' => $this->actor->identity()['name'],
                'type' => 'status-changed',
                'visibility' => 'public',
                'title' => $status->label(),
                'body' => $data['status_note'] ?? null,
                'public_area' => null,
                'occurred_at' => $now,
            ]);

            $this->audit('search-case.status-changed', $searchCase, [
                'from' => $searchCase->status->value,
                'to' => $status->value,
                'urgent_processes_stopped' => $isClosing,
            ]);

            return [
                'message' => $status->isClosed()
                    ? 'Search closed. Urgent alerts, open tasks, and temporary volunteer access were stopped.'
                    : 'Search status updated.',
                'search_case' => $searchCase->fresh(),
            ];
        });
    }

    private function validatedSectorId(SearchCase $searchCase, mixed $sectorId): ?int
    {
        if ($sectorId === null || $sectorId === '') {
            return null;
        }

        $exists = SearchSector::query()
            ->whereKey((int) $sectorId)
            ->where('search_case_id', $searchCase->id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['sector_id' => 'This sector belongs to another search.']);
        }

        return (int) $sectorId;
    }

    private function lockedTask(SearchCase $searchCase, int $taskId): SearchTask
    {
        return SearchTask::query()
            ->select([
                'id', 'search_case_id', 'search_sector_id', 'assignee_key',
                'assignee_name', 'status', 'safety_level', 'starts_at',
                'claimed_at', 'completed_at', 'result', 'version',
            ])
            ->where('search_case_id', $searchCase->id)
            ->lockForUpdate()
            ->findOrFail($taskId);
    }

    private function authorizeTaskAssignee(SearchCase $searchCase, SearchTask $task): void
    {
        if ($task->assignee_key !== $this->actor->key() && ! $searchCase->isManagedBy($this->actor->key())) {
            throw ValidationException::withMessages(['task_id' => 'This task is assigned to another volunteer.']);
        }
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, SearchCase $searchCase, array $metadata): void
    {
        AuditLog::query()->create([
            'actor_key' => $this->actor->key(),
            'actor_role' => $searchCase->isManagedBy($this->actor->key())
                ? 'search-coordinator'
                : 'search-volunteer',
            'action' => $action,
            'target_type' => SearchCase::class,
            'target_id' => (string) $searchCase->id,
            'metadata' => $metadata,
        ]);
    }
}
