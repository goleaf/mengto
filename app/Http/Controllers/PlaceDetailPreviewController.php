<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePlacesRequest;
use App\Services\PlacePresenter;
use Illuminate\Contracts\View\View;

final class PlaceDetailPreviewController extends Controller
{
    public function __invoke(
        BrowsePlacesRequest $request,
        string $place,
        PlacePresenter $places,
    ): View {
        $data = $places->detail($place, $request->validated('tab', 'overview'));

        abort_if($data === null, 404);

        return view('pet-social.places.show', $data);
    }
}
