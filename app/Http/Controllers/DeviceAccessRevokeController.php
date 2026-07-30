<?php

namespace App\Http\Controllers;

use App\Actions\RevokeDeviceAccess;
use App\Models\DeviceAccessGrant;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceAccessRevokeController extends Controller
{
    public function __invoke(
        SmartDevice $smartDevice,
        DeviceAccessGrant $deviceAccessGrant,
        RevokeDeviceAccess $revoke,
    ): RedirectResponse {
        Gate::authorize('share', $smartDevice);
        $revoke->handle($smartDevice, $deviceAccessGrant);

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', __('messages.temporary_access_revoked_immediately_c770471439'));
    }
}
