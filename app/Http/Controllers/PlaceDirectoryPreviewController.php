<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePlacesRequest;
use App\Services\PlacePresenter;
use Illuminate\Contracts\View\View;

final class PlaceDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowsePlacesRequest $request,
        PlacePresenter $places,
    ): View {
        return view('places.index', $places->directory($request->validated()));
    }
}
