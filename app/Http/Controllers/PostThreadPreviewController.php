<?php

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class PostThreadPreviewController extends Controller
{
    public function __invoke(string $post, PreviewService $preview): View
    {
        $data = $preview->postThreadData($post);

        abort_unless($data !== null, 404);

        return view('posts.show', $data);
    }
}
