<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationAction;
use App\Models\ForumModerationAppeal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

final class SubmitForumModerationAppeal
{
    /**
     * @param  array<string, mixed>  $evidence
     */
    public function handle(
        User $appellant,
        ForumModerationAction $action,
        string $reason,
        array $evidence = [],
    ): ForumModerationAppeal {
        if (
            ! $appellant->isActive()
            || $action->target_user_id !== $appellant->id
            || ! $action->appeal_available
        ) {
            throw new AuthorizationException;
        }

        if (mb_strlen(trim($reason)) < 20) {
            throw ValidationException::withMessages([
                'reason' => __('forum_moderation.validation.appeal_reason_length'),
            ]);
        }

        return ForumModerationAppeal::query()->firstOrCreate(
            [
                'forum_moderation_action_id' => $action->id,
                'appellant_user_id' => $appellant->id,
            ],
            [
                'status' => 'submitted',
                'reason' => trim($reason),
                'evidence' => $evidence,
                'submitted_at' => now(),
            ],
        );
    }
}
