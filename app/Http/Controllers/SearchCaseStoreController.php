<?php

namespace App\Http\Controllers;

use App\Actions\CreateSearchCase;
use App\Enums\ModerationStatus;
use App\Http\Requests\StoreSearchCaseRequest;
use App\Models\SearchCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SearchCaseStoreController extends Controller
{
    public function __invoke(StoreSearchCaseRequest $request, CreateSearchCase $create): RedirectResponse
    {
        Gate::authorize('create', SearchCase::class);
        $searchCase = $create->handle($request->validated());

        return to_route('lost-found.show', $searchCase)
            ->with(
                'feedback',
                $searchCase->moderation_status === ModerationStatus::Approved
                    ? __('messages.urgent_search_published_nearby_alerts_are_queued_and_the_401514c452')
                    : __('messages.search_draft_saved_for_safety_review_exact_location_and__176ff9e95d'),
            );
    }
}
