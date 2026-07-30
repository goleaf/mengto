<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseGroupsRequest;
use App\Services\PawCircleGroupPresenter;
use Illuminate\Contracts\View\View;

class GroupDetailPreviewController extends Controller
{
    public function __invoke(
        BrowseGroupsRequest $request,
        PawCircleGroupPresenter $groups,
        string $group,
    ): View {
        $data = $groups->detail(
            key: $group,
            tab: $request->validated('tab', 'overview'),
        );

        abort_if($data === null, 404);

        return view('pet-social.groups.show', $data);
    }
}
