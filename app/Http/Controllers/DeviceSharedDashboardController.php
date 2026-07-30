<?php

namespace App\Http\Controllers;

use App\Actions\ResolveDeviceAccess;
use App\Services\SmartDevicePresenter;
use Illuminate\Contracts\View\View;

class DeviceSharedDashboardController extends Controller
{
    public function __invoke(
        string $token,
        ResolveDeviceAccess $resolve,
        SmartDevicePresenter $presenter,
    ): View {
        $grant = $resolve->handle($token);

        return view('devices.shared', $presenter->shared($grant, $token));
    }
}
