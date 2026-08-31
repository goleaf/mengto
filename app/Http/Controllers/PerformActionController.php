<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PerformAction;
use App\Http\Requests\PerformActionRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\RedirectResponse;

class PerformActionController extends Controller
{
    public function __invoke(
        PerformActionRequest $request,
        PerformAction $action,
        AuthFactory $auth,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $result = $action->handle($user, $request->validated());
        $auth->guard('web')->setUser($user->fresh() ?? $user);

        if ($result['route'] !== null) {
            return to_route($result['route'], $result['parameters'] ?? [])
                ->with('feedback', $result['message']);
        }

        return back()->with('feedback', $result['message']);
    }
}
