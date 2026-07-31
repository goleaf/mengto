<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumBlock;
use App\Models\ForumMentorship;
use App\Models\User;
use App\Services\MentorshipAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class EndMentorship
{
    public function __construct(
        private Gate $gate,
        private MentorshipAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumMentorship $mentorship,
        bool $completed,
        string $reason,
        bool $blockCounterpart,
        int $expectedLockVersion,
    ): ForumMentorship {
        Validator::make([
            'reason' => $reason,
            'expected_lock_version' => $expectedLockVersion,
        ], [
            'reason' => ['required', 'string', 'min:2', 'max:2000'],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $blockCounterpart,
            $completed,
            $expectedLockVersion,
            $mentorship,
            $reason,
        ): ForumMentorship {
            $locked = ForumMentorship::query()
                ->lockForUpdate()
                ->findOrFail($mentorship->id);
            $this->gate->forUser($actor)->authorize('end', $locked);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.stale_mentorship'),
                ]);
            }

            $fromState = $locked->state;
            $toState = match (true) {
                $fromState === ForumMentorshipState::Requested => ForumMentorshipState::Cancelled,
                $completed => ForumMentorshipState::Completed,
                default => ForumMentorshipState::Ended,
            };

            $locked->update([
                'state' => $toState,
                'completed_at' => $toState === ForumMentorshipState::Completed ? now() : null,
                'ended_at' => now(),
                'ended_by_user_id' => $actor->id,
                'end_reason' => trim($reason),
                'open_key' => null,
                'lock_version' => $locked->lock_version + 1,
            ]);

            $eventType = $toState === ForumMentorshipState::Completed
                ? ForumMentorshipEventType::Completed
                : ForumMentorshipEventType::Ended;
            $this->audit->record(
                mentorship: $locked,
                actor: $actor,
                eventType: $eventType,
                reasonCode: $toState === ForumMentorshipState::Completed
                    ? 'participant-completed'
                    : 'participant-ended',
                summaryTranslationKey: $toState === ForumMentorshipState::Completed
                    ? 'forum_mentorship.events.completed'
                    : 'forum_mentorship.events.ended',
                fromState: $fromState,
                toState: $toState,
                idempotencyKey: "mentorship:{$locked->id}:ended",
            );

            if ($blockCounterpart) {
                $counterpartId = $locked->counterpartId($actor);
                $counterpart = $counterpartId === null
                    ? null
                    : User::query()->find($counterpartId);

                if ($counterpart instanceof User) {
                    ForumBlock::query()->updateOrCreate(
                        [
                            'user_key' => $actor->actor_key,
                            'blocked_author_key' => $counterpart->actor_key,
                        ],
                        ['reason' => 'mentorship-ended'],
                    );
                    $this->audit->record(
                        mentorship: $locked,
                        actor: $actor,
                        eventType: ForumMentorshipEventType::Blocked,
                        reasonCode: 'counterpart-blocked',
                        summaryTranslationKey: 'forum_mentorship.events.blocked',
                        metadata: ['counterpart_user_id' => $counterpart->id],
                        idempotencyKey: "mentorship:{$locked->id}:blocked:{$actor->id}",
                    );
                }
            }

            return $locked->refresh();
        }, 3);
    }
}
