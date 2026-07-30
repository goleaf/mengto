<?php

namespace App\Http\Controllers;

use App\Models\SmartDevice;
use App\Services\SmartDevicePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class SmartDeviceManageController extends Controller
{
    public function __invoke(
        SmartDevice $smartDevice,
        SmartDevicePresenter $presenter,
    ): View {
        Gate::authorize('update', $smartDevice);

        return view('devices.manage', $presenter->manage($smartDevice));
    }
}
