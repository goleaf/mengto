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
            ->with('feedback', __('messages.device_reading_saved_as_an_unverified_source_fact_fe90303ecd'));
    }
}
