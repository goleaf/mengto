<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePawCircleRequest;
use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class CirclePreviewController extends Controller
{
    public function __invoke(BrowsePawCircleRequest $request, PawCirclePreviewService $preview): View
    {
        $parameters = $request->validated();

        return view('pet-social.circle.index', $preview->circleData($parameters['filter'] ?? 'overview'));
    }
}
