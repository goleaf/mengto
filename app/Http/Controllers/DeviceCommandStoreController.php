<?php

namespace App\Http\Controllers;

use App\Actions\IssueDeviceCommand;
use App\Http\Requests\StoreDeviceCommandRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceCommandStoreController extends Controller
{
    public function __invoke(
        StoreDeviceCommandRequest $request,
        SmartDevice $smartDevice,
        IssueDeviceCommand $issue,
    ): RedirectResponse {
        Gate::authorize('control', $smartDevice);
        $issue->handle($smartDevice, $request->validated());

        return to_route('devices.show', $smartDevice)
            ->with('feedback', 'Command applied once and recorded in the device audit.');
    }
}
