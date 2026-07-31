<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumExpertAnswerStatus;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Models\ForumExpertSessionAnswer;
use App\Models\User;

final readonly class ForumExpertSessionAnswerPolicy
{
    public function __construct(
        private ForumExpertSessionPolicy $sessions,
    ) {}

    public function view(?User $user, ForumExpertSessionAnswer $answer): bool
    {
        if (
            $user?->isAdministrator() === true
            || $answer->author_user_id === $user?->id
            || ($user !== null && $this->sessions->moderate($user, $answer->session))
        ) {
            return true;
        }

        return $answer->status !== ForumExpertAnswerStatus::Withdrawn
            && $answer->question->moderation_status === ForumExpertQuestionModerationStatus::Approved
            && $this->sessions->view($user, $answer->session);
    }

    public function correct(?User $user, ForumExpertSessionAnswer $answer): bool
    {
        if ($user?->isAdministrator() === true) {
            return $answer->status !== ForumExpertAnswerStatus::Withdrawn;
        }

        return $answer->status !== ForumExpertAnswerStatus::Withdrawn
            && $this->sessions->answer($user, $answer->session)
            && $answer->author_user_id === $user?->id;
    }

    public function report(?User $user, ForumExpertSessionAnswer $answer): bool
    {
        return $user?->isActive() === true && $this->view($user, $answer);
    }
}
