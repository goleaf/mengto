<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseProfileRequest;
use App\Services\PawCircleProfilePresenter;
use Illuminate\Contracts\View\View;

class MemberProfilePreviewController extends Controller
{
    public function __invoke(
        BrowseProfileRequest $request,
        PawCircleProfilePresenter $profiles,
    ): View {
        $validated = $request->validated();

        return view('pet-social.profile.show', [
            'profile' => $profiles->ownerPage(
                (string) ($validated['tab'] ?? 'overview'),
                (string) ($validated['view'] ?? 'owner'),
            ),
        ]);
    }
}
