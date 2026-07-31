<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ConfirmationState;
use App\Models\ForumConfirmation;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumConfirmation
{
    private const RISK_CLASSES = [
        'low',
        'medical',
        'legal',
        'public-health',
        'safety',
    ];

    public function __construct(private Gate $gate) {}

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $structuredClaim
     */
    public function handle(
        User $requester,
        string $subjectType,
        string|int $subjectId,
        string $riskClass,
        ?string $claimText,
        array $structuredClaim = [],
        array $scope = [],
        int $requiredQuorum = 3,
        int $requiredDiversity = 2,
    ): ForumConfirmation {
        if (! $requester->isActive()) {
            throw new AuthorizationException;
        }

        if (! in_array($riskClass, self::RISK_CLASSES, true)) {
            throw ValidationException::withMessages([
                'risk_class' => __('forum_reputation.messages.invalid_confirmation_risk'),
            ]);
        }

        if ($subjectType === 'forum-topic') {
            $topic = ForumTopic::query()->findOrFail($subjectId);
            $this->gate->forUser($requester)->authorize('view', $topic);
        }

        if ($requiredQuorum < 2 || $requiredQuorum > 50) {
            throw ValidationException::withMessages([
                'required_quorum' => __('forum_reputation.messages.invalid_confirmation_quorum'),
            ]);
        }

        if ($requiredDiversity < 2 || $requiredDiversity > $requiredQuorum) {
            throw ValidationException::withMessages([
                'required_diversity' => __('forum_reputation.messages.invalid_confirmation_diversity'),
            ]);
        }

        return ForumConfirmation::query()->create([
            'subject_type' => $subjectType,
            'subject_id' => (string) $subjectId,
            'requester_user_id' => $requester->id,
            'state' => ConfirmationState::AwaitingConfirmation,
            'claim_text' => $claimText,
            'structured_claim' => $structuredClaim,
            'scope' => $scope,
            'risk_class' => $riskClass,
            'required_quorum' => $requiredQuorum,
            'required_diversity' => $requiredDiversity,
            'confidence' => 0,
            'supporting_votes' => 0,
            'opposing_votes' => 0,
            'abstentions' => 0,
            'review_deadline_at' => now()->addDays(14),
            'expires_at' => now()->addDays(30),
            'metadata' => [
                'medical_diagnosis_confirmable' => false,
                'legal_advice_confirmable' => false,
            ],
        ]);
    }
}
