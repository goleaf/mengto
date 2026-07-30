<?php

namespace App\Http\Controllers;

use App\Actions\PerformPawCircleAction;
use App\Http\Requests\PerformPawCircleActionRequest;
use Illuminate\Http\RedirectResponse;

class PerformPawCircleActionController extends Controller
{
    public function __invoke(
        PerformPawCircleActionRequest $request,
        PerformPawCircleAction $action,
    ): RedirectResponse {
        $result = $action->handle($request->validated());

        if ($result['route'] !== null) {
            return to_route($result['route'], $result['parameters'] ?? [])
                ->with('pawcircle.feedback', $result['message']);
        }

        return back()->with('pawcircle.feedback', $result['message']);
    }
}
