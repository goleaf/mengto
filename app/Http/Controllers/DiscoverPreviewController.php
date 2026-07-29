<?php

namespace App\Http\Controllers;

use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class DiscoverPreviewController extends Controller
{
    public function __invoke(PawCirclePreviewService $preview): View
    {
        return view('pet-social.discover.index', $preview->discoverData());
    }
}
