<?php

namespace App\Http\Controllers;

use App\Models\ExpertProfile;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ExpertProfileEditController extends Controller
{
    public function __invoke(ExpertProfile $expertProfile, ExpertPresenter $presenter): View
    {
        Gate::authorize('update', $expertProfile);

        return view('experts.editor', $presenter->editor($expertProfile));
    }
}
