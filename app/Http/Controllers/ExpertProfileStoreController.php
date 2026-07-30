<?php

namespace App\Http\Controllers;

use App\Actions\CreateExpertProfile;
use App\Http\Requests\StoreExpertProfileRequest;
use App\Models\ExpertProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExpertProfileStoreController extends Controller
{
    public function __invoke(
        StoreExpertProfileRequest $request,
        CreateExpertProfile $create,
    ): RedirectResponse {
        Gate::authorize('create', ExpertProfile::class);
        $profile = $create->handle($request->validated());

        return to_route('experts.show', $profile)
            ->with('feedback', 'Professional profile created. Verification details remain private while reviewed.');
    }
}
