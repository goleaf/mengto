<?php

namespace App\Http\Controllers;

use App\Actions\PerformExpertAction;
use App\Http\Requests\PerformExpertActionRequest;
use App\Models\ExpertProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExpertActionController extends Controller
{
    public function __invoke(
        PerformExpertActionRequest $request,
        ExpertProfile $expertProfile,
        PerformExpertAction $perform,
    ): RedirectResponse {
        Gate::authorize('view', $expertProfile);
        $message = $perform->handle($expertProfile, $request->validated());

        return back()->with('feedback', $message);
    }
}
