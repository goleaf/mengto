<?php

declare(strict_types=1);

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
        $validated = $request->validated();
        Gate::authorize('controlCommand', [$smartDevice, $validated['command_type']]);
        $issue->handle($smartDevice, $validated);

        return to_route('devices.show', $smartDevice)
            ->with('feedback', __('messages.command_accepted_once_delivery_status_is_shown_in_the_audit'));
    }
}
