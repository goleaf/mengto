<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PerformAction;
use App\Http\Requests\PerformActionRequest;
use Illuminate\Http\RedirectResponse;

class PerformActionController extends Controller
{
    public function __invoke(
        PerformActionRequest $request,
        PerformAction $action,
    ): RedirectResponse {
        $result = $action->handle($request->validated());

        if ($result['route'] !== null) {
            return to_route($result['route'], $result['parameters'] ?? [])
                ->with('feedback', $result['message']);
        }

        return back()->with('feedback', $result['message']);
    }
}
