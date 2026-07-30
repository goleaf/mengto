<?php

namespace App\Http\Controllers;

use App\Services\CreatedContentPresenter;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CreatedContentPreviewController extends Controller
{
    public function __invoke(
        Request $request,
        CreatedContentPresenter $created,
        PreviewService $preview,
    ): View {
        $content = $created->detail(
            (string) $request->route('kind'),
            (string) $request->route('item'),
        );

        abort_if($content === null, 404);

        return view('created.show', [
            'owner' => $preview->ownerData(),
            'content' => $content,
        ]);
    }
}
