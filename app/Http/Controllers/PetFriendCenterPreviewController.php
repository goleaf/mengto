<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePetFriendsRequest;
use App\Services\PetFriendCatalog;
use App\Services\PetFriendPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class PetFriendCenterPreviewController extends Controller
{
    public function __invoke(
        BrowsePetFriendsRequest $request,
        PetFriendCatalog $catalog,
        PetFriendPresenter $presenter,
    ): View|RedirectResponse {
        $parameters = $request->validated();

        if ($catalog->owned() === []) {
            return to_route('pets.index');
        }

        return view('pet-friends.index', $presenter->page(
            pet: (string) ($parameters['pet'] ?? ''),
            tab: (string) ($parameters['tab'] ?? 'friends'),
            intent: (string) ($parameters['intent'] ?? 'all'),
            sort: (string) ($parameters['sort'] ?? 'compatibility'),
            query: trim((string) ($parameters['q'] ?? '')),
        ));
    }
}
