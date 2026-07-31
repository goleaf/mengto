<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumMentorship;
use App\Models\ForumMentorshipMessage;
use App\Models\User;
use App\Services\MentorshipEligibility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class SendMentorshipMessage
{
    public function __construct(
        private Gate $gate,
        private MentorshipEligibility $eligibility,
    ) {}

    public function handle(
        User $sender,
        ForumMentorship $mentorship,
        string $body,
        string $idempotencyKey,
    ): ForumMentorshipMessage {
        Validator::make([
            'body' => $body,
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:2', 'max:4000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        $existing = ForumMentorshipMessage::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing instanceof ForumMentorshipMessage) {
            if (
                $existing->forum_mentorship_id !== $mentorship->id
                || $existing->sender_user_id !== $sender->id
            ) {
                throw ValidationException::withMessages([
                    'idempotency_key' => __('forum_mentorship.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        return DB::transaction(function () use (
            $body,
            $idempotencyKey,
            $mentorship,
            $sender,
        ): ForumMentorshipMessage {
            $locked = ForumMentorship::query()
                ->lockForUpdate()
                ->findOrFail($mentorship->id);
            $this->gate->forUser($sender)->authorize('message', $locked);
            $counterpartId = $locked->counterpartId($sender);
            $counterpart = $counterpartId === null
                ? null
                : User::query()->find($counterpartId);

            if (
                ! $counterpart instanceof User
                || $this->eligibility->usersBlockEachOther($sender, $counterpart)
            ) {
                throw ValidationException::withMessages([
                    'message' => __('forum_mentorship.validation.contact_unavailable'),
                ]);
            }

            return ForumMentorshipMessage::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'forum_mentorship_id' => $locked->id,
                    'sender_user_id' => $sender->id,
                    'body' => trim($body),
                    'created_at' => now(),
                ],
            );
        }, 3);
    }
}
