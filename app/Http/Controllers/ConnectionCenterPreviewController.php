<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseConnectionsRequest;
use App\Services\ConnectionPresenter;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\View\View;

final class ConnectionCenterPreviewController extends Controller
{
    public function __invoke(
        BrowseConnectionsRequest $request,
        ConnectionPresenter $connections,
        ProfilePresenter $profiles,
    ): View {
        $parameters = $request->validated();

        return view('pet-social.connections.index', [
            ...$connections->page(
                tab: (string) ($parameters['tab'] ?? 'following'),
                type: (string) ($parameters['type'] ?? 'all'),
                sort: (string) ($parameters['sort'] ?? 'recommended'),
            ),
            'owner' => $profiles->owner(),
        ]);
    }
}
