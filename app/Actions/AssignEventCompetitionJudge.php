<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\EventCompetition;
use App\Models\EventCompetitionCategory;
use App\Models\EventCompetitionJudgeAssignment;
use App\Models\ForumEventTeamMembership;
use App\Models\User;
use App\Policies\EventCompetitionPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class AssignEventCompetitionJudge
{
    public function handle(User $actor, EventCompetition $competition, EventCompetitionCategory $category, User $judge, ForumEventTeamMembership $membership, bool $identityVerified): EventCompetitionJudgeAssignment
    {
        if (! app(EventCompetitionPolicy::class)->manage($actor, $competition) || $category->competition_id !== $competition->id || $membership->forum_event_id !== $competition->forum_event_id || $membership->user_id !== $judge->id || $membership->role->value !== 'judge' || $membership->status->value !== 'active') { throw new AuthorizationException; }
        return DB::transaction(fn () => EventCompetitionJudgeAssignment::query()->firstOrCreate(['competition_id' => $competition->id, 'category_id' => $category->id, 'judge_user_id' => $judge->id], ['forum_event_team_membership_id' => $membership->id, 'status' => 'active', 'identity_verified' => $identityVerified]), 3);
    }
}
