<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RedirectForumTopic
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
    ) {}

    public function handle(
        User $actor,
        ForumTopic $source,
        ForumTopic $target,
        ForumTopicStatus $redirectStatus,
        string $reasonCode,
        int $expectedLockVersion,
    ): ForumTopic {
        $reasonCode = (string) Validator::make(
            ['reason_code' => trim($reasonCode)],
            ['reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/']],
        )->validate()['reason_code'];
        $redirectStatus = $redirectStatus->canonical();

        if (! in_array($redirectStatus, [
            ForumTopicStatus::Merged,
            ForumTopicStatus::Redirected,
        ], true)) {
            throw ValidationException::withMessages([
                'target' => __('forum_topic_lifecycle.validation.redirect_state'),
            ]);
        }

        $this->gate->forUser($actor)->authorize('redirect', $source);
        $this->gate->forUser($actor)->authorize('view', $target);

        return DB::transaction(function () use (
            $actor,
            $source,
            $target,
            $redirectStatus,
            $reasonCode,
            $expectedLockVersion,
        ): ForumTopic {
            $topics = ForumTopic::query()
                ->whereKey([$source->id, $target->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lockedSource = $topics->get($source->id);
            $lockedTarget = $topics->get($target->id);

            if (
                ! $lockedSource instanceof ForumTopic
                || ! $lockedTarget instanceof ForumTopic
                || $lockedSource->id === $lockedTarget->id
            ) {
                throw ValidationException::withMessages([
                    'target' => __('forum_topic_lifecycle.validation.redirect_target'),
                ]);
            }

            if ($lockedSource->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('forum_topic_lifecycle.validation.stale_state'),
                ]);
            }

            if (
                ! $lockedTarget->status->isPubliclyVisible()
                || in_array(
                    $lockedSource->id,
                    $lockedTarget->redirect_path ?? [],
                    true,
                )
            ) {
                throw ValidationException::withMessages([
                    'target' => __('forum_topic_lifecycle.validation.redirect_target'),
                ]);
            }

            $lockedSource->forceFill([
                'merged_into_topic_id' => $lockedTarget->id,
                'redirect_path' => [
                    $lockedTarget->id,
                    ...($lockedTarget->redirect_path ?? []),
                ],
            ])->save();

            return $this->lifecycle->transition(
                topic: $lockedSource,
                target: $redirectStatus,
                actor: $actor,
                reasonCode: $reasonCode,
                expectedLockVersion: $expectedLockVersion,
                metadata: ['target_topic_id' => $lockedTarget->id],
                eventType: $redirectStatus === ForumTopicStatus::Merged
                    ? ForumTopicLifecycleEventType::Merged
                    : ForumTopicLifecycleEventType::Redirected,
                administrativeOverride: true,
            );
        }, 3);
    }
}
