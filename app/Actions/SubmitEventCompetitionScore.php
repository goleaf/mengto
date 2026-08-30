<?php
declare(strict_types=1);
namespace App\Actions;
use App\Models\{EventCompetition,EventCompetitionCriterion,EventCompetitionEntry,EventCompetitionJudgeAssignment,EventCompetitionScore,EventCompetitionScoreRevision,User};
use Illuminate\Auth\Access\AuthorizationException; use Illuminate\Support\Facades\DB; use Illuminate\Validation\ValidationException;
final class SubmitEventCompetitionScore {
 public function handle(User $actor, EventCompetitionJudgeAssignment $assignment, EventCompetitionEntry $entry, EventCompetitionCriterion $criterion, int $units, ?string $comment, string $key): EventCompetitionScore {
  if($assignment->judge_user_id!==$actor->id || !$assignment->identity_verified || $assignment->status!=='active'){throw new AuthorizationException;}
  return DB::transaction(function()use($actor,$assignment,$entry,$criterion,$units,$comment,$key){$a=EventCompetitionJudgeAssignment::query()->lockForUpdate()->findOrFail($assignment->id);$e=EventCompetitionEntry::query()->lockForUpdate()->findOrFail($entry->id);$c=EventCompetitionCriterion::query()->lockForUpdate()->findOrFail($criterion->id);$competition=EventCompetition::query()->lockForUpdate()->findOrFail($a->competition_id);if($competition->status->value!=='judging_open'||$a->category_id!==$e->category_id||$c->category_id!==$e->category_id||$e->eligibility_status!=='eligible'||$a->conflicts()->where('entry_id',$e->id)->whereIn('status',['open','confirmed'])->exists()||($a->scoring_closes_at?->isPast()??false)){throw new ValidationException(validator([],[]));}if($units<$c->minimum_units||$units>$c->maximum_units||($c->comment_required&&blank($comment))){throw new ValidationException(validator([],[]));}$score=EventCompetitionScore::query()->firstOrCreate(['judge_assignment_id'=>$a->id,'entry_id'=>$e->id,'criterion_id'=>$c->id]);if($score->revisions()->exists()){throw new ValidationException(validator([],[]));}$score->revisions()->create(['revision_number'=>1,'value_units'=>$units,'comment'=>$comment,'actor_user_id'=>$actor->id,'reason_code'=>'score-submitted','idempotency_key'=>$key,'created_at'=>now()]);return $score;},3);
 }
}
