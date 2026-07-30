<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseGroupsRequest;
use App\Services\PawCircleGroupPresenter;
use Illuminate\Contracts\View\View;

class GroupDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowseGroupsRequest $request,
        PawCircleGroupPresenter $groups,
    ): View {
        $parameters = $request->validated();

        return view('pet-social.groups.index', $groups->directory(
            query: $parameters['q'] ?? '',
            filter: $parameters['filter'] ?? 'recommended',
            sort: $parameters['sort'] ?? 'active',
        ));
    }
}
