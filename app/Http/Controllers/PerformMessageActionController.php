<?php

namespace App\Http\Controllers;

use App\Actions\PerformMessageAction;
use App\Http\Requests\PerformMessageActionRequest;
use Illuminate\Http\RedirectResponse;

final class PerformMessageActionController extends Controller
{
    public function __invoke(
        PerformMessageActionRequest $request,
        PerformMessageAction $action,
    ): RedirectResponse {
        $result = $action->handle($request->validated());

        return redirect()
            ->route($result['route'], $result['parameters'])
            ->with('pawcircle.feedback', $result['message']);
    }
}
