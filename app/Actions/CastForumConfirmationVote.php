<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ConfirmationState;
use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationVote;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CastForumConfirmationVote
{
    private const STANCES = ['support', 'oppose', 'abstain'];

    private const OPEN_STATES = [
        ConfirmationState::AwaitingConfirmation,
        ConfirmationState::GatheringEvidence,
        ConfirmationState::CommunitySupported,
        ConfirmationState::Disputed,
    ];

    public function handle(
        User $voter,
        ForumConfirmation $confirmation,
        string $stance,
        string $independenceCluster,
        ?string $reasoning = null,
        bool $hasConflict = false,
        ?string $conflictType = null,
    ): ForumConfirmationVote {
        if (! $voter->isActive() || $voter->id === $confirmation->requester_user_id) {
            throw new AuthorizationException;
        }

        if (! in_array($stance, self::STANCES, true)) {
            throw ValidationException::withMessages([
                'stance' => __('forum_reputation.messages.invalid_confirmation_stance'),
            ]);
        }

        if ($hasConflict && blank($conflictType)) {
            throw ValidationException::withMessages([
                'conflict_type' => __('forum_reputation.messages.confirmation_conflict_required'),
            ]);
        }

        if (! $this->isEligibleReviewer($voter)) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use (
            $confirmation,
            $conflictType,
            $hasConflict,
            $independenceCluster,
            $reasoning,
            $stance,
            $voter,
        ): ForumConfirmationVote {
            $confirmation = ForumConfirmation::query()
                ->lockForUpdate()
                ->findOrFail($confirmation->id);

            if (
                ! in_array($confirmation->state, self::OPEN_STATES, true)
                || $confirmation->expires_at?->isPast() === true
            ) {
                throw ValidationException::withMessages([
                    'confirmation' => __('forum_reputation.messages.confirmation_closed'),
                ]);
            }

            if (ForumConfirmationVote::query()
                ->where('forum_confirmation_id', $confirmation->id)
                ->where('voter_user_id', $voter->id)
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'confirmation' => __('forum_reputation.messages.duplicate_confirmation_vote'),
                ]);
            }

            $vote = ForumConfirmationVote::query()->create([
                'forum_confirmation_id' => $confirmation->id,
                'voter_user_id' => $voter->id,
                'stance' => $stance,
                'weight' => 1,
                'has_conflict' => $hasConflict,
                'conflict_type' => $conflictType,
                'independence_cluster' => hash(
                    'sha256',
                    mb_strtolower(trim($independenceCluster)),
                ),
                'reasoning' => $reasoning,
                'status' => $hasConflict ? 'excluded-conflict' : 'eligible',
            ]);

            $this->recalculate($confirmation);

            return $vote;
        }, 3);
    }

    private function isEligibleReviewer(User $voter): bool
    {
        if ($voter->isAdministrator()) {
            return true;
        }

        if ($voter->created_at?->lessThanOrEqualTo(now()->subDays(7)) === true) {
            return true;
        }

        return ForumUserTrustLevel::query()
            ->where('user_id', $voter->id)
            ->where(static function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    private function recalculate(ForumConfirmation $confirmation): void
    {
        $votes = ForumConfirmationVote::query()
            ->where('forum_confirmation_id', $confirmation->id)
            ->where('status', 'eligible')
            ->get(['stance', 'weight', 'independence_cluster']);
        $supporting = $votes->where('stance', 'support')->count();
        $opposing = $votes->where('stance', 'oppose')->count();
        $abstentions = $votes->where('stance', 'abstain')->count();
        $eligibleDecisions = $supporting + $opposing;
        $confidence = $eligibleDecisions === 0
            ? 0
            : round($supporting / $eligibleDecisions, 4);
        $supportDiversity = $votes
            ->where('stance', 'support')
            ->pluck('independence_cluster')
            ->unique()
            ->count();
        $state = ConfirmationState::GatheringEvidence;
        $restrictedRisk = in_array($confirmation->risk_class, [
            'medical',
            'legal',
            'public-health',
        ], true);

        if ($opposing >= $confirmation->required_quorum) {
            $state = ConfirmationState::Disputed;
        } elseif (
            $supporting >= $confirmation->required_quorum
            && $supportDiversity >= $confirmation->required_diversity
        ) {
            $state = $restrictedRisk
                ? ConfirmationState::CommunitySupported
                : ConfirmationState::CommunityConfirmed;
        } elseif ($supporting > $opposing) {
            $state = ConfirmationState::CommunitySupported;
        }

        $confirmation->forceFill([
            'state' => $state,
            'confidence' => $confidence,
            'supporting_votes' => $supporting,
            'opposing_votes' => $opposing,
            'abstentions' => $abstentions,
            'decided_at' => in_array($state, [
                ConfirmationState::CommunityConfirmed,
                ConfirmationState::Disputed,
            ], true) ? now() : null,
        ])->save();
    }
}
