<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;

final readonly class ForumExpertSessionQuestionPolicy
{
    public function __construct(
        private ForumExpertSessionPolicy $sessions,
    ) {}

    public function view(?User $user, ForumExpertSessionQuestion $question): bool
    {
        if (
            $user?->isAdministrator() === true
            || $question->author_user_id === $user?->id
            || ($user !== null && $this->sessions->moderate($user, $question->session))
        ) {
            return true;
        }

        return $question->moderation_status === ForumExpertQuestionModerationStatus::Approved
            && ! in_array($question->status, [
                ForumExpertQuestionStatus::Withdrawn,
                ForumExpertQuestionStatus::Removed,
            ], true)
            && $this->sessions->view($user, $question->session);
    }

    public function withdraw(?User $user, ForumExpertSessionQuestion $question): bool
    {
        return $user?->isActive() === true
            && $question->author_user_id === $user->id
            && in_array($question->status, [
                ForumExpertQuestionStatus::Queued,
                ForumExpertQuestionStatus::Selected,
            ], true);
    }

    public function report(?User $user, ForumExpertSessionQuestion $question): bool
    {
        return $user?->isActive() === true
            && $this->view($user, $question);
    }
}
