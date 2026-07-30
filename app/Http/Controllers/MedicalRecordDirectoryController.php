<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class MedicalRecordDirectoryController extends Controller
{
    public function __invoke(MedicalRecordPresenter $presenter): View
    {
        Gate::authorize('viewAny', MedicalRecord::class);

        return view('medical-records.index', $presenter->directory());
    }
}
