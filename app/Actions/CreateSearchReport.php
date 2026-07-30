<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\SearchCase;
use App\Models\SearchReport;
use App\Services\ForumActor;
use App\Services\SearchSafety;
use Illuminate\Validation\ValidationException;

class CreateSearchReport
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly SearchSafety $safety,
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

        $report = SearchReport::query()->create([
            'search_case_id' => $searchCase->id,
            'sighting_id' => $data['sighting_id'] ?? null,
            'reporter_key' => $this->actor->key(),
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'priority' => $this->safety->priority($data),
            'status' => 'open',
        ]);

        AuditLog::query()->create([
            'actor_key' => $this->actor->key(),
            'actor_role' => 'community-member',
            'action' => 'search-report.created',
            'target_type' => SearchReport::class,
            'target_id' => (string) $report->id,
            'metadata' => [
                'search_case_id' => $searchCase->id,
                'sighting_id' => $report->sighting_id,
                'reason' => $report->reason,
                'priority' => $report->priority,
            ],
        ]);

        return $report;
    }
}
