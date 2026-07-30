<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class MedicalRecordManageController extends Controller
{
    public function __invoke(
        MedicalRecord $medicalRecord,
        MedicalRecordPresenter $presenter,
    ): View {
        Gate::authorize('update', $medicalRecord);

        return view('medical-records.manage', $presenter->show($medicalRecord, true));
    }
}
