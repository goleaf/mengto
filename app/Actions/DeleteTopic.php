<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Services\ForumActor;

final readonly class DeleteTopic
{
    public function __construct(
        private ChangeForumTopicState $changeState,
        private ForumActor $actor,
    ) {}

    public function handle(ForumTopic $topic): ForumTopic
    {
        return $this->changeState->handle(
            actor: $this->actor->requireUser(),
            topic: $topic,
            target: ForumTopicStatus::Removed,
            reasonCode: 'author-removed',
            expectedLockVersion: $topic->lock_version,
        );
    }
}
