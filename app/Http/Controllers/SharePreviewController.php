<?php

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class SharePreviewController extends Controller
{
    public function __invoke(string $target, PreviewService $preview): View
    {
        $data = $preview->shareData($target);

        abort_if($data === null, 404);

        return view('share.show', $data);
    }
}
