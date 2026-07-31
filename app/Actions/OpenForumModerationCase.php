<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OpenForumModerationCase
{
    public function handle(User $actor, ForumReport $report): ForumModerationCase
    {
        if (! $actor->isAdministrator()) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $report): ForumModerationCase {
            $report = ForumReport::query()->lockForUpdate()->findOrFail($report->id);
            $existing = $report->moderationCases()->first();

            if ($existing instanceof ForumModerationCase) {
                return $existing;
            }

            $case = ForumModerationCase::query()->create([
                'case_number' => 'MOD-'.Str::upper((string) Str::ulid()),
                'status' => 'awaiting-review',
                'priority' => $report->priority,
                'opened_by_user_id' => $actor->id,
                'subject_type' => $report->subject_type,
                'subject_id' => $report->subject_id,
                'summary_translation_key' => 'forum_moderation.messages.case_opened',
                'review_due_at' => $report->priority === 'critical'
                    ? now()->addHour()
                    : now()->addDays(3),
                'retention_until' => now()->addYears(2),
                'metadata' => ['automatic_conviction' => false],
            ]);
            $case->reports()->attach($report->id, [
                'linked_by_user_id' => $actor->id,
                'created_at' => now(),
            ]);
            $report->forceFill(['status' => 'awaiting-review'])->save();
            ForumReportEvent::query()->create([
                'forum_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'event_type' => 'case-opened',
                'from_status' => 'received',
                'to_status' => 'awaiting-review',
                'user_message_translation_key' => 'forum_moderation.messages.case_opened',
                'metadata' => ['case_number' => $case->case_number],
                'created_at' => now(),
            ]);

            return $case;
        }, 3);
    }
}
