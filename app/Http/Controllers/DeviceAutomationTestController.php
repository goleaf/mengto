<?php

namespace App\Http\Controllers;

use App\Actions\TestDeviceAutomation;
use App\Models\DeviceAutomation;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceAutomationTestController extends Controller
{
    public function __invoke(
        SmartDevice $smartDevice,
        DeviceAutomation $deviceAutomation,
        TestDeviceAutomation $test,
    ): RedirectResponse {
        Gate::authorize('control', $smartDevice);
        $test->handle($smartDevice, $deviceAutomation);

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', __('messages.simulation_completed_no_real_device_command_was_sent'));
    }
}
