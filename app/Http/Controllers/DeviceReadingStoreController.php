<?php

namespace App\Http\Controllers;

use App\Actions\RecordDeviceReading;
use App\Http\Requests\StoreDeviceReadingRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceReadingStoreController extends Controller
{
    public function __invoke(
        StoreDeviceReadingRequest $request,
        SmartDevice $smartDevice,
        RecordDeviceReading $record,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $record->handle($smartDevice, $request->validated());

        return to_route('devices.show', $smartDevice)
            ->with('feedback', 'Device reading saved as an unverified source fact.');
    }
}
