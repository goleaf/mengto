<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertSessionStatus;
use App\Models\ForumExpertSession;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ArchiveForumExpertSession
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumExpertSession $session,
        string $reasonCode,
        int $expectedLockVersion,
    ): ForumExpertSession {
        $this->gate->forUser($actor)->authorize('archive', $session);
        $validated = validator([
            'reason_code' => $reasonCode,
            'lock_version' => $expectedLockVersion,
        ], [
            'reason_code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ])->validate();

        return DB::transaction(function () use ($actor, $expectedLockVersion, $session, $validated): ForumExpertSession {
            $locked = ForumExpertSession::query()
                ->with('expertProfile')
                ->lockForUpdate()
                ->findOrFail($session->id);
            $this->gate->forUser($actor)->authorize('archive', $locked);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'archive' => __('forum_expert_sessions.validation.concurrent_change'),
                ]);
            }

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => ForumExpertSessionStatus::Archived,
                'archived_by_user_id' => $actor->id,
                'archived_at' => now(),
                'archive_reason_code' => $validated['reason_code'],
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                session: $locked,
                actor: $actor,
                eventType: 'archived',
                reasonCode: $validated['reason_code'],
                summaryTranslationKey: 'forum_expert_sessions.history.archived',
                fromStatus: $fromStatus,
                toStatus: ForumExpertSessionStatus::Archived->value,
                idempotencyKey: "expert-session:{$locked->id}:archived",
            );

            return $locked;
        }, 3);
    }
}
