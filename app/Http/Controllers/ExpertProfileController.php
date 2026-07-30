<?php

namespace App\Http\Controllers;

use App\Models\ExpertProfile;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ExpertProfileController extends Controller
{
    public function __invoke(ExpertProfile $expertProfile, ExpertPresenter $presenter): View
    {
        Gate::authorize('view', $expertProfile);

        return view('experts.show', $presenter->profile($expertProfile));
    }
}
