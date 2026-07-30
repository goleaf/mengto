<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseFeedRequest;
use App\Services\FeedPresenter;
use Illuminate\Contracts\View\View;

class PreviewController extends Controller
{
    public function __invoke(
        BrowseFeedRequest $request,
        FeedPresenter $feed,
    ): View {
        $filters = $request->validated();

        return view('home', $feed->page(
            mode: (string) ($filters['feed'] ?? 'home'),
            sort: (string) ($filters['sort'] ?? 'recommended'),
            type: (string) ($filters['type'] ?? 'all'),
            pet: (string) ($filters['pet'] ?? 'all'),
            page: (int) ($filters['page'] ?? 1),
        ));
    }
}
