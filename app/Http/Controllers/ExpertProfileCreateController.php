<?php

namespace App\Http\Controllers;

use App\Models\ExpertProfile;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ExpertProfileCreateController extends Controller
{
    public function __invoke(ExpertPresenter $presenter): View
    {
        Gate::authorize('create', ExpertProfile::class);

        return view('experts.editor', $presenter->editor());
    }
}
