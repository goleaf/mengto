<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePetFriendsRequest;
use App\Services\PawCirclePetFriendPresenter;
use Illuminate\Contracts\View\View;

final class PetFriendCenterPreviewController extends Controller
{
    public function __invoke(
        BrowsePetFriendsRequest $request,
        PawCirclePetFriendPresenter $presenter,
    ): View {
        $parameters = $request->validated();

        return view('pet-social.pet-friends.index', $presenter->page(
            pet: (string) ($parameters['pet'] ?? 'scout'),
            tab: (string) ($parameters['tab'] ?? 'friends'),
            intent: (string) ($parameters['intent'] ?? 'all'),
            sort: (string) ($parameters['sort'] ?? 'compatibility'),
            query: trim((string) ($parameters['q'] ?? '')),
        ));
    }
}
