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
            ->with('feedback', __('messages.temporary_access_created_the_link_is_shown_once_below'))
            ->with('device_access_url', $url);
    }
}
