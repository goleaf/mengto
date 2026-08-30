<?php

namespace App\Http\Controllers;

use App\Actions\SubmitSighting;
use App\Http\Requests\StoreSightingRequest;
use App\Models\SearchCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SightingStoreController extends Controller
{
    public function __invoke(
        StoreSightingRequest $request,
        SearchCase $searchCase,
        SubmitSighting $submit,
    ): RedirectResponse {
        Gate::authorize('submitSighting', $searchCase);
        $submit->handle($searchCase, $request->validated());

        return to_route('lost-found.show', $searchCase)
            ->with('feedback', __('messages.observation_received_the_coordinator_can_verify_it_without_exposing_your_exact_location'));
    }
}
