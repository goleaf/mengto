<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\SearchCase;
use App\Models\SearchReport;
use App\Models\Sighting;
use App\Services\ForumActor;
use App\Services\ForumReportReasonCatalog;
use App\Services\SearchSafety;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateSearchReport
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly SearchSafety $safety,
        private readonly ForumReportReasonCatalog $reasons,
        private readonly SubmitForumReport $submitForumReport,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(SearchCase $searchCase, array $data): SearchReport
    {
        if (isset($data['sighting_id'])) {
            $belongsToCase = $searchCase->sightings()
                ->whereKey((int) $data['sighting_id'])
                ->exists();

            if (! $belongsToCase) {
                throw ValidationException::withMessages([
                    'sighting_id' => __('messages.the_selected_sighting_does_not_belong_to_this_search_e5f40bd2e8'),
                ]);
            }
        }

        $reporter = $this->actor->requireUser();
        $sighting = isset($data['sighting_id'])
            ? Sighting::query()->findOrFail((int) $data['sighting_id'])
            : null;

        return DB::transaction(function () use (
            $data,
            $reporter,
            $searchCase,
            $sighting,
        ): SearchReport {
            $canonicalReason = $this->reasons->canonicalKey((string) $data['reason']);
            $forumReport = $this->submitForumReport->handle(
                reporter: $reporter,
                subject: $sighting ?? $searchCase,
                reasonKey: $canonicalReason,
                details: $data['details'] ?? null,
                truthfulnessConfirmed: (bool) $data['truthfulness_confirmed'],
                immediateSafety: (bool) ($data['immediate_safety'] ?? false),
                locationScope: $searchCase->city,
                metadata: ['search_case_id' => $searchCase->id],
            );
            $report = SearchReport::query()->create([
                'search_case_id' => $searchCase->id,
                'sighting_id' => $sighting?->id,
                'forum_report_id' => $forumReport->id,
                'reporter_id' => $reporter->id,
                'reporter_key' => $reporter->actor_key,
                'reason' => $canonicalReason,
                'details' => $data['details'] ?? null,
                'priority' => $this->safety->priority(['reason' => $canonicalReason]),
                'status' => 'open',
            ]);

            AuditLog::query()->create([
                'actor_key' => $reporter->actor_key,
                'actor_role' => 'community-member',
                'action' => 'search-report.created',
                'target_type' => SearchReport::class,
                'target_id' => (string) $report->id,
                'metadata' => [
                    'search_case_id' => $searchCase->id,
                    'sighting_id' => $report->sighting_id,
                    'forum_report_id' => $forumReport->id,
                    'reason' => $report->reason,
                    'priority' => $report->priority,
                ],
            ]);

            return $report;
        }, 3);
    }
}
