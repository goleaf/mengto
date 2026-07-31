<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumVisibility;
use App\Models\ForumGroup;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssociateForumTopicWithGroup
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        ForumTopic $topic,
    ): ForumTopic {
        $this->gate->forUser($actor)->authorize('createContent', $group);
        $this->gate->forUser($actor)->authorize('update', $topic);

        return DB::transaction(function () use ($actor, $group, $topic): ForumTopic {
            $lockedTopic = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);
            $this->gate->forUser($actor)->authorize('createContent', $group);
            $this->gate->forUser($actor)->authorize('update', $lockedTopic);

            if ($lockedTopic->forum_group_id !== null
                && $lockedTopic->forum_group_id !== $group->id
            ) {
                throw ValidationException::withMessages([
                    'topic' => __('forum_polls.validation.topic_already_grouped'),
                ]);
            }

            $lockedTopic->forceFill([
                'forum_group_id' => $group->id,
                'visibility' => ForumVisibility::Group,
            ])->save();

            return $lockedTopic->refresh();
        }, 3);
    }
}
