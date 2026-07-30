<?php

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class NeighborProfilePreviewController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        return view('pet-social.neighbors.show', $preview->ariNeighborProfileData());
    }
}
