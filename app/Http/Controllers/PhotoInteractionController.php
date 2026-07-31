<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PerformPhotoInteraction;
use App\Http\Requests\PhotoInteractionRequest;
use Illuminate\Http\RedirectResponse;

final class PhotoInteractionController extends Controller
{
    public function __invoke(
        PhotoInteractionRequest $request,
        PerformPhotoInteraction $action,
    ): RedirectResponse {
        $result = $action->handle($request->validated());

        return back()->with('feedback', $result['message']);
    }
}
