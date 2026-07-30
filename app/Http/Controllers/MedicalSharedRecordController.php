<?php

namespace App\Http\Controllers;

use App\Actions\ResolveMedicalAccess;
use App\Services\MedicalRecordPresenter;
use Illuminate\Contracts\View\View;

class MedicalSharedRecordController extends Controller
{
    public function __invoke(
        string $token,
        ResolveMedicalAccess $resolve,
        MedicalRecordPresenter $presenter,
    ): View {
        $grant = $resolve->handle($token);

        return view('medical-records.shared', $presenter->shared($grant, $token));
    }
}
