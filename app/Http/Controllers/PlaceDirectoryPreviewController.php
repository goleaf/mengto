<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePlacesRequest;
use App\Services\PawCirclePlacePresenter;
use Illuminate\Contracts\View\View;

final class PlaceDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowsePlacesRequest $request,
        PawCirclePlacePresenter $places,
    ): View {
        return view('pet-social.places.index', $places->directory($request->validated()));
    }
}
