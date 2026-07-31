<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PerformSearchAction;
use App\Http\Requests\PerformSearchActionRequest;
use App\Models\SearchCase;
use App\Services\ForumActor;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;

final class SearchActionController extends Controller
{
    public function __invoke(
        PerformSearchActionRequest $request,
        SearchCase $searchCase,
        PerformSearchAction $perform,
        ForumActor $actor,
        Gate $gate,
    ): RedirectResponse {
        $data = $request->validated();

        match ($data['action']) {
            'join-search', 'claim-task', 'start-task', 'complete-task' => $gate->authorize('volunteer', $searchCase),
            default => $gate->authorize('update', $searchCase),
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
            'archive-case',
        ], true) && $result['search_case']->isManagedBy($actor->key())
            ? 'lost-found.coordinate'
            : 'lost-found.show';

        return to_route($route, $result['search_case'])
            ->with('feedback', $result['message']);
    }
}
