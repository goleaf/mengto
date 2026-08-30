<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\EventCompetition;
use App\Models\EventCompetitionCategory;
use App\Models\EventCompetitionEntry;
use App\Models\ForumEventRegistration;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateEventCompetitionEntry
{
    public function handle(User $actor, EventCompetition $competition, EventCompetitionCategory $category, ForumEventRegistration $registration, string $displayName, string $idempotencyKey): EventCompetitionEntry
    {
        if ($registration->user_id !== $actor->id || $category->competition_id !== $competition->id || $registration->forum_event_id !== $competition->forum_event_id) { throw new AuthorizationException; }
        if (mb_strlen(trim($displayName)) < 3 || mb_strlen($idempotencyKey) < 16) { throw ValidationException::withMessages(['entry' => __('forum_events.validation.invalid_transition')]); }
        return DB::transaction(function () use ($actor, $competition, $category, $registration, $displayName): EventCompetitionEntry {
            $entry = EventCompetitionEntry::query()->firstOrCreate(['competition_id' => $competition->id, 'forum_event_registration_id' => $registration->id], ['category_id' => $category->id, 'entrant_user_id' => $actor->id, 'rule_version_id' => $competition->ruleVersions()->max('id'), 'stable_key' => 'event-competition-entry-'.Str::lower((string) Str::ulid()), 'display_name' => trim($displayName), 'status' => 'eligible', 'eligibility_status' => 'eligible', 'eligibility_snapshot' => ['registration_id' => $registration->id], 'rules_accepted_at' => now()]);
            return $entry;
        }, 3);
    }
}
