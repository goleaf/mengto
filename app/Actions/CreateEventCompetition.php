<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EventCompetitionStatus;
use App\Models\EventCompetition;
use App\Models\EventCompetitionHistory;
use App\Models\EventCompetitionRuleVersion;
use App\Models\ForumEvent;
use App\Models\User;
use App\Policies\ForumEventPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateEventCompetition
{
    public function handle(User $actor, ForumEvent $event, string $name, string $rules, string $idempotencyKey): EventCompetition
    {
        if (! app(ForumEventPolicy::class)->update($actor, $event)) { throw new AuthorizationException; }
        if (mb_strlen(trim($name)) < 3 || mb_strlen(trim($rules)) < 10 || mb_strlen($idempotencyKey) < 16) { throw ValidationException::withMessages(['competition' => __('forum_events.validation.invalid_transition')]); }
        return DB::transaction(function () use ($actor, $event, $name, $rules, $idempotencyKey): EventCompetition {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $existing = EventCompetitionHistory::query()->where('idempotency_key', 'competition:create:'.$idempotencyKey)->first();
            if ($existing !== null) { return EventCompetition::query()->findOrFail((int) $existing->metadata['competition_id']); }
            $competition = EventCompetition::query()->create(['forum_event_id' => $locked->id, 'organizer_user_id' => $actor->id, 'stable_key' => 'event-competition-'.Str::lower((string) Str::ulid()), 'name' => trim($name), 'status' => EventCompetitionStatus::Draft, 'result_visibility' => 'private']);
            EventCompetitionRuleVersion::query()->create(['competition_id' => $competition->id, 'version_number' => 1, 'rules' => trim($rules), 'checksum' => hash('sha256', trim($rules)), 'created_by_user_id' => $actor->id, 'created_at' => now()]);
            EventCompetitionHistory::query()->create(['competition_id' => $competition->id, 'actor_user_id' => $actor->id, 'event_type' => 'competition-created', 'reason_code' => 'competition-created', 'metadata' => ['competition_id' => $competition->id], 'idempotency_key' => 'competition:create:'.$idempotencyKey, 'created_at' => now()]);
            return $competition;
        }, 3);
    }
}
