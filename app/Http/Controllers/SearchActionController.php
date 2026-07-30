<?php

namespace App\Http\Controllers;

use App\Actions\PerformSearchAction;
use App\Http\Requests\PerformSearchActionRequest;
use App\Models\SearchCase;
use App\Services\ForumActor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SearchActionController extends Controller
{
    public function __invoke(
        PerformSearchActionRequest $request,
        SearchCase $searchCase,
        PerformSearchAction $perform,
        ForumActor $actor,
    ): RedirectResponse {
        $data = $request->validated();

        match ($data['action']) {
            'join-search', 'claim-task', 'start-task', 'complete-task' => Gate::authorize('volunteer', $searchCase),
            default => Gate::authorize('update', $searchCase),
        };

        $result = $perform->handle($searchCase, $data);
        $route = in_array($data['action'], [
            'create-sector',
            'create-task',
            'claim-task',
            'start-task',
            'complete-task',
            'confirm-sighting',
            'reject-sighting',
        ], true) && $result['search_case']->isManagedBy($actor->key())
            ? 'lost-found.coordinate'
            : 'lost-found.show';

        return to_route($route, $result['search_case'])
            ->with('feedback', $result['message']);
    }
}
