<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ConsultationController extends Controller
{
    public function __invoke(Consultation $consultation, ExpertPresenter $presenter): View
    {
        Gate::authorize('view', $consultation);

        return view('experts.consultation', $presenter->booking($consultation->booking));
    }
}
