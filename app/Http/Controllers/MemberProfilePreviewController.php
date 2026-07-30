<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseProfileRequest;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\View\View;

class MemberProfilePreviewController extends Controller
{
    public function __invoke(
        BrowseProfileRequest $request,
        ProfilePresenter $profiles,
    ): View {
        $validated = $request->validated();

        return view('profile.show', [
            'profile' => $profiles->ownerPage(
                (string) ($validated['tab'] ?? 'overview'),
                (string) ($validated['view'] ?? 'owner'),
            ),
        ]);
    }
}
