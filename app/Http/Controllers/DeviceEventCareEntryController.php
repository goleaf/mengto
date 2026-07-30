<?php

namespace App\Http\Controllers;

use App\Actions\PromoteDeviceEventToCareJournal;
use App\Http\Requests\PromoteDeviceEventRequest;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceEventCareEntryController extends Controller
{
    public function __invoke(
        PromoteDeviceEventRequest $request,
        SmartDevice $smartDevice,
        DeviceEvent $deviceEvent,
        PromoteDeviceEventToCareJournal $promote,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $promote->handle($smartDevice, $deviceEvent);

        return to_route('devices.show', $smartDevice)
            ->with('feedback', __('messages.device_event_added_to_care_as_a_needs_review_entry_1dddafc9c2'));
    }
}
