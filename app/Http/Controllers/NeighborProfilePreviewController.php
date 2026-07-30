<?php

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class NeighborProfilePreviewController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        return view('neighbors.show', $preview->ariNeighborProfileData());
    }
}
