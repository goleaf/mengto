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
                    ? 'Urgent search published. Nearby alerts are queued and the coordination workspace is ready.'
                    : 'Search draft saved for safety review. Exact location and contact remain protected.',
            );
    }
}
