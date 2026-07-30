<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class MedicalRecordController extends Controller
{
    public function __invoke(
        MedicalRecord $medicalRecord,
        MedicalRecordPresenter $presenter,
    ): View {
        Gate::authorize('view', $medicalRecord);

        return view('medical-records.show', $presenter->show($medicalRecord));
    }
}
