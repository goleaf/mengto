<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumEventLifecycleState;
use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventVersion;
use App\Models\User;
use App\Services\ForumEventLifecycleSnapshot;
use Illuminate\Support\Facades\DB;

final readonly class InitializeForumEventLifecycle
{
    public function __construct(private ForumEventLifecycleSnapshot $snapshots) {}

    public function handle(
        ForumEvent $event,
        ?User $actor = null,
        string $reasonCode = 'lifecycle-initialized',
    ): ForumEventLifecycleState {
        $actorId = $actor === null ? null : $actor->id;

        return DB::transaction(function () use ($actorId, $event, $reasonCode): ForumEventLifecycleState {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);

            if ($locked->owner_user_id === null && $locked->organizer_user_id !== null) {
                $locked->forceFill(['owner_user_id' => $locked->organizer_user_id])->save();
            }

            $snapshot = $this->snapshots->event($locked);
            $version = ForumEventVersion::query()->firstOrCreate(
                [
                    'forum_event_id' => $locked->id,
                    'version_number' => $locked->current_version_number,
                ],
                [
                    'created_by_user_id' => $actorId ?? $locked->organizer_user_id,
                    'kind' => $locked->published_at === null ? 'draft' : 'published',
                    'reason_code' => $reasonCode,
                    'snapshot' => $snapshot,
                    'snapshot_checksum' => $this->snapshots->checksum($snapshot),
                    'material_fields' => [],
                    'published_at' => $locked->published_at,
                    'created_at' => now(),
                ],
            );
            $occurrence = ForumEventOccurrence::query()->firstOrCreate(
                ['stable_key' => $locked->stable_key.'-occurrence-1'],
                [
                    'forum_event_id' => $locked->id,
                    'status' => $locked->status,
                    'starts_at' => $locked->starts_at,
                    'ends_at' => $locked->ends_at,
                    'timezone' => $locked->timezone,
                    'format' => $locked->format,
                    'capacity' => $locked->capacity,
                    'location_scope' => $locked->location_scope,
                    'exact_location' => $locked->exact_location,
                    'online_url' => $locked->online_url,
                    'is_override' => false,
                ],
            );

            if ($locked->owner_user_id !== null) {
                ForumEventTeamMembership::query()->firstOrCreate(
                    [
                        'forum_event_id' => $locked->id,
                        'user_id' => $locked->owner_user_id,
                        'role' => ForumEventTeamRole::Owner->value,
                    ],
                    [
                        'invited_by_user_id' => $actorId,
                        'status' => ForumEventTeamMembershipStatus::Active,
                        'starts_at' => $locked->created_at,
                    ],
                );
            }

            return new ForumEventLifecycleState($occurrence, $version);
        }, 3);
    }
}
