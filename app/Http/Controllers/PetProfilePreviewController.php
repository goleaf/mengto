<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseProfileRequest;
use App\Services\PawCircleProfilePresenter;
use Illuminate\Contracts\View\View;

class PetProfilePreviewController extends Controller
{
    public function __invoke(
        BrowseProfileRequest $request,
        PawCircleProfilePresenter $profiles,
    ): View {
        $validated = $request->validated();
        $profile = $profiles->petPage(
            (string) $request->route('pet', 'scout'),
            (string) ($validated['tab'] ?? 'feed'),
            (string) ($validated['view'] ?? 'owner'),
        );

        abort_if($profile === null, 404);

        return view('pet-social.pets.show', compact('profile'));
    }
}
