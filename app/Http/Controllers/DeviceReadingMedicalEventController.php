<?php

namespace App\Http\Controllers;

use App\Actions\PromoteDeviceReadingToMedicalRecord;
use App\Http\Requests\PromoteDeviceEventRequest;
use App\Models\DeviceReading;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceReadingMedicalEventController extends Controller
{
    public function __invoke(
        PromoteDeviceEventRequest $request,
        SmartDevice $smartDevice,
        DeviceReading $deviceReading,
        PromoteDeviceReadingToMedicalRecord $promote,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $promote->handle($smartDevice, $deviceReading);

        return to_route('devices.show', $smartDevice)
            ->with('feedback', 'Reading added to the health record as a device-sourced item requiring review.');
    }
}
