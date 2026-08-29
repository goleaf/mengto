<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use App\Services\ForumTopicLifecycleProjection;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ChangeForumTopicState
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
        private ForumTopicLifecycleProjection $projection,
    ) {}

    public function handle(
        User $actor,
        ForumTopic $topic,
        ForumTopicStatus $target,
        string $reasonCode,
        ?int $expectedLockVersion = null,
    ): ForumTopic {
        $reasonCode = (string) Validator::make(
            ['reason_code' => trim($reasonCode)],
            ['reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/']],
        )->validate()['reason_code'];
        $target = $target->canonical();
        $ability = $actor->isAdministrator()
            ? 'moderateLifecycle'
            : match ($target) {
                ForumTopicStatus::Restored => 'restore',
                ForumTopicStatus::Archived => 'archive',
                ForumTopicStatus::Removed => 'remove',
                ForumTopicStatus::Open => 'reopen',
                ForumTopicStatus::Solved,
                ForumTopicStatus::PartiallySolved,
                ForumTopicStatus::Outdated => 'update',
                default => 'moderateLifecycle',
            };
        $this->gate->forUser($actor)->authorize($ability, $topic);
        $snapshot = $this->projection->snapshot($topic);

        if (! $actor->isAdministrator()) {
            if (
                $target === ForumTopicStatus::Archived
                && ! $snapshot->allowsAuthorArchive
            ) {
                $this->notAllowed();
            }

            if (
                $target === ForumTopicStatus::Removed
                && ! $snapshot->allowsAuthorRemove
            ) {
                $this->notAllowed();
            }

            if (
                $target === ForumTopicStatus::Open
                && ! $snapshot->allowsAuthorReopen
            ) {
                $this->notAllowed();
            }
        }

        return $this->lifecycle->transition(
            topic: $topic,
            target: $target,
            actor: $actor,
            reasonCode: $reasonCode,
            expectedLockVersion: $expectedLockVersion,
            administrativeOverride: $actor->isAdministrator(),
        );
    }

    private function notAllowed(): never
    {
        throw ValidationException::withMessages([
            'status' => __('forum_topic_lifecycle.validation.category_rule'),
        ]);
    }
}
