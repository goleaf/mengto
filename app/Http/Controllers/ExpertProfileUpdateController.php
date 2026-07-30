<?php

namespace App\Http\Controllers;

use App\Actions\UpdateExpertProfile;
use App\Http\Requests\UpdateExpertProfileRequest;
use App\Models\ExpertProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExpertProfileUpdateController extends Controller
{
    public function __invoke(
        UpdateExpertProfileRequest $request,
        ExpertProfile $expertProfile,
        UpdateExpertProfile $update,
    ): RedirectResponse {
        Gate::authorize('update', $expertProfile);
        $profile = $update->handle($expertProfile, $request->validated());

        return to_route('experts.show', $profile)->with('feedback', 'Professional profile updated.');
    }
}
