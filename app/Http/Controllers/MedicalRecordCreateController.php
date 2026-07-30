<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class MedicalRecordCreateController extends Controller
{
    public function __invoke(MedicalRecordPresenter $presenter): View
    {
        Gate::authorize('create', MedicalRecord::class);

        return view('medical-records.create', $presenter->editor());
    }
}
