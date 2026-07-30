<?php

namespace App\Http\Controllers;

use App\Models\SmartDevice;
use App\Services\SmartDevicePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class SmartDeviceCreateController extends Controller
{
    public function __invoke(SmartDevicePresenter $presenter): View
    {
        Gate::authorize('create', SmartDevice::class);

        return view('devices.create', $presenter->editor());
    }
}
