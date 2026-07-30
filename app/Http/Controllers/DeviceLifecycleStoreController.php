<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordDeviceLifecycle;
use App\Http\Requests\StoreDeviceLifecycleRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DeviceLifecycleStoreController extends Controller
{
    public function __invoke(
        StoreDeviceLifecycleRequest $request,
        SmartDevice $smartDevice,
        RecordDeviceLifecycle $record,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $record->handle($smartDevice, $request->validated());

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', __('devices.feedback.lifecycle_recorded'));
    }
}
