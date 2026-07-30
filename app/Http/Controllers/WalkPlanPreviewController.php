<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseRequest;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class WalkPlanPreviewController extends Controller
{
    public function __invoke(BrowseRequest $request, PreviewService $preview): View
    {
        $parameters = $request->validated();

        return view('pet-social.walks.index', $preview->walkPlanData($parameters['filter'] ?? 'upcoming'));
    }
}
