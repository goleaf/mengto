<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorship;
use App\Models\User;
use App\Services\MentorshipAudit;
use App\Services\MentorshipEligibility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RespondToMentorship
{
    public function __construct(
        private Gate $gate,
        private MentorshipEligibility $eligibility,
        private MentorshipAudit $audit,
    ) {}

    public function handle(
        User $mentor,
        ForumMentorship $mentorship,
        bool $accept,
        string $response,
        bool $safetyAcknowledged,
        int $expectedLockVersion,
    ): ForumMentorship {
        Validator::make([
            'response' => $response,
            'expected_lock_version' => $expectedLockVersion,
        ], [
            'response' => ['required', 'string', 'min:2', 'max:2000'],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
        ])->validate();

        if ($accept && ! $safetyAcknowledged) {
            throw ValidationException::withMessages([
                'safetyAcknowledged' => __('forum_mentorship.validation.safety_required'),
            ]);
        }

        return DB::transaction(function () use (
            $accept,
            $expectedLockVersion,
            $mentor,
            $mentorship,
            $response,
        ): ForumMentorship {
            $profile = ForumMentorProfile::query()
                ->where('user_id', $mentorship->mentor_user_id)
                ->lockForUpdate()
                ->firstOrFail();
            $locked = ForumMentorship::query()
                ->lockForUpdate()
                ->findOrFail($mentorship->id);
            $this->gate->forUser($mentor)->authorize('respond', $locked);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.stale_mentorship'),
                ]);
            }

            $fromState = $locked->state;

            if ($accept && ! $this->eligibility->profileHasActiveCapacity($profile)) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.capacity_reached'),
                ]);
            }

            $toState = $accept
                ? ForumMentorshipState::Active
                : ForumMentorshipState::Declined;
            $locked->update([
                'state' => $toState,
                'mentor_response' => trim($response),
                'mentor_safety_acknowledged_at' => $accept ? now() : null,
                'accepted_at' => $accept ? now() : null,
                'declined_at' => $accept ? null : now(),
                'ended_at' => $accept ? null : now(),
                'open_key' => $accept ? $locked->open_key : null,
                'lock_version' => $locked->lock_version + 1,
            ]);

            $this->audit->record(
                mentorship: $locked,
                actor: $mentor,
                eventType: $accept
                    ? ForumMentorshipEventType::Accepted
                    : ForumMentorshipEventType::Declined,
                reasonCode: $accept ? 'mentor-accepted' : 'mentor-declined',
                summaryTranslationKey: $accept
                    ? 'forum_mentorship.events.accepted'
                    : 'forum_mentorship.events.declined',
                fromState: $fromState,
                toState: $toState,
                idempotencyKey: "mentorship:{$locked->id}:response",
            );

            return $locked->refresh();
        }, 3);
    }
}
