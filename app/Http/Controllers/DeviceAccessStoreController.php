<?php

namespace App\Http\Controllers;

use App\Actions\CreateDeviceAccessGrant;
use App\Http\Requests\StoreDeviceAccessRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceAccessStoreController extends Controller
{
    public function __invoke(
        StoreDeviceAccessRequest $request,
        SmartDevice $smartDevice,
        CreateDeviceAccessGrant $create,
    ): RedirectResponse {
        Gate::authorize('share', $smartDevice);
        $result = $create->handle($smartDevice, $request->validated());
        $url = route('device-access.show', $result['token']);

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', 'Temporary access created. The link is shown once below.')
            ->with('device_access_url', $url);
    }
}
