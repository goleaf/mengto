<?php

namespace App\Http\Controllers;

use App\Actions\ResolvePlaceMergeRedirect;
use App\Http\Requests\BrowsePlacesRequest;
use App\Models\User;
use App\Services\PlacePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class PlaceDetailPreviewController extends Controller
{
    public function __invoke(
        BrowsePlacesRequest $request,
        string $place,
        PlacePresenter $places,
        ResolvePlaceMergeRedirect $resolveMerge,
    ): View|RedirectResponse {
        $actor = $request->user();
        $destination = $resolveMerge->handle($actor instanceof User ? $actor : null, $place);

        if ($destination !== null) {
            return redirect()
                ->route('places.show', ['place' => $destination->slug], 302)
                ->header('Cache-Control', 'private, no-store');
        }

        $data = $places->detail($place, $request->validated('tab', 'overview'));

        abort_if($data === null, 404);

        return view('places.show', $data);
    }
}
