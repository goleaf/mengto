<?php

namespace App\Http\Controllers;

use App\Actions\AcknowledgeDeviceEvent;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceEventAcknowledgeController extends Controller
{
    public function __invoke(
        SmartDevice $smartDevice,
        DeviceEvent $deviceEvent,
        AcknowledgeDeviceEvent $acknowledge,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $acknowledge->handle($smartDevice, $deviceEvent);

        return to_route('devices.show', $smartDevice)
            ->with('feedback', __('messages.event_acknowledged_the_original_device_fact_remains_in_h_0ad111098c'));
    }
}
