<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CloseForumModerationCase
{
    private const MAX_INTERACTIVE_REPORTS = 100;

    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        ForumModerationCase $case,
        int $expectedLockVersion,
        string $idempotencyKey,
    ): ForumModerationCase {
        $idempotencyKey = trim($idempotencyKey);

        if (
            mb_strlen($idempotencyKey) < 16
            || mb_strlen($idempotencyKey) > 190
        ) {
            throw ValidationException::withMessages([
                'idempotency_key' => __(
                    'forum_moderation.validation.invalid_closure_idempotency_key',
                ),
            ]);
        }

        $this->gate->forUser($actor)->authorize('update', $case);

        return DB::transaction(function () use (
            $actor,
            $case,
            $expectedLockVersion,
            $idempotencyKey,
        ): ForumModerationCase {
            $locked = ForumModerationCase::query()
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($actor)->authorize('update', $locked);

            if (
                $locked->closed_at !== null
                && is_string($locked->closure_idempotency_key)
                && hash_equals(
                    $locked->closure_idempotency_key,
                    $idempotencyKey,
                )
            ) {
                return $locked;
            }

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __(
                        'forum_moderation.validation.case_version_conflict',
                    ),
                ]);
            }

            if ($locked->closed_at !== null) {
                throw ValidationException::withMessages([
                    'case' => __(
                        'forum_moderation.validation.case_already_closed',
                    ),
                ]);
            }

            if ($locked->resolved_at === null) {
                throw ValidationException::withMessages([
                    'case' => __(
                        'forum_moderation.validation.case_resolution_required',
                    ),
                ]);
            }

            if (ForumModerationCase::query()
                ->where('closure_idempotency_key', $idempotencyKey)
                ->where('id', '!=', $locked->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'idempotency_key' => __(
                        'forum_moderation.validation.closure_idempotency_key_conflict',
                    ),
                ]);
            }

            $locked->forceFill([
                'status' => 'closed',
                'closed_at' => now(),
                'lock_version' => $locked->lock_version + 1,
                'closure_idempotency_key' => $idempotencyKey,
            ])->save();

            $reports = $locked->reports()
                ->select(['forum_reports.id', 'forum_reports.status'])
                ->orderBy('forum_reports.id')
                ->limit(self::MAX_INTERACTIVE_REPORTS + 1)
                ->lockForUpdate()
                ->get();

            if ($reports->count() > self::MAX_INTERACTIVE_REPORTS) {
                throw ValidationException::withMessages([
                    'case' => __(
                        'forum_moderation.validation.case_report_limit',
                    ),
                ]);
            }

            if ($reports->isNotEmpty()) {
                $createdAt = now();
                $metadata = json_encode(
                    ['moderation_case_id' => $locked->id],
                    JSON_THROW_ON_ERROR,
                );
                $events = $reports->map(
                    static fn (ForumReport $report): array => [
                        'forum_report_id' => $report->id,
                        'actor_user_id' => $actor->id,
                        'event_type' => 'moderation-case-closed',
                        'from_status' => $report->status,
                        'to_status' => $report->status,
                        'user_message_translation_key' => 'forum_moderation.messages.case_closed',
                        'metadata' => $metadata,
                        'created_at' => $createdAt,
                    ],
                )->all();

                ForumReportEvent::query()->insert($events);
            }

            return $locked;
        }, 3);
    }
}
