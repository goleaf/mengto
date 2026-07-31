<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendSearchContactRelay;
use App\Data\SearchContactRelayData;
use App\Http\Requests\StoreSearchContactRelayRequest;
use App\Models\SearchCase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class SearchContactRelayController extends Controller
{
    public function __invoke(
        StoreSearchContactRelayRequest $request,
        SearchCase $searchCase,
        SendSearchContactRelay $send,
    ): RedirectResponse {
        Gate::authorize('contact', $searchCase);
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $send->handle(
            $user,
            $searchCase,
            SearchContactRelayData::fromValidated($request->validated()),
        );

        return to_route('lost-found.show', $searchCase)
            ->with('feedback', __('lost_found.messages.relay_submitted'));
    }
}
