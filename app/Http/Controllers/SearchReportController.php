<?php

namespace App\Http\Controllers;

use App\Actions\CreateSearchReport;
use App\Http\Requests\StoreSearchReportRequest;
use App\Models\SearchCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SearchReportController extends Controller
{
    public function __invoke(
        StoreSearchReportRequest $request,
        SearchCase $searchCase,
        CreateSearchReport $create,
    ): RedirectResponse {
        Gate::authorize('report', $searchCase);
        $report = $create->handle($searchCase, $request->validated());

        return to_route('lost-found.show', $searchCase)
            ->with(
                'feedback',
                $report->priority === 'high'
                    ? __('messages.high_priority_safety_report_sent_for_review')
                    : __('messages.report_sent_for_review'),
            );
    }
}
