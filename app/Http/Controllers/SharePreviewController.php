<?php

namespace App\Http\Controllers;

use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class SharePreviewController extends Controller
{
    public function __invoke(string $target, PawCirclePreviewService $preview): View
    {
        $data = $preview->shareData($target);

        abort_if($data === null, 404);

        return view('pet-social.share.show', $data);
    }
}
