<?php

namespace App\Http\Controllers;

use App\Models\SmartDevice;
use App\Services\SmartDevicePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class SmartDeviceController extends Controller
{
    public function __invoke(
        SmartDevice $smartDevice,
        SmartDevicePresenter $presenter,
    ): View {
        Gate::authorize('view', $smartDevice);

        return view('devices.show', $presenter->show($smartDevice));
    }
}
