<?php

namespace App\Http\Controllers;

use App\Actions\CreateDeviceAutomation;
use App\Http\Requests\StoreDeviceAutomationRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceAutomationStoreController extends Controller
{
    public function __invoke(
        StoreDeviceAutomationRequest $request,
        SmartDevice $smartDevice,
        CreateDeviceAutomation $create,
    ): RedirectResponse {
        Gate::authorize('control', $smartDevice);
        $create->handle($smartDevice, $request->validated());

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', __('messages.automation_saved_with_cooldown_and_safety_limits_ec8a23dcc4'));
    }
}
