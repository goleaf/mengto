<?php

namespace App\Http\Controllers;

use App\Actions\CreateReview;
use App\Http\Requests\StoreReviewRequest;
use App\Models\ExpertProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewStoreController extends Controller
{
    public function __invoke(
        StoreReviewRequest $request,
        ExpertProfile $expertProfile,
        CreateReview $create,
    ): RedirectResponse {
        Gate::authorize('view', $expertProfile);
        $create->handle($expertProfile, $request->validated());

        return to_route('experts.show', $expertProfile)->with('feedback', 'Verified client review published.');
    }
}
