<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseConnectionsRequest;
use App\Services\ConnectionPresenter;
use Illuminate\Contracts\View\View;

final class ConnectionCenterPreviewController extends Controller
{
    public function __invoke(
        BrowseConnectionsRequest $request,
        ConnectionPresenter $connections,
    ): View {
        $parameters = $request->validated();

        return view('connections.index', [
            ...$connections->page(
                tab: (string) ($parameters['tab'] ?? 'following'),
                type: (string) ($parameters['type'] ?? 'all'),
                sort: (string) ($parameters['sort'] ?? 'recommended'),
            ),
        ]);
    }
}
