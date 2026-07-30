<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseFeedRequest;
use App\Services\PawCircleFeedPresenter;
use Illuminate\Contracts\View\View;

class PetSocialPreviewController extends Controller
{
    public function __invoke(
        BrowseFeedRequest $request,
        PawCircleFeedPresenter $feed,
    ): View {
        $filters = $request->validated();

        return view('pet-social.index', $feed->page(
            mode: (string) ($filters['feed'] ?? 'home'),
            sort: (string) ($filters['sort'] ?? 'recommended'),
            type: (string) ($filters['type'] ?? 'all'),
            pet: (string) ($filters['pet'] ?? 'all'),
            page: (int) ($filters['page'] ?? 1),
        ));
    }
}
