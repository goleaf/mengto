<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseGroupsRequest;
use App\Services\GroupPresenter;
use Illuminate\Contracts\View\View;

class GroupDetailPreviewController extends Controller
{
    public function __invoke(
        BrowseGroupsRequest $request,
        GroupPresenter $groups,
        string $group,
    ): View {
        $data = $groups->detail(
            key: $group,
            tab: $request->validated('tab', 'overview'),
        );

        abort_if($data === null, 404);

        return view('groups.show', $data);
    }
}
